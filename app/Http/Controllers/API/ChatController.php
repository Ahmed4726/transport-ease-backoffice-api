<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\Conversation;
use App\Models\TripBooking;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    use ApiResponse;

    public function conversation(Request $request, TripBooking $booking)
    {
        $user = $request->user();
        if (!$user) {
            return $this->error('Unauthorized.', [], 403);
        }

        if ($user->passenger && $booking->passenger_id === $user->passenger->id) {
            $conversation = $this->findOrCreateConversation($booking);
            return $this->success('Conversation fetched successfully.', $this->conversationPayload($conversation));
        }

        if ($user->driver && $booking->driver_trip && $booking->driver_trip->driver_id === $user->driver->id) {
            $conversation = $this->findOrCreateConversation($booking);
            return $this->success('Conversation fetched successfully.', $this->conversationPayload($conversation));
        }

        return $this->error('You are not allowed to access this conversation.', [], 403);
    }

    public function sendMessage(Request $request, TripBooking $booking)
    {
        $user = $request->user();
        if (!$user) {
            return $this->error('Unauthorized.', [], 403);
        }

        $conversation = $this->resolveConversationForUser($user, $booking);
        if (!$conversation) {
            return $this->error('You are not allowed to send messages for this booking.', [], 403);
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'min:1', 'max:1000'],
        ]);

        $message = $conversation->messages()->create([
            'sender_id' => $user->id,
            'message' => trim($validated['message']),
        ]);

        return $this->success('Message sent successfully.', [
            'message' => $message,
            'conversation' => $this->conversationPayload($conversation),
        ], 201);
    }

    public function markRead(Request $request, TripBooking $booking)
    {
        $user = $request->user();
        if (!$user) {
            return $this->error('Unauthorized.', [], 403);
        }

        $conversation = $this->resolveConversationForUser($user, $booking);
        if (!$conversation) {
            return $this->error('You are not allowed to access this conversation.', [], 403);
        }

        $conversation->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return $this->success('Messages marked as read.', $this->conversationPayload($conversation));
    }

    private function resolveConversationForUser($user, TripBooking $booking): ?Conversation
    {
        if ($user->passenger && $booking->passenger_id === $user->passenger->id) {
            return $this->findOrCreateConversation($booking);
        }

        if ($user->driver && $booking->driver_trip && $booking->driver_trip->driver_id === $user->driver->id) {
            return $this->findOrCreateConversation($booking);
        }

        return null;
    }

    private function findOrCreateConversation(TripBooking $booking): Conversation
    {
        return Conversation::query()
            ->firstOrCreate([
                'booking_id' => $booking->id,
                'driver_trip_id' => $booking->driver_trip_id,
                'driver_id' => $booking->trip?->driver_id,
                'passenger_id' => $booking->passenger_id,
            ]);
    }

    private function conversationPayload(Conversation $conversation): array
    {
        return [
            'id' => $conversation->id,
            'booking_id' => $conversation->booking_id,
            'driver_trip_id' => $conversation->driver_trip_id,
            'driver' => [
                'id' => $conversation->driver?->id,
                'name' => $conversation->driver?->user?->name,
            ],
            'passenger' => [
                'id' => $conversation->passenger?->id,
                'name' => $conversation->passenger?->user?->name,
            ],
            'messages' => $conversation->messages()->with('sender')->latest()->get()->map(fn ($message) => [
                'id' => $message->id,
                'sender_id' => $message->sender_id,
                'message' => $message->message,
                'read_at' => $message->read_at?->toISOString(),
                'created_at' => $message->created_at?->toISOString(),
                'is_from_me' => Auth::id() === $message->sender_id,
            ])->values()->all(),
            'unread_count' => $conversation->messages()->where('sender_id', '!=', Auth::id())->whereNull('read_at')->count(),
        ];
    }
}

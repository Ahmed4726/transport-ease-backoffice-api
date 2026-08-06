<?php

namespace App\Services\Admin;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Passenger;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class PassengerService
{
    public function getPassengers(array $filters): LengthAwarePaginator
    {
        return Passenger::query()
            ->with('user')
            ->when(!empty($filters['status']), function ($query) use ($filters) {
                $query->whereHas('user', function ($q) use ($filters) {
                    $q->where('status', $filters['status']);
                });
            })
            ->when(!empty($filters['search']), function ($query) use ($filters) {
                $search = $filters['search'];
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%")
                        ->orWhere('phone', 'LIKE', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();
    }

    public function getPassenger(int $id): Passenger
    {
        return Passenger::with('user')->findOrFail($id);
    }

    public function getSummary(): array
    {
        $passengerUsersQuery = User::query()
            ->where('role', UserRole::PASSENGER);

        return [
            'totalPassengers' => $passengerUsersQuery->count(),
            'approvedPassengers' => (clone $passengerUsersQuery)->where('status', UserStatus::APPROVED)->count(),
            'pendingPassengers' => (clone $passengerUsersQuery)->where('status', UserStatus::PENDING)->count(),
            'rejectedPassengers' => (clone $passengerUsersQuery)->where('status', UserStatus::REJECTED)->count(),
        ];
    }

    public function createPassenger(array $data): Passenger
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
            'role' => UserRole::PASSENGER,
            'status' => UserStatus::from($data['status']),
        ]);

        return Passenger::create([
            'user_id' => $user->id,
        ]);
    }

    public function updatePassenger(Passenger $passenger, array $data): Passenger
    {
        $passenger->user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'status' => UserStatus::from($data['status']),
            'password' => isset($data['password']) ? Hash::make($data['password']) : $passenger->user->password,
        ]);

        return $passenger->refresh();
    }

    public function deletePassenger(Passenger $passenger): void
    {
        $passenger->user->delete();
        $passenger->delete();
    }
}

<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RegistrationReceived extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function build()
    {
        $role = $this->user->UserRole instanceof \App\Enums\UserRole
            ? $this->user->role->value
            : (string) $this->user->role;

        return $this->subject('We received your registration request')
            ->view('emails.registration_received')
            ->with([
                'name' => $this->user->name,
                'role' => ucfirst($role),
            ]);
    }
}

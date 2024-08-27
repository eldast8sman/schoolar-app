<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Mail\SendOTPMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class RegisterUserEmail
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(UserRegistered $event): void
    {
        $user = $event->user;
        $user->name = $user->first_name.' '.$user->last_name;
        Mail::to($user)->send(new SendOTPMail($user->name, $event->otp));
    }
}

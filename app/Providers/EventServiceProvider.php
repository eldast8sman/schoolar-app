<?php

namespace App\Providers;

use App\Events\LoadDefaultModules;
use App\Events\UserRegistered;
use App\Listeners\LoadDefaultAssessmentType;
use App\Listeners\LoadDefaultClasses;
use App\Listeners\LoadDefaultGradingSystem;
use App\Listeners\LoadDefaultSubjects;
use App\Listeners\RegisterUserEmail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        UserRegistered::class => [
            RegisterUserEmail::class
        ],
        LoadDefaultModules::class => [
            LoadDefaultClasses::class,
            LoadDefaultSubjects::class,
            LoadDefaultGradingSystem::class,
            LoadDefaultAssessmentType::class
        ]
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}

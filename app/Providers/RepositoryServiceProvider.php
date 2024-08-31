<?php

namespace App\Providers;

use App\Repositories\AbstractRepository;
use App\Repositories\Interfaces\AbstractRepositoryInterface;
use App\Repositories\Interfaces\SchoolLocationRepositoryInterface;
use App\Repositories\Interfaces\SchoolRepositoryInterface;
use App\Repositories\Interfaces\SessionRepositoryInterface;
use App\Repositories\Interfaces\TermRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\SchoolLocationRepository;
use App\Repositories\SchoolRepository;
use App\Repositories\SessionRepository;
use App\Repositories\TermRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(AbstractRepositoryInterface::class, AbstractRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(SchoolRepositoryInterface::class, SchoolRepository::class);
        $this->app->bind(SchoolLocationRepositoryInterface::class, SchoolLocationRepository::class);
        $this->app->bind(SessionRepositoryInterface::class, SessionRepository::class);
        $this->app->bind(TermRepositoryInterface::class, TermRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}

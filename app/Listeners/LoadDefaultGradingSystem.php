<?php

namespace App\Listeners;

use App\Events\LoadDefaultModules;
use App\Models\GradingSystem;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LoadDefaultGradingSystem
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
    public function handle(LoadDefaultModules $event): void
    {
        $location = $event->location;
        $grading_json = file_get_contents(base_path('data/jsons/default_grading_systems.json'));
        $gradings = json_decode($grading_json, true);
        $grades = $gradings[$location->location_type];
        foreach($grades as $grade){
            $grade['school_id'] = $location->school_id;
            $grade['school_location_id'] = $location->id;
            GradingSystem::create($grade);
        }
    }
}

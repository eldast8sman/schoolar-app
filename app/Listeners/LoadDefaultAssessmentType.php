<?php

namespace App\Listeners;

use App\Events\LoadDefaultModules;
use App\Models\AssessmentType;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LoadDefaultAssessmentType
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
        $assessments = json_decode(file_get_contents(base_path('data/json/default_assessment_types.json')), true);
        AssessmentType::create([
            'school_id' => $location->school_id,
            'school_location_id' => $location->id,
            'assessment_scores' => json_encode($assessments),
            'minimum_pass_score' => 50
        ]);
    }
}

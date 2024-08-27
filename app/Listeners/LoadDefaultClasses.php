<?php

namespace App\Listeners;

use App\Events\LoadDefaultModules;
use App\Models\MainClass;
use App\Models\SubClass;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LoadDefaultClasses
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        
    }

    /**
     * Handle the event.
     */
    public function handle(LoadDefaultModules $event): void
    {
        $location = $event->location;
        if(strtolower($location->country) == 'nigeria'){
            if(strtolower($location->location_type) == "primary"){
                for($i=1; $i<=6; $i++){
                    $class = MainClass::create([
                        'school_id' => $location->school_id,
                        'school_location_id' => $location->id,
                        'class_level' => $i,
                        'name' => 'Primary '.$i
                    ]);
                    SubClass::create([
                        'school_id' => $location->school_id,
                        'school_location_id' => $location->id,
                        'main_class_id' => $class->id,
                        'name' => "A"
                    ]);
                }
            } elseif(strtolower($location->location_type) == "secondary"){
                for($i=1; $i<=3; $i++){
                    $class = MainClass::create([
                        'school_id' => $location->school_id,
                        'school_location_id' => $location->id,
                        'class_level' => $i,
                        'name' => 'JSS '.$i
                    ]);
                    SubClass::create([
                        'school_id' => $location->school_id,
                        'school_location_id' => $location->id,
                        'main_class_id' => $class->id,
                        'name' => 'A'
                    ]);
                }

                for($i=1; $i<=3; $i++){
                    $class = MainClass::create([
                        'school_id' => $location->school_id,
                        'school_location_id' => $location->id,
                        'class_level' => $i + 3,
                        'name' => 'SSS '.$i
                    ]);
                    SubClass::create([
                        'school_id' => $location->school_id,
                        'school_location_id' => $location->id,
                        'main_class_id' => $class->id,
                        'name' => 'A',
                        'type' => 'sciences'
                    ]);
                    SubClass::create([
                        'school_id' => $location->school_id,
                        'school_location_id' => $location->id,
                        'main_class_id' => $class->id,
                        'name' => 'B',
                        'type' => 'arts'
                    ]);
                    SubClass::create([
                        'school_id' => $location->school_id,
                        'school_location_id' => $location->id,
                        'main_class_id' => $class->id,
                        'name' => 'C',
                        'type' => 'commerce'
                    ]);
                }
            }
        }
    }
}

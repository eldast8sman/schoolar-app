<?php

namespace App\Listeners;

use App\Events\LoadDefaultModules;
use App\Models\MainClass;
use App\Models\Subject;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LoadDefaultSubjects
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

        $subjects_json = file_get_contents(base_path('data/json/default_subjects.json'));
        $subjects = json_decode($subjects_json, true);
        $classes = MainClass::where('school_location_id', $location->id)->get();
        foreach($classes as $class){
            if(strtolower($location->country) == 'nigeria'){
                if(strtolower($location->location_type) == 'primary'){
                    foreach($class->sub_classes as $subclass){
                        foreach($subjects['primary'] as $subject){
                            Subject::create([
                                'school_id' => $location->school_id,
                                'school_location_id' => $location->id,
                                'main_class_id' => $class->id,
                                'sub_class_id' => $subclass->id,
                                'name' => $subject['subject'],
                                'compulsory' => $subject['compulsory']
                            ]);
                        }
                    }
                } elseif(strtolower($location->location_type) == 'secondary'){
                    if($class->class_level <= 3){
                        foreach($class->sub_classes as $subclass){
                            foreach($subjects['junior_secondary'] as $subject){
                                Subject::create([
                                    'school_id' => $location->school_id,
                                    'school_location_id' => $location->id,
                                    'main_class_id' => $class->id,
                                    'sub_class_id' => $subclass->id,
                                    'name' => $subject['subject'],
                                    'compulsory' => $subject['compulsory']
                                ]);
                            }
                        }
                    } elseif($class->class_level >= 4){
                        $subjects = $subjects['senior_secondary'];
                        foreach($class->sub_classes as $subclass){
                            foreach($subjects[$subclass->type] as $subject){
                                Subject::create([
                                    'school_id' => $location->school_id,
                                    'school_location_id' => $location->id,
                                    'main_class_id' => $class->id,
                                    'sub_class_id' => $subclass->id,
                                    'name' => $subject['subject'],
                                    'compulsory' => $subject['compulsory']
                                ]);
                            }
                        }
                    }
                }
            }
        }
    }
}

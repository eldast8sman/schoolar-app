<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePromotionCriteriaRequest;
use App\Models\MainClass;
use App\Models\PromotionCriteria;
use App\Models\SchoolLocation;
use App\Models\SchoolSession;
use App\Models\SubClass;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PromotionCriteriaController extends Controller
{
    private $user;

    public function __construct()
    {
        $this->middleware('auth:user-api');
        $this->user = AuthController::user();
    }

    public static function criteria(PromotionCriteria $criteria) : PromotionCriteria
    {
        $pass_subjects = [];
        $subjects_array = json_decode($criteria->must_pass_subjects, true);
        foreach($subjects_array as $subject_id){
            if(strpos($subject_id, "|") !== false){
                $subjects = [];
                $ids = explode("|", $subject_id);
                foreach($ids as $id){
                    $subjects[] = Subject::find($id)->name;
                }
                $pass_subjects[] = join("|", $subjects);
            } else {
                $pass_subjects[] = Subject::find($subject_id)->name;
            }
        }
        $criteria->must_pass_subjects = $pass_subjects;

        return $criteria;
    }

    public function load_default($session_uuid){
        $location = SchoolLocation::find($this->user->school_location);
        $session = SchoolSession::where('uuid', $session_uuid)->where('school_location_id', $location->id)->first();
        if(empty($session)){
            return response([
                'status' => 'failed',
                'message' => 'No School Session was fetched'
            ], 404);
        }

        if($location->country == 'Nigeria'){
            $criteria = FunctionController::promotion_criteria()[$location->location_type];
            if($location->location_type == "primary"){
                $data = [
                    'school_id' => $this->user->school_id,
                    'location_id' => $location->id,
                    'must_pass_count' => $criteria['must_pass_count'],
                    'session_id' => $session->id
                ];

                $subclasses = SubClass::where('school_location_id', $location->id);
                if($subclasses->count() > 0){
                    foreach($subclasses as $subclass){
                        $data['main_class_id'] = $subclass->main_calss_id;
                        $data['sub_class_id'] = $subclass->id;

                        $uuid = Str::uuid().'-'.time();
                        $data['uuid'] = $uuid;

                        $subject_ids = [];
                        foreach($criteria['must_pass_subjects'] as $subject){
                            if(strpos($subject, "|") !== false){
                                $ids = [];
                                $subjects = explode("|", $subject);
                                foreach($subjects as $subject){
                                    if(!empty($c_subject = Subject::where('school_location_id', $location->id)->where('sub_class_id', $subclass->id)->where('name', $subject)->first())){
                                        $ids[] = $c_subject->id;
                                    }
                                }
                                $subject_ids[] = join("|", $ids);
                            } else {
                                if(!empty($c_subject = Subject::where('school_location_id', $location->id)->where('sub_class_id', $subclass->id)->where('name', $subject)->first())){
                                    $subject_ids[] = "{$c_subject->id}";
                                }
                            }
                        
                        }
                        $data['must_pass_subjects'] = json_encode($subject_ids);
                        if(!empty($prom_criteria = PromotionCriteria::where('sub_class_id', $subclass->id)->where('session_id', $session->id)->first())){
                            $prom_criteria->update($data);
                        } else {
                            $prom_criteria = PromotionCriteria::create($data);
                        }
                    }
                }
            } elseif($location->location_type == "secondary") {
                $data = [
                    'school_id' => $this->user->school_id,
                    'location_id' => $location->id,
                    'must_pass_count' => $criteria['must_pass_count'],
                    'session_id' => $session->id
                ];

                $main_classes = MainClass::where('school_location_id', $location->id)->get();
                foreach($main_classes as $main_class){
                    if($main_class->class_level <= 3){
                        $criteria = $criteria['junior_secondary'];
                    } elseif($main_class->class_level > 3){
                        $criteria = $criteria['senior_secondary'];
                    }

                    $data['main_class_id'] = $main_class->id;
                    $sub_classes = SubClass::where('main_class_id', $main_class->id)->get();
                    foreach($sub_classes as $subclass){
                        $data['sub_class_id'] = $subclass->id;

                        $uuid = Str::uuid().'-'.time();
                        $data['uuid'] = $uuid;
                        if($main_class->class_level > 3){
                            $criteria = $criteria[$subclass->type];
                        }

                        $subject_ids = [];
                        foreach($criteria['must_pass_subjects'] as $subject){
                            if(strpos($subject, "|") !== false){
                                $ids = [];
                                $subjects = explode("|", $subject);
                                foreach($subjects as $subject){
                                    if(!empty($c_subject = Subject::where('school_location_id', $location->id)->where('sub_class_id', $subclass->id)->where('name', $subject)->first())){
                                        $ids[] = $c_subject->id;
                                    }
                                }
                                $subject_ids[] = join("|", $ids);
                            } else {
                                if(!empty($c_subject = Subject::where('school_location_id', $location->id)->where('sub_class_id', $subclass->id)->where('name', $subject)->first())){
                                    $subject_ids[] = "{$c_subject->id}";
                                }
                            }
                        
                        }
                        $data['must_pass_subjects'] = json_encode($subject_ids);
                        if(!empty($prom_criteria = PromotionCriteria::where('sub_class_id', $subclass->id)->where('session_id', $session->id)->first())){
                            $prom_criteria->update($data);
                        } else {
                            $prom_criteria = PromotionCriteria::create($data);
                        }
                    }
                }
            }
        }

        return response([
            'status' => 'success',
            'message' => 'Default Promotion Criteria Set for all Classes'
        ], 200);
    }

    public function store(StorePromotionCriteriaRequest $request, $uuid){
        $subclass = SubClass::where('uuid', $uuid)->where('school_location_id', $this->user->school_location_id)->first();
        if(empty($subclass)){
            return response([
                'status' => 'failed',
                'message' => 'No Class was fetched'
            ], 404);
        }
        $session = SchoolSession::where('uuid', $request->session_uuid)->where('school_location_id', $this->user->school_location_id);
        if(empty($session)){
            return response([
                'status' => 'failed',
                'message' => 'Wrong School Session'
            ], 404);
        }

        $all = $request->all();
        $subject_ids = [];
        foreach($all['must_pass_subjects'] as $subject){
            if(strpos($subject, "|") !== false){
                $ids = [];
                $subjects = explode("|", $subject);
                foreach($subjects as $subj){
                    if(!empty($c_subject = Subject::where('sub_class_id', $subclass->id)->where('id', $subj)->first())){
                        $ids[] = $c_subject->id;
                    }
                } 
                $subject_ids[] = join("|", $ids);
            } else {
                if(!empty($c_subject = Subject::where('sub_class_id', $subclass->id)->where('id', $subject)->first())){
                    $subject_ids[] = $subject;
                }
            }
        }
        $all['must_pass_subjects'] = json_encode($subject_ids);
        $all['school_session_id'] = SchoolSession::where('uuid', $request->session_uuid)->first()->id;
        $all['school_id'] = $this->user->school_id;
        $all['school_location_id'] = $this->user->school_location_id;
        $all['main_class_id'] = $subclass->main_class_id;
        $all['sub_class_id'] = $subclass->id;

        if(!empty($criteria = PromotionCriteria::where('sub_class_id', $subclass->id)->first)){
            $criteria->update($all);
        } else {
            $criteria = PromotionCriteria::create($all);
            if(!$criteria){
                return response([
                    'status' => 'failed',
                    'message' => 'Promotion Criteria was not created'
                ], 500);
            }
        }

        return response([
            'status' => 'success',
            'message' => 'Promotion Criteria Uploaded successfully',
            'data' => self::criteria($criteria)
        ], 200);
    }
}

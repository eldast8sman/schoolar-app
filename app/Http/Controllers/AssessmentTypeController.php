<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAssessmentTypeRequest;
use App\Models\AssessmentType;
use Illuminate\Http\Request;

class AssessmentTypeController extends Controller
{
    private $user;

    public function __construct()
    {
        $this->middleware('auth:user-api');
        $this->user = AuthController::user();
    }

    public function load_default(){
        $assessments = FunctionController::default_assessment_types();

        if(!empty($assessment_type = AssessmentType::where('school_location_id', $this->user->school_location_id)->first())){
            $assessment_type->update([
                'assessment_scores' => json_encode($assessments),
                'minimum_pass_score' => 50
            ]);
        } else {
            $assessment_type = AssessmentType::create([
                'school_id' => $this->user->school_id,
                'school_location_id' => $this->user->school_location_id,
                'assessment_scores' => json_encode($assessments),
                'minimum_pass_score' => 50
            ]);
        }

        $assessment_type->assessment_scores = $assessments;

        return response([
            'status' => 'success',
            'message' => 'Assessment Types successfully set',
            'data' => $assessment_type
        ], 200);
    }

    public function store(StoreAssessmentTypeRequest $request){
        $all = $request->all();
        $all['assessment_scores'] = json_encode($all['assessment_scores']);
        $all['school_id'] = $this->user->school_id;
        $all['school_location_id'] = $this->user->school_location_id;

        if(!empty($assessment = AssessmentType::where('school_location_id', $this->user->school_location_id)->first())){
            $assessment->update($all);
        } else {
            $assessment = AssessmentType::create($all);
        }

        $assessment->assessment_scores = $request->assessment_scores;

        return response([
            'status' => 'success',
            'message' => 'Assessment Types successfully set',
            'data' => $assessment
        ], 200);
    }

    public function index(){
        $assessment_types = AssessmentType::where('school_location_id', $this->user->school_location_id)->first();
        if(!empty($assessment_types)){
            $assessment_types->assessment_scores = json_decode($assessment_types->assessment_scores);
        }

        return response([
            'status' => 'success',
            'message' => 'Assessment Type fetched successfully',
            'data' => $assessment_types
        ], 200);
    }
}

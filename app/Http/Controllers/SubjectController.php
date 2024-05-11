<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignTeacherToSubjectRequest;
use App\Http\Requests\StoreAssessmentTypeRequest;
use App\Http\Requests\StoreMultipleSubjectRequest;
use App\Http\Requests\StoreSubjecBookRequest;
use App\Http\Requests\StoreSubjectRequest;
use App\Http\Requests\UpdateSubjectBookRequest;
use App\Models\AssessmentType;
use App\Models\MainClass;
use App\Models\School;
use App\Models\Subject;
use App\Models\SubClass;
use Illuminate\Http\Request;
use App\Models\SchoolLocation;
use App\Models\SchoolSubjectBooks;
use App\Models\SchoolTeacher;
use Illuminate\Support\Str;

class SubjectController extends Controller
{
    private $user;
    private $disk = 'public';

    public function __construct(){
        $this->middleware('auth:user-api');
        $this->user = AuthController::user();
    }

    public function load_default_subjects(SubClass $class){
        if(($class->school_id != $this->user->school_id) and ($class->school_location_id != $this->user->school_location_id)){
            return response([
                'status' => 'failed',
                'message' => 'No Class was fetched'
            ]);
        }
        $location = SchoolLocation::find($class->school_location_id);
        if(strtolower($location->country) == 'nigeria'){
            $subjects = FunctionController::default_subjects();
            if($location->location_type == "primary"){
                foreach($subjects['primary'] as $subject){
                    if(Subject::where('sub_class_id', $class->id)->where('name', $subject['subject'])->count() < 1){
                        Subject::create([
                            'school_id' => $class->school_id,
                            'school_location_id' => $class->school_location_id,
                            'main_class_id' => $class->main_class_id,
                            'sub_class_id' => $class->id,
                            'name' => $subject['subject'],
                            'compulsory' => $subject['compulsory']
                        ]);
                    }
                }
            } elseif(strtolower($location->location_type) == 'secondary'){
                $main_class = MainClass::find($class->main_class_id);
                if(($main_class->class_level >= 1) and ($main_class->class_level <= 3)){
                    foreach($subjects['junior_secondary'] as $subject){
                        if(Subject::where('sub_class_id', $class->id)->where('name', $subject['subject'])->count() < 1){
                            Subject::create([
                                'school_id' => $class->school_id,
                                'school_location_id' => $class->school_location_id,
                                'main_class_id' => $class->main_class_id,
                                'sub_class_id' => $class->id,
                                'name' => $subject['subject'],
                                'compulsory' => $subject['compulsory']
                            ]);
                        }
                    }
                } elseif(($main_class->class_level >=4) and ($main_class->class_level <= 6)){
                    if($class->type != "general"){
                        foreach($subjects['senior_secondary'][$class->type] as $subject){
                            if(Subject::where('sub_class_id', $class->id)->where('name', $subject['subject'])->count() < 1){
                                Subject::create([
                                    'school_id' => $class->school_id,
                                    'school_location_id' => $class->school_location_id,
                                    'main_class_id' => $class->main_class_id,
                                    'sub_class_id' => $class->id,
                                    'name' => $subject['subject'],
                                    'compulsory' => $subject['compulsory']
                                ]);
                            }
                        }
                    } else {
                        foreach($subjects['senior_secondary']['sciences'] as $subject){
                            if(Subject::where('sub_class_id', $class->id)->where('name', $subject['subject'])->count() < 1){
                                Subject::create([
                                    'school_id' => $class->school_id,
                                    'school_location_id' => $class->school_location_id,
                                    'main_class_id' => $class->main_class_id,
                                    'sub_class_id' => $class->id,
                                    'name' => $subject['subject'],
                                    'compulsory' => $subject['compulsory']
                                ]);
                            }
                        }

                        foreach($subjects['senior_secondary']['arts'] as $subject){
                            if(Subject::where('sub_class_id', $class->id)->where('name', $subject['subject'])->count() < 1){
                                Subject::create([
                                    'school_id' => $class->school_id,
                                    'school_location_id' => $class->school_location_id,
                                    'main_class_id' => $class->main_class_id,
                                    'sub_class_id' => $class->id,
                                    'name' => $subject['subject'],
                                    'compulsory' => $subject['compulsory']
                                ]);
                            }
                        }

                        foreach($subjects['senior_secondary']['commerce'] as $subject){
                            if(Subject::where('sub_class_id', $class->id)->where('name', $subject['subject'])->count() < 1){
                                Subject::create([
                                    'school_id' => $class->school_id,
                                    'school_location_id' => $class->school_location_id,
                                    'main_class_id' => $class->main_class_id,
                                    'sub_class_id' => $class->id,
                                    'name' => $subject['subject'],
                                    'compulsory' => $subject['compulsory']
                                ]);
                            }
                        }
                    }
                }
            }
        }

        return response([
            'status' => 'success',
            'message' => 'Default Subjects Loaded successfully'
        ], 200);
    }

    public static function subject(Subject $subject, $user) : Subject {
        $subject->main_class = MainClass::find($subject->main_class_id);
        $subject->sub_class = SubClass::find($subject->sub_class_id);
        if(!empty($subject->primary_teacher)){
            $subject->primary_teacher = SchoolTeacher::find($subject->primary_teacher);
        }
        if(!empty($subject->support_teacher)){
            $subject->support_teacher = SchoolTeacher::find($subject->support_teacher);
        }
        $subject->books = SchoolSubjectBooks::where('subject_id', $subject->id)->get();
        $assessment_type = AssessmentType::where('school_location_id', $user->school_location_id)
                            ->where('source', 'subject')->where('source_id', $subject->id)->first();
        if(!empty($assessment_type)){
            $assessment_type->assessment_scores = json_decode($assessment_type->assessment_scores, true);
        } else {
            $assessment_type = null;
        }
        $subject->assessment_types = $assessment_type;

        return $subject;
    }

    public function store(StoreSubjectRequest $request, SubClass $subclass){
        if(($subclass->school_id != $this->user->school_id) or ($subclass->school_location_id != $this->user->school_location_id)){
            return response([
                'status' => 'failed',
                'message' => 'No Sub Class was fetched'
            ], 409);
        }
        if(!empty($request->primary_teacher)){
            $teacher = SchoolTeacher::find($request->primary_teacher);
            if(($teacher->school_id != $this->user->school_id) or ($teacher->school_location_id != $this->user->school_location_id)){
                return response([
                    'status' => 'failed',
                    'message' => 'Invalid Primary Teacher'
                ], 409);
            }
        }
        if(!empty($request->support_teacher)){
            $teacher = SchoolTeacher::find($request->support_teacher);
            if(($teacher->school_id != $this->user->school_id) or ($teacher->school_location_id != $this->user->school_location_id)){
                return response([
                    'status' => 'failed',
                    'message' => 'Invalid Primary Teacher'
                ], 409);
            }
        }

        if(Subject::where('sub_class_id', $subclass->id)->where('name', $request->name)->count() > 0){
            return response([
                'status' => 'failed',
                'message' => 'Subject already added'
            ]);
        }

        $subject = Subject::create([
            'school_id' => $subclass->school_id,
            'school_location_id' => $subclass->school_location_id,
            'main_class_id' => $subclass->main_class_id,
            'sub_class_id' => $subclass->id,
            'name' => $request->name,
            'compulsory' => $request->compulsory,
            'primary_teacher' => !empty($request->primary_teacher) ? $request->primary_teacher : null,
            'support_teacher' => !empty($request->support_teacher) ? $request->support_teacher : null
        ]);

        return response([
            'status' => 'success',
            'message' => 'Subject added to Class successfully',
            'data' => self::subject($subject, $this->user)
        ], 200);
    }

    public function store_multiple(StoreMultipleSubjectRequest $request, SubClass $subclass){
        if(($subclass->school_id != $this->user->school_id) or ($subclass->school_location_id != $this->user->school_location_id)){
            return response([
                'status' => 'failed',
                'message' => 'No Sub Class was fetched'
            ], 409);
        }

        $added_subjects = [];
        $subjects = $request->subjects;
        foreach($subjects as $subject){
            if(!empty($subject['primary_teacher'])){
                $teacher = SchoolTeacher::find($subject['primary_teacher']);
                if(($teacher->school_id != $this->user->school_id) or ($teacher->school_location_id != $this->user->school_location_id)){
                    continue;
                }
            }
            if(!empty($subject['support_teacher'])){
                $teacher = SchoolTeacher::find($subject['support_teacher']);
                if(($teacher->school_id != $this->user->school_id) or ($teacher->school_location_id != $this->user->school_location_id)){
                    continue;
                }
            }
            if(Subject::where('sub_class_id', $subclass->id)->where('name', $subject['name'])->count() > 0){
                continue;
            }

            $added_subject = Subject::create([
                'school_id' => $subclass->school_id,
                'school_location_id' => $subclass->school_location_id,
                'main_class_id' => $subclass->main_class_id,
                'sub_class_id' => $subclass->id,
                'name' => $subject['name'],
                'compulsory' => $subject['compulsory'],
                'primary_teacher' => !empty($subject['primary_teacher']) ? $subject['primary_teacher'] : null,
                'support_teacher' => !empty($subject['support_teacher']) ? $subject['support_teacher'] : null
            ]);

            $added_subjects[] = self::subject($added_subject, $this->user);
        }

        return response([
            'status' => 'success',
            'message' => 'Subjects added to Class successfully',
            'data' => $added_subjects
        ], 200);
    }

    public function index(SubClass $subclass){
        $search = !empty($_GET['search']) ? (string)$_GET['search'] : "";
        $limit = !empty($_GET['limit']) ? (int)$_GET['limit'] : 10;

        $subjects = Subject::where('school_id', $this->user->school_id)->where('school_location_id', $this->user->school_location_id)->where('sub_class_id', $subclass->id)->orderBy('name', 'asc');
        if(!empty($search)){
            $subjects = $subjects->where('name', 'like', '%'.$search.'%');
        }
        if($subjects->count() < 1){
            return response([
                'status' => 'failed',
                'message' => 'No Subject has been added to this Class'
            ]);
        }
        
        $subjects = $subjects->paginate($limit);
        foreach($subjects as $subject){
            $subject = self::subject($subject, $this->user);
        }

        return response([
            'status' => 'success',
            'message' => 'Subjects fetched successfully',
            'data' => $subjects
        ], 200);
    }

    public function show(Subject $subject){
        if(($subject->school_id != $this->user->school_id) or ($subject->school_location_id != $this->user->school_location_id)){
            return response([
                'status' => 'failed',
                'message' => 'No Subject was fetched'
            ], 409);
        }

        return response([
            'status' => 'success',
            'message' => 'Subject fetched successfully',
            'data' => self::subject($subject, $this->user)
        ], 200);
    }

    public function assessment_type(StoreAssessmentTypeRequest $request, Subject $subject){
        if($subject->school_location_id != $this->user->school_location_id){
            return response([
                'status' => 'failed',
                'message' => 'No Subject was fetched'
            ], 404);
        }

        $data = [
            'school_id' => $this->user->school_id,
            'school_location_id' => $this->user->school_location_id,
            'source' => 'subject',
            'source_id' => $subject->id,
            'assessment_scores' => json_encode($request->assessment_scores),
            'minimum_pass_score' => $request->minimum_pass_score
        ];
        $type = AssessmentType::where('school_location_id', $this->user->school_location_id)->where('source', 'subject')->where('source_id', $subject->id)->first();
        if(empty($type)){
            $type = AssessmentType::create($data);
        } else {
            $type->update($data);
        }

        return response([
            'status' => 'success',
            'message' => 'Assessment Type created for this Subject successfully',
            'data' => $type
        ], 200);
    }

    public function update(StoreSubjectRequest $request, Subject $subject){
        if(($subject->school_id != $this->user->school_id) or ($subject->school_location_id != $this->user->school_location_id)){
            return response([
                'status' => 'failed',
                'message' => 'No Subject was fetched'
            ], 409);
        }
        if(!empty($request->primary_teacher)){
            $teacher = SchoolTeacher::find($request->primary_teacher);
            if(($teacher->school_id != $this->user->school_id) or ($teacher->school_location_id != $this->user->school_location_id)){
                return response([
                    'status' => 'failed',
                    'message' => 'Invalid Primary Teacher'
                ], 409);
            }
        }
        if(!empty($request->secondary_teacher)){
            $teacher = SchoolTeacher::find($request->secondary_teacher);
            if(($teacher->school_id != $this->user->school_id) or ($teacher->school_location_id != $this->user->school_location_id)){
                return response([
                    'status' => 'failed',
                    'message' => 'Invalid Primary Teacher'
                ], 409);
            }
        }
        if(Subject::where('sub_class_id', $subject->sub_class_id)->where('name', $request->name)->where('id', '<>', $subject->id)->count() > 0){
            return response([
                'status' => 'failed',
                'message' => 'Subject already added'
            ]);
        }

        $all = $request->all();
        $subject->update($all);

        return response([
            'status' => 'success',
            'message' => 'Subject updated to Class successfully',
            'data' => self::subject($subject, $this->user)
        ], 200);
    }

    public function assign_primary_teacher(AssignTeacherToSubjectRequest $request, Subject $subject){
        if(($subject->school_id != $this->user->school_id) or ($subject->school_location_id != $this->user->school_location_id)){
            return response([
                'status' => 'failed',
                'message' => 'No Subject was fetched'
            ], 409);
        }
        if ($subject->primary_teacher == $request->teacher_id or $subject->support_teacher == $request->teacher_id) {
            return response([
                'status' => 'failed',
                'message' => 'This teacher has already been assigned to this subject'
            ], 409);
        }
        $teacher = SchoolTeacher::find($request->teacher_id);
        if(($teacher->school_id != $this->user->school_id) or ($teacher->school_location_id != $this->user->school_location_id)){
            return response([
                'status' => 'failed',
                'message' => 'Invalid Teacher'
            ], 409);
        }

        $subject->primary_teacher = $request->teacher_id;
        $subject->save();

        return response([
            'status' => 'success',
            'message' => 'Teacher assigned to Subject successfully',
            'data' => self::subject($subject, $this->user)
        ], 200);
    }

    public function assign_secondary_teacher(AssignTeacherToSubjectRequest $request, Subject $subject){
        if(($subject->school_id != $this->user->school_id) or ($subject->school_location_id != $this->user->school_location_id)){
            return response([
                'status' => 'failed',
                'message' => 'No Subject was fetched'
            ], 409);
        }
        $teacher = SchoolTeacher::find($request->teacher_id);
        if(($teacher->school_id != $this->user->school_id) or ($teacher->school_location_id != $this->user->school_location_id)){
            return response([
                'status' => 'failed',
                'message' => 'Invalid Teacher'
            ], 409);
        }

        $subject->support_teacher = $request->teacher_id;
        $subject->save();

        return response([
            'status' => 'success',
            'message' => 'Teacher assigned to Subject successfully',
            'data' => self::subject($subject, $this->user)
        ], 200);
    }

    public function store_book(StoreSubjecBookRequest $request, Subject $subject) {
        if(($subject->school_id != $this->user->school_id) or ($subject->school_location_id != $this->user->school_location_id)){
            return response([
                'status' => 'failed',
                'message' => 'No Subject was fetched'
            ], 409);
        }

        if (!empty(SchoolSubjectBooks::where('book_name', $request->book_name)->where('sub_class_id', $subject->sub_class_id)->where('subject_id', $subject->id)->first())) {
            return response([
                'status' => 'failed',
                'message' => 'This book already exists for this subject'
            ], 409);
        }

        $all = $request->all();

        if(isset($request->file) and !empty($request->file)){
            $school = School::find($this->user->school_id);
            if(empty($school)){
                return response([
                    'status' => 'failed',
                    'message' => 'No School was fetched'
                ], 409);
                exit;
            }

            $path = $school->slug.'/books';
            $disk = !empty($request->disk) ? $request->disk : $this->disk;

            if($upload = FunctionController::uploadFile($path, $request->file('file'), $disk)){
                $file_url = $upload['file_url'];
                $file_path = $upload['file_path'];
                $file_size = $upload['file_size'];
                $file_disk = $disk;
            } else {
                $file_url = "";
                $file_path = "";
                $file_disk = "";
                $file_size = 0;
            }
        } else {
            $file_url = "";
            $file_path = "";
            $file_disk = "";
            $file_size = 0;
        }

        $all = $request->except(['file']);
        $all['school_id'] = $this->user->school_id;
        $all['school_location_id'] = $this->user->school_location_id;
        $all['subject_name'] = $subject->name;
        $all['main_class_id'] = $subject->main_class_id;
        $all['sub_class_id'] = $subject->sub_class_id;
        $all['disk'] = $file_disk;
        $all['file_path'] = $file_path;
        $all['file_url'] = $file_url;
        $all['file_size'] = $file_size;

        $all['subject_id'] = $subject->id;
        for($i=1; $i<=20; $i++){
            $uuid = Str::uuid();
            if(SchoolSubjectBooks::where('school_location_id', $this->user->school_location_id)->where('uuid', $uuid)->count() < 1){
                $all['uuid'] = $uuid;
                break;
            } else {
                continue;
            }
        }

        if(!$book = SchoolSubjectBooks::create($all)){
            return response([
                'status' => 'failed',
                'message' => 'Book upload failed'
            ], 409);
        }

        if (!empty($subjects = Subject::where('name', $subject->name)->where('main_class_id', $subject->main_class_id)->where('id', '<>', $subject->id)->get())) {
            foreach($subjects as $main_subject) {
                $all['subject_id'] = $main_subject->id;
                for($i=1; $i<=20; $i++){
                    $uuid = Str::uuid();
                    if(SchoolSubjectBooks::where('school_location_id', $this->user->school_location_id)->where('uuid', $uuid)->count() < 1){
                        $all['uuid'] = $uuid;
                        break;
                    } else {
                        continue;
                    }
                }
                if(empty(SchoolSubjectBooks::where('book_name', $request->book_name)->where('subject_id', $main_subject->id)->first())) {
                    SchoolSubjectBooks::create($all);
                }
            }
        };
        
        return response([
            'status' => 'success',
            'message' => 'Book saved successfully',
            'data' => $book
        ], 200);
    }

    public function update_book (UpdateSubjectBookRequest $request, SchoolSubjectBooks $book) {
        if(($book->school_id != $this->user->school_id) or ($book->school_location_id != $this->user->school_location_id)){
            return response([
                'status' => 'failed',
                'message' => 'No Book was fetched'
            ], 409);
        }
        if (!empty(SchoolSubjectBooks::where('book_name', $request->book_name)->where('id', '<>', $book->id)->first())) {
            return response([
                'status' => 'failed',
                'message' => 'This book already exists for this subject'
            ], 409);
        }

        if(isset($request->file) and !empty($request->file)){
            $school = School::find($this->user->school_id);
            if(empty($school)){
                return response([
                    'status' => 'failed',
                    'message' => 'No School was fetched'
                ], 409);
                exit;
            }
            FunctionController::deleteFile($book->file_path);
            $path = $school->slug.'/books';
            $disk = !empty($request->disk) ? $request->disk : $this->disk;

            if($upload = FunctionController::uploadFile($path, $request->file('file'), $disk)){
                $file_url = $upload['file_url'];
                $file_path = $upload['file_path'];
                $file_size = $upload['file_size'];

                $all['file_path'] = $file_path;
                $all['file_url'] = $file_url;
                $all['file_size'] = $file_size;
            }
        }

        $all = $request->except(['file']);
        $book = SchoolSubjectBooks::find($book->id);
        $old_book_name = $book->book_name;

        if(!$book->update($all)){
            return response([
                'status' => 'failed',
                'message' => 'Book update failed'
            ], 409);
        }
        
        if (!empty($subjects = SchoolSubjectBooks::where('name', $old_book_name)->where('main_class_id', $book->main_class_id)->where('id', '<>', $book->id)->get())) {
            foreach($subjects as $main_subject) {
                $main_subject->update($all);
            }
        };
        
        return response([
            'status' => 'success',
            'message' => 'Book saved successfully',
            'data' => $book
        ], 200);
    }

    public function remove_book (SchoolSubjectBooks $book) {
        if (empty($book) or ($book->school_id != $this->user->school_id) or ($book->school_location_id != $this->user->school_location_id)) {
            return response([
                'status' => 'failed',
                'message' => 'No book was fetched'
            ], 404);
        }

        if (!empty($book->delete())) {
            if (!empty($other_boooks = SchoolSubjectBooks::where('book_name', $book->book_name)->where('main_calss_id', $book->main_class_id)->get())) {
                foreach($other_boooks as $other_boook) {
                    $other_boook->delete();
                }
            }
        }

        return response([
            'status' => 'success',
            'message' => 'Book removed successfully',
            'data' => $book
        ], 200);
    }

    public function show_book (SchoolSubjectBooks $book) {
        if (empty($book) or ($book->school_id != $this->user->school_id) or ($book->school_location_id != $this->user->school_location_id)) {
            return response([
                'status' => 'failed',
                'message' => 'No book was fetched'
            ], 404);
        }

        return response([
            'status' => 'success',
            'message' => 'Book fetched successfully',
            'data' => $book
        ], 200);
    }
}

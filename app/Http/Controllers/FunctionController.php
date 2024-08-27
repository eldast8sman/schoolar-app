<?php

namespace App\Http\Controllers;

use App\Models\AssessmentType;
use App\Models\GradingSystem;
use App\Models\MainClass;
use App\Models\School;
use App\Models\SchoolLocation;
use App\Models\SubClass;
use App\Models\Subject;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FunctionController extends Controller
{
    public static function uploadFile($file_path, $file, $disk='public'){
        if($file instanceof UploadedFile){
            if($stored = Storage::disk($disk)->putFile($file_path, $file)){
                return [
                    'file_url' => Storage::disk($disk)->url($stored),
                    'file_path' => $stored,
                    'file_size' => Storage::disk($disk)->size($stored),
                    'file_disk' => $disk
                ];
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public static function deleteFile($path, $disk='public'){
        if(Storage::disk($disk)->exists($path)){
            Storage::disk($disk)->delete($path);
        }
    }
}

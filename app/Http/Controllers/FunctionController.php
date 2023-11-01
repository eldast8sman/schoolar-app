<?php

namespace App\Http\Controllers;

use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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

    public static function default_subjects(){
        return [
            "primary" => array(
                array("subject" => "English Language", "compulsory" => true),
                array("subject" => "Mathematics", "compulsory" => true),
                array("subject" => "Basic Science", "compulsory" => true),
                array("subject" => "Social Studies", "compulsory" => true),
                array("subject" => "P.H.E. (Physical and Health Education)", "compulsory" => true),
                array("subject" => "C.R.S. (Christian Religious Studies)", "compulsory" => false),
                array("subject" => "I.R.S. (Islamic Religious Studies)", "compulsory" => false),
                array("subject" => "Yoruba Language", "compulsory" => false),
                array("subject" => "Igbo Language", "compulsory" => false),
                array("subject" => "Hausa Language", "compulsory" => false),
                array("subject" => "Basic Technology", "compulsory" => false),
                array("subject" => "Agricultural Science", "compulsory" => false),
                array("subject" => "Computer Studies", "compulsory" => false),
                array("subject" => "Civic Education", "compulsory" => false),
                array("subject" => "Creative and Cultural Arts", "compulsory" => false),
            ),
            "junior_secondary" => array(
                array("subject" => "English Language", "compulsory" => true),
                array("subject" => "Mathematics", "compulsory" => true),
                array("subject" => "Basic Science", "compulsory" => true),
                array("subject" => "Basic Technology", "compulsory" => true),
                array("subject" => "Physical and Health Education", "compulsory" => true),
                array("subject" => "Civic Education", "compulsory" => true),
                array("subject" => "Social Studies", "compulsory" => true),
                array("subject" => "Christian Religious Studies", "compulsory" => false),
                array("subject" => "Islamic Religious Studies", "compulsory" => false),
                array("subject" => "Business Studies", "compulsory" => false),
                array("subject" => "Agricultural Science", "compulsory" => false),
                array("subject" => "Home Economics", "compulsory" => false),
                array("subject" => "Computer Studies", "compulsory" => false),
                array("subject" => "Basic Electricity", "compulsory" => false),
                array("subject" => "French", "compulsory" => false),
                array("subject" => "Yoruba Language", "compulsory" => false),
                array("subject" => "Igbo Language", "compulsory" => false),
                array("subject" => "Hausa Language", "compulsory" => false),
                array("subject" => "Visual Arts", "compulsory" => false),
                array("subject" => "Music", "compulsory" => false),
                array("subject" => "Arabic", "compulsory" => false),
                array("subject" => "Technical Drawing", "compulsory" => false),
                array("subject" => "Home Economics", "compulsory" => false),
                array("subject" => "Basic Electricity", "compulsory" => false),
                array("subject" => "Building Construction", "compulsory" => false),
                array("subject" => "Computer Science", "compulsory" => false),
                array("subject" => "Physical and Health Education", "compulsory" => false),
                array("subject" => "Insurance", "compulsory" => false),
                array("subject" => "Data Processing", "compulsory" => false),
                array("subject" => "Further Mathematics", "compulsory" => false),
                array("subject" => "Food and Nutrition", "compulsory" => false),
                array("subject" => "Animal Husbandry", "compulsory" => false),
                array("subject" => "Photography", "compulsory" => false),
            ),
            "senior_secondary" => array(
                "sciences" => array(
                    array("subject" => "English Language", "compulsory" => true),
                    array("subject" => "Mathematics", "compulsory" => true),
                    array("subject" => "Physics", "compulsory" => true),
                    array("subject" => "Chemistry", "compulsory" => true),
                    array("subject" => "Biology", "compulsory" => true),
                    array("subject" => "Further Mathematics", "compulsory" => true),
                    array("subject" => "Geography", "compulsory" => true),
                    array("subject" => "Technical Drawing", "compulsory" => false),
                    array("subject" => "Agricultural Science", "compulsory" => false),
                    array("subject" => "Computer Science", "compulsory" => false),
                ),
                "arts" => array(
                    array("subject" => "English Language", "compulsory" => true),
                    array("subject" => "Mathematics", "compulsory" => true),
                    array("subject" => "Literature in English", "compulsory" => true),
                    array("subject" => "Government", "compulsory" => true),
                    array("subject" => "History", "compulsory" => true),
                    array("subject" => "Christian Religious Studies", "compulsory" => false),
                    array("subject" => "Islamic Religious Studies", "compulsory" => false),
                    array("subject" => "Yoruba Language", "compulsory" => false),
                    array("subject" => "Igbo Language", "compulsory" => false),
                    array("subject" => "Hausa Language", "compulsory" => false),
                    array("subject" => "Visual Arts", "compulsory" => false),
                    array("subject" => "Music", "compulsory" => false),
                    array("subject" => "French", "compulsory" => false),
                    array("subject" => "Arabic", "compulsory" => false),
                ),
                "commerce" => array(
                    array("subject" => "English Language", "compulsory" => true),
                    array("subject" => "Mathematics", "compulsory" => true),
                    array("subject" => "Economics", "compulsory" => true),
                    array("subject" => "Accounting", "compulsory" => true),
                    array("subject" => "Commerce", "compulsory" => true),
                    array("subject" => "Financial Accounting", "compulsory" => true),
                    array("subject" => "Government", "compulsory" => false),
                    array("subject" => "Civic Education", "compulsory" => false),
                ),
            )
        ];
    }
}

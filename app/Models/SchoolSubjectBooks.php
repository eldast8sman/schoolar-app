<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolSubjectBooks extends Model
{

    use HasFactory;

    protected $fillable = [
        'uuid',
        'school_id',
        'school_location_id',
        'main_class_id',
        'sub_class_id',
        'subject_id',
        'subject_name',
        'book_name',
        'authors',
        'year_published',
        'compulsory',//1or0
        'can_purchase_externally', //1or0
        'cost',
        'disk',
        'file_path',
        'file_url',
        'file_size',
    ];
}

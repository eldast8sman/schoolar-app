<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassTimeTable extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'uuid',
        'school_id',
        'school_location_id',
        'time_table_class_group_id',
        'day',
        'time_breakdown',
    ];
}

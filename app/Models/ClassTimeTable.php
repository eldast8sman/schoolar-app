<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassTimeTable extends Model
{
    use HasFactory, HasUuid;
    
    protected $fillable = [
        'school_id',
        'school_location_id',
        'time_table_class_group_id',
        'day',
        'time_breakdown',
    ];
}

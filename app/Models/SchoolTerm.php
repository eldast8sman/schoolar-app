<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolTerm extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'school_location_id',
        'school_session_id',
        'term_name',
        'start_date',
        'end_date',
        'position',
        'status'
    ];

    public function school_session(){
        return $this->belongsTo(SchoolSession::class);
    }
}

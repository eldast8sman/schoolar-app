<?php

namespace App\Models\Parent;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParentSchoolParent extends Model
{
    use HasFactory;

    protected $fillable = [
        'parents_id',
        'school_parent_id'
    ];
}

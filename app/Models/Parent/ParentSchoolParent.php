<?php

namespace App\Models\Parent;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParentSchoolParent extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'parents_id',
        'school_parent_id'
    ];
}

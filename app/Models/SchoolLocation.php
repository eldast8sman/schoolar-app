<?php

namespace App\Models;

use App\Traits\HasUuid;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolLocation extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'school_id',
        'location_type',
        'syllabus',
        'address',
        'town',
        'lga',
        'state',
        'country'
    ];

    public function school_sessions(){
        return $this->hasMany(SchoolSession::class);
    }

    public function school_terms(){
        return $this->hasMany(SchoolTerm::class);
    }

    public function current_session(){
        return $this->school_sessions()->where('status', 2)->where('start_date', '<=', Carbon::now()->format('Y-m-d'))->where('end_date', '>=', Carbon::now()->format('Y-m-d'))->first();
    }

    public function current_term(){
        return !empty($this->current_sessoion) ?
                $this->current_session()->school_terms()->where('status', 2)->where('start_date', '<=', Carbon::now()->format('Y-m-d'))->where('end_date', '>=', Carbon::now()->format('Y-m-d'))->first()
                : null;
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }
}

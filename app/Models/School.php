<?php

namespace App\Models;

use App\Traits\HasUuid;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class School extends Model
{
    use HasFactory, HasSlug, HasUuid;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'country',
        'logo_id'
    ];

    public function getSlugOptions() : SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    public function logo(){
        return $this->belongsTo(FileManager::class);
    }

    public function school_locations(){
        return $this->hasMany(SchoolLocation::class);
    }
}

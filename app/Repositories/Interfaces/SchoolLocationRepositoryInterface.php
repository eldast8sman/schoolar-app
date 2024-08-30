<?php

namespace App\Repositories\Interfaces;

use App\Models\SchoolLocation;
use Illuminate\Http\Request;

interface SchoolLocationRepositoryInterface extends AbstractRepositoryInterface
{
     public function store(Request $request);

     public function switch_location(SchoolLocation $location);
}  
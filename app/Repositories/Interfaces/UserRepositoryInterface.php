<?php

namespace App\Repositories\Interfaces;

use Illuminate\Http\Request;

interface UserRepositoryInterface extends AbstractRepositoryInterface
{
     public function store(Request $request);
}  
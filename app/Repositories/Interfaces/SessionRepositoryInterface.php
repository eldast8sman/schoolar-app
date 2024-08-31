<?php

namespace App\Repositories\Interfaces;

use App\Models\SchoolSession;
use Illuminate\Http\Request;

interface SessionRepositoryInterface extends AbstractRepositoryInterface
{
     public function store(Request $request);

     public function index($search='', $limit=10, $filter=null);

     public function update_session(Request $request, SchoolSession $session);

     public function destroy(SchoolSession $session);
}  
<?php

namespace App\Repositories\Interfaces;

use App\Models\SchoolSession;
use App\Models\SchoolTerm;
use Illuminate\Http\Request;

interface TermRepositoryInterface extends AbstractRepositoryInterface
{
     public function store(Request $request, SchoolSession $session);

     public function update_term(Request $request, SchoolTerm $term);

     public function destroy(SchoolTerm $term);
}  
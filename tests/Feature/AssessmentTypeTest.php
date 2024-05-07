<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AssessmentTypeTest extends TestCase
{
    use RefreshDatabase;

    public function test_load_default(){
        $token = $this->get_token();

        $store_assessments = $this->getJson(route('assesmentType.loadDefault'), ['authorization: Bearer '.$token])->assertOk()->json();
        $this->assertEquals($store_assessments['status'], 'success');
        $this->assertDatabaseHas('assessment_types', ['minimum_pass_score' => 50]);
    }
}

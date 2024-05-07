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

    public function test_store_assessment_type(){
        $token = $this->get_token();

        $store_type = $this->postJson(route('assessmentType.store'), self::assessment_type_data(), ['authorization: Bearer '.$token])->assertOk()->json();
        $this->assertEquals($store_type['status'], 'success');
        $this->assertStringContainsString(self::assessment_type_data()['assessment_scores'][0]['assessment_type'], json_encode($store_type['data']));
    }

    public function test_fetch_assessment_type(){
        $token = $this->get_token();

        $this->postJson(route('assessmentType.store'), self::assessment_type_data(), ['authorization: Bearer '.$token])->json();
        $fetch_type = $this->getJson(route('assessmentType.index'), ['authorization: Bearer '.$token])->assertOk()->json();
        $this->assertEquals($fetch_type['status'], 'success');
        $this->assertStringContainsString(self::assessment_type_data()['assessment_scores'][0]['assessment_type'], json_encode($fetch_type['data']));
    }
}

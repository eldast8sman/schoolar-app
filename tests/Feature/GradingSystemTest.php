<?php

namespace Tests\Feature;

use App\Http\Controllers\FunctionController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GradingSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_grades(){
        $token = $this->get_token();
        
        $add_grade = $this->postJson(route('gradingSystem.post'), self::grading_data(), ['authorization: Bearer '.$token])->assertOk()->json();
        $this->assertEquals($add_grade['status'], 'success');
        $this->assertEquals($add_grade['data']['grade'], self::grading_data()['grade']);
        $this->assertDatabaseHas('grading_systems', ['remarks' => self::grading_data()['remarks']]);
    }

    public function test_load_default(){
        $token = $this->get_token();

        $add_grades = $this->getJson(route('gradingSystem.loadDefault'), ['authorization: Bearer '.$token])->assertOk()->json();
        $this->assertEquals($add_grades['status'], 'success');
        $this->assertDatabaseHas('grading_systems', ['remarks' => 'Excellent', 'grade' => 'A1']);
    }

    public function test_fetch_grades(){
        $token = $this->get_token();

        $this->getJson(route('gradingSystem.loadDefault'), ['authorization: Bearer '.$token])->json();
        $fetch_grades = $this->getJson(route('gradingSystem.index'), ['authorization: Bearer '.$token])->assertOk()->json();
        $this->assertEquals($fetch_grades['status'], 'success');
        $this->assertEquals(count($fetch_grades['data']), count(FunctionController::default_grading_systems()['secondary']));
    }

    public function test_show_grade(){
        $token = $this->get_token();

        $add_grade = $this->postJson(route('gradingSystem.post'), self::grading_data(), ['authorization: Bearer '.$token])->json();
        $fetch_grade = $this->getJson(route('gradingSystem.show', $add_grade['data']['uuid']), ['authorization: Bearer '.$token])->assertOk()->json();
        $this->assertEquals($fetch_grade['status'], 'success');
        $this->assertEquals($fetch_grade['data']['grade'], self::grading_data()['grade']);
    }

    public function test_update_grade(){
        $token = $this->get_token();

        $add_grade = $this->postJson(route('gradingSystem.post'), self::grading_data(), ['authorization: Bearer '.$token])->json();
        $update_grade = $this->putJson(route('gradingSystem.update', $add_grade['data']['uuid']), [
            'grade' => 'A+',
            'minimum' => 85,
            'maximum' => 100,
            'remarks' => 'Distinction'
        ], ['authorization: Bearer '.$token])->assertOk()->json();
        $this->assertEquals($update_grade['status'], 'success');
        $this->assertDatabaseHas('grading_systems', ['minimum' => 85]);
    }

    public function test_delete_grade(){
        $token = $this->get_token();

        $def_grade = $this->getJson(route('gradingSystem.loadDefault'), ['authorization: Bearer '.$token])->json();
        $del_grade = $this->deleteJson(route('gradingSystem.delete', $def_grade['data'][0]['uuid']), ['authorization: Bearer '.$token])->assertOk()->json();
        $this->assertEquals($del_grade['status'], 'success');
        $this->assertDatabaseMissing('grading_systems', ['uuid' => $def_grade['data'][0]['uuid'], 'minimum' => $def_grade['data'][0]['minimum']]);
    }
}

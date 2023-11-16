<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SchoolStudentTest extends TestCase
{
    use RefreshDatabase;

    public function test_add_school_student(){
        $token = $this->get_token();
        $class = $this->add_class($token);
        $data = self::student_data($class['data']['sub_classes'][0]['id']);

        $add_student = $this->postJson(route('schoolStudent.store'), $data, ['authorization: Bearer '.$token])->assertOk()->json();
        $this->assertEquals($add_student['status'], 'success');
        $this->assertDatabaseHas('students', ['first_name' => $data['first_name']]);
    }
}

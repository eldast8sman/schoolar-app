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

    public function test_add_student_health_info(){
        $token = $this->get_token();
        $student = $this->add_student($token);

        $data = [
            'weight' => 30,
            'weight_measurement' => 'Kg',
            'height' => '5.8',
            'height_measurement' => 'Ft',
            'blood_group' => 'O+',
            'genotype' => 'SS',
            'immunizations' => ['Cholera', 'Hepatitis', 'Polio'],
            'disabled' => true,
            'disability' => ['Blindness']
        ];

        $health_info = $this->postJson(route('schoolStudent.healthInfo.store', $student['data']['uuid']), $data, ['authorization: Bearer'.$token])->assertOk()->json();
        $this->assertEquals($health_info['status'], 'success');
        $this->assertDatabaseHas('student_health_infos', ['immunizations' => join(',', $data['immunizations'])]);
        $this->assertDatabaseHas('school_students', ['registration_stage' => 2]);
    }

    public function test_skip_student_health_info(){
        $token = $this->get_token();
        $student = $this->add_student($token);
        $skip = $this->getJson(route('schoolStudent.healthInfo.skip', $student['data']['uuid']), ['authorization: Bearer'.$token])->assertOk()->json();
        $this->assertEquals($skip['status'], 'success');
        $this->assertDatabaseHas('school_students', ['registration_stage' => 2]);
    }
}

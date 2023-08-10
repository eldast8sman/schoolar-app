<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SchoolTeacherTest extends TestCase
{
    use RefreshDatabase;

    public function test_add_school_teacher(){
        $token = $this->get_token();
        $class = $this->add_class($token);
        $data = self::teacher_data();
        $data['form_class'] = [
            'class_type' => 'sub_class',
            'class_id' => $class['data']['sub_classes'][0]['id']
        ];
        $add_teacher = $this->postJson(route('schoolTeacher.store'), $data, ['authorization: Bearer '.$token])->assertOk()->json();
        $this->assertEquals($add_teacher['status'], 'success');
        $this->assertDatabaseHas('teacher_certifications', ['certification' => $data['certifications'][0]['certification']]);
        $this->assertEquals(count($add_teacher['data']['certifications']), count($data['certifications']));
        $this->assertEquals($data['disk'], $add_teacher['data']['file_disk']);
        $this->assertEquals($add_teacher['data']['status'], 1);
    }

}

<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
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

    public function test_fetch_teachers(){
        $token = $this->get_token();
        $this->add_teacher($token);
        $teachers = $this->getJson(route('schoolTacher.index'), ['authorization: Bearer '.$token])->assertOk()->json();
        $this->assertEquals(count($teachers['data']['data']), 1);
        $this->assertEquals($teachers['status'], 'success');
        $this->assertEquals($teachers['data']['data'][0]['first_name'], self::teacher_data()['first_name']);
    }

    public function test_fetch_single_teacher(){
        $token = $this->get_token();
        $teacher = $this->add_teacher($token);
        $fetched = $this->getJson(route('schoolTeacher.show', $teacher['data']['id']), ['authorization: Bearer '.$token])->assertOk()->json();
        $this->assertEquals($fetched['data']['first_name'], $teacher['data']['first_name']);
        $this->assertEquals($fetched['status'], 'success');
    }

    public function test_update_teacher(){
        $token = $this->get_token();
        $teacher = $this->add_teacher($token);
        $data = self::teacher_data();
        unset($data['certifications']);
        $data['first_name'] = 'Updated Name';
        $data['last_name'] = 'Updated Last';
        $updated = $this->postJson(route('schoolTeacher.update', $teacher['data']['id']), $data, ['authorization: Bearer '.$token])->assertOk()->json();
        $this->assertEquals($updated['status'], 'success');
        $this->assertEquals($updated['data']['first_name'], $data['first_name']);
    }

    public function test_add_certification(){
        $token = $this->get_token();
        $teacher = $this->add_teacher($token);
        $cert_data = [
            'school_teacher_id' => $teacher['data']['id'],
            'certification' => 'Added Certification',
            'file' => UploadedFile::fake()->create('added_cert.png', 400, 'image/png')
        ];
        $certification = $this->postJson(route('certification.add'), $cert_data, ['authorization: Bearer '.$token])->assertOk()->json();
        $this->assertEquals($certification['status'], 'success');
        $this->assertDatabaseHas('teacher_certifications', ['certification' => 'Added Certification']);
        $this->assertEquals($certification['data']['school_teacher_id'], $teacher['data']['id']);
    }

    public function test_update_certification(){
        $token = $this->get_token();
        $teacher = $this->add_teacher($token);
        $id = $teacher['data']['certifications'][0]['id'];
        $data = [
            'certification' => 'Updated Certification',
            'file' => UploadedFile::fake()->create('updated_training_cert.pdf', 400, 'application/pdf')
        ];
        $updated = $this->postJson(route('certification.update', $id), $data, ['authorization: Bearer: '.$token])->assertOk()->json();
        $this->assertEquals($updated['data']['certification'], $data['certification']);
    }

    public function test_delete_certification(){
        $token = $this->get_token();
        $teacher = $this->add_teacher($token);
        $id = $teacher['data']['certifications'][0]['id'];
        $this->deleteJson(route('certification.delete', $id), [], ['authorization: Bearer: '.$token])->assertOk()->json();
        $this->assertDatabaseMissing('teacher_certifications', ['id' => $id]);
    }
}

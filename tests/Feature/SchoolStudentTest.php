<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SchoolStudentTest extends TestCase
{
    use RefreshDatabase;

    public function test_add_school_student(){
        $token = $this->get_token();
        $class = $this->add_class($token);
        $data = self::student_data($class['data']['sub_classes'][0]['id']);

        $add_student = $this->postJson(route('schoolStudent.store'), $data, $this->authorize($token))->assertOk()->json();
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

    public function test_add_new_parent(){
        $token = $this->get_token();
        $student = $this->add_student($token);
        $data = self::parent_data();
        $data['primary'] = true;
        $data['relationship'] = 'Father';

        $new_parent = $this->postJson(route('schoolStudent.newParent.add', $student['data']['uuid']), $data, ['authorization: Bearer'.$token])->assertOk()->json();
        $this->assertEquals($new_parent['status'], 'success');
        $this->assertDatabaseHas('school_parents', ['mobile' => $data['mobile']]);
        $this->assertDatabaseHas('parents', ['mobile' => $data['mobile']]);
    }

    public function test_store_existing_parent(){
        $token = $this->get_token();
        $student = $this->add_student($token);
        $data = self::parent_data();
        $data['primary'] = true;
        $data['relationship'] = 'Father';
        $parent = $this->postJson(route('schoolStudent.newParent.add', $student['data']['uuid']), $data, ['authorization: Bearer'.$token])->json();
        $parent_uuid = $parent['data']['parents'][0]['uuid'];

        $student_data =  [
            'first_name' => "FirstName1",
            'last_name' => 'LastName1',
            'middle_name' => 'MiddleName1',
            'mobile' => '08098787876',
            'email' => 'email@domain.org',
            'registration_id' => 'TST-2023-105',
            'sub_class_id' => $student['data']['sub_class_id'],
            'file' => UploadedFile::fake()->create('student_avatar.png', 300, 'image/png'),
            'gender' => 'Male',
            'dob' => '2010-10-01'
        ];
        $ano_student = $this->postJson(route('schoolStudent.store'), $student_data, ['authorization: Bearer'.$token])->json();
        $parent_data = [
            'parent_uuid' => $parent_uuid,
            'primary' => true,
            'relationship' => 'Father'
        ];
        $add_parent = $this->postJson(route('schoolStudent.newParent.existing', $ano_student['data']['uuid']), $parent_data, ['authorization: Bearer'.$token])->assertOk()->json();
        $this->assertEquals($add_parent['status'], 'success');
        $this->assertEquals($parent_uuid, $add_parent['data']['parents'][0]['uuid']);
    }

    public function test_skip_add_parent(){
        $token = $this->get_token();
        $student = $this->add_student($token);
        $skip = $this->getJson(route('schoolStudent.newParent.skip', $student['data']['uuid']), ['authorization: Bearer'.$token])->assertOk()->json();
        $this->assertEquals($skip['status'], 'success');
        $this->assertDatabaseHas('school_students', ['registration_stage' => 3]);
    }

    public function test_fetch_students(){
        $token = $this->get_token();
        $class = $this->add_class($token);
        $data = self::student_data($class['data']['sub_classes'][0]['id']);
        $this->postJson(route('schoolStudent.store'), $data, ['authorization: Bearer '.$token])->json();
        $students = $this->getJson(route('schoolStudent.index'), ['authorization: Bearer '.$token])->assertOk()->json();
        $this->assertEquals($students['status'], 'success');
        $this->assertEquals(count($students['data']['data']), 1);
        $this->assertEquals($students['data']['data'][0]['first_name'], $data['first_name']);
        $this->assertEquals($students['data']['data'][0]['class_level'], $class['data']['class_level']);
    }

    public function test_fetch_student(){
        $token = $this->get_token();
        $class = $this->add_class($token);
        $data = self::student_data($class['data']['sub_classes'][0]['id']);
        $added = $this->postJson(route('schoolStudent.store'), $data, ['authorization: Bearer '.$token])->json();
        $student = $this->getJson(route('schoolStudent.show', $added['data']['uuid']), ['authorization: Bearer '.$token])->assertOk()->json();
        $this->assertEquals($student['status'], 'success');
        $this->assertEquals($student['data']['first_name'], $data['first_name']);
        $this->assertEquals($student['data']['class_level'], $class['data']['class_level']);
    }
}

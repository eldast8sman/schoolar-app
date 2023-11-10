<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SubjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_load_default_subjects(){
        $token = $this->get_token();
        $subclass = $this->add_subclass($token);

        $load = $this->getJson(route('classes.subClass.loadSubject', $subclass['data']['id']), ['authorization: Bearer '.$token])->assertOk()->json();
        $this->assertEquals($load['status'], 'success');
        $this->assertDatabaseHas('subjects', ['sub_class_id' => $subclass['data']['id']]);
    }

    public function test_add_subject(){
        $token = $this->get_token();
        $subclass = $this->add_subclass($token);

        $data = [
            'name' => 'Test Subject',
            'compulsory' => true
        ];

        $subject = $this->postJson(route('classes.subClass.addSubject', $subclass['data']['id']), $data, ['authorization: Bearer '.$token])->assertOk()->json();
        $this->assertEquals($subject['status'], 'success');
        $this->assertEquals($subject['data']['name'], $data['name']);
    }

    public function test_add_multiple_subjects() : void
    {
        $token = $this->get_token();
        $subclass = $this->add_subclass($token);

        $data = [
            'subjects' => [
                [
                    'name' => 'Test Subject 1',
                    'compulsory' => true
                ],
                [
                    'name' => 'Test Subject 2',
                    'compulsory' => false
                ],
                [
                    'name' => 'Test Subject 3',
                    'compulsory' => true
                ],
                [
                    'name' => 'Test Subject 4',
                    'compulsory' => false
                ]
            ]
        ];

        $subjects = $this->postJson(route('classes.subClass.addMultipleSubject', $subclass['data']['id']), $data, ['authorization: Bearer '.$token])->assertOk()->json();
        $this->assertEquals($subjects['status'], 'success');
        $this->assertEquals(count($subjects['data']), count($data));
    }

    public function test_fetch_subjects_by_sub_class(){
        $token = $this->get_token();
        $subclass = $this->add_subclass($token);

        $data1 = [
            'name' => 'Test Subject 1',
            'compulsory' => true
        ];
        $this->postJson(route('classes.subClass.addSubject', $subclass['data']['id']), $data1, ['authorization: Bearer '.$token])->json();
        $data2 = [
            'name' => 'Test Subject 2',
            'compulsory' => true
        ];
        $this->postJson(route('classes.subClass.addSubject', $subclass['data']['id']), $data2, ['authorization: Bearer '.$token])->json();

        $subjects = $this->getJson(route('classes.subClass.fetchSubjects', $subclass['data']['id']), ['authorization: Bearer '.$token])->assertOk()->json();
        $this->assertEquals($subjects['status'], 'success');
        $this->assertEquals(count($subjects['data']), 2);
    }

    public function test_show_single_subject(){
        $token = $this->get_token();
        $subclass = $this->add_subclass($token);

        $data = [
            'name' => 'Test Subject 1',
            'compulsory' => true
        ];
        $added = $this->postJson(route('classes.subClass.addSubject', $subclass['data']['id']), $data, ['authorization: Bearer '.$token])->json();

        $subject = $this->getJson(route('subject.show', $added['data']['id']), ['authorization: Bearer '.$token])->assertOk()->json();
        $this->assertEquals($subject['status'], 'success');
        $this->assertEquals($subject['data']['name'], $data['name']);
    }

    public function test_update_subject(){
        $token = $this->get_token();
        $subclass = $this->add_subclass($token);

        $data = [
            'name' => 'Test Subject 1',
            'compulsory' => true
        ];
        $added = $this->postJson(route('classes.subClass.addSubject', $subclass['data']['id']), $data, ['authorization: Bearer '.$token])->json();

        $update_data = [
            'name' => 'Updated Subject',
            'compulsory' => true
        ];
        $updated = $this->putJson(route('subjects.update', $added['data']['id']), $update_data, ['authorization: Bearer '.$token])->assertOk()->json();
        $this->assertEquals($updated['status'], 'success');
        $this->assertEquals($updated['data']['name'], $update_data['name']);
        $this->assertDatabaseHas('subjects', ['name' => $update_data['name']]);
    }

    public function test_assign_primary_teacher(){
        $token = $this->get_token();
        $subclass = $this->add_subclass($token);

        $data = [
            'name' => 'Test Subject 1',
            'compulsory' => true
        ];
        $added = $this->postJson(route('classes.subClass.addSubject', $subclass['data']['id']), $data, ['authorization: Bearer '.$token])->json();
        $teacher = $this->postJson(route('schoolTeacher.store'), self::teacher_data(), ['authorization: Bearer '.$token])->json();
        $data = [
            'teacher_id' => $teacher['data']['id']
        ];
        $assign = $this->postJson(route('subject.assignPrimaryTeacher', $added['data']['id']), $data, ['authorization: Bearer '.$token])->assertOk()->json();
        $this->assertEquals($assign['status'], 'success');
    }

    public function test_assign_secondary_teacher(){
        $token = $this->get_token();
        $subclass = $this->add_subclass($token);

        $data = [
            'name' => 'Test Subject 1',
            'compulsory' => true
        ];
        $added = $this->postJson(route('classes.subClass.addSubject', $subclass['data']['id']), $data, ['authorization: Bearer '.$token])->json();
        $teacher = $this->postJson(route('schoolTeacher.store'), self::teacher_data(), ['authorization: Bearer '.$token])->json();
        $data = [
            'teacher_id' => $teacher['data']['id']
        ];
        $assign = $this->postJson(route('subject.assignSecondaryTeacher', $added['data']['id']), $data, ['authorization: Bearer '.$token])->assertOk()->json();
        $this->assertEquals($assign['status'], 'success');
    }
}

<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SchoolParentTest extends TestCase
{
    use RefreshDatabase;

    public function test_add_school_parent(){
        $token = $this->get_token();
        $data = self::parent_data();

        $add_parent = $this->postJson(route('schoolParent.store'), $data, $this->authorize($token))->assertOk()->json();
        $this->assertEquals($add_parent['status'], 'success');
        $this->assertDatabaseHas('parents', ['first_name' => $data['first_name']]);
        $this->assertDatabaseHas('parents', ['mobile' => $data['mobile']]);
    }

    public function test_fetch_parents(){
        $token = $this->get_token();
        $add_parent = $this->add_parent($token);
        $parent = $this->getJson(route('schoolParent.index'), $this->authorize($token))->assertOk()->json();
        $this->assertEquals($parent['status'], 'success');
        $this->assertEquals($parent['data']['data'][0]['first_name'], $add_parent['data']['first_name']);
    }

    public function test_fetch_single_parent(){
        $token = $this->get_token();
        $add_parent = $this->add_parent($token);
        $parent = $this->getJson(route('schoolParent.show', $add_parent['data']['uuid']), $this->authorize($token))->assertOk()->json();
        $this->assertEquals($parent['status'], 'success');
    }

    public function test_assign_student(){
        $token = $this->get_token();
        $parent = $this->add_parent($token);
        $student = $this->add_student($token);
        $data = [
            'student_uuid' => $student['data']['uuid'],
            'relationship' => 'Father',
            'primary' => true
        ];
        $assign = $this->postJson(route('schoolParent.assignStudent', $parent['data']['uuid']), $data, $this->authorize($token))->assertOk()->json();
        $this->assertEquals($assign['status'], 'success');
        $this->assertEquals($assign['data']['students'][0]['first_name'], $student['data']['first_name']);
    }
}

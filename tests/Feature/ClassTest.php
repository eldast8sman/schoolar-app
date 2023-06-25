<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ClassTest extends TestCase
{
    use RefreshDatabase;

    public $data = [
        'first_name' => 'First Name',
        'last_name' => 'Last Name',
        'email' => 'email@email.com',
        'school_name' => 'Test School',
        'school_type' => 'group',
        'country' => 'Nigeria',
        'state' => 'Lagos',
        'address' => 'Test Address',
        'password' => 'password',
        'password_confirmation' => 'password'
    ];

    public $classData = [
        'class_level' => 1,
        'name' => 'JSS 1',
        'sub_classes' => 5
    ];

    public function login_user($data){
        $user = $this->postJson(route('user.signup'), $data)->json();
        $token = $user['data']['authorization']['token'];
        return $token;
    }

    public function test_add_class(){
        $token = $this->login_user($this->data);

        $add_class = $this->postJson(route('classes.store'), $this->classData, ['authorization: Bearer '.$token])->assertOk()->json();
        $this->assertEquals($add_class['status'], 'success');
        $this->assertEquals($add_class['data']['name'], 'JSS 1');
        $this->assertEquals(count($add_class['data']['sub_classes']), 5);
    }

    public function add_class($token){
        return $this->postJson(route('classes.store'), $this->classData, ['authorization: Bearer '.$token])->json();
    }

    public function test_fetch_all_classes(){
        $token = $this->login_user($this->data);
        $this->add_class($token);
        $classes = $this->getJson(route('classes.index'), ['authorization: Bearer '.$token])->assertOk()->json();
        $this->assertEquals(count($classes['data']), 1);
        $this->assertEquals($classes['status'], 'success');
        $this->assertEquals($classes['data'][0]['name'], $this->classData['name']);
    }

    public function test_fetch_single_class(){
        $token = $this->login_user($this->data);
        $class = $this->add_class($token);
        $fetched = $this->getJson(route('classes.show', $class['data']['id']), ['authorization: Bearer '.$token])->assertOk()->json();
        $this->assertEquals($fetched['data']['name'], $this->classData['name']);
    }

    public function test_update_class(){
        $token = $this->login_user($this->data);
        $class = $this->add_class($token);
        $updateData = [
            'name' => 'JSS 2',
            'class_level' => 2
        ];
        $updated = $this->putJson(route('classes.update', $class['data']['id']), $updateData, ['authorization: Bearer '.$token])->assertOk()->json();
        $this->assertEquals($updated['status'], 'success');
        $this->assertNotEquals($updated['data']['name'], $class['data']['name']);
        $this->assertEquals($updated['data']['name'], $updateData['name']);
    }
}

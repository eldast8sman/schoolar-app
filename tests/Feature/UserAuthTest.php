<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserAuthTest extends TestCase
{
    use RefreshDatabase;

    public $data = [
        'first_name' => 'First Name',
        'last_name' => 'Last Name',
        'email' => 'email@email.com',
        'school_name' => 'Test School',
        'school_type' => 'Independent',
        'country' => 'Nigeria',
        'state' => 'Lagos',
        'password' => 'password',
        'password_confirmation' => 'password'
    ];

    public function test_user_signup(){
        $data = $this->data;
        $response = $this->postJson(route('user.signup'), $data)
                    ->assertOk()
                    ->json();

        $this->assertEquals($data['first_name'], $response['data']['first_name']);
        $this->assertDatabaseHas('users', ['first_name' => $data['first_name']]);
        $this->assertDatabaseHas('schools', ['name' => $data['school_name']]);
        $this->assertDatabaseHas('schools', ['id' => $response['data']['school_id']]);
        $this->assertDatabaseHas('school_locations', ['id' => $response['data']['school_location_id']]);
        $this->assertDatabaseHas('school_locations', ['school_id' => $response['data']['school_id']]);
        $this->assertDatabaseHas('user_schools', ['user_id' => $response['data']['id']]);
        $this->assertDatabaseHas('user_schools', ['school_id' => $response['data']['school_id']]);
        $this->assertDatabaseMissing('users', ['otp', '']);
    }

    public function test_user_details(){
        $data = $this->data;
        $user = $this->postJson(route('user.signup'), $data)->json();

        $response = $this->getJson(route('user_details'), ['authorization: Bearer '.$user['data']['authorization']['token'] ])->assertOk()->json();

        $this->assertEquals($response['data']['first_name'], $data['first_name']);
        $this->assertEquals($user['data']['school_id'], $response['data']['school_id']);
    }

    public function test_resend_verification_mail(){
        $data = $this->data;
        $user = $this->postJson(route('user.signup'), $data)->json();

        $response = $this->getJson(route('resend_user_otp'), ['authorization: Bearer '.$user['data']['authorization']['token']])->assertOk()->json();
        $this->assertEquals($response['status'], 'success');
    }
}

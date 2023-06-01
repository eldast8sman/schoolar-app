<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_signup(){
        //prepare
        $data = [
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

        $response = $this->postJson(route('user.signup'), $data)
                    ->assertOk()
                    ->json();
    }
}

<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserSchoolTest extends TestCase
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

    public function login_user($data){
        $user = $this->postJson(route('user.signup'), $data)->json();
        $token = $user['data']['authorization']['token'];
        return $token;
    }

    public function test_add_multiple_locations_to_school(){
        $data = $this->data;
        $token = $this->login_user($data);

        $location_data = ['locations' => "[{\"address\": \"Address 1\",\"town\": \"Town1\",\"lga\": \"Lga 1\",\"state\": \"State 1\",\"country\": \"Country 1\"},{\"address\": \"Address 2\",\"town\": \"Town2\",\"lga\": \"Lga 2\",\"state\": \"State 2\",\"country\": \"Country 2\"}]"];
        $response = $this->postJson(route('school.add_locations'), $location_data, ['authorization: Bearer '.$token])->assertOk()->json();

        $this->assertEquals($response['status'], 'success');
    }

    public function test_locations_cannot_be_added_to_independent_schools(){
        $data = $this->data;
        $data['school_type'] = 'independent';
        $token = $this->login_user($data);

        $location_data = ['locations' => "[{\"address\": \"Address 1\",\"town\": \"Town1\",\"lga\": \"Lga 1\",\"state\": \"State 1\",\"country\": \"Country 1\"},{\"address\": \"Address 2\",\"town\": \"Town2\",\"lga\": \"Lga 2\",\"state\": \"State 2\",\"country\": \"Country 2\"}]"];
        $response = $this->postJson(route('school.add_locations'), $location_data, ['authorization: Bearer '.$token])->assertStatus(409)->json();

        $this->assertEquals($response['status'], 'failed');
    }
}

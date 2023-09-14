<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserSchoolTest extends TestCase
{
    use RefreshDatabase;

    public function login_user($data){
        $user = $this->postJson(route('user.signup'), $data)->json();
        $token = $user['data']['authorization']['token'];
        return $token;
    }

    public function test_add_multiple_locations_to_school(){
        $data = self::user_data();
        $token = $this->login_user($data);

        $locations = self::locations();

        $location_data = ['locations' => $locations];
        $response = $this->postJson(route('school.add_locations'), $location_data, ['authorization: Bearer '.$token])->assertOk()->json();

        $this->assertEquals($response['status'], 'success');

        $user = $this->getJson(route('user_details'), ['authorization: Bearer '.$token])->json();
        $this->assertEquals(count($user['data']['schools'][0]['locations']), count($locations)+1);
    }

    public function test_locations_cannot_be_added_to_independent_schools(){
        $data = self::user_data();
        $data['school_type'] = 'independent';
        $token = $this->login_user($data);

        $location_data = ['locations' => "[{\"address\": \"Address 1\",\"town\": \"Town1\",\"lga\": \"Lga 1\",\"state\": \"State 1\",\"country\": \"Country 1\"},{\"address\": \"Address 2\",\"town\": \"Town2\",\"lga\": \"Lga 2\",\"state\": \"State 2\",\"country\": \"Country 2\"}]"];
        $response = $this->postJson(route('school.add_locations'), $location_data, ['authorization: Bearer '.$token])->assertStatus(409)->json();

        $this->assertEquals($response['status'], 'failed');
    }

    public function test_switch_school_location(){
        $data = self::user_data();
        $token = $this->login_user($data);

        $locations = self::locations();

        $location_data = ['locations' => $locations];
        $this->postJson(route('school.add_locations'), $location_data, ['authorization: Bearer '.$token])->json();
        $user = $this->getJson(route('user_details'), ['authorization: Bearer '.$token])->json();

        $change_location = $this->getJson(route('switch_location', $user['data']['schools'][0]['locations'][1]['id']), ['authorization: Bearer '.$token])->assertOk()->json();

        $this->assertEquals($change_location['status'], 'success');
        $this->assertEquals($change_location['data']['school_location_id'], $user['data']['schools'][0]['locations'][1]['id']);
    }
}

<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ClassTest extends TestCase
{
    use RefreshDatabase;

   public function login_user($data){
        $user = $this->postJson(route('user.signup'), $data)->json();
        $token = $user['data']['authorization']['token'];
        return $token;
    }

    public function test_add_class(){
        $token = $this->login_user(self::user_data());

        $add_class = $this->postJson(route('classes.store'), self::class_data(), ['authorization: Bearer '.$token])->assertOk()->json();
        $this->assertEquals($add_class['status'], 'success');
        $this->assertEquals($add_class['data']['name'], 'JSS 1');
        $this->assertEquals(count($add_class['data']['sub_classes']), 5);
    }

    public function add_class($token){
        return $this->postJson(route('classes.store'), self::class_data(), ['authorization: Bearer '.$token])->json();
    }

    public function test_fetch_all_classes(){
        $token = $this->login_user(self::user_data());
        $this->add_class($token);
        $classes = $this->getJson(route('classes.index'), ['authorization: Bearer '.$token])->assertOk()->json();
        $this->assertEquals(count($classes['data']), 1);
        $this->assertEquals($classes['status'], 'success');
        $this->assertEquals($classes['data'][0]['name'], self::class_data()['name']);
    }

    public function test_fetch_single_class(){
        $token = $this->login_user(self::user_data());
        $class = $this->add_class($token);
        $fetched = $this->getJson(route('classes.show', $class['data']['id']), ['authorization: Bearer '.$token])->assertOk()->json();
        $this->assertEquals($fetched['data']['name'], self::class_data()['name']);
    }

    public function test_update_class(){
        $token = $this->login_user(self::user_data());
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

    public function test_update_subClass(){
        $token = $this->login_user(self::user_data());
        $class = $this->add_class($token);
        $sub_class = $class['data']['sub_classes'][0];
        $updateData = [
            'name' => 'Promise'
        ];
        $updated = $this->putJson(route('classes.subClass.update', $sub_class['id']), $updateData, ['authorization: Bearer '.$token])->assertOk()->json();
        $this->assertEquals($updated['status'], 'success');
        $this->assertNotEquals($updated['data']['name'], $sub_class['name']);
        $this->assertEquals($updated['data']['name'], $updateData['name']);
    }

    public function test_delete_class(){
        $token = $this->login_user(self::user_data());
        $class = $this->add_class($token);
        $this->deleteJson(route('classes.delete', $class['data']['id']), [], ['authorization: Bearer '.$token])->assertOk();
        $this->assertDatabaseMissing('main_classes', ['name' => $class['data']['name'], 'id' => $class['data']['id']]);
        $this->assertDatabaseEmpty('sub_classes');
    }

    public function test_delete_subClass(){
        $token = $this->login_user(self::user_data());
        $class = $this->add_class($token);
        $sub_class = $class['data']['sub_classes'][0];
        $this->deleteJson(route('classes.subClass.delete', $sub_class['id']), [], ['authorization: Bearer '.$token]);
        $this->assertDatabaseMissing('sub_classes', ['name' => $sub_class['name'], 'id' => $sub_class['name']]);
        $new_class = $this->getJson(route('classes.show', $class['data']['id']), ['authorization: Bearer '.$token])->json();
        $this->assertNotEquals($new_class['data']['sub_classes'][0]['id'], $sub_class['id']);
    }

    public function add_other_locations($token){
        $location_data = ['locations' => json_encode(self::locations())];
        return $this->postJson(route('school.add_locations'), $location_data, ['authorization: Bearer '.$token])->json();
    }

    public function test_fetching_other_locations(){
        $token = $this->login_user(self::user_data());
        $this->add_other_locations($token);

        $locations = $this->getJson(route('other_locations'), ['authorization: Bearer '.$token])->assertOk()->json();
        $this->assertEquals($locations['status'], 'success');
        $this->assertEquals(count($locations['data']), count(self::locations()));
    }
}

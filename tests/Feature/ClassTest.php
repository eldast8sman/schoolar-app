<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ClassTest extends TestCase
{
    use RefreshDatabase;

    public function test_add_class(){
        $token = $this->get_token();

        $add_class = $this->postJson(route('classes.store'), self::class_data(), ['authorization: Bearer '.$token])->assertOk()->json();
        $this->assertEquals($add_class['status'], 'success');
        $this->assertEquals($add_class['data']['name'], 'JSS 1');
        $this->assertEquals(count($add_class['data']['sub_classes']), 5);
    }

    public function test_fetch_all_classes(){
        $token = $this->get_token();
        $this->add_class($token);
        $classes = $this->getJson(route('classes.index'), ['authorization: Bearer '.$token])->assertOk()->json();
        $this->assertEquals(count($classes['data']), 1);
        $this->assertEquals($classes['status'], 'success');
        $this->assertEquals($classes['data'][0]['name'], self::class_data()['name']);
    }

    public function test_fetch_single_class(){
        $token = $this->get_token();
        $class = $this->add_class($token);
        $fetched = $this->getJson(route('classes.show', $class['data']['id']), ['authorization: Bearer '.$token])->assertOk()->json();
        $this->assertEquals($fetched['data']['name'], self::class_data()['name']);
    }

    public function test_update_class(){
        $token = $this->get_token();
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
        $token = $this->get_token();
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

    public function test_sort_class_levels(){
        $token = $this->get_token();

        $class1 = [
            'class_level' => 1,
            'name' => 'JSS 1',
            'sub_classes' => 5
        ];
        $class_one = $this->postJson(route('classes.store'), $class1, ['authorization: Bearer '.$token])->assertOk()->json();

        $class2 = [
            'class_level' => 2,
            'name' => 'JSS 2',
            'sub_classes' => 5
        ];
        $class_two = $this->postJson(route('classes.store'), $class2, ['authorization: Bearer '.$token])->assertOk()->json();

        $class3 = [
            'class_level' => 3,
            'name' => 'SSS 2',
            'sub_classes' => 5
        ];
        $class_three = $this->postJson(route('classes.store'), $class3, ['authorization: Bearer '.$token])->assertOk()->json();

        $class4 = [
            'class_level' => 4,
            'name' => 'SSS 1',
            'sub_classes' => 5
        ];
        $class_four = $this->postJson(route('classes.store'), $class4, ['authorization: Bearer '.$token])->assertOk()->json();

        $data = [
            "classes" => [
                $class_one['data']['id'],
                $class_two['data']['id'],
                $class_four['data']['id'],
                $class_three['data']['id']
            ]
        ];

        $this->postJson(route('classes.sortClassLevel'), $data, ['authorization: Bearer '.$token])->assertOk();
    }

    public function test_delete_class(){
        $token = $this->get_token();
        $class = $this->add_class($token);
        $this->deleteJson(route('classes.delete', $class['data']['id']), [], ['authorization: Bearer '.$token])->assertOk();
        $this->assertDatabaseMissing('main_classes', ['name' => $class['data']['name'], 'id' => $class['data']['id']]);
        $this->assertDatabaseEmpty('sub_classes');
    }

    public function test_delete_subClass(){
        $token = $this->get_token();
        $class = $this->add_class($token);
        $sub_class = $class['data']['sub_classes'][0];
        $this->deleteJson(route('classes.subClass.delete', $sub_class['id']), [], ['authorization: Bearer '.$token]);
        $this->assertDatabaseMissing('sub_classes', ['name' => $sub_class['name'], 'id' => $sub_class['name']]);
        $new_class = $this->getJson(route('classes.show', $class['data']['id']), ['authorization: Bearer '.$token])->json();
        $this->assertNotEquals($new_class['data']['sub_classes'][0]['id'], $sub_class['id']);
    }

    public function add_other_locations($token){
        $location_data = ['locations' => self::locations()];
        return $this->postJson(route('school.add_locations'), $location_data, ['authorization: Bearer '.$token])->json();
    }

    public function test_fetching_other_locations(){
        $token = $this->get_token();
        $this->add_other_locations($token);

        $locations = $this->getJson(route('other_locations'), ['authorization: Bearer '.$token])->assertOk()->json();
        $this->assertEquals($locations['status'], 'success');
        $this->assertEquals(count($locations['data']), count(self::locations()));
    }

    public function test_add_sub_class(){
        $token = $this->get_token();
        $class = $this->add_class($token);
        $data = [
            'name' => 'Added SubClass',
            'type' => 'general',
            'load_default' => true
        ];

        $subclass = $this->postJson(route('classes.subClass.store', $class['data']['id']), $data, ['authorization: Bearer '.$token])->assertOk()->json();
        $this->assertEquals($subclass['status'], 'success');
    }

    public function test_show_subClass(){
        $token = $this->get_token();
        $subclass = $this->add_subclass($token);

        $fetched = $this->getJson(route('subClass.show', $subclass['data']['id']), ['authorization: Bearer '.$token])->assertOk()->json();
        $this->assertEquals($fetched['status'], 'success');
        $this->assertEquals($fetched['data']['name'], $subclass['data']['name']);
    } 

    public function test_assign_teacher_to_sub_class(){
        $token = $this->get_token();
        $subclass = $this->add_subclass($token);
        $t_data = self::teacher_data();
        $teacher = $this->postJson(route('schoolTeacher.store'), $t_data, ['authorization: Bearer '.$token])->json();

        $data = [
            'teacher_id' => $teacher['data']['id']
        ];

        $assign = $this->postJson(route('classes.subClass.assignTeacher', $subclass['data']['id']), $data, ['authorization: Bearer '.$token])->assertOk()->json();
        $this->assertEquals($assign['status'], 'success');

        $fetched = $this->getJson(route('subClass.show', $subclass['data']['id']), ['authorization: Bearer '.$token])->json();
        $this->assertEquals($teacher['data']['id'], $fetched['data']['teacher_id']);
    }

    public function test_load_default_classes(){
        $token = $this->get_token();
        $data = [
            'load_subjects' => true
        ];

        $load = $this->postJson(route('classes.loadDefault'), $data, ['authorization: Bearer '.$token])->assertOk()->json();
        $this->assertEquals($load['status'], 'success');
    }
}

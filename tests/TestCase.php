<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function setup(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
    }

    public static function locations(){
        $locations = [
            [
                "address" => "Address 1",
                "town" => "Town1",
                "lga" => "Lga 1",
                "state" => "State 1",
                "country" => "Country 1"
            ],
            [
                "address" => "Address 2",
                "town" => "Town2",
                "lga" => "Lga 2",
                "state" => "State 2",
                "country" => "Country 2"
            ]
        ];

        return $locations;
    }

    public static function user_data(){
        return [
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
    }

    public static function class_data(){
        return [
            'class_level' => 1,
            'name' => 'JSS 1',
            'sub_classes' => 5
        ];
    }
}

<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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
                "location_type" => "secondary",
                "syllabus" => "waec",
                "address" => "Address 1",
                "town" => "Town1",
                "lga" => "Lga 1",
                "state" => "State 1",
                "country" => "Country 1",
                "load_default" => true
            ],
            [
                "location_type" => "secondary",
                "syllabus" => "waec",
                "address" => "Address 2",
                "town" => "Town2",
                "lga" => "Lga 2",
                "state" => "State 2",
                "country" => "Country 2",
                "load_default" => true
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
            "location_type" => "secondary",
            "syllabus" => "waec",
            'country' => 'Nigeria',
            'state' => 'Lagos',
            'address' => 'Test Address',
            'password' => 'password',
            'password_confirmation' => 'password',
            'load_default' => false
        ];
    }

    public static function class_data(){
        return [
            'class_level' => 1,
            'name' => 'JSS 1',
            'sub_classes' => 5
        ];
    }

    public static function teacher_data(){
        return [
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'email' => 'email@hos.com',
            'mobile' => '08012345678',
            'file' => UploadedFile::fake()->create('teacher_avatar.png', 300, 'image/png'),
            'disk' => 'public',
            'certifications' => [
                [
                    'certification' => 'Teacher Training School Certificate',
                    'file' => UploadedFile::fake()->create('training_cert.pdf', 400, 'application/pdf')
                ],
                [
                    'certification' => 'Another Certification',
                    'file' => UploadedFile::fake()->create('another_cert.png', 300, 'image/png')
                ]
            ]
        ];
    }

    public function get_token(){
        $user = $this->postJson(route('user.signup'), self::user_data())->json();
        $token = $user['data']['authorization']['token'];
        return $token;
    }

    public function add_class($token){
        return $this->postJson(route('classes.store'), self::class_data(), ['authorization: Bearer '.$token])->json();
    }

    public function add_teacher($token){
        $class = $this->add_class($token);
        $data = self::teacher_data();
        $data['form_class'] = [
            'class_type' => 'sub_class',
            'class_id' => $class['data']['sub_classes'][0]['id']
        ];
        $add_teacher = $this->postJson(route('schoolTeacher.store'), $data, ['authorization: Bearer '.$token])->json();
        return $add_teacher;
    }
}

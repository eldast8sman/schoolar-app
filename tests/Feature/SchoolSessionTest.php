<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SchoolSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_add_session(){
        $token = $this->get_token();

        $add_session = $this->postJson(route('schoolSession.store'), self::sessioin_data(), ['authorization: Bearer '.$token])->assertOk()->json();
        $this->assertEquals($add_session['status'], 'success');
        $this->assertEquals($add_session['data']['session_name'], self::sessioin_data()['session_name']);
        $this->assertDatabaseHas('school_terms', ['position' => 1]);
    }

    public function test_add_term(){
        $token = $this->get_token();
        $session_data = self::sessioin_data();
        $session_data['load_default'] = false;

        $add_session = $this->postJson(route('schoolSession.store'), $session_data, ['authorization: Bearer '.$token])->json();

        $add_term = $this->postJson(route('schoolSession.schoolTerm.store', $add_session['data']['uuid']), self::term_data(), ['authorization: Bearer'.$token])->assertOk()->json();
        $this->assertEquals($add_term['status'], 'success');
        $this->assertEquals($add_term['data']['term_name'], self::term_data()['term_name']);
    }

    public function test_fetch_sessions(){
        $token = $this->get_token();
        $session_data = self::sessioin_data();
        
        $add_session = $this->postJson(route('schoolSession.store'), $session_data, ['authorization: Bearer '.$token])->json();
        $fetch_session = $this->getJson(route('schoolSession.index'), ['authorization: Bearer '.$token])->assertOk()->json();
        $this->assertEquals($add_session['status'], 'success');
    }
    
    public function test_update_session(){
        $token = $this->get_token();
        $session = $this->add_session($token);
        $new_data = [
            'session_name' => '2024/2025 Session',
            'start_date' => '2024-09-15',
            'end_date' => '2025-09-14',
            'status' => 0
        ];

        $update = $this->putJson(route('schoolSession.update', $session['data']['uuid']), $new_data, ['authorization: Bearer: '.$token])->assertOk()->json();
        $this->assertEquals($update['status'], 'success');
        $this->assertGreaterThan(count($update['data']['terms']), 3);
    }

    public function test_update_term(){
        $token = $this->get_token();
        $session = $this->add_session($token);

        $new_data = [
            'term_name' => 'Firsting Term',
            'start_date' => '2024-09-10',
            'end_date' => '2024-12-12',
            'status' => 0,
            'position' => 1
        ];
        $uuid = $session['data']['terms'][0]['uuid'];

        $update = $this->putJson(route('schoolTerm.update', $uuid), $new_data, ['authorization: Bearer '.$token])->assertOk()->json();
        $this->assertEquals($update['status'], 'success');
        $this->assertDatabaseHas('school_terms', ['term_name' => $new_data['term_name']]);
        $this->assertDatabaseHas('school_terms', ['start_date' => $new_data['start_date']]);
    }

    public function test_delete_session(){
        $token = $this->get_token();
        $session = $this->add_session($token);

        $this->deleteJson(route('schoolSession.delete', $session['data']['uuid']), [], ['authorization: Bearer '.$token])->assertOk()->json();
    }
}
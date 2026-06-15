<?php
namespace Tests\Feature;
use Illuminate\Foundation\Testing\RefreshDatabase; use Tests\TestCase;
class AuthTest extends TestCase { use RefreshDatabase; public function test_user_can_register_and_receive_token(): void { $response = $this->postJson('/api/v1/register', ['name'=>'Nikola','email'=>'nikola@example.com','password'=>'password123','password_confirmation'=>'password123']); $response->assertCreated()->assertJsonStructure(['token','user'=>['id','name','email']]); $this->assertDatabaseHas('users',['email'=>'nikola@example.com']); } public function test_login_rejects_bad_credentials(): void { $this->postJson('/api/v1/login',['email'=>'missing@example.com','password'=>'wrong'])->assertStatus(422); } }

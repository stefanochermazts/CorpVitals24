<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\Company;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'manager']);
        Role::create(['name' => 'viewer']);
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_with_valid_credentials(): void
    {
        $team = Team::factory()->create();
        $company = Company::factory()->create(['team_id' => $team->id]);
        
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'team_id' => $team->id,
            'company_id' => $company->id,
        ]);
        
        $user->assignRole('manager');

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/dashboard');
    }

    public function test_users_cannot_authenticate_with_invalid_password(): void
    {
        $team = Team::factory()->create();
        $company = Company::factory()->create(['team_id' => $team->id]);
        
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'team_id' => $team->id,
            'company_id' => $company->id,
        ]);

        $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_remember_me_functionality_works(): void
    {
        $team = Team::factory()->create();
        $company = Company::factory()->create(['team_id' => $team->id]);
        
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'team_id' => $team->id,
            'company_id' => $company->id,
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
            'remember' => true,
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/dashboard');
        
        // Verify remember token was set
        $this->assertNotNull($user->fresh()->remember_token);
    }

    public function test_authenticated_users_cannot_access_login_page(): void
    {
        $team = Team::factory()->create();
        $company = Company::factory()->create(['team_id' => $team->id]);
        
        $user = User::factory()->create([
            'team_id' => $team->id,
            'company_id' => $company->id,
        ]);

        $response = $this->actingAs($user)->get('/login');

        $response->assertRedirect('/dashboard');
    }

    public function test_logout_functionality_works(): void
    {
        $team = Team::factory()->create();
        $company = Company::factory()->create(['team_id' => $team->id]);
        
        $user = User::factory()->create([
            'team_id' => $team->id,
            'company_id' => $company->id,
        ]);

        $this->actingAs($user);

        $response = $this->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    public function test_login_rate_limiting_works(): void
    {
        $team = Team::factory()->create();
        $company = Company::factory()->create(['team_id' => $team->id]);
        
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'team_id' => $team->id,
            'company_id' => $company->id,
        ]);

        // Attempt login 6 times with wrong password
        for ($i = 0; $i < 6; $i++) {
            $this->post('/login', [
                'email' => 'test@example.com',
                'password' => 'wrong-password',
            ]);
        }

        // 6th attempt should be rate limited
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString(
            'troppi',
            strtolower($response->session()->get('errors')->first('email'))
        );
    }

    public function test_login_validation_works(): void
    {
        $response = $this->post('/login', [
            'email' => 'not-an-email',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['email', 'password']);
    }
}


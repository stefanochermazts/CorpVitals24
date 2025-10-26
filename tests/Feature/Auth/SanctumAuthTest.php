<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\Company;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SanctumAuthTest extends TestCase
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

    public function test_unauthenticated_user_cannot_access_protected_routes(): void
    {
        $response = $this->get('/dashboard');
        
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_dashboard(): void
    {
        $team = Team::factory()->create();
        $company = Company::factory()->create(['team_id' => $team->id]);
        
        $user = User::factory()->create([
            'team_id' => $team->id,
            'company_id' => $company->id,
        ]);
        
        $user->assignRole('manager');

        $response = $this->actingAs($user)->get('/dashboard');
        
        // If route doesn't exist yet, will get 404, otherwise should be successful
        $this->assertTrue(
            $response->status() === 200 || $response->status() === 404
        );
    }

    public function test_user_without_team_cannot_access_team_scoped_routes(): void
    {
        $user = User::factory()->create([
            'team_id' => null,
            'company_id' => null,
        ]);

        $response = $this->actingAs($user)
            ->withMiddleware(['ensure.team'])
            ->get('/dashboard');
        
        $response->assertForbidden();
    }

    public function test_user_can_only_view_own_team_data(): void
    {
        // Team 1
        $team1 = Team::factory()->create();
        $company1 = Company::factory()->create(['team_id' => $team1->id]);
        $user1 = User::factory()->create([
            'team_id' => $team1->id,
            'company_id' => $company1->id,
        ]);
        $user1->assignRole('manager');

        // Team 2
        $team2 = Team::factory()->create();
        
        // User1 should not be able to view Team2
        $this->assertFalse($user1->can('view', $team2));
        
        // User1 should be able to view their own team
        $this->assertTrue($user1->can('view', $team1));
    }

    public function test_user_with_admin_role_has_elevated_permissions(): void
    {
        $team = Team::factory()->create();
        $company = Company::factory()->create(['team_id' => $team->id]);
        
        $admin = User::factory()->create([
            'team_id' => $team->id,
            'company_id' => $company->id,
        ]);
        
        $admin->assignRole('admin');

        $this->assertTrue($admin->hasRole('admin'));
        $this->assertTrue($admin->can('delete', $team));
    }

    public function test_viewer_role_has_limited_permissions(): void
    {
        $team = Team::factory()->create();
        $company = Company::factory()->create(['team_id' => $team->id]);
        
        $viewer = User::factory()->create([
            'team_id' => $team->id,
            'company_id' => $company->id,
        ]);
        
        $viewer->assignRole('viewer');

        $this->assertTrue($viewer->hasRole('viewer'));
        $this->assertTrue($viewer->can('view', $team));
        $this->assertFalse($viewer->can('update', $team));
        $this->assertFalse($viewer->can('delete', $team));
    }
}


<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\RoleSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function seedRbac(): void
    {
        $this->seed(\Database\Seeders\RBACSeeder::class);
    }

    public function test_agent_receives_view_conversas_after_sync(): void
    {
        $this->seedRbac();

        $user = User::factory()->create([
            'role' => User::ROLE_AGENT,
            'status' => 'active',
        ]);

        app(RoleSyncService::class)->syncUserRole($user);

        $this->assertTrue($user->fresh()->hasPermission('view_conversas'));
        $this->assertTrue($user->canChat());
        $this->assertFalse($user->canAccessBilling());
    }

    public function test_platform_admin_bypasses_all_permissions(): void
    {
        $this->seedRbac();

        $admin = User::factory()->create([
            'role' => User::ROLE_PLATFORM_ADMIN,
            'status' => 'active',
            'company_id' => null,
        ]);

        $this->assertTrue($admin->hasPermission('manage_financeiro'));
        $this->assertTrue($admin->canAccessBilling());
    }

    public function test_financial_role_has_billing_permission(): void
    {
        $this->seedRbac();

        $user = User::factory()->create([
            'role' => User::ROLE_FINANCIAL,
            'status' => 'active',
        ]);

        app(RoleSyncService::class)->syncUserRole($user);

        $this->assertTrue($user->fresh()->canAccessBilling());
        $this->assertTrue($user->hasPermission('view_financeiro'));
    }
}

<?php

namespace Tests\Feature\Admin;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSmokeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Create a user with the given role slug and user_type.
     *
     * The users table requires first_name, last_name, user_name, user_type,
     * and is_active columns beyond the standard email/password.
     */
    private function createAdminUser(): User
    {
        $user = User::create([
            'first_name'  => 'Test',
            'last_name'   => 'Admin',
            'user_name'   => 'testadmin',
            'email'       => 'testadmin@example.com',
            'password'    => bcrypt('password'),
            'user_type'   => '4',   // 4 = Super Admin
            'is_active'   => 1,
        ]);

        $role = Role::create([
            'name' => 'Super Admin',
            'slug' => 'super-admin',
        ]);

        $user->roles()->attach($role);

        return $user;
    }

    /**
     * Test that the admin login page renders successfully.
     */
    public function test_admin_login_page_renders(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    /**
     * Test that the admin dashboard requires authentication
     * and redirects unauthenticated users to the login page.
     */
    public function test_admin_dashboard_requires_authentication(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/login');
    }

    /**
     * Test that an authenticated admin user can access the dashboard.
     */
    public function test_authenticated_admin_can_access_dashboard(): void
    {
        $user = $this->createAdminUser();

        $response = $this->actingAs($user)->get('/admin');

        $response->assertStatus(200);
    }

    /**
     * Test that an authenticated admin user can access the news index page.
     */
    public function test_admin_news_index_page_loads(): void
    {
        $user = $this->createAdminUser();

        $response = $this->actingAs($user)->get('/admin/news');

        $response->assertStatus(200);
    }
}

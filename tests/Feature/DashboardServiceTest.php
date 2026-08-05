<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Driver;
use App\Models\User;
use App\Services\Admin\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_data_only_includes_real_driver_profiles(): void
    {
        $approvedUser = User::create([
            'name' => 'Approved Driver',
            'email' => 'approved@example.com',
            'phone' => '1111111111',
            'password' => bcrypt('secret'),
            'role' => UserRole::DRIVER,
            'status' => UserStatus::APPROVED,
        ]);

        Driver::create([
            'user_id' => $approvedUser->id,
            'cnic' => '1111',
            'license_number' => 'LIC-1',
            'license_expiry' => now()->addYear(),
            'profile_photo' => 'drivers/profile/test.jpg',
            'cnic_front' => 'drivers/cnic/test.jpg',
            'cnic_back' => 'drivers/cnic/test.jpg',
            'license_front' => 'drivers/license/test.jpg',
            'license_back' => 'drivers/license/test.jpg',
        ]);

        $pendingUser = User::create([
            'name' => 'Pending Driver',
            'email' => 'pending@example.com',
            'phone' => '2222222222',
            'password' => bcrypt('secret'),
            'role' => UserRole::DRIVER,
            'status' => UserStatus::PENDING,
        ]);

        Driver::create([
            'user_id' => $pendingUser->id,
            'cnic' => '2222',
            'license_number' => 'LIC-2',
            'license_expiry' => now()->addYear(),
            'profile_photo' => 'drivers/profile/test.jpg',
            'cnic_front' => 'drivers/cnic/test.jpg',
            'cnic_back' => 'drivers/cnic/test.jpg',
            'license_front' => 'drivers/license/test.jpg',
            'license_back' => 'drivers/license/test.jpg',
        ]);

        User::create([
            'name' => 'Orphan Driver',
            'email' => 'orphan@example.com',
            'phone' => '3333333333',
            'password' => bcrypt('secret'),
            'role' => UserRole::DRIVER,
            'status' => UserStatus::PENDING,
        ]);

        $service = new DashboardService();
        $data = $service->getDashboardData();

        $this->assertSame(2, $data['totalDrivers']);
        $this->assertSame(1, $data['approvedDrivers']);
        $this->assertSame(1, $data['pendingDrivers']);
        $this->assertSame(0, $data['rejectedDrivers']);
        $this->assertCount(2, $data['recentDrivers']);
        $this->assertNotContains('orphan@example.com', $data['recentDrivers']->pluck('email'));
    }
}

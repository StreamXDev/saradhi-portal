<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $permissions = [

            [
                'group_name' => 'dashboard',
                'permissions' => [
                    'dashboard.view',
                    'dashboard.edit',
                ]
            ],
            [
                'group_name' => 'admin',
                'permissions' => [
                    // admin Permissions
                    'admin.create',
                    'admin.view',
                    'admin.edit',
                    'admin.delete',
                    'admin.approve',
                ]
            ],
            [
                'group_name' => 'role',
                'permissions' => [
                    // role Permissions
                    'role.create',
                    'role.view',
                    'role.edit',
                    'role.delete',
                    'role.approve',
                ]
            ],
            [
                'group_name' => 'profile',
                'permissions' => [
                    // profile Permissions
                    'profile.view',
                    'profile.edit',
                    'profile.delete',
                    'profile.update',
                ]
            ],
            [
                'group_name' => 'membership_request',
                'permissions' => [
                    'membership_request.verification.show',
                    'membership_request.verification.verify',
                    'membership_request.review.show',
                    'membership_request.review.review',
                    'membership_request.approval.show',
                    'membership_request.approval.approve',
                    'membership_request.confirm',
                ]
            ],
            [
                'group_name' => 'user',
                'permissions' => [
                    // user Permissions
                    'user.create',
                    'user.view',
                    'user.edit',
                    'user.delete',
                    'user.approve',
                ]
            ],
        ];

        $admin = User::where('username', 'superadmin')->first();
        $roleSuperAdmin = $this->maybeCreateSuperAdminRole($admin);
        $this->createMemberRoles();

        // Create and Assign Permissions
        for ($i = 0; $i < count($permissions); $i++) {
            $permissionGroup = $permissions[$i]['group_name'];
            for ($j = 0; $j < count($permissions[$i]['permissions']); $j++) {
                $permissionExist = Permission::where('name', $permissions[$i]['permissions'][$j])->first();
                if (is_null($permissionExist)) {
                    $permission = Permission::create(
                        [
                            'name' => $permissions[$i]['permissions'][$j],
                            'group_name' => $permissionGroup,
                            'guard_name' => 'web'
                        ]
                    );
                    $roleSuperAdmin->givePermissionTo($permission);
                    $permission->assignRole($roleSuperAdmin);
                }
            }
        }

        // Assign super admin role permission to superadmin user
        if ($admin) {
            $admin->assignRole($roleSuperAdmin);
        }
    }

    private function createMemberRoles()
    {
        $memberRole = Role::where('name', 'member')->where('guard_name', 'web')->first();

        if(is_null($memberRole)){
            $memberRole = Role::create(['name' => 'member', 'guard_name' => 'web']);
        }
        
        $treasurerRole = Role::where('name', 'treasurer')->where('guard_name', 'web')->first();
        if(is_null($treasurerRole)){
            $treasurerRole = Role::create(['name' => 'treasurer', 'guard_name' => 'web']);
        }

        $secretaryRole = Role::where('name', 'secretary')->where('guard_name', 'web')->first();
        if(is_null($secretaryRole)){
            $secretaryRole = Role::create(['name' => 'secretary', 'guard_name' => 'web']);
        }

        $presidentRole = Role::where('name', 'president')->where('guard_name', 'web')->first();
        if(is_null($presidentRole)){
            $presidentRole = Role::create(['name' => 'president', 'guard_name' => 'web']);
        }

        return true;
    }

    private function maybeCreateSuperAdminRole($admin): Role
    {
        if (is_null($admin)) {
            $roleSuperAdmin = Role::create(['name' => 'superadmin', 'guard_name' => 'web']);
        } else {
            $roleSuperAdmin = Role::where('name', 'superadmin')->where('guard_name', 'web')->first();
        }

        if (is_null($roleSuperAdmin)) {
            $roleSuperAdmin = Role::create(['name' => 'superadmin', 'guard_name' => 'web']);
        }

        return $roleSuperAdmin;
    }
}

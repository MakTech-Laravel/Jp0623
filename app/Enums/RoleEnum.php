<?php

namespace App\Enums;

enum RoleEnum: string
{
    case SUPER_ADMIN = 'super-admin';
    case ADMIN = 'admin';
    case EDITOR = 'editor';
    case AUTHOR = 'author';
    case VIEWER = 'viewer';
    case USER = 'user';

    // -------------------------------------------------------------------------
    // guard() — guard name for this role.
    // Must match PermissionEnum::guard() and config/permission.php guard_name.
    // -------------------------------------------------------------------------
    public function guard(): string
    {
        return GuardEnum::WEB->value; // change to 'api' for Passport
    }

    // -------------------------------------------------------------------------
    // permissions() — which Permissions cases this role receives.
    //
    // super-admin returns [] intentionally — Gate::before grants everything.
    // Assigning all permissions to super-admin would bloat the pivot table.
    //
    // Add or remove cases here when role permissions change.
    // Re-run: php artisan db:seed --class=RoleSeeder
    // -------------------------------------------------------------------------
    public function permissions(): array
    {
        return match ($this) {
            // Gate::before grants everything (admin excludes roles.* / permissions.*).
            self::SUPER_ADMIN => [],
            self::ADMIN => [],

            self::EDITOR => [
                PermissionEnum::DASHBOARD_VIEW,
                PermissionEnum::POSTS_VIEW,
                PermissionEnum::POSTS_CREATE,
                PermissionEnum::POSTS_EDIT,
                PermissionEnum::POSTS_PUBLISH,
                PermissionEnum::FILE_UPLOAD_INDEX,
                PermissionEnum::FILE_UPLOAD_STORE,
            ],

            self::AUTHOR => [
                PermissionEnum::DASHBOARD_VIEW,
                PermissionEnum::POSTS_VIEW,
                PermissionEnum::POSTS_CREATE,
                PermissionEnum::POSTS_EDIT,
            ],

            self::VIEWER => [
                PermissionEnum::DASHBOARD_VIEW,
                PermissionEnum::POSTS_VIEW,
            ],

            self::USER => [
                PermissionEnum::DASHBOARD_VIEW,
                PermissionEnum::POSTS_VIEW,
            ],
        };
    }
}

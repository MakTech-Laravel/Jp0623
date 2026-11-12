<?php

namespace App\Enums;

enum PermissionEnum: string
{
    // DASHBOARD
    case DASHBOARD_VIEW = 'dashboard.view';
    case REPOSITORY_VIEW = 'repository.view';
    case DOCUMENTATION_VIEW = 'documentation.view';

        // FILE UPLOAD
    case FILE_UPLOAD_INDEX = 'file-upload.index';
    case FILE_UPLOAD_STORE = 'file-upload.store';

        // POSTS
    case POSTS_INDEX = 'posts.index';
    case POSTS_VIEW = 'posts.view';
    case POSTS_CREATE = 'posts.create';
    case POSTS_EDIT = 'posts.edit';
    case POSTS_DELETE = 'posts.delete';
    case POSTS_PUBLISH = 'posts.publish';

        // USERS
    case USERS_INDEX = 'users.index';
    case USERS_VIEW = 'users.view';
    case USERS_CREATE = 'users.create';
    case USERS_EDIT = 'users.edit';
    case USERS_DELETE = 'users.delete';

        // ROLES
    case ROLES_INDEX = 'roles.index';
    case ROLES_VIEW = 'roles.view';
    case ROLES_CREATE = 'roles.create';
    case ROLES_EDIT = 'roles.edit';
    case ROLES_DELETE = 'roles.delete';

        // PERMISSIONS (developer-defined, read-only — listing & export only)
    case PERMISSIONS_INDEX = 'permissions.index';
    case PERMISSIONS_EXPORT = 'permissions.export';

        // SETTINGS
    case SETTINGS_INDEX = 'settings.index';
    case SETTINGS_VIEW = 'settings.view';
    case SETTINGS_CREATE = 'settings.create';
    case SETTINGS_EDIT = 'settings.edit';
    case SETTINGS_DELETE = 'settings.delete';
    case SETTINGS_IMPORT = 'settings.import';
    case SETTINGS_EXPORT = 'settings.export';
    case SETTINGS_PRINT = 'settings.print';

        // MACHINE CATEGORIES
    case MACHINE_CATEGORIES_INDEX = 'machine-categories.index';
    case MACHINE_CATEGORIES_VIEW = 'machine-categories.view';
    case MACHINE_CATEGORIES_CREATE = 'machine-categories.create';
    case MACHINE_CATEGORIES_EDIT = 'machine-categories.edit';
    case MACHINE_CATEGORIES_DELETE = 'machine-categories.delete';

        // MACHINES
    case MACHINES_INDEX = 'machines.index';
    case MACHINES_VIEW = 'machines.view';
    case MACHINES_CREATE = 'machines.create';
    case MACHINES_EDIT = 'machines.edit';
    case MACHINES_DELETE = 'machines.delete';

        // PRODUCTS
    case PRODUCTS_INDEX = 'products.index';
    case PRODUCTS_VIEW = 'products.view';
    case PRODUCTS_CREATE = 'products.create';
    case PRODUCTS_EDIT = 'products.edit';
    case PRODUCTS_DELETE = 'products.delete';

        // VENDING REQUESTS
    case VENDING_REQUESTS_INDEX = 'vending-requests.index';
    case VENDING_REQUESTS_VIEW = 'vending-requests.view';
    case VENDING_REQUESTS_EDIT = 'vending-requests.edit';
    case VENDING_REQUESTS_DELETE = 'vending-requests.delete';

        // COFFEE SERVICE REQUESTS
    case COFFEE_SERVICE_REQUESTS_INDEX = 'coffee-service-requests.index';
    case COFFEE_SERVICE_REQUESTS_VIEW = 'coffee-service-requests.view';
    case COFFEE_SERVICE_REQUESTS_EDIT = 'coffee-service-requests.edit';
    case COFFEE_SERVICE_REQUESTS_DELETE = 'coffee-service-requests.delete';

        // HOME FAQS
    case HOME_FAQS_INDEX = 'home-faqs.index';
    case HOME_FAQS_CREATE = 'home-faqs.create';
    case HOME_FAQS_EDIT = 'home-faqs.edit';
    case HOME_FAQS_DELETE = 'home-faqs.delete';

        // MACHINE FAQS
    case MACHINE_FAQS_INDEX = 'machine-faqs.index';
    case MACHINE_FAQS_CREATE = 'machine-faqs.create';
    case MACHINE_FAQS_EDIT = 'machine-faqs.edit';
    case MACHINE_FAQS_DELETE = 'machine-faqs.delete';

        // OFFICE COFFEE FAQS
    case OFFICE_COFFEE_FAQS_INDEX = 'office-coffee-faqs.index';
    case OFFICE_COFFEE_FAQS_CREATE = 'office-coffee-faqs.create';
    case OFFICE_COFFEE_FAQS_EDIT = 'office-coffee-faqs.edit';
    case OFFICE_COFFEE_FAQS_DELETE = 'office-coffee-faqs.delete';

        // LOCATION TYPES
    case LOCATION_TYPES_INDEX = 'location-types.index';
    case LOCATION_TYPES_CREATE = 'location-types.create';
    case LOCATION_TYPES_EDIT = 'location-types.edit';
    case LOCATION_TYPES_DELETE = 'location-types.delete';

        // OPERATOR SMS
    case OPERATOR_SMS_INDEX = 'operator-sms.index';
    case OPERATOR_SMS_SEND = 'operator-sms.send';

    // -------------------------------------------------------------------------
    // group() — returns the display group for the `group` DB column.
    // Used by PermissionSeeder and the role management UI (grouped checkboxes).
    // -------------------------------------------------------------------------
    public function group(): string
    {
        return match ($this) {
            self::DASHBOARD_VIEW,
            self::REPOSITORY_VIEW,
            self::DOCUMENTATION_VIEW => 'Dashboard',

            self::FILE_UPLOAD_INDEX,
            self::FILE_UPLOAD_STORE => 'File Upload',

            self::POSTS_INDEX,
            self::POSTS_VIEW,
            self::POSTS_CREATE,
            self::POSTS_EDIT,
            self::POSTS_DELETE,
            self::POSTS_PUBLISH => 'Posts',

            self::USERS_INDEX,
            self::USERS_VIEW,
            self::USERS_CREATE,
            self::USERS_EDIT,
            self::USERS_DELETE => 'Users',

            self::ROLES_INDEX,
            self::ROLES_VIEW,
            self::ROLES_CREATE,
            self::ROLES_EDIT,
            self::ROLES_DELETE => 'Roles',

            self::PERMISSIONS_INDEX,
            self::PERMISSIONS_EXPORT => 'Permissions',

            self::SETTINGS_INDEX,
            self::SETTINGS_VIEW,
            self::SETTINGS_CREATE,
            self::SETTINGS_EDIT,
            self::SETTINGS_DELETE,
            self::SETTINGS_IMPORT,
            self::SETTINGS_EXPORT,
            self::SETTINGS_PRINT => 'Settings',

            self::MACHINE_CATEGORIES_INDEX,
            self::MACHINE_CATEGORIES_VIEW,
            self::MACHINE_CATEGORIES_CREATE,
            self::MACHINE_CATEGORIES_EDIT,
            self::MACHINE_CATEGORIES_DELETE => 'Machine Categories',

            self::MACHINES_INDEX,
            self::MACHINES_VIEW,
            self::MACHINES_CREATE,
            self::MACHINES_EDIT,
            self::MACHINES_DELETE => 'Machines',

            self::PRODUCTS_INDEX,
            self::PRODUCTS_VIEW,
            self::PRODUCTS_CREATE,
            self::PRODUCTS_EDIT,
            self::PRODUCTS_DELETE => 'Products',

            self::VENDING_REQUESTS_INDEX,
            self::VENDING_REQUESTS_VIEW,
            self::VENDING_REQUESTS_EDIT,
            self::VENDING_REQUESTS_DELETE => 'Vending Requests',

            self::COFFEE_SERVICE_REQUESTS_INDEX,
            self::COFFEE_SERVICE_REQUESTS_VIEW,
            self::COFFEE_SERVICE_REQUESTS_EDIT,
            self::COFFEE_SERVICE_REQUESTS_DELETE => 'Coffee Service Requests',

            self::HOME_FAQS_INDEX,
            self::HOME_FAQS_CREATE,
            self::HOME_FAQS_EDIT,
            self::HOME_FAQS_DELETE => 'Home FAQs',

            self::MACHINE_FAQS_INDEX,
            self::MACHINE_FAQS_CREATE,
            self::MACHINE_FAQS_EDIT,
            self::MACHINE_FAQS_DELETE => 'Machine FAQs',

            self::OFFICE_COFFEE_FAQS_INDEX,
            self::OFFICE_COFFEE_FAQS_CREATE,
            self::OFFICE_COFFEE_FAQS_EDIT,
            self::OFFICE_COFFEE_FAQS_DELETE => 'Office Coffee FAQs',

            self::LOCATION_TYPES_INDEX,
            self::LOCATION_TYPES_CREATE,
            self::LOCATION_TYPES_EDIT,
            self::LOCATION_TYPES_DELETE => 'Location Types',

            self::OPERATOR_SMS_INDEX,
            self::OPERATOR_SMS_SEND => 'Operator SMS',
        };
    }

    // -------------------------------------------------------------------------
    // guard() — returns the guard name for this permission.
    // Change to 'api' for Passport API-only apps.
    // -------------------------------------------------------------------------
    public function guard(): string
    {
        return GuardEnum::WEB->value; // change to 'api' for Passport
    }
}

<?php

use App\Enums\PermissionEnum;
use App\Http\Controllers\Admin\CoffeeServiceRequestController as AdminCoffeeServiceRequestController;
use App\Http\Controllers\Admin\HomeFaqController;
use App\Http\Controllers\Admin\LocationTypeController;
use App\Http\Controllers\Admin\MachineCategoryController;
use App\Http\Controllers\Admin\MachineController;
use App\Http\Controllers\Admin\MachineFaqController;
use App\Http\Controllers\Admin\OfficeCoffeeFaqController;
use App\Http\Controllers\Admin\OperatorSmsController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VendingRequestController as AdminVendingRequestController;
use App\Http\Controllers\Auth\LocationSuggestController;
use App\Http\Controllers\Auth\RegisterAccountController;
use App\Http\Controllers\Auth\RegisterOtpController;
use App\Http\Controllers\Auth\RegisterOtpVerifyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FileUploadDemoController;
use App\Http\Controllers\Frontend\AboutController;
use App\Http\Controllers\Frontend\CitiesController;
use App\Http\Controllers\Frontend\CoffeeServiceRequestController;
use App\Http\Controllers\Frontend\ContactController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\IndustriesController;
use App\Http\Controllers\Frontend\LegalController;
use App\Http\Controllers\Frontend\MachinesController;
use App\Http\Controllers\Frontend\MicroMarketsController;
use App\Http\Controllers\Frontend\OfficeCoffeeController;
use App\Http\Controllers\Frontend\OperatorsController;
use App\Http\Controllers\Frontend\ProductsController;
use App\Http\Controllers\Frontend\ResourcesController;
use App\Http\Controllers\Frontend\SchoolsController;
use App\Http\Controllers\Frontend\VendingRequestController;
use App\Http\Controllers\Operator\AccountController;
use App\Http\Controllers\Operator\BillingController;
use App\Http\Controllers\Operator\LeadController;
use App\Http\Controllers\Operator\RouteController;
use App\Http\Controllers\Operator\SubscriptionController;
use App\Http\Controllers\PostAttachmentController;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/vending-requests', [VendingRequestController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('vending-requests.store');
Route::post('/coffee-service-requests', [CoffeeServiceRequestController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('coffee-service-requests.store');
Route::get('/machines', [MachinesController::class, 'index'])->name('machines');
Route::get('/machines/{machine}', [MachinesController::class, 'show'])->name('machines.show');
Route::get('/products', [ProductsController::class, 'index'])->name('products');
Route::get('/micro-markets', [MicroMarketsController::class, 'index'])->name('micro-markets');
Route::get('/office-coffee', [OfficeCoffeeController::class, 'index'])->name('office-coffee');
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::get('/about', [AboutController::class, 'index'])->name('about');
Route::get('/schools', [SchoolsController::class, 'index'])->name('schools');
Route::get('/resources', [ResourcesController::class, 'index'])->name('resources');
Route::get('/cities', [CitiesController::class, 'index'])->name('cities');
Route::get('/operators', [OperatorsController::class, 'index'])->name('operators');
Route::get('/legal/{page}', [LegalController::class, 'show'])
    ->whereIn('page', ['privacy', 'terms', 'operator-terms', 'cookies'])
    ->name('legal.show');
Route::get('/industries', [IndustriesController::class, 'index'])->name('industries');
Route::get('/industries/{industry}', [IndustriesController::class, 'show'])->name('industries.show');

Route::get('/register/location-suggest', LocationSuggestController::class)
    ->middleware('throttle:60,1')
    ->name('register.location-suggest');

Route::post('/register/account', [RegisterAccountController::class, 'store'])
    ->middleware(['guest', 'throttle:10,1'])
    ->name('register.account');

Route::middleware(['auth', 'throttle:5,1'])->group(function () {
    Route::post('/register/otp', [RegisterOtpController::class, 'store'])
        ->name('register.otp');
    Route::post('/register/otp/verify', [RegisterOtpVerifyController::class, 'store'])
        ->name('register.otp.verify');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('leads', [LeadController::class, 'index'])->name('leads.index');
    Route::get('leads/purchased', [LeadController::class, 'purchased'])->name('leads.purchased');
    Route::get('leads/tickets', [LeadController::class, 'tickets'])->name('leads.tickets');
    Route::get('leads/faq', [LeadController::class, 'faq'])->name('leads.faq');
    Route::post('leads/{source}/{lead}/purchase', [LeadController::class, 'purchase'])
        ->whereIn('source', ['vending', 'coffee'])
        ->whereNumber('lead')
        ->name('leads.purchase');
    Route::get('routes', [RouteController::class, 'index'])->name('routes.index');
    Route::get('routes/sell', [RouteController::class, 'sell'])->name('routes.sell');
    Route::get('account', [AccountController::class, 'index'])->name('account.index');
    Route::get('account/location', [AccountController::class, 'location'])->name('account.location');
    Route::get('subscription', [SubscriptionController::class, 'index'])->name('subscription.index');
    Route::get('billing', [BillingController::class, 'index'])->name('billing.index');

    // ── Demo landing page ─────────────────────────────────────────────────────
    Route::get('/file-upload-demo', [FileUploadDemoController::class, 'index'])
        ->name('file-upload-demo.index')->middleware('permission:' . PermissionEnum::FILE_UPLOAD_INDEX->value);

    // ── Single / multiple file upload (used by demos 1 & 2) ──────────────────
    Route::post('/upload', [FileUploadDemoController::class, 'store'])
        ->name('upload.store')->middleware('permission:' . PermissionEnum::FILE_UPLOAD_STORE->value);

    // ── Edit-mode endpoint (demo 3) ───────────────────────────────────────────
    Route::post('/posts/{post}', [PostAttachmentController::class, 'update'])
        ->name('posts.update')->middleware('permission:' . PermissionEnum::POSTS_EDIT->value);

    // ── Admin: access management ──────────────────────────────────────────────
    Route::prefix('admin')->name('admin.')->group(function () {
        // Legacy users URLs → user management.
        Route::redirect('users', '/admin/user-management');
        Route::redirect('users/create', '/admin/user-management/create');
        Route::get('users/{user}', fn(User $user) => redirect()->route('admin.user-management.show', $user));
        Route::get('users/{user}/edit', fn(User $user) => redirect()->route('admin.user-management.edit', $user));

        // User management — accounts with the `user` role only.
        Route::controller(UserController::class)->prefix('user-management')->name('user-management.')->group(function () {
            Route::get('/', 'index')->name('index')
                ->middleware('permission:' . PermissionEnum::USERS_INDEX->value);
            Route::get('create', 'create')->name('create')
                ->middleware('permission:' . PermissionEnum::USERS_CREATE->value);
            Route::post('/', 'store')->name('store')
                ->middleware(['permission:' . PermissionEnum::USERS_CREATE->value, HandlePrecognitiveRequests::class]);
            Route::get('{user}', 'show')->name('show')
                ->middleware('permission:' . PermissionEnum::USERS_VIEW->value);
            Route::get('{user}/edit', 'edit')->name('edit')
                ->middleware('permission:' . PermissionEnum::USERS_EDIT->value);
            Route::put('{user}', 'update')->name('update')
                ->middleware(['permission:' . PermissionEnum::USERS_EDIT->value, HandlePrecognitiveRequests::class]);
            Route::delete('{user}', 'destroy')->name('destroy')
                ->middleware('permission:' . PermissionEnum::USERS_DELETE->value);
        });

        // Admin management — accounts with the `admin` role only (super-admin actors).
        Route::controller(UserController::class)->prefix('admin-management')->name('admin-management.')->group(function () {
            Route::get('/', 'index')->name('index')
                ->middleware('permission:' . PermissionEnum::USERS_INDEX->value);
            Route::get('create', 'create')->name('create')
                ->middleware('permission:' . PermissionEnum::USERS_CREATE->value);
            Route::post('/', 'store')->name('store')
                ->middleware(['permission:' . PermissionEnum::USERS_CREATE->value, HandlePrecognitiveRequests::class]);
            Route::get('{user}', 'show')->name('show')
                ->middleware('permission:' . PermissionEnum::USERS_VIEW->value);
            Route::get('{user}/edit', 'edit')->name('edit')
                ->middleware('permission:' . PermissionEnum::USERS_EDIT->value);
            Route::put('{user}', 'update')->name('update')
                ->middleware(['permission:' . PermissionEnum::USERS_EDIT->value, HandlePrecognitiveRequests::class]);
            Route::delete('{user}', 'destroy')->name('destroy')
                ->middleware('permission:' . PermissionEnum::USERS_DELETE->value);
        });

        // Roles — full CRUD with grouped permission assignment.
        Route::controller(RoleController::class)->group(function () {
            Route::get('roles', 'index')->name('roles.index')
                ->middleware('permission:' . PermissionEnum::ROLES_INDEX->value);
            Route::get('roles/create', 'create')->name('roles.create')
                ->middleware('permission:' . PermissionEnum::ROLES_CREATE->value);
            Route::post('roles', 'store')->name('roles.store')
                ->middleware(['permission:' . PermissionEnum::ROLES_CREATE->value, HandlePrecognitiveRequests::class]);
            Route::get('roles/{role}/edit', 'edit')->name('roles.edit')
                ->middleware('permission:' . PermissionEnum::ROLES_EDIT->value);
            Route::put('roles/{role}', 'update')->name('roles.update')
                ->middleware(['permission:' . PermissionEnum::ROLES_EDIT->value, HandlePrecognitiveRequests::class]);
            Route::delete('roles/{role}', 'destroy')->name('roles.destroy')
                ->middleware('permission:' . PermissionEnum::ROLES_DELETE->value);
        });

        // Permissions — read-only listing + CSV / Excel export.
        Route::controller(PermissionController::class)->group(function () {
            Route::get('permissions', 'index')->name('permissions.index')
                ->middleware('permission:' . PermissionEnum::PERMISSIONS_INDEX->value);
            Route::get('permissions/export', 'export')->name('permissions.export')
                ->middleware('permission:' . PermissionEnum::PERMISSIONS_EXPORT->value);
        });

        // Machine categories — required before creating machines.
        Route::controller(MachineCategoryController::class)->group(function () {
            Route::get('machine-categories', 'index')->name('machine-categories.index')
                ->middleware('permission:' . PermissionEnum::MACHINE_CATEGORIES_INDEX->value);
            Route::get('machine-categories/create', 'create')->name('machine-categories.create')
                ->middleware('permission:' . PermissionEnum::MACHINE_CATEGORIES_CREATE->value);
            Route::post('machine-categories', 'store')->name('machine-categories.store')
                ->middleware(['permission:' . PermissionEnum::MACHINE_CATEGORIES_CREATE->value, HandlePrecognitiveRequests::class]);
            Route::get('machine-categories/{machine_category}/edit', 'edit')->name('machine-categories.edit')
                ->middleware('permission:' . PermissionEnum::MACHINE_CATEGORIES_EDIT->value);
            Route::put('machine-categories/{machine_category}', 'update')->name('machine-categories.update')
                ->middleware(['permission:' . PermissionEnum::MACHINE_CATEGORIES_EDIT->value, HandlePrecognitiveRequests::class]);
            Route::delete('machine-categories/{machine_category}', 'destroy')->name('machine-categories.destroy')
                ->middleware('permission:' . PermissionEnum::MACHINE_CATEGORIES_DELETE->value);
        });

        // Machines — full CRUD with category assignment.
        Route::controller(MachineController::class)->group(function () {
            Route::get('machines', 'index')->name('machines.index')
                ->middleware('permission:' . PermissionEnum::MACHINES_INDEX->value);
            Route::get('machines/create', 'create')->name('machines.create')
                ->middleware('permission:' . PermissionEnum::MACHINES_CREATE->value);
            Route::post('machines', 'store')->name('machines.store')
                ->middleware(['permission:' . PermissionEnum::MACHINES_CREATE->value, HandlePrecognitiveRequests::class]);
            Route::get('machines/{machine}', 'show')->name('machines.show')
                ->middleware('permission:' . PermissionEnum::MACHINES_VIEW->value);
            Route::get('machines/{machine}/edit', 'edit')->name('machines.edit')
                ->middleware('permission:' . PermissionEnum::MACHINES_EDIT->value);
            Route::put('machines/{machine}', 'update')->name('machines.update')
                ->middleware(['permission:' . PermissionEnum::MACHINES_EDIT->value, HandlePrecognitiveRequests::class]);
            Route::delete('machines/{machine}', 'destroy')->name('machines.destroy')
                ->middleware('permission:' . PermissionEnum::MACHINES_DELETE->value);
        });

        // Products — full CRUD (catalog cards).
        Route::controller(ProductController::class)->group(function () {
            Route::get('products', 'index')->name('products.index')
                ->middleware('permission:' . PermissionEnum::PRODUCTS_INDEX->value);
            Route::get('products/create', 'create')->name('products.create')
                ->middleware('permission:' . PermissionEnum::PRODUCTS_CREATE->value);
            Route::post('products', 'store')->name('products.store')
                ->middleware(['permission:' . PermissionEnum::PRODUCTS_CREATE->value, HandlePrecognitiveRequests::class]);
            Route::get('products/{product}', 'show')->name('products.show')
                ->middleware('permission:' . PermissionEnum::PRODUCTS_VIEW->value);
            Route::get('products/{product}/edit', 'edit')->name('products.edit')
                ->middleware('permission:' . PermissionEnum::PRODUCTS_EDIT->value);
            Route::put('products/{product}', 'update')->name('products.update')
                ->middleware(['permission:' . PermissionEnum::PRODUCTS_EDIT->value, HandlePrecognitiveRequests::class]);
            Route::delete('products/{product}', 'destroy')->name('products.destroy')
                ->middleware('permission:' . PermissionEnum::PRODUCTS_DELETE->value);
        });

        // Vending requests — public form inbox.
        Route::controller(AdminVendingRequestController::class)->group(function () {
            Route::get('vending-requests', 'index')->name('vending-requests.index')
                ->middleware('permission:' . PermissionEnum::VENDING_REQUESTS_INDEX->value);
            Route::get('vending-requests/{vending_request}', 'show')->name('vending-requests.show')
                ->middleware('permission:' . PermissionEnum::VENDING_REQUESTS_VIEW->value);
            Route::put('vending-requests/{vending_request}', 'update')->name('vending-requests.update')
                ->middleware('permission:' . PermissionEnum::VENDING_REQUESTS_EDIT->value);
            Route::delete('vending-requests/{vending_request}', 'destroy')->name('vending-requests.destroy')
                ->middleware('permission:' . PermissionEnum::VENDING_REQUESTS_DELETE->value);
        });

        // Coffee service requests — office coffee form inbox.
        Route::controller(AdminCoffeeServiceRequestController::class)->group(function () {
            Route::get('coffee-service-requests', 'index')->name('coffee-service-requests.index')
                ->middleware('permission:' . PermissionEnum::COFFEE_SERVICE_REQUESTS_INDEX->value);
            Route::get('coffee-service-requests/{coffee_service_request}', 'show')->name('coffee-service-requests.show')
                ->middleware('permission:' . PermissionEnum::COFFEE_SERVICE_REQUESTS_VIEW->value);
            Route::put('coffee-service-requests/{coffee_service_request}', 'update')->name('coffee-service-requests.update')
                ->middleware('permission:' . PermissionEnum::COFFEE_SERVICE_REQUESTS_EDIT->value);
            Route::delete('coffee-service-requests/{coffee_service_request}', 'destroy')->name('coffee-service-requests.destroy')
                ->middleware('permission:' . PermissionEnum::COFFEE_SERVICE_REQUESTS_DELETE->value);
        });

        // Manual SMS to operators by distance / search.
        Route::controller(OperatorSmsController::class)->group(function () {
            Route::get('operator-sms', 'index')->name('operator-sms.index')
                ->middleware('permission:' . PermissionEnum::OPERATOR_SMS_INDEX->value);
            Route::post('operator-sms', 'send')->name('operator-sms.send')
                ->middleware('permission:' . PermissionEnum::OPERATOR_SMS_SEND->value);
        });

        // Home FAQs — public homepage FAQ items.
        Route::controller(HomeFaqController::class)->group(function () {
            Route::get('home-faqs', 'index')->name('home-faqs.index')
                ->middleware('permission:' . PermissionEnum::HOME_FAQS_INDEX->value);
            Route::get('home-faqs/create', 'create')->name('home-faqs.create')
                ->middleware('permission:' . PermissionEnum::HOME_FAQS_CREATE->value);
            Route::post('home-faqs', 'store')->name('home-faqs.store')
                ->middleware(['permission:' . PermissionEnum::HOME_FAQS_CREATE->value, HandlePrecognitiveRequests::class]);
            Route::get('home-faqs/{home_faq}/edit', 'edit')->name('home-faqs.edit')
                ->middleware('permission:' . PermissionEnum::HOME_FAQS_EDIT->value);
            Route::put('home-faqs/{home_faq}', 'update')->name('home-faqs.update')
                ->middleware(['permission:' . PermissionEnum::HOME_FAQS_EDIT->value, HandlePrecognitiveRequests::class]);
            Route::delete('home-faqs/{home_faq}', 'destroy')->name('home-faqs.destroy')
                ->middleware('permission:' . PermissionEnum::HOME_FAQS_DELETE->value);
        });

        // Machine FAQs — public machines-page FAQ items.
        Route::controller(MachineFaqController::class)->group(function () {
            Route::get('machine-faqs', 'index')->name('machine-faqs.index')
                ->middleware('permission:' . PermissionEnum::MACHINE_FAQS_INDEX->value);
            Route::get('machine-faqs/create', 'create')->name('machine-faqs.create')
                ->middleware('permission:' . PermissionEnum::MACHINE_FAQS_CREATE->value);
            Route::post('machine-faqs', 'store')->name('machine-faqs.store')
                ->middleware(['permission:' . PermissionEnum::MACHINE_FAQS_CREATE->value, HandlePrecognitiveRequests::class]);
            Route::get('machine-faqs/{machine_faq}/edit', 'edit')->name('machine-faqs.edit')
                ->middleware('permission:' . PermissionEnum::MACHINE_FAQS_EDIT->value);
            Route::put('machine-faqs/{machine_faq}', 'update')->name('machine-faqs.update')
                ->middleware(['permission:' . PermissionEnum::MACHINE_FAQS_EDIT->value, HandlePrecognitiveRequests::class]);
            Route::delete('machine-faqs/{machine_faq}', 'destroy')->name('machine-faqs.destroy')
                ->middleware('permission:' . PermissionEnum::MACHINE_FAQS_DELETE->value);
        });

        // Office coffee FAQs — public office coffee page FAQ items.
        Route::controller(OfficeCoffeeFaqController::class)->group(function () {
            Route::get('office-coffee-faqs', 'index')->name('office-coffee-faqs.index')
                ->middleware('permission:' . PermissionEnum::OFFICE_COFFEE_FAQS_INDEX->value);
            Route::get('office-coffee-faqs/create', 'create')->name('office-coffee-faqs.create')
                ->middleware('permission:' . PermissionEnum::OFFICE_COFFEE_FAQS_CREATE->value);
            Route::post('office-coffee-faqs', 'store')->name('office-coffee-faqs.store')
                ->middleware(['permission:' . PermissionEnum::OFFICE_COFFEE_FAQS_CREATE->value, HandlePrecognitiveRequests::class]);
            Route::get('office-coffee-faqs/{office_coffee_faq}/edit', 'edit')->name('office-coffee-faqs.edit')
                ->middleware('permission:' . PermissionEnum::OFFICE_COFFEE_FAQS_EDIT->value);
            Route::put('office-coffee-faqs/{office_coffee_faq}', 'update')->name('office-coffee-faqs.update')
                ->middleware(['permission:' . PermissionEnum::OFFICE_COFFEE_FAQS_EDIT->value, HandlePrecognitiveRequests::class]);
            Route::delete('office-coffee-faqs/{office_coffee_faq}', 'destroy')->name('office-coffee-faqs.destroy')
                ->middleware('permission:' . PermissionEnum::OFFICE_COFFEE_FAQS_DELETE->value);
        });

        // Location types — chips on the public vending request form.
        Route::controller(LocationTypeController::class)->group(function () {
            Route::get('location-types', 'index')->name('location-types.index')
                ->middleware('permission:' . PermissionEnum::LOCATION_TYPES_INDEX->value);
            Route::get('location-types/create', 'create')->name('location-types.create')
                ->middleware('permission:' . PermissionEnum::LOCATION_TYPES_CREATE->value);
            Route::post('location-types', 'store')->name('location-types.store')
                ->middleware(['permission:' . PermissionEnum::LOCATION_TYPES_CREATE->value, HandlePrecognitiveRequests::class]);
            Route::get('location-types/{location_type}/edit', 'edit')->name('location-types.edit')
                ->middleware('permission:' . PermissionEnum::LOCATION_TYPES_EDIT->value);
            Route::put('location-types/{location_type}', 'update')->name('location-types.update')
                ->middleware(['permission:' . PermissionEnum::LOCATION_TYPES_EDIT->value, HandlePrecognitiveRequests::class]);
            Route::delete('location-types/{location_type}', 'destroy')->name('location-types.destroy')
                ->middleware('permission:' . PermissionEnum::LOCATION_TYPES_DELETE->value);
        });
    });
});

require __DIR__ . '/settings.php';

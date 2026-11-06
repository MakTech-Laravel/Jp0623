<?php

use App\Enums\PermissionEnum;
use App\Http\Controllers\Admin\MachineCategoryController;
use App\Http\Controllers\Admin\MachineController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\RegisterAccountController;
use App\Http\Controllers\Auth\RegisterOtpController;
use App\Http\Controllers\Auth\RegisterOtpVerifyController;
use App\Http\Controllers\Auth\RegisterTrialController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FileUploadDemoController;
use App\Http\Controllers\Frontend\AboutController;
use App\Http\Controllers\Frontend\AtmController;
use App\Http\Controllers\Frontend\CitiesController;
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
use App\Http\Controllers\Operator\AccountController;
use App\Http\Controllers\Operator\BillingController;
use App\Http\Controllers\Operator\LeadController;
use App\Http\Controllers\Operator\RouteController;
use App\Http\Controllers\Operator\SubscriptionController;
use App\Http\Controllers\PostAttachmentController;
use Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/machines', [MachinesController::class, 'index'])->name('machines');
Route::get('/machines/{machine}', [MachinesController::class, 'show'])->name('machines.show');
Route::get('/products', [ProductsController::class, 'index'])->name('products');
Route::get('/micro-markets', [MicroMarketsController::class, 'index'])->name('micro-markets');
Route::get('/office-coffee', [OfficeCoffeeController::class, 'index'])->name('office-coffee');
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::get('/about', [AboutController::class, 'index'])->name('about');
Route::get('/atm', [AtmController::class, 'index'])->name('atm');
Route::get('/schools', [SchoolsController::class, 'index'])->name('schools');
Route::get('/resources', [ResourcesController::class, 'index'])->name('resources');
Route::get('/cities', [CitiesController::class, 'index'])->name('cities');
Route::get('/operators', [OperatorsController::class, 'index'])->name('operators');
Route::get('/legal/{page}', [LegalController::class, 'show'])
    ->whereIn('page', ['privacy', 'terms', 'operator-terms', 'cookies'])
    ->name('legal.show');
Route::get('/industries', [IndustriesController::class, 'index'])->name('industries');
Route::get('/industries/{industry}', [IndustriesController::class, 'show'])->name('industries.show');

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
    Route::inertia('/register/payment', 'auth/register-payment')
        ->name('register.payment');
    Route::post('/register/trial', [RegisterTrialController::class, 'store'])
        ->name('register.trial');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('leads', [LeadController::class, 'index'])->name('leads.index');
    Route::get('leads/purchased', [LeadController::class, 'purchased'])->name('leads.purchased');
    Route::get('leads/tickets', [LeadController::class, 'tickets'])->name('leads.tickets');
    Route::get('leads/submit', [LeadController::class, 'submit'])->name('leads.submit');
    Route::get('leads/commission', [LeadController::class, 'commission'])->name('leads.commission');
    Route::get('leads/faq', [LeadController::class, 'faq'])->name('leads.faq');
    Route::get('routes', [RouteController::class, 'index'])->name('routes.index');
    Route::get('routes/sell', [RouteController::class, 'sell'])->name('routes.sell');
    Route::get('account', [AccountController::class, 'index'])->name('account.index');
    Route::get('account/location', [AccountController::class, 'location'])->name('account.location');
    Route::get('subscription', [SubscriptionController::class, 'index'])->name('subscription.index');
    Route::get('billing', [BillingController::class, 'index'])->name('billing.index');

    // ── Demo landing page ─────────────────────────────────────────────────────
    Route::get('/file-upload-demo', [FileUploadDemoController::class, 'index'])
        ->name('file-upload-demo.index')->middleware('permission:'.PermissionEnum::FILE_UPLOAD_INDEX->value);

    // ── Single / multiple file upload (used by demos 1 & 2) ──────────────────
    Route::post('/upload', [FileUploadDemoController::class, 'store'])
        ->name('upload.store')->middleware('permission:'.PermissionEnum::FILE_UPLOAD_STORE->value);

    // ── Edit-mode endpoint (demo 3) ───────────────────────────────────────────
    Route::post('/posts/{post}', [PostAttachmentController::class, 'update'])
        ->name('posts.update')->middleware('permission:'.PermissionEnum::POSTS_EDIT->value);

    // ── Admin: access management ──────────────────────────────────────────────
    Route::prefix('admin')->name('admin.')->group(function () {
        // Users — full CRUD with Precognition on write routes.
        Route::controller(UserController::class)->group(function () {
            Route::get('users', 'index')->name('users.index')
                ->middleware('permission:'.PermissionEnum::USERS_INDEX->value);
            Route::get('users/create', 'create')->name('users.create')
                ->middleware('permission:'.PermissionEnum::USERS_CREATE->value);
            Route::post('users', 'store')->name('users.store')
                ->middleware(['permission:'.PermissionEnum::USERS_CREATE->value, HandlePrecognitiveRequests::class]);
            Route::get('users/{user}', 'show')->name('users.show')
                ->middleware('permission:'.PermissionEnum::USERS_VIEW->value);
            Route::get('users/{user}/edit', 'edit')->name('users.edit')
                ->middleware('permission:'.PermissionEnum::USERS_EDIT->value);
            Route::put('users/{user}', 'update')->name('users.update')
                ->middleware(['permission:'.PermissionEnum::USERS_EDIT->value, HandlePrecognitiveRequests::class]);
            Route::delete('users/{user}', 'destroy')->name('users.destroy')
                ->middleware('permission:'.PermissionEnum::USERS_DELETE->value);
        });

        // Roles — full CRUD with grouped permission assignment.
        Route::controller(RoleController::class)->group(function () {
            Route::get('roles', 'index')->name('roles.index')
                ->middleware('permission:'.PermissionEnum::ROLES_INDEX->value);
            Route::get('roles/create', 'create')->name('roles.create')
                ->middleware('permission:'.PermissionEnum::ROLES_CREATE->value);
            Route::post('roles', 'store')->name('roles.store')
                ->middleware(['permission:'.PermissionEnum::ROLES_CREATE->value, HandlePrecognitiveRequests::class]);
            Route::get('roles/{role}/edit', 'edit')->name('roles.edit')
                ->middleware('permission:'.PermissionEnum::ROLES_EDIT->value);
            Route::put('roles/{role}', 'update')->name('roles.update')
                ->middleware(['permission:'.PermissionEnum::ROLES_EDIT->value, HandlePrecognitiveRequests::class]);
            Route::delete('roles/{role}', 'destroy')->name('roles.destroy')
                ->middleware('permission:'.PermissionEnum::ROLES_DELETE->value);
        });

        // Permissions — read-only listing + CSV / Excel export.
        Route::controller(PermissionController::class)->group(function () {
            Route::get('permissions', 'index')->name('permissions.index')
                ->middleware('permission:'.PermissionEnum::PERMISSIONS_INDEX->value);
            Route::get('permissions/export', 'export')->name('permissions.export')
                ->middleware('permission:'.PermissionEnum::PERMISSIONS_EXPORT->value);
        });

        // Machine categories — required before creating machines.
        Route::controller(MachineCategoryController::class)->group(function () {
            Route::get('machine-categories', 'index')->name('machine-categories.index')
                ->middleware('permission:'.PermissionEnum::MACHINE_CATEGORIES_INDEX->value);
            Route::get('machine-categories/create', 'create')->name('machine-categories.create')
                ->middleware('permission:'.PermissionEnum::MACHINE_CATEGORIES_CREATE->value);
            Route::post('machine-categories', 'store')->name('machine-categories.store')
                ->middleware(['permission:'.PermissionEnum::MACHINE_CATEGORIES_CREATE->value, HandlePrecognitiveRequests::class]);
            Route::get('machine-categories/{machine_category}/edit', 'edit')->name('machine-categories.edit')
                ->middleware('permission:'.PermissionEnum::MACHINE_CATEGORIES_EDIT->value);
            Route::put('machine-categories/{machine_category}', 'update')->name('machine-categories.update')
                ->middleware(['permission:'.PermissionEnum::MACHINE_CATEGORIES_EDIT->value, HandlePrecognitiveRequests::class]);
            Route::delete('machine-categories/{machine_category}', 'destroy')->name('machine-categories.destroy')
                ->middleware('permission:'.PermissionEnum::MACHINE_CATEGORIES_DELETE->value);
        });

        // Machines — full CRUD with category assignment.
        Route::controller(MachineController::class)->group(function () {
            Route::get('machines', 'index')->name('machines.index')
                ->middleware('permission:'.PermissionEnum::MACHINES_INDEX->value);
            Route::get('machines/create', 'create')->name('machines.create')
                ->middleware('permission:'.PermissionEnum::MACHINES_CREATE->value);
            Route::post('machines', 'store')->name('machines.store')
                ->middleware(['permission:'.PermissionEnum::MACHINES_CREATE->value, HandlePrecognitiveRequests::class]);
            Route::get('machines/{machine}', 'show')->name('machines.show')
                ->middleware('permission:'.PermissionEnum::MACHINES_VIEW->value);
            Route::get('machines/{machine}/edit', 'edit')->name('machines.edit')
                ->middleware('permission:'.PermissionEnum::MACHINES_EDIT->value);
            Route::put('machines/{machine}', 'update')->name('machines.update')
                ->middleware(['permission:'.PermissionEnum::MACHINES_EDIT->value, HandlePrecognitiveRequests::class]);
            Route::delete('machines/{machine}', 'destroy')->name('machines.destroy')
                ->middleware('permission:'.PermissionEnum::MACHINES_DELETE->value);
        });
    });
});

require __DIR__.'/settings.php';

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;

// Guest routes (not logged in)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login.show');
    Route::post('/verify-otp', [LoginController::class, 'verifyOTP'])->name('otp.verify');
    Route::post('/resend-otp', [LoginController::class, 'resendOTP'])->name('otp.resend');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', function () {
        return view('profile.index');
    })->name('profile');

    // User Management Routes (Admin and HOR access)
    Route::prefix('users')->middleware('role:admin|hor')->group(function () {
        // Index page (viewing users)
        Route::get('/', [UserController::class, 'index'])->name('users.index');

        // User CRUD routes
        Route::get('/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/', [UserController::class, 'store'])->name('users.store');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        // Location assignment
        Route::get('/{user}/assign-location', [UserController::class, 'showAssignLocation'])->name('users.assign-location');
        Route::post('/{user}/assign-location', [UserController::class, 'assignLocation'])->name('users.assign-location.store');
        Route::get('/{user}/locations', [UserController::class, 'getAvailableLocations'])->name('users.locations');
    });

    // Feature routes (placeholders - we'll create these later)
    Route::get('/financial', function () {
        return view('financial.index');
    })->name('financial.index')->middleware('can:view_financial');

    Route::get('/humanitarian', function () {
        return view('humanitarian.index');
    })->name('humanitarian.index')->middleware('can:view_humanitarian');

    Route::get('/reports', function () {
        return view('reports.index');
    })->name('reports.index')->middleware('can:view_reports');

    Route::get('/zones', function () {
        return view('zones.index');
    })->name('zones.index')->middleware('can:view_zones');

    Route::get('/settings', function () {
        return view('settings.index');
    })->name('settings.index')->middleware('can:manage_settings');

    Route::get('/inbox', function () {
        return view('inbox.index');
    })->name('inbox.index')->middleware('can:manage_settings');

    // Humanitarian Request Management
    Route::prefix('humanitarian')->middleware('can:view_humanitarian')->group(function () {
        Route::get('/', [App\Http\Controllers\HumanitarianRequestController::class, 'index'])->name('humanitarian.index');
        Route::get('/active', [App\Http\Controllers\HumanitarianRequestController::class, 'active'])->name('humanitarian.active');
        Route::get('/completed', [App\Http\Controllers\HumanitarianRequestController::class, 'completed'])->name('humanitarian.completed');
        Route::get('/drafts', [App\Http\Controllers\HumanitarianRequestController::class, 'drafts'])->name('humanitarian.drafts');

        Route::middleware('can:create_humanitarian')->group(function () {
            Route::get('/create', [App\Http\Controllers\HumanitarianRequestController::class, 'create'])->name('humanitarian.create');
            Route::post('/', [App\Http\Controllers\HumanitarianRequestController::class, 'store'])->name('humanitarian.store');
        });

        // Export routes (must come before /{id} wildcard route)
        Route::middleware('can:final_approve_humanitarian')->group(function () {
            Route::get('/export-monthly-pdf', [App\Http\Controllers\HumanitarianRequestController::class, 'exportMonthlyPDF'])->name('humanitarian.export-monthly-pdf');
            Route::get('/export-active-pdf', [App\Http\Controllers\HumanitarianRequestController::class, 'exportActivePDF'])->name('humanitarian.export-active-pdf');
        });

        Route::get('/{id}', [App\Http\Controllers\HumanitarianRequestController::class, 'show'])->name('humanitarian.show');

        Route::middleware('can:edit_humanitarian')->group(function () {
            Route::get('/{id}/edit', [App\Http\Controllers\HumanitarianRequestController::class, 'edit'])->name('humanitarian.edit');
            Route::put('/{id}', [App\Http\Controllers\HumanitarianRequestController::class, 'update'])->name('humanitarian.update');
            Route::delete('/{id}', [App\Http\Controllers\HumanitarianRequestController::class, 'destroy'])->name('humanitarian.destroy');
        });

        Route::middleware('can:approve_humanitarian')->group(function () {
            Route::post('/{id}/approve', [App\Http\Controllers\HumanitarianRequestController::class, 'approve'])->name('humanitarian.approve');
            Route::post('/{id}/reject', [App\Http\Controllers\HumanitarianRequestController::class, 'reject'])->name('humanitarian.reject');
        });

        Route::middleware('can:mark_ready_humanitarian')->group(function () {
            Route::post('/{id}/mark-ready', [App\Http\Controllers\HumanitarianRequestController::class, 'markReady'])->name('humanitarian.mark-ready');
        });

        Route::middleware('can:mark_collected_humanitarian')->group(function () {
            Route::post('/{id}/mark-collected', [App\Http\Controllers\HumanitarianRequestController::class, 'markCollected'])->name('humanitarian.mark-collected');
        });

        Route::middleware('can:final_approve_humanitarian')->group(function () {
            Route::get('/{id}/download', [App\Http\Controllers\HumanitarianRequestController::class, 'download'])->name('humanitarian.download');
            Route::post('/{id}/final-approve', [App\Http\Controllers\HumanitarianRequestController::class, 'finalApprove'])->name('humanitarian.final-approve');
        });

        // AJAX routes
        Route::get('/api/search-voters', [App\Http\Controllers\HumanitarianRequestController::class, 'searchVoters'])->name('humanitarian.search-voters');
        Route::get('/api/search-members', [App\Http\Controllers\HumanitarianRequestController::class, 'searchMembers'])->name('humanitarian.search-members');
        Route::get('/{id}/amount', [App\Http\Controllers\HumanitarianRequestController::class, 'getAmount'])->name('humanitarian.get-amount');
    });

    // Budget Management (HOR and Admin)
    Route::prefix('budgets')->middleware('can:view_humanitarian')->group(function () {
        // Index accessible by both HOR and Admin
        Route::get('/', [App\Http\Controllers\BudgetController::class, 'index'])
            ->name('budgets.index')
            ->middleware('role:hor|admin');

        // Create/Store only by HOR
        Route::get('/create', [App\Http\Controllers\BudgetController::class, 'create'])
            ->name('budgets.create')
            ->middleware('role:hor');
        Route::post('/', [App\Http\Controllers\BudgetController::class, 'store'])
            ->name('budgets.store')
            ->middleware('role:hor');

        // Edit/Update only by HOR (of their own zones)
        Route::get('/{id}/edit', [App\Http\Controllers\BudgetController::class, 'edit'])
            ->name('budgets.edit')
            ->middleware('role:hor');
        Route::put('/{id}', [App\Http\Controllers\BudgetController::class, 'update'])
            ->name('budgets.update')
            ->middleware('role:hor');

        // Delete only by HOR (of their own zones)
        Route::delete('/{id}', [App\Http\Controllers\BudgetController::class, 'destroy'])
            ->name('budgets.destroy')
            ->middleware('role:hor');

        // AJAX routes (HOR only)
        Route::get('/zone/{zoneId}', [App\Http\Controllers\BudgetController::class, 'getBudgetsForZone'])
            ->name('budgets.for-zone')
            ->middleware('role:hor');
    });

    // Budget API Routes (for budget preview - HOR only)
    Route::prefix('api/budgets')->middleware(['can:view_humanitarian', 'role:hor'])->group(function () {
        Route::post('/preview', [App\Http\Controllers\BudgetController::class, 'getBudgetPreview'])->name('api.budgets.preview');
        Route::get('/my-zones', [App\Http\Controllers\BudgetController::class, 'getMyZoneBudgets'])->name('api.budgets.my-zones');
    });

    // Monthly List Management
    Route::prefix('monthly-list')->middleware('can:view_humanitarian')->group(function () {
        Route::get('/', [App\Http\Controllers\MonthlyListController::class, 'index'])->name('monthly-list.index');
        Route::post('/add', [App\Http\Controllers\MonthlyListController::class, 'add'])->name('monthly-list.add');
        Route::delete('/{id}', [App\Http\Controllers\MonthlyListController::class, 'remove'])->name('monthly-list.remove');
        Route::post('/publish-all', [App\Http\Controllers\MonthlyListController::class, 'publishAll'])->name('monthly-list.publish-all');
    });

    // Inbox
    Route::prefix('inbox')->middleware('can:view_inbox')->group(function () {
        Route::get('/', [App\Http\Controllers\InboxController::class, 'index'])->name('inbox.index');
        Route::post('/{id}/read', [App\Http\Controllers\InboxController::class, 'markAsRead'])->name('inbox.mark-read');
        Route::post('/read-all', [App\Http\Controllers\InboxController::class, 'markAllAsRead'])->name('inbox.mark-all-read');
        Route::post('/clear-all', [App\Http\Controllers\InboxController::class, 'clearAll'])->name('inbox.clear-all');
        Route::delete('/{id}', [App\Http\Controllers\InboxController::class, 'destroy'])->name('inbox.destroy');
        Route::get('/api/unread-count', [App\Http\Controllers\InboxController::class, 'unreadCount'])->name('inbox.unread-count');
    });

    // PW Members Management
    Route::prefix('pw-members')->group(function () {
        Route::get('/', [App\Http\Controllers\PwMemberController::class, 'index'])->name('pw-members.index');
        Route::get('/search', [App\Http\Controllers\PwMemberController::class, 'search'])->name('pw-members.search');
        Route::get('/search-available-voters', [App\Http\Controllers\PwMemberController::class, 'searchAvailableVoters'])->name('pw-members.search-available-voters');

        Route::middleware('role:admin|hor')->group(function () {
            Route::get('/create', [App\Http\Controllers\PwMemberController::class, 'create'])->name('pw-members.create');
            Route::post('/', [App\Http\Controllers\PwMemberController::class, 'store'])->name('pw-members.store');
            Route::get('/{id}/edit', [App\Http\Controllers\PwMemberController::class, 'edit'])->name('pw-members.edit');
            Route::put('/{id}', [App\Http\Controllers\PwMemberController::class, 'update'])->name('pw-members.update');
            Route::delete('/{id}', [App\Http\Controllers\PwMemberController::class, 'destroy'])->name('pw-members.destroy');
        });

        Route::get('/{id}', [App\Http\Controllers\PwMemberController::class, 'show'])->name('pw-members.show');
    });

    // Voters List (Read-only)
    Route::prefix('voters-list')->group(function () {
        Route::get('/', [App\Http\Controllers\VotersListController::class, 'index'])->name('voters-list.index');
        Route::get('/search', [App\Http\Controllers\VotersListController::class, 'search'])->name('voters-list.search');
        Route::get('/{id}/check-pw-member', [App\Http\Controllers\VotersListController::class, 'checkPwMember'])->name('voters-list.check-pw-member');
    });
});

Route::get('/403', function(){
    return view('errors.403');
})->name('errors.403');

// Redirect root to appropriate page
Route::get('/', function () {
    return Auth::check() ? redirect()->route('dashboard') : redirect()->route('login.show');
});

require __DIR__ . '/auth.php';

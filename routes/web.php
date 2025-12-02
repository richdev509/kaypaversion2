<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

// Page d'accueil publique
Route::get('/', function () {
    return view('welcome-kaypa');
})->name('home');

// Routes publiques pour partenariat
Route::prefix('partenaire')->name('affiliate.')->group(function () {
    Route::get('/demande', [\App\Http\Controllers\AffiliatePublicController::class, 'showForm'])->name('form');
    Route::post('/demande', [\App\Http\Controllers\AffiliatePublicController::class, 'submitRequest'])->name('submit');
    Route::get('/verification/{id}', [\App\Http\Controllers\AffiliatePublicController::class, 'showVerifyForm'])->name('verify-form');
    Route::post('/verification/{id}', [\App\Http\Controllers\AffiliatePublicController::class, 'verifyCode'])->name('verify');
    Route::post('/renvoyer-code/{id}', [\App\Http\Controllers\AffiliatePublicController::class, 'resendCode'])->name('resend-code');
});

// Dashboard Admin (protégé par authentification)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Routes du profil utilisateur
Route::middleware('auth')->group(function () {
    Route::get('/profile', function () {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    })->name('profile.edit');

    Route::patch('/profile', function () {
        // Logic pour mettre à jour le profil
        return redirect()->route('profile.edit');
    })->name('profile.update');

    Route::delete('/profile', function () {
        // Logic pour supprimer le compte
        return redirect('/');
    })->name('profile.destroy');

    // Routes Clients
    Route::resource('clients', \App\Http\Controllers\ClientController::class);
    Route::get('/clients-search', [\App\Http\Controllers\ClientController::class, 'search'])->name('clients.search');

    // Routes vérification KYC
    Route::get('/clients/{client}/verify-kyc', [\App\Http\Controllers\ClientController::class, 'verifyKyc'])->name('clients.verify-kyc');
    Route::put('/clients/{client}/update-kyc', [\App\Http\Controllers\ClientController::class, 'updateKyc'])->name('clients.update-kyc');

    // Routes mise à jour KYC (documents + photos)
    Route::get('/clients/{client}/update-kyc-form', [\App\Http\Controllers\ClientController::class, 'updateKycForm'])->name('clients.update-kyc-form');
    Route::put('/clients/{client}/process-kyc-update', [\App\Http\Controllers\ClientController::class, 'processKycUpdate'])->name('clients.process-kyc-update');

    // Routes AJAX pour localisation (cascade géographique)
    Route::get('/get-communes/{departmentId}', [\App\Http\Controllers\ClientController::class, 'getCommunes'])->name('get.communes');
    Route::get('/get-cities/{communeId}', [\App\Http\Controllers\ClientController::class, 'getCities'])->name('get.cities');

    // Routes pour scan mobile (QR Code)
    Route::get('/clients/check-upload/{token}', [\App\Http\Controllers\ClientController::class, 'checkUpload'])->name('clients.check-upload');

    // Routes AJAX pour vérification unicité
    Route::post('/clients/check-unique', [\App\Http\Controllers\ClientController::class, 'checkUnique'])->name('clients.check-unique');

    // Routes Comptes
    Route::resource('accounts', \App\Http\Controllers\AccountController::class);
    Route::post('/accounts/{account}/status', [\App\Http\Controllers\AccountController::class, 'updateStatus'])->name('accounts.status.update');

    // Routes Corrections/Ajustements de transactions
    Route::post('/transactions/{transaction}/cancel', [\App\Http\Controllers\AccountTransactionAdjustmentController::class, 'cancel'])->name('transactions.cancel');
    Route::post('/accounts/{account}/adjustments', [\App\Http\Controllers\AccountTransactionAdjustmentController::class, 'createAdjustment'])->name('accounts.adjustments.create');
    Route::get('/accounts/{account}/corrections', [\App\Http\Controllers\AccountTransactionAdjustmentController::class, 'showCorrectionForm'])->name('accounts.corrections');

    // Routes Administration Rôles & Permissions (Admin uniquement)
    Route::prefix('admin')->name('admin.')->group(function () {
        // Rôles
        Route::get('/roles', [\App\Http\Controllers\RolePermissionController::class, 'indexRoles'])->name('roles.index');
        Route::get('/roles/create', [\App\Http\Controllers\RolePermissionController::class, 'createRole'])->name('roles.create');
        Route::post('/roles', [\App\Http\Controllers\RolePermissionController::class, 'storeRole'])->name('roles.store');
        Route::get('/roles/{role}/edit', [\App\Http\Controllers\RolePermissionController::class, 'editRole'])->name('roles.edit');
        Route::put('/roles/{role}', [\App\Http\Controllers\RolePermissionController::class, 'updateRole'])->name('roles.update');
        Route::delete('/roles/{role}', [\App\Http\Controllers\RolePermissionController::class, 'destroyRole'])->name('roles.destroy');

        // Permissions
        Route::get('/permissions', [\App\Http\Controllers\RolePermissionController::class, 'indexPermissions'])->name('permissions.index');
        Route::post('/permissions', [\App\Http\Controllers\RolePermissionController::class, 'storePermission'])->name('permissions.store');
        Route::delete('/permissions/{permission}', [\App\Http\Controllers\RolePermissionController::class, 'destroyPermission'])->name('permissions.destroy');
    });

    // Routes AJAX pour comptes
    Route::get('/plans/{plan}/montants', [\App\Http\Controllers\AccountController::class, 'getPlanMontants'])->name('plans.montants');

    // Routes Paiements (Dépôts)
    Route::post('/accounts/{account}/payments', [\App\Http\Controllers\PaymentController::class, 'store'])->name('payments.store');

    // Routes Retraits
    Route::get('/accounts/{account}/withdrawals/create', [\App\Http\Controllers\WithdrawalController::class, 'create'])->name('withdrawals.create');
    Route::post('/accounts/{account}/withdrawals', [\App\Http\Controllers\WithdrawalController::class, 'store'])->name('withdrawals.store');

    // Routes Branches (Gestion des Agences)
    Route::resource('branches', \App\Http\Controllers\BranchController::class);

    // Routes Users (Gestion des Utilisateurs)
    Route::resource('users', \App\Http\Controllers\UserController::class);
    Route::get('/users/{user}/permissions', [\App\Http\Controllers\UserController::class, 'editPermissions'])->name('users.permissions.edit');
    Route::post('/users/{user}/permissions', [\App\Http\Controllers\UserController::class, 'updatePermissions'])->name('users.permissions.update');
    Route::delete('/users/{user}/permissions', [\App\Http\Controllers\UserController::class, 'resetPermissions'])->name('users.permissions.reset');
    Route::get('/roles-permissions', [\App\Http\Controllers\UserController::class, 'rolesPermissions'])->name('users.roles-permissions');

    // Routes 2FA (Authentification à deux facteurs)
    Route::prefix('two-factor')->name('two-factor.')->group(function () {
        Route::get('/enable', [\App\Http\Controllers\TwoFactorController::class, 'enable'])->name('enable');
        Route::post('/confirm', [\App\Http\Controllers\TwoFactorController::class, 'confirm'])->name('confirm');
        Route::get('/verify', [\App\Http\Controllers\TwoFactorController::class, 'show'])->name('show')->withoutMiddleware(['auth']);
        Route::post('/verify', [\App\Http\Controllers\TwoFactorController::class, 'verify'])->name('verify')->withoutMiddleware(['auth']);
        Route::post('/disable', [\App\Http\Controllers\TwoFactorController::class, 'disable'])->name('disable');
        Route::get('/recovery-codes', [\App\Http\Controllers\TwoFactorController::class, 'showRecoveryCodes'])->name('recovery-codes');
        Route::post('/recovery-codes/regenerate', [\App\Http\Controllers\TwoFactorController::class, 'regenerateRecoveryCodes'])->name('recovery-codes.regenerate');
        Route::delete('/devices/{device}', [\App\Http\Controllers\TwoFactorController::class, 'removeDevice'])->name('device.remove');
    });

    // Routes Reports (Rapports)
    Route::get('/reports', [\App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
    Route::post('/reports/generate', [\App\Http\Controllers\ReportController::class, 'generate'])->name('reports.generate');

    // Routes Fund Movements (Gestion Financière)
    Route::get('/fund-movements', [\App\Http\Controllers\FundMovementController::class, 'index'])->name('fund-movements.index');
    Route::get('/fund-movements/create', [\App\Http\Controllers\FundMovementController::class, 'create'])->name('fund-movements.create');
    Route::post('/fund-movements', [\App\Http\Controllers\FundMovementController::class, 'store'])->name('fund-movements.store');
    Route::get('/fund-movements/{fundMovement}', [\App\Http\Controllers\FundMovementController::class, 'show'])->name('fund-movements.show');
    Route::post('/fund-movements/{fundMovement}/approve', [\App\Http\Controllers\FundMovementController::class, 'approve'])->name('fund-movements.approve');
    Route::post('/fund-movements/{fundMovement}/reject', [\App\Http\Controllers\FundMovementController::class, 'reject'])->name('fund-movements.reject');

    // Routes Dashboard Analytics
    Route::get('/analytics', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard.analytics');
    Route::get('/analytics/realtime', [\App\Http\Controllers\DashboardController::class, 'getRealtimeStats'])->name('dashboard.realtime-stats');

    // Routes Gestion Caisse Succursale
    Route::get('/branch-cash', [\App\Http\Controllers\BranchCashController::class, 'index'])->name('branch-cash.index');

    // Routes Gestion Accès Client (Online Access)
    Route::get('/client-access', [\App\Http\Controllers\ClientAccessController::class, 'index'])->name('client-access.index');
    Route::match(['get', 'post'], '/client-access/search', [\App\Http\Controllers\ClientAccessController::class, 'search'])->name('client-access.search');
    Route::post('/client-access/{client}/grant', [\App\Http\Controllers\ClientAccessController::class, 'grantAccess'])->name('client-access.grant');
    Route::delete('/client-access/{client}/revoke', [\App\Http\Controllers\ClientAccessController::class, 'revokeAccess'])->name('client-access.revoke');

    // Routes Gestion Affiliés / Partenaires (Admin & Comptable)
    Route::prefix('affilies')->name('affiliates.')->group(function () {
        Route::get('/', [\App\Http\Controllers\AffiliateController::class, 'index'])->name('index');
        Route::get('/{affiliate}', [\App\Http\Controllers\AffiliateController::class, 'show'])->name('show');
        Route::post('/{affiliate}/approve', [\App\Http\Controllers\AffiliateController::class, 'approve'])->name('approve');
        Route::post('/{affiliate}/reject', [\App\Http\Controllers\AffiliateController::class, 'reject'])->name('reject');
        Route::post('/{affiliate}/toggle-block', [\App\Http\Controllers\AffiliateController::class, 'toggleBlock'])->name('toggle-block');
        Route::post('/{affiliate}/resend-code', [\App\Http\Controllers\AffiliateController::class, 'resendCode'])->name('resend-code');
        Route::post('/{affiliate}/paiement', [\App\Http\Controllers\AffiliateController::class, 'storePaiement'])->name('paiement');
    });
});

// Routes publiques pour scan mobile (pas d'authentification requise)
Route::get('/clients/scan/{token}', [\App\Http\Controllers\ClientController::class, 'scanForm'])->name('clients.scan');
Route::post('/clients/scan/{token}', [\App\Http\Controllers\ClientController::class, 'scanUpload'])->name('clients.scan.upload');

// Routes Mobile Login (accès client)
Route::prefix('mobile')->name('mobile.')->group(function () {
    Route::get('/login', [\App\Http\Controllers\MobileAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [\App\Http\Controllers\MobileAuthController::class, 'login']);
    Route::get('/dashboard', [\App\Http\Controllers\MobileAuthController::class, 'dashboard'])->name('dashboard');
    Route::post('/logout', [\App\Http\Controllers\MobileAuthController::class, 'logout'])->name('logout');
    Route::get('/change-password', [\App\Http\Controllers\MobileAuthController::class, 'showChangePasswordForm'])->name('change-password');
    Route::post('/change-password', [\App\Http\Controllers\MobileAuthController::class, 'changePassword']);
});

// Test de connexion à la base de données KAYPA
Route::get('/test-db', function () {
    try {
        // Tester la connexion
        DB::connection()->getPdo();

        // Compter les enregistrements (LECTURE SEULE)
        $clientsCount = DB::table('clients')->count();
        $accountsCount = DB::table('accounts')->count();
        $paymentsCount = DB::table('account_transactions')->where('type', 'PAIEMENT')->count();
        $withdrawalsCount = DB::table('account_transactions')->where('type', 'RETRAIT')->count();
        $transactionsCount = DB::table('account_transactions')->count();
        $plansCount = DB::table('plans')->count();

        return response()->json([
            'status' => 'success',
            'message' => '✅ Connexion à la base de données KAYPA réussie!',
            'app_version' => env('APP_VERSION', 'v2'),
            'database' => config('database.connections.mysql.database'),
            'host' => config('database.connections.mysql.host'),
            'statistics' => [
                'clients' => $clientsCount,
                'accounts' => $accountsCount,
                'payments' => $paymentsCount,
                'withdrawals' => $withdrawalsCount,
                'transactions' => $transactionsCount,
                'plans' => $plansCount,
            ],
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => '❌ Erreur de connexion',
            'error' => $e->getMessage(),
        ], 500);
    }
});

// Inclure les routes d'authentification
require __DIR__.'/auth.php';

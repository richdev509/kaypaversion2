<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\AccountTransaction;
use App\Models\Transfer;
use App\Policies\UserPolicy;
use App\Policies\TransferPolicy;
use App\Observers\AccountTransactionObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Enregistrer les policies
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Transfer::class, TransferPolicy::class);

        // Enregistrer les observers
        AccountTransaction::observe(AccountTransactionObserver::class);
    }
}

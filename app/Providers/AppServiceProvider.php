<?php

namespace App\Providers;

use App\Models\Account;
use App\Models\Deposit;
use App\Models\Withdrawal;
use App\Policies\AccountPolicy;
use App\Policies\DepositPolicy;
use App\Policies\WithdrawalPolicy;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->configurePolicies();
        $this->configureDefaults();
    }

    protected function configurePolicies(): void
    {
        Gate::policy(Account::class, AccountPolicy::class);
        Gate::policy(Deposit::class, DepositPolicy::class);
        Gate::policy(Withdrawal::class, WithdrawalPolicy::class);
    }

    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}

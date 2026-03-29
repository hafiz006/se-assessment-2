<?php

use App\Livewire\Admin;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', Admin\Dashboard::class)->name('dashboard');

        Route::get('accounts', Admin\Accounts\Index::class)->name('accounts.index');
        Route::get('accounts/{account}', Admin\Accounts\Show::class)->name('accounts.show');

        Route::get('deposits', Admin\Deposits\Index::class)->name('deposits.index');
        Route::get('deposits/create', Admin\Deposits\Create::class)->name('deposits.create');
        Route::get('deposits/{deposit}', Admin\Deposits\Show::class)->name('deposits.show');

        Route::get('withdrawals', Admin\Withdrawals\Index::class)->name('withdrawals.index');
        Route::get('withdrawals/{withdrawal}', Admin\Withdrawals\Review::class)->name('withdrawals.review');

        Route::get('prices', Admin\MetalPrices\Index::class)->name('prices.index');
    });

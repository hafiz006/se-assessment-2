<?php

namespace App\Http\Middleware;

use App\Enums\AccountStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $account = $request->user()?->account;

        if ($account && $account->status !== AccountStatus::Active) {
            return redirect()->route('dashboard')
                ->with('error', 'Your account is ' . $account->status->value . ' and cannot perform transactions.');
        }

        return $next($request);
    }
}

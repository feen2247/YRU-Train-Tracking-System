<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        $statusLower = strtolower(trim($user->status ?? ''));
        $rightsLower = strtolower(trim($user->usage_rights ?? ''));
        $isSuspended = in_array($statusLower, ['ระงับการใช้งาน', 'ระงับ', 'suspended', 'inactive', 'banned', 'blocked', 'disabled'])
                    || in_array($rightsLower, ['ระงับการใช้งาน', 'ระงับ', 'suspended', 'inactive', 'banned', 'blocked', 'disabled'])
                    || str_contains($statusLower, 'ระงับ')
                    || str_contains($rightsLower, 'ระงับ')
                    || str_contains($statusLower, 'suspend')
                    || str_contains($rightsLower, 'suspend');

        if ($isSuspended) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login');
        }

        $userRole = $user->user_role;

        if (in_array($userRole, $roles)) {
            return $next($request);
        }

        abort(403, 'Unauthorized action. You do not have the required role (' . implode(', ', $roles) . ') to access this resource.');
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Contoh pemakaian di route:
     *   Route::middleware(['auth', 'role:admin'])->group(...);
     *   Route::middleware(['auth', 'role:customer'])->group(...);
     *   Route::middleware(['auth', 'role:admin,super admin'])->group(...); // lebih dari 1 role
     *
     * Perbandingan tidak case-sensitive (Admin, admin, ADMIN dianggap sama).
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $roles): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403, 'Unauthorized action.');
        }

        $userRole = strtolower($user->role?->name ?? '');
        $allowedRoles = array_map(fn ($r) => strtolower(trim($r)), explode(',', $roles));

        if (!in_array($userRole, $allowedRoles, true)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}

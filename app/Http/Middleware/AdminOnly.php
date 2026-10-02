<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
class AdminOnly {
    public function handle(Request $r, Closure $next) {
        abort_unless($r->user()?->is_admin, 403, 'Administrators only.');
        return $next($r);
    }
}

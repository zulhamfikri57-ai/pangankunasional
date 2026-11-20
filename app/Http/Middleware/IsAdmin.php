<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || auth()->user()->peran_sektor !== 'admin') {
            return redirect()->route('beranda')->with('success', 'Akses ditolak. Halaman ini khusus Administrator.');
        }

        return $next($request);
    }
}
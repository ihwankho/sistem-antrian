<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

class Handler extends ExceptionHandler
{
    /**
     * Convert an authentication exception into a response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        // Untuk request API, kembalikan response JSON
        if ($request->is('api/*') || $request->expectsJson()) {
            return response()->json(['status' => false, 'message' => 'Token tidak valid atau sudah kadaluarsa'], 401);
        }
        
        // Untuk request web, redirect ke halaman login
        return redirect()->guest('/login')->with('error', 'Sesi Anda telah berakhir. Silakan login kembali.');
    }

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->renderable(function (AuthenticationException $e, Request $request) {
            // Tangani khusus untuk request web
            if (!$request->is('api/*') && !$request->expectsJson()) {
                return redirect('/login')->with('error', 'Sesi Anda telah berakhir. Silakan login kembali.');
            }
            
            return response()->json(['status' => false, 'message' => 'Token tidak valid atau sudah kadaluarsa'], 401);
        });

        $this->reportable(function (Throwable $e) {
            //
        });
    }
}
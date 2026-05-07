<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Mailer\Exception\TransportException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, string>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        // Redirect expired sessions to home with login popup (frontend only)
        if ($exception instanceof TokenMismatchException && ! $request->is('admin/*') && ! $request->is('admin')) {
            return redirect()->route('home', ['login_required' => 1]);
        }

        // Catch mail/SMTP transport errors globally — log silently and show a toast
        if ($exception instanceof TransportException) {
            Log::error('Mail transport error: ' . $exception->getMessage());

            if ($request->wantsJson()) {
                return response()->json(['message' => 'Something went wrong. Please try again.'], 500);
            }

            return redirect()->back()->with('error', 'Something went wrong. Please try again.');
        }

        return parent::render($request, $exception);
    }
}

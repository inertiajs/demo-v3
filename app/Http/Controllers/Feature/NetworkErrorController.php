<?php

namespace App\Http\Controllers\Feature;

use Inertia\Inertia;
use Inertia\Response;

class NetworkErrorController
{
    public function httpExceptions(): Response
    {
        return Inertia::render('Features/Errors/HttpExceptions');
    }

    public function httpException403(): never
    {
        abort(403, 'Forbidden');
    }

    public function httpException404(): never
    {
        abort(404, 'Not Found');
    }

    public function httpException500(): never
    {
        abort(500, 'Server Error');
    }

    public function httpExceptionUnhandled(): never
    {
        abort(418, "I'm a teapot");
    }

    public function networkErrors(): Response
    {
        return Inertia::render('Features/Errors/NetworkErrors');
    }
}

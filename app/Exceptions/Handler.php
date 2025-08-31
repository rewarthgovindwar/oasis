<?php

namespace App\Exceptions;

use Exception;
use Throwable;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    public function register()
    {

        $this->renderable(function (Throwable $throwable, $request) {
            if ($throwable instanceof ValidationException) {
                $statusCode = 422;
            } elseif ($throwable instanceof HttpExceptionInterface) {
                $statusCode = $throwable->getStatusCode();
            } else {
                $statusCode = 500;
            }
            if($statusCode == 500)
            {                
                if(!empty(request()->headers->get('referer')) && request()->headers->get('referer') !== request()->fullUrl())
                {
                    Toastr::error($throwable->getMessage(), 'Failed');
                    return back();
                }
            }
        });
       
    }

    /**
     * Report or log an exception.
     *
     * @param  Exception  $throwable
     */
    public function report(Throwable $throwable): void
    {
        parent::report($throwable);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Exception  $throwable
     * @return \Illuminate\Http\Response
     */
    public function render($request, Throwable $throwable)
    {
        if ($throwable instanceof \Illuminate\Session\TokenMismatchException) {
            return redirect('login');
        }

        return parent::render($request, $throwable);

    }
}

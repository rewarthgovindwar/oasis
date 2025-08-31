<?php

namespace App\Http\Controllers;

class PaymentGatewayCallbackController extends Controller
{
    public function successCallback(string $method)
    {
        $classMap = config('paymentGateway.'.$method);
        $new_payment = new $classMap();

        return $new_payment->successCallback();
    }

    public function cancelCallback(string $method)
    {

        $classMap = config('paymentGateway.'.$method);

        $new_payment = new $classMap();

        return $new_payment->cancelCallBack();
    }
}

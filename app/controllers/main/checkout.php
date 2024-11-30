<?php

namespace app\controllers\main;

use app\controllers\abstract\controller;
use Stripe\Stripe;

class checkout extends controller
{
    public function index(){
        $stripe = new Stripe\StripeClient;
    }
}

<?php

namespace app\enums;

enum stripeMode: string
{
    case PAYMENT = "payment";
    case SETUP = "setup";
    case SUBSCRIPTION = "subscription";
}

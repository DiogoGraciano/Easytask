<?php

namespace app\enums;

enum stripeInterval: string
{
    case DAY = "day";
    case MONTH = "month";
    case WEEK = "week";
    case YEAR = "year";
}

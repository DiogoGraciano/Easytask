<?php

namespace app\view\layout;

use app\view\layout\abstract\layout;

class success extends layout
{
    public function __construct()
    {
        $this->setTemplate("success.html");
    }
}
<?php

namespace app\view\layout;

use app\view\layout\abstract\layout;

class cancel extends layout
{
    public function __construct()
    {
        $this->setTemplate("cancel.html");
    }
}
<?php

namespace app\controllers\main;

use app\helpers\mensagem as mensagemLayout;
use app\controllers\abstract\controller;

class mensagem extends controller
{
    public const addHeader = false;

    public const addFooter = false;

    public const addHead = false;

    public const permitAccess = true;

    public function index(){
        (new mensagemLayout)->show();
    }
}

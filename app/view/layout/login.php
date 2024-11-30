<?php

namespace app\view\layout;
use app\view\layout\abstract\layout;
use core\url;
use app\models\config;

class login extends layout{

    public function __construct($usuario="",$senha=""){
        $this->setTemplate("login.html");
        $this->tpl->caminho = url::getUrlBase();
        $this->tpl->action_login = "login/action";
        $this->tpl->usuario = $usuario;
        $this->tpl->senha = $senha;
        $this->tpl->action_cadastro_usuario = "login/user";
        $this->tpl->action_esqueci = "login/forget";

        // $empresa = (new config)->get(1);
        // $recapcha = $this->getTemplate("recapcha.html");
        // $recapcha->element_id = "g-recaptcha-login-response";
        // $recapcha->empresa = $empresa;
        // $this->tpl->recapcha = $recapcha->parse();
    }
}
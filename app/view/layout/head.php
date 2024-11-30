<?php

namespace app\view\layout;

use app\helpers\functions;
use app\models\config;
use app\models\cidade;
use app\models\estado;
use app\view\layout\abstract\layout;
use core\url;

class head extends layout{

    public function __construct(string $titulo=""){

        $this->setTemplate("head.html");

        $this->tpl->robots = "index,follow";
       
        $empresa = (new config)->get(1);

        $this->tpl->empresa = $empresa;
        $this->tpl->caminho = url::getUrlBase();
        $this->tpl->caminho_completo = url::getUrlCompleta();
        $this->tpl->title = $titulo;
        $this->tpl->class = functions::createNameId($titulo);
    }
}

?>
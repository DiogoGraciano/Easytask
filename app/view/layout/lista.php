<?php

namespace app\view\layout;
use app\view\layout\abstract\layout;
use app\helpers\mensagem;

class lista extends layout{

    private $lista;

    public function __construct()
    {
        $this->setTemplate("lista.html");
    }

    public function setLista(string $titulo){
        $this->tpl->titulo = $titulo;
        if($this->lista){
            foreach ($this->lista as $objeto){
                $this->tpl->url_objeto = $objeto->url_objeto;
                $this->tpl->titulo_objeto = $objeto->titulo_objeto; 
                $this->tpl->block("BLOCK_LISTA");
            } 
        }
        else
            $this->tpl->block("BLOCK_NO_LISTA");  

        return $this;
    }

    public function addButton(string $button){
        $this->tpl->button = $button;
        $this->tpl->block("BLOCK_BUTTONS");
        return $this;
    }

    public function addObjeto(string $url_objeto,string $titulo_objeto){
        $this->lista[] =  json_decode('{"url_objeto":"'.$url_objeto.'","titulo_objeto":"'.$titulo_objeto.'"}');
        return $this;
    }
}

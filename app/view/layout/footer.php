<?php
namespace app\view\layout;

use app\models\login;
use app\view\layout\abstract\layout;
use app\models\menu;
use core\url;

class footer extends layout
{

    public function __construct(string $logo = "assets\imagens\logo_grande.webp",int $tamanho_logo = 4)
    {
        $this->setTemplate("footer.html");
        if($logo){
            $this->tpl->logo = url::getUrlBase().$logo;
            $this->tpl->tamanho_logo = $tamanho_logo;
            $this->tpl->block("LOGO_FOOTER");
        }
        $this->tpl->caminho = url::getUrlBase();
        $this->tpl->ano = date("Y");
    }

    public function addLink(string $link,string $titulo,string $extra = ""):footer
    {
        $this->tpl->link = $link;
        $this->tpl->titulo = $titulo;
        $this->tpl->extra = $extra;
        $this->tpl->block("LINK_FOOTER");
        return $this;
    }

    public function setSection(string $titulo = "",int $tamanho = 3,array $links = []):footer
    {
        $this->tpl->titulo = $titulo;
        $this->tpl->tamanho = $tamanho;
        if($links){
            
        }
        $this->tpl->block("SECTION_FOOTER");
        return $this;
    }

    public function setSectionPagina(){
        $model = new menu;
    
        $menus = $model->getByFilter(ativo:1);

        $user = login::getLogged();
        
        $i = 1;
        $titulo = "Paginas";
        foreach ($menus as $menu){
            if($user && in_array($user->tipo_usuario,json_decode($menu["tipo_usuario"]))){
                if($menu["controller"])
                    $this->addLink(url::getUrlBase().$menu["controller"],$menu["nome"]);

                if($i == 7 && !$this->isMobile()){
                    $this->setSection($titulo,2);
                    $titulo = "&nbsp;";
                    $i = 1;
                }

                $i++;
            } 
        }
        if($i != 1)
            $this->setSection($titulo,2);

        return $this;
    }

    public function show():void
    {
        $this->setSectionPagina();
        (new wave(4,"#0b5ed7",name:"footer",margin:3))->show();
        $this->tpl->show();
    }

    public function parse():string
    {
        $this->setSectionPagina();
        return (new wave(4,"#0b5ed7",name:"footer",margin:3))->parse().$this->tpl->parse();
    }
}

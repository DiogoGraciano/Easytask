<?php

namespace app\controllers\main;

use app\controllers\abstract\controller;
use app\models\category as ModelsCategory;
use app\models\login;
use app\view\layout\consulta;
use app\view\layout\elements;
use app\view\layout\filter;
use app\view\layout\form;
use app\view\layout\modal;
use app\view\layout\pagination;

class category extends controller
{
    public const headTitle = "Categoria";

    public function index($parameters = []){
        
        $nome = $this->getValue("nome");

        $elements = new elements;

        $filter = new filter($this->url."category/index/");
        $filter->addbutton($elements->button("Buscar","buscar","submit","btn btn-primary pt-2"))
                ->addFilter(3,$elements->input("nome","Nome:",$nome));

        $cadastro = new consulta(false,"Consulta Categoria");

        $categoria = new ModelsCategory;

        $modal = new modal("modal-maintence","Manutenção Categoria",$this->form()->parse(),"modal fade modal-xl");

        $cadastro->addButtons($elements->buttonModal("Adicionar","manutencao","#modal-maintence"))
                ->addColumns("1","Id","id")
                ->addColumns("70","Nome","nome")
                ->addColumns("15","Ações","acoes")
                ->addMaintenceModal($modal,"/category/index")
                ->setData($this->url . "category/updateForm/", 
                        $this->url . "category/action/", 
                        $categoria->getByFilter($nome,limit:$this->getLimit(),offset:$this->getOffset()),
                        "id")
                ->addPagination(new pagination(
                    $categoria->getLastCount("getByFilter"),
                    "category/index",
                    "#consulta-admin",
                    limit:$this->getLimit()))
                ->addFilter($filter)
                ->show();
    }

    public function updateForm($parameters = []):void
    {
        $this->form($parameters)->show();
    }

    public function form($parameters = [],?ModelsCategory $categoria = null):form
    {
        $elements = new elements;

        $form = new form($this->url."category/action");

        $dado = $categoria?:(new ModelsCategory())->get(isset($parameters[0])?$parameters[0]:null);

        $form->setHidden("cd",$dado->id);
        $form->setElement($elements->input("nome","Nome: ",$dado->nome));
        $form->setButton($elements->button("Salvar","salvar"));

        return $form;
    }

    public function action($parameters = []){

        if(isset($parameters[0])){
            $categoria = new ModelsCategory;
            $categoria->id = $parameters[0];
            $categoria->remove();
            $this->index();
            return;
        }

        $user = login::getLogged();

        $categoria = new ModelsCategory;
        $categoria->id = $this->getValue("cd");
        $categoria->nome = $this->getValue("nome");
        $categoria->id_usuario = $user->id;
        $categoria->set();

        $this->form($categoria->id,$categoria)->show();
    }
}

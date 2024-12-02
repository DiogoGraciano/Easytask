<?php

namespace app\models;

use diogodg\neoorm\abstract\model;
use diogodg\neoorm\migrations\table;
use diogodg\neoorm\migrations\column;
use app\helpers\mensagem;

final class menu extends model {
    public const table = "menu";

    public function __construct() {
        parent::__construct(self::table,get_class($this));
    }

    public static function table(){
        return (new table(self::table,comment:"Tabela de menus do adm"))
                ->addColumn((new column("id","INT"))->isPrimary()->setComment("ID menu"))
                ->addColumn((new column("controller","VARCHAR",50))->setComment("Nome do controller sem o sufixo"))
                ->addColumn((new column("link","VARCHAR",200))->setComment("link do menu"))
                ->addColumn((new column("tipo_usuario","JSON"))->setComment("array de tipo de usuarios"))
                ->addColumn((new column("nome","VARCHAR",100))->isNotNull()->setComment("Nome do menu"))
                ->addColumn((new column("class_icone","VARCHAR",200))->isNotNull()->setComment("Classe do icone do menu"))
                ->addColumn((new column("ordem","INT"))->isNotNull()->setComment("Ordem Menu"))
                ->addColumn((new column("target_blank","TINYINT"))->setDefaut(0)->isNotNull()->setComment("Abrir em nova guia?"))
                ->addColumn((new column("ativo","TINYINT"))->setDefaut(1)->isNotNull()->setComment("ativo?"));
    }

    public function getByFilter(?string $controller = null,?string $nome = null,?int $ativo = null,?int $limit = null,?int $offset = null,$asArray = true):array|bool
    {
        if($controller){
            $this->addFilter("controller","LIKE","%".$controller."%");
        }

        if($nome){
            $this->addFilter("nome","LIKE","%".$nome."%");
        }

        if($ativo){
            $this->addFilter("ativo","=",$ativo);
        }

        $this->addOrder("ordem","ASC");

        if($limit && $offset){
            self::setLastCount($this);
            $this->addLimit($limit);
            $this->addOffset($offset);
        }
        elseif($limit){
            self::setLastCount($this);
            $this->addLimit($limit);
        }

        if($asArray){
            $this->asArray();
        }

        $result = $this->selectAll();
        
        if($result)
            return $result;
        
        return [];
    }

    public function set():menu|null
    {
        $mensagens = [];

        $this->controller = htmlspecialchars(trim($this->controller));
        $this->link = htmlspecialchars(trim($this->link));

        if($this->id && !self::get($this->id)->id)
            $mensagens[] = "Menu não encontrada";

        if(!($this->nome = htmlspecialchars(trim($this->nome))))
            $mensagens[] = "Nome é obrigatorio";

        if(!$this->controller && !$this->link)
            $mensagens[] = "Controller ou Link deve ser informado"; 

        if(($this->ordem = $this->ordem) < 0)
            $mensagens[] = "Ordem invalida"; 

        if($this->ativo < 0 || $this->ativo > 1)
            $mensagens[] = "O valor de ativo deve ser entre 1 e 0"; 

        if($this->target_blank < 0 || $this->target_blank > 1)
            $mensagens[] = "O valor de abri em nova guia deve ser entre 1 e 0"; 

        if($mensagens){
            mensagem::setErro(...$mensagens);
            return null;
        }

        if ($this->store()){
            mensagem::setSucesso("Menu salvo com sucesso");
            return $this;
        }
        
        return null;
    }

    public static function prepareData(array $dados){
        $finalResult = [];
        foreach ($dados as $dado){

            if(is_subclass_of($dado,"diogodg\\neoorm\db")){
                $dado = $dado->getArrayData();
            }
            
            $dado["ativo"] = $dado["ativo"]?"Sim":"Não";

            $finalResult[] = $dado;
        }

        return $finalResult;
    }

    public static function seed(){
        $menu = new self;
        if(!$menu->addLimit(1)->selectColumns("id")){
            $menu->controller = "task";
            $menu->class_icone = "fa-solid fa-list-check";
            $menu->tipo_usuario = json_encode([1,2]);
            $menu->nome = "Tarefa";
            $menu->ordem = 1;
            $menu->target_blank = 0;
            $menu->ativo = 1;
            $menu->store();
            $menu = new self;
            $menu->controller = "task/listagem";
            $menu->class_icone = "fa-solid fa-list";
            $menu->tipo_usuario = json_encode([2]);
            $menu->nome = "Listagem de Tarefas";
            $menu->ordem = 1;
            $menu->target_blank = 0;
            $menu->ativo = 1;
            $menu->store();
            $menu = new self;
            $menu->controller = "category";
            $menu->class_icone = "fa-solid fa-layer-group";
            $menu->tipo_usuario = json_encode([1,2]);
            $menu->nome = "Categoria";
            $menu->ordem = 1;
            $menu->target_blank = 0;
            $menu->ativo = 1;
            $menu->store();
            $menu = new self;
            $menu->controller = "checkout";
            $menu->class_icone = "fa-solid fa-crown";
            $menu->tipo_usuario = json_encode([1]);
            $menu->nome = "Se Torne Pro";
            $menu->ordem = 1;
            $menu->target_blank = 0;
            $menu->ativo = 1;
            $menu->store();
            $menu = new self;
            $menu->controller = "checkout";
            $menu->class_icone = "fa-solid fa-crown";
            $menu->tipo_usuario = json_encode([2]);
            $menu->nome = "Planos";
            $menu->ordem = 1;
            $menu->target_blank = 0;
            $menu->ativo = 1;
            $menu->store();
            $menu = new self;
            $menu->controller = "login/deslogar";
            $menu->class_icone = "fa-solid fa-right-from-bracket";
            $menu->tipo_usuario = json_encode([0,1,2]);
            $menu->nome = "Deslogar";
            $menu->ordem = 99;
            $menu->target_blank = 0;
            $menu->ativo = 1;
            $menu->store();
        }
    }
}




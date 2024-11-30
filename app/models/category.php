<?php
namespace app\models;

use diogodg\neoorm\abstract\model;
use diogodg\neoorm\migrations\table;
use diogodg\neoorm\migrations\column;
use app\helpers\mensagem;

final class category extends model {
    public const table = "category";

    public function __construct() {
        parent::__construct(self::table,get_class($this));
    }

    public static function table(){
        return (new table(self::table,comment:"Tabela de categoria"))
                ->addColumn((new column("id","INT"))->isPrimary()->setComment("ID"))
                ->addColumn((new column("id_usuario","INT"))->isForeingKey(user::table())->setComment("ID"))
                ->addColumn((new column("nome","VARCHAR",250))->isNotNull()->setComment("Nome"));
    }

    public function getByfilter(?string $nome = null,?int $id_usuario = null,?int $limit = null,?int $offset = null,?bool $asArray = true){

        if($nome)
            $this->addFilter("nome","like","%".$nome."%");

        if($id_usuario)
            $this->addFilter("id_usuario","=",$id_usuario);

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

        return $this->selectAll();
    }

    public function set():category|null
    {
        $mensagens = [];

        if(!($this->nome = htmlspecialchars(trim($this->nome)))){
            $mensagens[] = "Nome é obrigatorio";
        }

        if(!$this->id_usuario || !(new user)->get($this->id_usuario)->id){
            $mensagens[] = "Usuario não encontrado";
        }

        if($mensagens){
            mensagem::setErro(...$mensagens);
            return null;
        }
        
        if ($this->store()){
            mensagem::setSucesso("Categoria salva com sucesso");
            return $this;
        }

        return null;
    }

    public function remove():bool
    {
        if((new task)->get($this->id,"id_categoria")->id){
            mensagem::setErro("Não é possivel excluir a categoria, já existe uma tarefa vinculada a ela, remova a tarefa para poder excluir");
            return false;
        }

        return $this->delete($this->id);
    }
}
<?php

namespace app\models;

use app\helpers\functions;
use app\helpers\mensagem;
use diogodg\neoorm\abstract\model;
use diogodg\neoorm\migrations\table;
use diogodg\neoorm\migrations\column;

final class task extends model {

    public const table = "task";

    public function __construct() {
        parent::__construct(self::table,get_class($this));
    }

    public static function table(){
        return (new table(self::table,comment:"Tabela de tasks"))
                ->addColumn((new column("id","INT"))->isPrimary()->setComment("ID agendamento"))
                ->addColumn((new column("id_usuario","INT"))->isForeingKey(user::table())->setComment("ID da tabela usuario"))
                ->addColumn((new column("titulo","VARCHAR",150))->isNotNull()->setComment("titulo do agendamento"))
                ->addColumn((new column("dt_ini","TIMESTAMP"))->isNotNull()->setComment("Data inicial de agendamento"))
                ->addColumn((new column("dt_fim","TIMESTAMP"))->isNotNull()->setComment("Data final de agendamento"))
                ->addColumn((new column("id_categoria","INT"))->isForeingKey(category::table()))
                ->addColumn((new column("status","VARCHAR",1))->setComment("P = Pendente A = Em andamento C = Concluída"))
                ->addColumn((new column("obs","VARCHAR",1000))->setComment("Observações do agendamento"));
    }

    public function set(){
        
        $mensagens = [];

        if($this->id && !(new self)->get($this->id)->id){
            $mensagens[] = "Agendamento não encontrada";
        }

        if(!$this->id_usuario || !(new user)->get($this->id_usuario)->id){
            $mensagens[] = "Usuario não encontrado";
        }

        if(!$this->id_categoria || !(new category)->get($this->id_usuario)->id){
            $mensagens[] = "Categoria não encontrada";
        }

        if(!$this->titulo = htmlspecialchars(ucwords(strtolower(trim($this->titulo))))){
            $mensagens[] = "Titulo deve ser informado";
        }

        if(!$this->dt_ini = functions::dateTimeBd($this->dt_ini)){
            $mensagens[] = "Data inicial invalida";
        }

        if(!$this->dt_fim = functions::dateTimeBd($this->dt_fim)){
            $mensagens[] = "Data final invalida";
        }

        if($this->status != "P" && $this->status != "A" && $this->status != "C"){
            $mensagens[] = "Status informado invalido";
        }

        if($mensagens){
            mensagem::setErro(...$mensagens);
            return null;
        }

        $this->obs = htmlspecialchars(trim($this->obs));

        if ($this->store()){
            mensagem::setSucesso("Tarefa salva com sucesso");
            return $this;
        }
            
        return null;
    }

    public function getEventsbyFilter(?string $dt_ini = null,?string $dt_fim = null,?int $id_usuario = null,?int $id_categoria = null,?string $status = null):array
    {
        if($dt_ini)
            $this->addFilter("dt_ini",">=",functions::dateTimeBd($dt_ini));
        if($dt_fim)
            $this->addFilter("dt_fim","<=",functions::dateTimeBd($dt_fim));
        if($id_categoria)
            $this->addFilter("id_categoria","=",$id_categoria);
        if($id_usuario)
            $this->addFilter("id_usuario","=",$id_usuario);
        if($status)
            $this->addFilter("status","=",$status);
                      
        $results = $this->selectAll();

        $return = [];

        if ($results){
            foreach ($results as $result){
                $return[] = [
                    'id' => ($result->id),
                    'title' => $result->titulo,
                    'start' => $result->dt_ini,
                    'end' => $result->dt_fim,
                ];
            }
        }
        return $return;
    }
}
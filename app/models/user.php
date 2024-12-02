<?php
namespace app\models;

use diogodg\neoorm\abstract\model;
use diogodg\neoorm\migrations\table;
use diogodg\neoorm\migrations\column;
use app\helpers\functions;
use app\helpers\mensagem;

final class user extends model {
    public const table = "user";

    public function __construct() {
        parent::__construct(self::table,get_class($this));
    }

    public static function table(){
        return (new table(self::table, comment: "Tabela de usuários"))
                ->addColumn((new column("id","INT"))->isPrimary()->isNotNull()->setComment("ID do usuário"))
                ->addColumn((new column("nome", "VARCHAR", 500))->isNotNull()->setComment("Nome do usuário"))
                ->addColumn((new column("senha", "VARCHAR", 150))->setComment("Senha do usuário"))
                ->addColumn((new column("email", "VARCHAR", 200))->isUnique()->setComment("Email do usuário"))
                ->addColumn((new column("tipo_usuario","INT"))->isNotNull()->setComment("Tipo de usuário: 0 -> ADM, 1 -> Usuário, 2 -> Usuário Premium"))
                ->addColumn((new column("id_stripe","VARCHAR", 150))->setComment("ID do usuário stripe"))
                ->addColumn((new column("criado","TIMESTAMP"))->isNotNull()->setDefaut("CURRENT_TIMESTAMP",true));
    }

    public function getByFilter(?int $id_empresa = null,?string $nome = null,?int $tipo_usuario = null,?string $email = null,?int $limit = null,?int $offset = null,?bool $asArray = true):array
    {
        if($id_empresa){
            $this->addFilter("id_empresa", "=", $id_empresa);
        }

        if($nome){
            $this->addFilter("nome","LIKE","%".$nome."%");
        }

        if($email){
            $this->addFilter("email","LIKE","%".$email."%");
        }

        if($tipo_usuario !== null){
            $this->addFilter("tipo_usuario", "=", $tipo_usuario);
        }

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

        return $this->selectColumns(self::table.'.id',self::table.'.nome',self::table.'.cpf_cnpj',self::table.'.telefone',self::table.'.senha',self::table.'.email',self::table.'.tipo_usuario');
    }

    public function prepareData(array $dados){
        $dadosFinal = [];
        if ($dados){
            foreach ($dados as $dado){

                if(is_subclass_of($dado,"diogodg\\neoorm\db")){
                    $dado = $dado->getArrayData();
                }

                if ($dado["cpf_cnpj"]){
                    $dado["cpf_cnpj"] = functions::formatCnpjCpf($dado["cpf_cnpj"]);
                }
                if ($dado["telefone"]){
                    $dado["telefone"] = functions::formatPhone($dado["telefone"]);
                }

                $dadosFinal[] = $dado;
            }
        }
        
        return $dadosFinal;
    }

    public function set():user|null
    {
        $mensagens = [];

        $usuario = (new self);

        if(!($this->nome = htmlspecialchars((trim($this->nome))))){
            $mensagens[] = "Nome é invalido";
        }

        $usuario = $usuario->get($this->id);
        if($this->id && !$usuario->id){
            $mensagens[] = "Usuario não existe";
        }
        
        if($this->tipo_usuario != 2 && $this->tipo_usuario != 1){
            $mensagens[] = "Tipo de usuario invalido";
        }

        $this->senha = $this->senha != $usuario->senha ? password_hash(trim($this->senha),PASSWORD_DEFAULT) : $usuario->senha;

        $this->criado = functions::dateTimeBd("now");

        if($this->id_stripe)
            $this->id_stripe = htmlspecialchars((trim($this->id_stripe)));

        if($mensagens){
            mensagem::setErro(...$mensagens);
            return null;
        }

        if ($this->store()){
            mensagem::setSucesso("Salvo com sucesso");
            return $this;
        }

        mensagem::setErro("Erro ao cadastrar usuario");
        return null;
    }
}
<?php
namespace app\models;

use diogodg\neoorm\abstract\model;
use diogodg\neoorm\migrations\table;
use diogodg\neoorm\migrations\column;
use app\helpers\functions;
use app\helpers\mensagem;

class config extends model {

    public const table = "config";

    public function __construct() {
        parent::__construct(self::table,get_class($this));
    }

    public static function table(){
        return (new table(self::table,comment:"Tabela de config"))
                ->addColumn((new column("id","INT"))->isPrimary()->setComment("ID calendar"))
                ->addColumn((new column("telefone","VARCHAR",15))->setComment("Telefone"))
                ->addColumn((new column("celular","VARCHAR",15))->setComment("Celular"))
                ->addColumn((new column("descricao","VARCHAR",300))->setComment("Meta Descrição"))
                ->addColumn((new column("keywords","VARCHAR",300))->setComment("Meta Palavras Chaves"))
                ->addColumn((new column("nome","VARCHAR",150))->setComment("Nome empresa"))
                ->addColumn((new column("contato_email","VARCHAR",150))->setComment("Contato Email"))
                ->addColumn((new column("stripe_price_id","VARCHAR",150))->setComment("Id do produto/preço na stripe"))
                ->addColumn((new column("smtp_servidor","VARCHAR",150))->setComment("SMTP Servidor"))
                ->addColumn((new column("smtp_port","SMALLINT"))->setComment("SMTP Port"))
                ->addColumn((new column("smtp_encryption","VARCHAR",3))->setComment("SMTP Encryption"))
                ->addColumn((new column("smtp_usuario","VARCHAR",150))->setComment("SMTP Usuario"))
                ->addColumn((new column("smtp_senha","VARCHAR",150))->setComment("SMTP Senha"))
                ->addColumn((new column("recaptcha_site_key","VARCHAR",150))->setComment("Chave Site Recapcha"))
                ->addColumn((new column("recaptcha_secret_key","VARCHAR",150))->setComment("Chave Secreta Recapcha"))
                ->addColumn((new column("recaptcha_minimal_score","TINYINT"))->setComment("Score Minimo Recapcha"))
                ->addColumn((new column("ativo","TINYINT"))->setDefaut(1)->setComment("Ativo"));
    }

    public function set():null|self
    {
        $mensagens = [];
        
        if(!functions::validaCep($this->cep = functions::onlynumber($this->cep))){
            $mensagens[] = "CEP é invalido";
        }

        $this->smtp_encryption   = htmlspecialchars(trim($this->smtp_encryption));

        if($this->smtp_encryption !== "tls" && $this->smtp_encryption !== "ssl"){
            $mensagens[] = "Criptografia SMTP Invalida";
        }

        $this->recaptcha_minimal_score = intval($this->recaptcha_minimal_score);

        if($this->recaptcha_minimal_score < 0 && $this->recaptcha_minimal_score > 10){
            $mensagens[] = "Recapcha Score Minimo deve ser maior que 0 e menor que 10";
        }

        if($mensagens){
            mensagem::setErro(...$mensagens);
            return null;
        }

        $this->telefone = functions::formatPhone($this->telefone);
        $this->celular = functions::formatPhone($this->celular);
        $this->contato_email = htmlspecialchars(trim($this->contato_email));
        $this->nome        = htmlspecialchars(trim($this->nome));
        $this->keywords    = htmlspecialchars(trim($this->keywords));
        $this->descricao   = htmlspecialchars(trim($this->descricao));
        $this->smtp_servidor = htmlspecialchars(trim($this->smtp_servidor));
        $this->smtp_port   = intval($this->smtp_port);
        $this->smtp_usuario = htmlspecialchars(trim($this->smtp_usuario));
        $this->smtp_senha    = htmlspecialchars(trim($this->smtp_senha));
        $this->recaptcha_site_key = htmlspecialchars(trim($this->recaptcha_site_key));
        $this->recaptcha_secret_key = htmlspecialchars(trim($this->recaptcha_secret_key));
        
        if ($this->store()){
            mensagem::setSucesso("Empresa salva com sucesso");
            return $this;
        }
        
        return null;
    }

    public static function seed(){
        $config = new self;
        if(!$config->addLimit(1)->selectColumns("id")){
            $config->ativo = 1;
            $config->store();
        }
    }
}
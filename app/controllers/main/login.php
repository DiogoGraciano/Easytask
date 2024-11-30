<?php 
namespace app\controllers\main;
use app\controllers\abstract\controller;
use app\models\login as ModelsLogin;
use app\models\user;
use app\view\layout\login as lyLogin;
use app\view\layout\elements;
use app\view\layout\form;
use app\helpers\email;
use app\helpers\functions;
use app\helpers\logger;
use app\helpers\mensagem;
use app\helpers\recapcha;
use app\view\layout\email as LayoutEmail;

final class login extends controller{

    public const headTitle = "Login";

    public const permitAccess = true;
    
    public const addHeader = false;

    public function index(array $parameters = []):void
    {
        $login = new lyLogin();
        $login->show();
    }

    public function action(array $parameters = []):void
    {
        //$recapcha = (new recapcha())->siteverify($this->getValue("g-recaptcha-login-response"));

        // if(!$recapcha){
        //     $this->go("login");
        // }

        $usuario = $this->getValue('usuario');
        $senha = $this->getValue('senha');
        
        $login = ModelsLogin::login($usuario,$senha);

        if ($login){
            $this->go("");
        }else {
            $this->go("login");
        }
    }

    public function user(array $parameters = [],?user $user = null):void
    {
        $form = new form($this->url."login/userAction","usuario");//,hasRecapcha:true);

        $dado = $user?:(new user)->get(isset($parameters[0])?$parameters[0]:null);

        $elements = new elements();

        $form->setHidden("cd",$dado->id);

        $form->setElement($elements->titulo(1,"Cadastro de Usuario"))
        ->setElement(
            $elements->input("nome","Nome",$dado->nome,true)
        )->setElement(
            $elements->input("email","Email",$dado->email,true,type:"email")
        )->setElement(
            $elements->input("senha","Senha","",$dado->senha?false:true,type:"password"),
        );

        $form->setButton($elements->button("Salvar", "submitUsuario"));
        $form->setButton($elements->button("Voltar", "voltar", "button", "btn btn-primary w-100 pt-2 btn-block", "location.href='".($this->url)."'"))
        ->show();
    }

    public function userAction(array $parameters = []):void
    {
        // $recapcha = (new recapcha())->siteverify($this->getValue("g-recaptcha-usuario-response"));

        // if(!$recapcha){
        //     $this->user();
        //     return;
        // }

        $usuario               = new user;
        $usuario->id           = intval($this->getValue('cd'));
        $usuario->nome         = $this->getValue('nome');
        $senha                 = $this->getValue('senha');
        $usuario->senha        = $senha;
        $usuario->email        = $this->getValue('email');
        $usuario->tipo_usuario = 1;
        $usuario->ativo        = 1;

        try {
            if ($usuario->set()){
                $user = ModelsLogin::getLogged();
                if(!$user){
                    $user = ModelsLogin::login($usuario->email,$senha);
                    $this->go("");
                }
            }
        } catch (\Exception $e) {
            mensagem::setErro("Erro ao salvar usuário");
            logger::error($e->getMessage());
            login::deslogar();
            $this->user([$usuario->id],$usuario);
            return;
        }
                   
        mensagem::setSucesso(false);
        $this->user([$usuario->id],$usuario);
    }

    public function forget(array $parameters = []):void
    {
        $elements = new elements;

        $form = new form($this->url."login/sendForget","login",hasRecapcha:true);
        $form->setElement($elements->titulo(1,"Esqueci minha senha"));
        $form->setElement($elements->input("email","E-mail","",true,type:"email"));
        $form->setButton($elements->button("Recuperar","recuperar"));
        $form->setButton($elements->button("Voltar", "voltar", "button", "btn btn-primary w-100 pt-2 btn-block", "location.href='".($this->url."login")."'"));
        $form->show();
    }

    public function sendForget(array $parameters = []):void
    {
        $recapcha = (new recapcha())->siteverify($this->getValue("g-recaptcha-login-response"));

        if(!$recapcha){
            $this->forget();
        }

        $usuario = (new user)->getByFilter(email:$this->getValue("email"),limit:1);

        if(isset($usuario[0]) && $usuario = $usuario[0]){
            $email = new email;
            $email->addEmail($usuario["email"]);

            $redefinir = new LayoutEmail();
            $redefinir->setEmailBtn("login/reset/".functions::encrypt($usuario["id"]),"Resetar Senha","Clique no botão a baixo para resetar sua senha, caso não foi você que solicitou essa alteração, pode excluir esse email sem problemas.");

            $email->send("Resetar senha",$redefinir->parse(),true);
            mensagem::setMensagem("Verifique seu email ({$usuario["email"]}) para resetar sua senha");
            $this->go("");
        }

        mensagem::setErro("Nenhum usuario encontrado, revise as campos informados e tente novamente");
        $this->forget();
    }

    public function reset(array $parameters = []){

        if(!isset($parameters[0])){
            (new error())->index();
            return;
        }

        $elements = new elements;

        $form = new form($this->url."login/actionReset/".$parameters[0],hasRecapcha:true);
        $form->setElement($elements->titulo(1,"Resetar Senha"));
        $form->setElement($elements->input("senha","Senha","",true,type:"password"));
        $form->setButton($elements->button("Redefinir","redefinir"));
        $form->setButton($elements->button("Voltar", "voltar", "button", "btn btn-primary w-100 pt-2 btn-block", "location.href='".($this->url."login")."'"));
        $form->show();
    }

    public function actionReset(array $parameters = []):void
    {
        if(!isset($parameters[0])){
            (new error())->index();
            return;
        }

        $usuario = (new user)->get(functions::decrypt($parameters[0]));
        $usuario->senha = $this->getValue("senha");

        if($usuario->set()){
            $this->index();
            return;
        }

        $this->reset($parameters);
    }

    public function deslogar(array $parameters = []):void
    {
        ModelsLogin::deslogar();

        $this->go("login");
    }
}
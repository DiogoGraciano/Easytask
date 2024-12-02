<?php

namespace app\controllers\main;

use app\controllers\abstract\controller;
use app\helpers\mensagem;
use app\models\login;
use app\models\stripe;
use app\models\user;
use app\view\layout\prices;
use app\view\layout\success;
use app\view\layout\cancel;
use core\session;
use core\url;

class checkout extends controller
{
    public const methods = ["success" => ["addHead" => true,"addHeader" => false,"addFooter" => true],"cancel" => ["addHead" => true,"addHeader" => false,"addFooter" => true]];

    public function index(){
        $user = login::getLogged();

        $prices = new prices();
        $prices->addHasBlock("Cadastro Ilimitado de Tarefas");
        $prices->addHasBlock("Cadastro Ilimitado de Categorias");
        $prices->addNotHasBlock("Filtro por datas");
        $prices->addNotHasBlock("Modificar cores tarefas");
        $prices->addNotHasBlock("Remover em massa tarefas",);
        $prices->addNotHasBlock("Atualizar status de tarefas em massa");
        if($user->tipo_usuario == 2)
            $prices->addButtom($this->url."checkout/cancel","Desistir do Pro");
        $prices->setPrice("Gratuito","R$0.00\Mensais",$user->tipo_usuario == 1?true:false);

        $prices->addHasBlock("Cadastro Ilimitado de Tarefas");
        $prices->addHasBlock("Cadastro Ilimitado de Categorias");
        $prices->addHasBlock("Filtro por datas");
        $prices->addHasBlock("Modificar cores tarefas");
        $prices->addHasBlock("Remover em massa tarefas");
        $prices->addHasBlock("Atualizar status de tarefas em massa");
        if($user->tipo_usuario == 1)
            $prices->addButtom($this->url."checkout/stripe","Se Tornar Pro");
        $prices->setPrice("Pro","R$20.00\Mensais",$user->tipo_usuario == 2?true:false);
        $prices->show();
    }

    public function stripe(){
        $stripe = new stripe;
        $stripe->addItem("EasyTask Pro",20);
        $stripeSection = $stripe->createSection(login::getLogged()->email,$this->url."checkout/success",$this->url."checkout/cancel");
        session::set("stripe_on",true);
        url::goToSite($stripeSection->url);
    }

    public function success(){

        session::set("stripe_on",true);

        if(!session::get("stripe_on")){
            (new error())->index();
            return;
        }

        $stripe = new stripe;
        $sectionStripe = $stripe->getSection($this->urlQuery["session_id"]);

        if($sectionStripe && isset($sectionStripe->payment_status) && $sectionStripe->payment_status == "paid"){
            $user = login::getLogged();
            $user = (new user())->get($user->id);
            $user->tipo_usuario = 2;
            $user->id_stripe = $sectionStripe->customer;
            $user->set();
        }

        mensagem::setSucesso("");

        session::set("user",(object)$user->getArrayData());
        session::set("stripe_on",false);

        (new success)->show();
    }

    public function cancel(){
        $user = login::getLogged();

        if(!$user->id_stripe){
            (new error())->index();
            return;
        }

        $stripe = new stripe;
        $stripe->subscriptionCancelAll($user->id_stripe);
        $user = (new user())->get($user->id);
        $user->tipo_usuario = 1;
        $user->id_stripe = null;
        $user->set();

        mensagem::setSucesso("");

        session::set("user",(object)$user->getArrayData());
        session::set("stripe_on",false);

        (new cancel)->show();
    }
}

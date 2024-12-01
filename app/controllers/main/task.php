<?php 
namespace app\controllers\main;
use app\controllers\abstract\controller;
use app\helpers\functions;
use app\helpers\mensagem;
use app\models\category;
use app\view\layout\agenda;
use app\models\login;
use app\models\task as ModelsTask;
use app\view\layout\elements;
use app\view\layout\filter;
use app\view\layout\form;
use app\view\layout\modal;

final class task extends controller{

    public const headTitle = "Tarefa";

    public const permitAccess = true;

    public const methods = ["loadEventos" => ["addHead" => false,"addHeader" => false,"addFooter" => false]];

    public function index(array $parameters = []){

        $id_status = intval($this->getValue("status"));
        $id_categoria = intval($this->getValue("categoria"));

        $elements = new elements;

        $user = login::getLogged();

        $modal = new modal("modal-task","Manutenção de Tarefa",$this->getFormTask()->parse(),"modal fade modal-xl");

        $filter = new filter($this->url."task/index/".$user->id);
        $filter->addbutton($elements->buttonHtmx("Buscar","buscar",$this->url."task/index/".$user->id,"#agenda"));
        $categoria = new category();
        $categorias = $categoria->getByfilter(id_usuario:$user->id);

        $elements->addOption("","Todas");
        foreach ($categorias as $categoria){
            $elements->addOption($categoria["id"],$categoria["nome"]);
        }
        $filter->addFilter(6,$elements->select("categoria","Categoria:",$id_categoria));

        $elements->setOptionsByArray(["" => "Todos","P" => "Pendente","A" => "Em andamento","C" => "Concluída"]);
        $filter->addFilter(6,$elements->select("status","Status:",$id_status));

        $agenda = new agenda();
        $agenda->addModal($modal,"task/updateModal/");
        $agenda->set(
            "task/loadEventos/".$user->id."/".($id_categoria?:0)."/".($id_status?:0)."/",
            "dom,seg,ter,qua,qui,sex,sab",
            "00:00",
            "23:59"
        )->addFilter($filter)->show();
    }

    public function getFormTask($parameters = [],?ModelsTask $task = null,?string $dt_ini = null,?string $dt_fim = null):form
    {
        $elements = new elements;

        $user = login::getLogged();

        $form = new form($this->url."task/action","task");

        $dado = $task?:(new ModelsTask())->get(isset($parameters[0])?$parameters[0]:null);

        $elements->setOptionsByArray(["P" => "Pendente","A" => "Em andamento","C" => "Concluída"]);
        $status = $elements->select("status","Status",$dado->status);

        $categoria = new category();
        $elements->addOption("","Sem Categoria");
        $categorias = $categoria->getByfilter(id_usuario:$user->id);
        foreach ($categorias as $categoria){
            $elements->addOption($categoria["id"],$categoria["nome"]);
        }
        $categoria = $elements->select("categoria","Categoria:",$dado->id_categoria);

        $form->setHidden("cd",$dado->id)
            ->addCustomElement(12,$elements->input("titulo","Titulo",$dado->titulo,true))->setCustomElements()
            ->addCustomElement(12,$categoria)
            ->addCustomElement(12,$status)
            ->addCustomElement("6",$elements->input("dt_ini","Data Inicial:",functions::dateTimeBd($dt_ini?:$dado->dt_ini),true,true,"",type:"datetime-local",class:"form-control form-control-date"),"dt_ini")
            ->addCustomElement("6",$elements->input("dt_fim","Data Final:",functions::dateTimeBd($dt_fim?:$dado->dt_fim),true,true,"",type:"datetime-local",class:"form-control form-control-date"),"dt_fim")
            ->setCustomElements();

        $form->setElement($elements->textarea("obs","Observações:",$dado->obs,false,false,"","3","12"));

        $form->setButton($elements->button("Salvar","submit"));
        if($dado->id){
            $form->setButton($elements->buttonHtmx("Excluir","excluir",$this->url."task/action/".$dado->id,"#modal-task-content",swap:"innerHTML",confirmMessage:"Tem certeza que deseja excluir?",class:"btn btn-danger w-100 pt-2 btn-block"));
        }
        
        return $form;
    }

    public function updateModal(array $parameters = []):void
    {
        $this->getFormTask($parameters,
                            dt_ini:isset($this->urlQuery["start"])?$this->urlQuery["start"]:null,
                            dt_fim:isset($this->urlQuery["end"])?$this->urlQuery["end"]:null)
                            ->show();
    }

    public function action(array $parameters = []):void
    {
        if(isset($parameters[0])){
            $task = (new ModelsTask)->get($parameters[0]);

            if($task->id && $task->remove()){
                mensagem::setSucesso("Removido com Sucesso");
                $this->getFormTask([],null,$task->dt_ini,$task->dt_fim)->show();
                return;
            }

            mensagem::setErro("Erro ao remover");
            $this->getFormTask([],null,$task->dt_ini,$task->dt_fim)->show();
            return;
        }

        $user = login::getLogged();

        $task = new ModelsTask;
        $task->id = $this->getValue("cd");
        $task->id_usuario = $user->id;
        $task->titulo = $this->getValue("titulo");
        $task->id_categoria = $this->getValue("categoria");
        $task->status = $this->getValue("status");
        $task->dt_ini = $this->getValue("dt_ini");
        $task->dt_fim = $this->getValue("dt_fim");
        $task->obs = $this->getValue("obs");
        $task->set();

        $this->getFormTask([],$task)->show();
    }

    public function loadEventos(array $parameters = []):void
    {
        if(isset($this->urlQuery["start"],$this->urlQuery["end"],$parameters[0])){
            $eventos = (new ModelsTask)->getEventsbyFilter(
                        functions::dateTimeBd($this->urlQuery["start"]),
                        functions::dateTimeBd($this->urlQuery["end"]),
                        $parameters[0],
                        isset($parameters[1])?$parameters[1]:null,
                        isset($parameters[2])?$parameters[2]:null
                    );
            echo json_encode($eventos);
            return;
        }

        echo json_encode([]);
    }
}
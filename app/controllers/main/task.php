<?php 
namespace app\controllers\main;
use app\controllers\abstract\controller;
use app\helpers\functions;
use app\helpers\mensagem;
use app\models\category;
use app\view\layout\agenda;
use app\models\login;
use app\models\task as ModelsTask;
use app\view\layout\consulta;
use app\view\layout\elements;
use app\view\layout\filter;
use app\view\layout\form;
use app\view\layout\modal;
use app\view\layout\pagination;
use diogodg\neoorm\connection;

final class task extends controller{

    public const headTitle = "Tarefa";

    public const methods = ["loadEventos" => ["addHead" => false,"addHeader" => false,"addFooter" => false]];

    public function index(array $parameters = []){

        $status = $this->getValue("status");
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
        $filter->addFilter(3,$elements->select("categoria","Categoria:",$id_categoria));

        $elements->setOptionsByArray(["" => "Todos","P" => "Pendente","A" => "Em andamento","C" => "Concluída"]);
        $filter->addFilter(3,$elements->select("status","Status:",$status));

        $agenda = new agenda();
        $agenda->addModal($modal,"task/updateModal/");
        $agenda->set(
            "task/loadEventos/".$user->id."/".($id_categoria?:0)."/".($status?:0)."/",
            "dom,seg,ter,qua,qui,sex,sab",
            "00:00",
            "23:59"
        )->addFilter($filter)->show();
    }

    public function listagem(array $parameters = []){

        $user = login::getLogged();

        if($user->tipo_usuario == 1){
            (new error)->index();
            return;
        }

        $elements = new elements;

        $formModal = new form("/task/massStatus","massStatus","#consulta-admin","#consulta-admin","Tem certeza que deseja alterar o status?");
        $elements->setOptionsByArray(["P" => "Pendente","A" => "Em andamento","C" => "Concluída"]);
        $formModal->setElement(3,$elements->select("statusMass","Status:"));
        $formModal->setButton($elements->button("Alterar Status","massStatus"));
        $modal = new modal("modal-status","Alterar Status Em Massa",$formModal->parse());
        $modal->show();

        $cadastro = new consulta(true,"Consulta Tarefas");

        $id_categoria = intval($this->getValue("categoria"));
        $status = $this->getValue("status");
        $dt_ini = $this->getValue("dt_ini");
        $dt_fim = $this->getValue("dt_fim");

        $filter = new filter($this->url."task/listagem");
        $filter->addbutton($elements->button("Buscar","buscar","submit","btn btn-primary pt-2"));

        $categoria = new category();
        $categorias = $categoria->getByfilter(id_usuario:$user->id);

        $elements->addOption("","Todas");
        foreach ($categorias as $categoria){
            $elements->addOption($categoria["id"],$categoria["nome"]);
        }
        $filter->addFilter(3,$elements->select("categoria","Categoria:",$id_categoria));

        $elements->setOptionsByArray(["" => "Todos","P" => "Pendente","A" => "Em andamento","C" => "Concluída"]);
        $filter->addFilter(3,$elements->select("status","Status:",$status));

        $filter->addFilter(3, $elements->input("dt_ini","Data Inicial:",$dt_ini,false,false,type:"datetime-local",class:"form-control form-control-date"));
        $filter->addFilter(3, $elements->input("dt_fim","Data Final:",$dt_fim,false,false,type:"datetime-local",class:"form-control form-control-date"));

        $cadastro->addButtons($elements->buttonModal("Adicionar","manutencao","#modal-task"))
                ->addButtons($elements->buttonModal("Alterar Status","massStatus","#modal-status"))
                ->addButtons($elements->buttonHtmx("Deletar Tarefa","tarefadelete","massDelete","#consulta-admin",confirmMessage:"Tem certeza que deseja deletar?",includes:"#consulta-admin"));

        $cadastro->addColumns("1","Id","id")
                ->addColumns("15","Titulo","titulo")
                ->addColumns("12","Data Inicial","dt_ini")
                ->addColumns("12","Data Final","dt_fim")
                ->addColumns("10","Categoria","category")
                ->addColumns("10","Status","status")
                ->addColumns("13","Ações","acoes");

        $task = new ModelsTask;
        $dados = $task->prepareList($task->getByFilter($dt_ini,$dt_fim,$user->id,$id_categoria,$status,$this->getLimit(),$this->getOffset()));
       
        $modal = new modal("modal-task","Manutenção de Tarefa",$this->getFormTask()->parse(),"modal fade modal-xl");

        $cadastro
        ->setData($this->url."task/updateModal",$this->url."task/action",$dados,"id")
        ->addMaintenceModal($modal,"task/index")
        ->addPagination(new pagination(
            $task::getLastCount("getByFilter"),
            "task/listagem",
            "#consulta-admin",
            limit:$this->getLimit()))
        ->addFilter($filter)
        ->show();
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

        $form->setHidden("cd",$dado->id);

        if($user->tipo_usuario == 2){
            $form->addCustomElement("1 col-sm-12 mb-2",$elements->input("cor","Cor:",$dado->cor?:"#4267b2",false,false,"",type:"color",class:"form-control form-control-color"))
                ->addCustomElement(11,$elements->input("titulo","Titulo",$dado->titulo,true));
        }else{
            $form->addCustomElement(12,$elements->input("titulo","Titulo",$dado->titulo,true));
        }
           
        $form->addCustomElement(12,$categoria)
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

    public function massDelete(array $parameters = []):void
    {
        try{
            connection::beginTransaction();

            $ids = $this->getValue("massaction");

            $mensagem = "";
            $mensagem_erro = "";

            if ($ids){
                foreach ($ids as $id) {
                    $task = (new ModelsTask)->get($id);
                    if($task->remove())
                        $mensagem .= $task->id." <br> ";
                    else
                        $mensagem_erro .= $task->id." <br> ";
                }
                $mensagem_erro = rtrim($mensagem_erro," <br> ");
                $mensagem = rtrim($mensagem," <br> ");
            }
            else{
                mensagem::setErro("Selecione ao menos um agendamento");
                $this->listagem();
                return;
            }

        }catch(\Exception $e){
            mensagem::setSucesso(false);
            mensagem::setErro("Erro inesperado ocorreu, tente novamente",$e->getMessage());
            connection::rollback();
        }

        if($mensagem)
            mensagem::setSucesso("Agendamentos cancelados com sucesso: <br>".$mensagem);

        if($mensagem_erro)
            mensagem::setErro("Agendamentos não cancelados: <br>".$mensagem_erro);

        connection::commit();

        $this->listagem();
    }

    public function massStatus(array $parameters = []):void
    {
        try{
            connection::beginTransaction();

            $ids = $this->getValue("massaction");
            $status = $this->getValue("statusMass");

            $mensagem = "";
            $mensagem_erro = "";

            if ($ids){
                foreach ($ids as $id) {
                    $task = (new ModelsTask)->get($id);
                    $task->status = $status;
                    if($task->set())
                        $mensagem .= $task->id." <br> ";
                    else
                        $mensagem_erro .= $task->id." <br> ";
                }
                $mensagem_erro = rtrim($mensagem_erro," <br> ");
                $mensagem = rtrim($mensagem," <br> ");
            }
            else{
                mensagem::setErro("Selecione ao menos uma conta");
                $this->listagem();
                return;
            }

        }catch(\Exception $e){
            mensagem::setSucesso(false);
            mensagem::setErro("Erro inesperado ocorreu, tente novamente",$e->getMessage());
            connection::rollback();
        }

        if($mensagem)
            mensagem::setSucesso("Contas pagas com sucesso: <br>".$mensagem);

        if($mensagem_erro)
            mensagem::setErro("Contas não pagas: <br>".$mensagem_erro);

        connection::commit();

        $this->listagem();
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
        $task->cor = $this->getValue("cor")?:"#4267b2";
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
<?php

namespace app\view\layout;

use app\view\layout\abstract\layout;
use app\view\layout\elements;

class consulta extends layout
{
    private array $buttons = [];
    private tabelaMobile|tabela $table;
    private bool $massaction = false;
    private bool $hasMaintenceModal = false;
    private string $idMaintenceModal = "";

    public function __construct(bool $massaction = false,string $titulo = "Consulta")
    {
        $this->table = $this->isMobile() ? new tabelaMobile : new tabela;

        $this->massaction = $massaction;

        if ($this->massaction) 
            $this->table->addColumns("1", $this->isMobile() ? "Selecionar" : "","massaction");

        $this->setTemplate("consulta.html");

        $this->tpl->titulo = $titulo;
    }

    public function addFilter(filter $filter){
        $this->tpl->filter = $filter->parse();
        return $this;
    }

    public function addDiv(div $div){
        $this->tpl->div = $div->parse();
        return $this;
    }

    public function addFilterModal(modal $modal){
        $this->tpl->modal = $modal->parse();
        $this->tpl->block("BLOCK_MODAL");
        return $this;
    }

    public function addMaintenceModal(modal $modal,string $urlUpdateConsulta = ""){
        $this->hasMaintenceModal = true;
        $this->idMaintenceModal = $modal->getId();
        $this->tpl->id_modal_maintence = $this->idMaintenceModal;
        $modal->show();
        $this->tpl->url_update_consulta = $urlUpdateConsulta;
        $this->tpl->block("BLOCK_MODAL_MAINTENCE");
        return $this;
    }

    public function addPagination(pagination $pagination){
        $this->tpl->pagination = $pagination->parse();
        $this->tpl->block('BLOCK_PAGINATION');
        return $this;
    }

    public function setData(string $pagina_manutencao,string $pagina_action,null|bool|array $dados,string $coluna_action = "id"):consulta
    {
        foreach ($this->buttons as $button) {
            $this->tpl->button = $button;
            $this->tpl->block("BLOCK_BUTTONS");
        }

        if($this->hasMaintenceModal){
            $this->tpl->pagina_manutencao = $pagina_manutencao;
        }
        
        if (isset($dados[0])) {
            $elements = (new elements);
            foreach ($dados as $data) {
                if(is_subclass_of($data,"diogodg\\neoorm\db")){
                    $data = $data->getArrayData();
                }

                if(is_array($data) && array_key_exists($coluna_action,$data)){
                    if($this->massaction)
                        $data["massaction"] = $elements->checkbox("massaction[]", false, false, false, false, $data[$coluna_action]);

                    if($this->hasMaintenceModal)
                        $data["acoes"] = $elements->button("Editar","editar","button",class:"btn btn-primary",action:"updateModal(this)",extra:'consulta-id="'.$data[$coluna_action].'"');
                    else
                        $data["acoes"] = $elements->button("Editar","editar","button",class:"btn btn-primary",action:"location.href='".$pagina_manutencao.'/'.$data[$coluna_action]."'");
                    
                    $data["acoes"] .= $elements->buttonHtmx("Excluir","excluir",$pagina_action.'/'.$data[$coluna_action],"#consulta-admin",class:"btn btn-primary",confirmMessage:"Deseja realmente excluir e os possiveis registros vinculados?");
                }

                if(is_array($data))
                    $this->table->addRow($data);
            }
            $this->tpl->table = $this->table->parse();

            if($this->massaction)
                $this->tpl->block("BLOCK_MASSACTION");

        }else{
            $this->tpl->block('BLOCK_SEMDADOS');
        }

        return $this;
    }

    public function addColumns(string|int $width,string $nome,string $coluna):consulta
    {
        $this->table->addColumns($width,$nome,$coluna);
        return $this;
    }

    public function addButtons(string $button):consulta
    {
        $this->buttons[] = $button;
        return $this;
    }
}

?>

<?php

namespace app\view\layout;

use app\helpers\functions;
use app\view\layout\abstract\layout;

class modal extends layout{

    private string $id = "modal";

    public function __construct(string $id = "modal",string $title = "Modal",string $content = "",string $class = "modal fade")
    {
        $this->setTemplate("modal.html");

        $this->id = functions::createNameId($id);
        $this->tpl->id = $this->id;
        $this->tpl->title = $title;
        $this->tpl->class = $class;
        $this->tpl->content = $content;
    }

    public function getId(){
        return $this->id;
    }
}

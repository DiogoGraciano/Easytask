<?php

namespace app\view\layout;

use app\view\layout\abstract\layout;

class prices extends layout
{
    public function __construct()
    {
        $this->setTemplate("prices.html");
    }

    public function addHasBlock(string $title):prices
    {
        $this->tpl->title = $title;
        $this->tpl->block("BLOCK_HAS");

        return $this;
    }

    public function addNotHasBlock(string $title):prices
    {
        $this->tpl->title = $title;
        $this->tpl->block("BLOCK_NOT_HAS");

        return $this;
    }

    public function addButtom(string $buttom_link,string $buttom_label):prices
    {
        $this->tpl->buttom_link = $buttom_link;
        $this->tpl->buttom_label = strtoupper($buttom_label);
        $this->tpl->block("BLOCK_BUTTOM");

        return $this;
    }

    public function setPrice(string $name,string $price,bool $is_selected = false):prices
    {
        $this->tpl->name = $name;
        $this->tpl->price = $price;
        if($is_selected){
            $this->tpl->block("BLOCK_SELECTED");
        }
        $this->tpl->block("BLOCK_PRICING");

        return $this;
    }
}

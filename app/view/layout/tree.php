<?php

namespace app\view\layout;

use app\view\layout\abstract\layout;

class tree extends layout
{
    private elements $elements;
    private string $html = "";

    public function __construct(array $treeArray)
    {
        $this->elements = new elements();
        $this->html = $this->buildTree($treeArray);
    }

    private function buildTree(array $treeArray): string
    {
        $listItems = "";

        foreach ($treeArray as $node) {
            $hasChildren = isset($node['children']) && is_array($node['children']) && count($node['children']) > 0;

            $content = $this->elements->span($node['text'], "");

            if ($hasChildren) {
                $content = $this->elements->span($node['text'], "caret");
                $content .= $this->buildTree($node['children']);
            }

            $listItems .= $this->elements->li($content, "list-group-item");
        }

        return $this->elements->ul($listItems, "nested list-group");
    }

    public function parse(): string
    {
        return preg_replace("/nested/","tree",$this->html,1);
    }

    public function show():void{
        echo preg_replace("/nested/","tree",$this->html,1);
    }
}

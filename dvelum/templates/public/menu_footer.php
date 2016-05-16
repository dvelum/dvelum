<?php

$createFooterNode = function (Tree $tree , $parent , Page $page , Tree $pagesTree) use(&$createFooterNode)
{
    $s = '';

    if(!$tree->hasChilds($parent))
        return '';

    $childs = $tree->getChilds($parent);

    ($parent === 0) ? $isSection = true : $isSection = false;


    foreach($childs as $k => $v)
    {
        if(!$v['data']['published'])
            continue;

        if($isSection){
            $s .= '<div class="section">';
        }else{
            $s .= '<li>';
        }

        $class='';

        if($page->code === $v['data']['page_code'] || in_array($v['data']['page_id'] , $pagesTree->getParentsList($page->id) , true))
            $class.='active';

        if($v['data']['link_url'] !== false){
            $s .= '<a  href="' . $v['data']['link_url'] . '" class="'.$class.'">' . $v['data']['title'] . '</a>';
        }else{
            $s .=  '<span class="item">' . $v['data']['title'] . '</span>';
        }

        if($tree->hasChilds($v['id'])){
            $s .='<ul>';
            $s .= $createFooterNode($tree , $v['id'] , $page , $pagesTree);
            $s .= '</ul>';
        }



        if($isSection){
            $s .= '</div>';
        }else{
            $s .= '</li>';
        }
    }

    return $s;
};

$pagesTree = $this->get('pagesTree');

$tree = new Tree();
$menuData = $this->get('menuData');

if(!empty($menuData))
    foreach($menuData as $k => $v)
        $tree->addItem($v['tree_id'] , $v['parent_id'] , $v , $v['order']);

echo '<div class="menu">'.$createFooterNode($tree , 0 , $this->get('page') , $pagesTree).'</div>';


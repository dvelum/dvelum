<?php

$createNode = function ($tree , $parent , Page $page , Tree $pagesTree) use(&$createNode)
{
    $s = '';

    if(!$tree->hasChilds($parent))
        return '';

    $items = $tree->getChilds($parent);

    $s .= '<ul>';

    foreach($items as $k => $v)
    {
        if(!$v['data']['published'])
            continue;

        $class='';

        if($page->code === $v['data']['page_code'] || in_array($v['data']['page_id'] , $pagesTree->getParentsList($page->id) , true))
            $class = 'active';

        $s .= '<li><a href="' . $v['data']['link_url'] . '" class="'.$class.'">' . $v['data']['title'] . '</a></li>';

        // if($tree->hasChilds($v['id']))
        // $s.='<li>' . $createNode($tree , $v['id'] , $page, $pagesTree) . '</li>';
    }
    $s .= '</ul>';
    return $s;
};

$pagesTree = $this->get('pagesTree');
$tree = new Tree();
$menuData = $this->get('menuData');

if(!empty($menuData))
  foreach($menuData as $k => $v)
    $tree->addItem($v['tree_id'] , $v['parent_id'] , $v , $v['order']);

echo '<nav class="nav top">' . $createNode($tree , 0 , $this->get('page') , $pagesTree) . '</nav>';
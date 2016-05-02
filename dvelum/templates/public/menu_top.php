<?php

$createNode = function ($tree , $parent , Page $page , Tree $pagesTree) use(&$createNode)
{
  $s = '';
  
  if(! $tree->hasChilds($parent))
    return '';
  
  $childs = $tree->getChilds($parent);
  
  $first = false;
  
  foreach($childs as $k => $v)
  {
    if(! $v['data']['published'])
      continue;
    
    if($parent == 0)
      if($first)
        $s .= '<div class="first sec">';
      else
        $s .= '<div class="first">';
    
    $s .= '<ul>';
    if($page->code === $v['data']['page_code'] || in_array($v['data']['page_id'] , $pagesTree->getParentsList($page->id) , true))
      $s .= '<li class="active">';
    else
      $s .= '<li>';
    
    $s .= '<a href="' . $v['data']['link_url'] . '"><span>' . $v['data']['title'] . '</span></a>
         </li>';
    
    // if($tree->hasChilds($v['id']))
    // $s.='<li>' . $createNode($tree , $v['id'] , $page) . '</li>';
    
    $s .= '</ul>';
    
    if($parent == 0)
      $s .= '</div>';
    
    $first = true;
  }
  return $s;
};

$pagesTree = $this->get('pagesTree');
$tree = new Tree();
$menuData = $this->get('menuData');
if(!empty($menuData))
  foreach($menuData as $k => $v)
    $tree->addItem($v['tree_id'] , $v['parent_id'] , $v , $v['order']);

echo $createNode($tree , 0 , $this->get('page') , $pagesTree);
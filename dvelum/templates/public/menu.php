<?php 


$createMenuNode = function (Tree $tree , $parent , Page $page  , Tree $pagesTree) use (&$createMenuNode)
{
	 $s='';
	 
	 if(!$tree->hasChilds($parent))
	 	return '';
	 	
	 $childs = $tree->getChilds($parent);
	 
     foreach ($childs as $k=>$v)
     {
     	if(!$v['data']['published'])
     		continue;

         if($page->code === $v['data']['page_code'] || in_array($v['data']['page_id'], $pagesTree->getParentsList($page->id),true))
             $class='active';
         else
             $class='';
          
         $s.='<div class="menuNode">
                 <div class="'.$class.'">
                 	<a href="'.$v['data']['link_url'].'">'.$v['data']['title'].'</a>
                 </div>';
          
         if($tree->hasChilds($v['id']))
             $s.=$createMenuNode($tree , $v['id'] , $page , $pagesTree);
          
         $s.='</div>';
      } 
      return $s;     
};

$pagesTree = $this->get('pagesTree');
$tree = new Tree();
$menuData = $this->get('menuData');
$config = $this->get('config');

if(is_array($menuData) && !empty($menuData))
   foreach ($menuData as $k=>$v)
      $tree->addItem($v['tree_id'], $v['parent_id'], $v , $v['order']);

?>
<div class="blockItem">
<?php 
	if($config['show_title']) 
		echo '<div class="blockTitle">' , $config['title'] , '</div>'; 
?>
	<div class="blockContent">	
<?php 
	if($tree->hasChilds(0))
	  echo $createMenuNode($tree , 0 , $this->get('page') , $pagesTree);
?>
	</div>
</div>




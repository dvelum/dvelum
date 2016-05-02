<?php
if(!defined('DVELUM'))exit;

function recursiveElement(Designer_Debugger $debugger , $parent = 0)
{
    $s='';
    $tree = $debugger->getTree();
    $childs = $tree->getChilds($parent);

    foreach ($childs as $k=>$v)
    {
        $item = $v['data'];
        $name = $v['id'];
        $hasChilds = $tree->hasChilds($name);

        $s.='<div class="objectInfo" id="o_inf_'.$name.'">
                <div class="objectName">
                    <div class="collapseButton" data-id="'.$name.'" data-prefix="o_item_">
                       <img id="img_o_item_'.$name.'"  src="'.Request::wwwRoot().'i/system/plus.gif" align="left"><div class="catName">'. $name . '</div>
                   </div>
                </div>
            <div class="sep"></div>
            <div class="objectItem collapsed" id="o_item_'.$name.'">' . commentItem($debugger , $name);
             
            if($hasChilds)
                $s.= ' <b>[Childs]</b>: <br>' . recursiveElement($debugger , $name);
             
         $s.='</div>
        </div>';
    }
    return $s;
}

function commentItem(Designer_Debugger $debugger , $name)
{
    $wwwRoot = Request::wwwRoot();
    $s= '
       <div class="objectBody">
           <span class="title">' . $debugger->getObjectPHPClass($name) . ' <b>' . $name . '</b> [' . $debugger->getObjectExtClass($name) . ']</span>   
       </div>  
       
       <div class="collapseButton" data-id="'.$name.'" data-prefix="prop_">
           <img id="img_prop_'.$name.'"  src="'.$wwwRoot.'i/system/plus.gif" align="left"><div class="catName">[Ext Properties]</div>
       </div>
       <div class="sep"></div>
       '.objectProperties($debugger->getObjectProperties($name , true) , 'prop_'.$name).' 

               
      <div class="collapseButton" data-id="'.$name.'" data-prefix="events_">
           <img id="img_events_'.$name.'"  src="'.$wwwRoot.'i/system/plus.gif" align="left"><div class="catName">[Ext Events]</div>
       </div>
       <div class="sep"></div>
       '.objectEvents($debugger->getObjectEvents($name) , 'events_'.$name).'          

       ';

    
    if($debugger->isExtendedObject($name))
    {
      $s.='
      <div class="collapseButton" data-id="'.$name.'" data-prefix="extmethods_">
      <img id="img_extmethods_'.$name.'"  src="'.$wwwRoot.'i/system/plus.gif" align="left"><div class="catName">[Local JS Methods]</div>
      </div>
      <div class="sep"></div>
      '.objectLocalMethods($debugger->getObjectLocalMethods($name) , 'extmethods_'.$name).
      '<div class="sep"></div>';
      
    }
    
       
               
    $s.='<div class="collapseButton" data-id="'.$name.'" data-prefix="vars_">
           <img id="img_vars_'.$name.'"  src="'.$wwwRoot.'i/system/plus.gif" align="left"> <div class="catName">[PHP Variables]</div>
       </div>
       <div class="sep"></div>
       '.objectVariables($debugger->getObjectVariables($name) , 'vars_'.$name).'   

               
       <div class="collapseButton" data-id="'.$name.'" data-prefix="method_">
           <img id="img_method_'.$name.'"  src="'.$wwwRoot.'i/system/plus.gif" align="left"><div class="catName">[PHP Methods]</div>
       </div>
       <div class="sep"></div>
       '.objectMethods($debugger->getObjectMethods($name) , 'method_'.$name).' 
                           
       <div class="sep"></div>';
    
    return $s;
}

function objectProperties(array $data , $id)
{
    $s='<table id="'.$id.'" width="100%" class="collapsed" border="0" cellspadding="0" cellspacing="0">';
    $s.= '<tr>
             <th>Name</th>
             <th>Value</th>
        </tr>';
    foreach ($data as $key=>$value)
        $s.='<tr class="row" >
                <td width="150">'.$key.'</td>
                <td>'.$value.' &nbsp;</td>
             </tr>';
    
    $s.='</table>';
    return $s;
}

function objectEvents(array $data , $id)
{
    $s='<table id="'.$id.'" width="100%" class="collapsed" border="0" cellspadding="0" cellspacing="0">';
    $s.= '<tr>
             <th>Is Local</th>
             <th>Event</th>
             <th>Arguments</th>
             <th>Code</th>
        </tr>';
    foreach ($data as $event)
    {
        $paramsList = array();
        if(!empty($event['params']))
            foreach ($event['params'] as $name=>$type)
                $paramsList[] = '<span style="color:green;">'.$type.'</span> '.$name;
        
        
        if(isset($event['is_local']) && $event['is_local'])
          $isLocal = 'Yes';
        else
          $isLocal = '&nbsp;';
        
        $s.='<tr class="row" >
                <td width="20">'.$isLocal.'</td>
                <td width="120">'.$event['name'].'</td>
                <td width="300">'.implode(',<br>',$paramsList).'</td>
                <td><pre>'. $event['value'] .'</pre></td>
             </tr>';
    }
    $s.='</table>';
    return $s;
}

function objectVariables(array $data , $id)
{
    $s='<table id="'.$id.'" width="100%" class="collapsed" border="0" cellspadding="0" cellspacing="0">';
    $s.= '<tr>
             <th>Name</th>
             <th>Value</th>   
        </tr>';
    
    foreach ($data as $item)
    {
        $s.='<tr class="row" >
                <td width="120">'.$item['access'].'</td>
                <td width="150">'.$item['name'].'</td>
                <td><pre>'. var_export($item['value'] , true).'</pre></td>
              </tr>';
    }
    $s.='</table>';
    return $s;
}

function objectMethods(array $data , $id)
{
    $s='<table id="'.$id.'" width="100%" class="collapsed" border="0" cellspadding="0" cellspacing="0">
        <tr>
             <th>Modifiers</th>
             <th>Name</th>
             <th>Params</th>   
        </tr>';
    foreach ($data as $item)
    {
        $s.='<tr class="row" >
                <td width="120">'.$item['access'].'</td>
                <td width="150">'.$item['name'].'</td>
                <td>('. implode(' , ',$item['params']).')</td>
             </tr>';
    }
    $s.='</table>';
    return $s;
}

function objectLocalMethods(array $methods , $id)
{
  $s='<div id="'.$id.'" style=="width:100%" class="collapsed">';
  if(!empty($methods)){
    foreach ($methods as $item){
      $s.='<div><pre>'.$item->getJsDoc().'<br>'.$item->getName().':function('.$item->getParamsLine().'){'."\n".Utils_String::addIndent($item->getCode())."\n".'}</pre></div>';
    }
  }
  $s.='</div>';
  return $s;
}


$project = $this->get('project');

if(!$project)
    exit;


$debugger = new Designer_Debugger($project);
$wwwRoot = Request::wwwRoot();

$res = Resource::getInstance();
$res->addCss('/css/system/style.css' , 2);
$res->addCss('/css/system/gray/style.css' , 3);
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

<script type="text/javascript" src="<?php echo $wwwRoot;?>js/lib/jquery-2.0.0.min.js"></script>
<title>Project Debug</title>
    <?php
    echo $res->includeCss();
    ?>
</head>
<body>
<?php 

if($debugger->getTree()->hasChilds(0))
    echo recursiveElement($debugger , 0);
?>
<script type="text/javascript">
$(document).ready(function(){	
    $('.collapseButton').bind('click',function(){
    	var dataId = $(this).attr('data-id');
    	var dataPrefix = $(this).attr('data-prefix');    	
    	var item = $('#' + dataPrefix + dataId);

    	if(item.hasClass('collapsed')){
    	    item.show();
    	    $('#img_' + dataPrefix + dataId).attr('src' , '<?php echo $wwwRoot;?>i/system/minus.gif');
    	    item.removeClass('collapsed');
    	   
    	}else{
    		item.hide();
    	    $('#img_' + dataPrefix + dataId).attr('src' , '<?php echo $wwwRoot;?>i/system/plus.gif');
    	    item.addClass('collapsed');
    	}
    });
});
</script>
</body>
</html>
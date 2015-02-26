<?php
if(!defined('DVELUM'))exit;

$stdModules = '';
$devModules = '';
$lang = Lang::lang();
$modules = $this->get('userModules');
asort($modules);
$wwwRoot = Request::wwwRoot();

$modList = $this->get('modules');


foreach($modules as $k=>$v)
{
	if(!isset($modList[$k]) || !$modList[$k]['active'] || (isset($modList[$k]['in_menu']) && !$modList[$k]['in_menu']))
		continue;

	if(strtolower($this->get('urlPath')) === strtolower($k))
		$class='class="mSelected"';
	else
		$class = '';

    if(!isset($modList[$k]['dev']) || !$modList[$k]['dev'])
        $stdModules .= '<li class="dv_mainMenuItem" ><a href="' . Request::url(array($this->get('adminPath'),$k)) . '"><div '.$class.'>' . $modList[$k]['title'] .'</div></a></li>';
    else
        $devModules .= '<li class="dv_mainMenuItem" ><a href="' . Request::url(array($this->get('adminPath'),$k)) . '"><div '.$class.'>' . $modList[$k]['title'] .'</div></a></li>';
}
?>
<div id="mainMenu" class="x-hidden ">
	<ul class="dv_mainMenu">
        <?php  echo $stdModules; ?>
       <li class="dv_mainMenuItem"><a href="?logout=1"><div><img src="<?php echo $wwwRoot;?>i/system/designer/exit.png" border="0" title="<?php echo $lang->get('LOGOUT');?>"></div></a></li>
	</ul>
</div>
	<div id="systemMenu" class="x-hidden ">
		<ul class="dv_mainMenu">
<?php
if($this->get('development'))
	 echo $devModules;
 ?>
		</ul>
	</div>
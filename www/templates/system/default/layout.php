<?php
if(!defined('DVELUM'))exit;

	header('Content-Type: text/html; charset=utf-8');

	$res = Resource::getInstance();
	$res->addJs('/js/app/system/common.js' , -2);
	$res->addJs('/js/app/system/Application.js' , -1);

	$res->addJs('/js/lib/jquery.js', 1 , true , 'head');

	if($this->get('development'))
	    $res->addJs('/js/lib/extjs4/ext-all-debug.js', 2 , true , 'head');
	else
	    $res->addJs('/js/lib/extjs4/ext-all.js', 2 , true , 'head');

	$res->addJs('/js/lang/'.$this->get('lang').'.js', 3 , true , 'head');
	$res->addJs('/js/lib/extjs4/locale/ext-lang-'.$this->get('lang').'.js', 4 , true , 'head');

	$res->addInlineJs('var developmentMode = '.intval($this->get('development')).';');
	$res->addCss('/js/lib/extjs4/resources/css/ext-all-gray.css' , 1);
	$res->addCss('/templates/system/default/css/style.css' , 2);


	$token = '';
	if($this->get('useCSRFToken')){
		$csrf = new Security_Csrf();
		$token = $csrf->createToken();
	}

	$wwwRoot = Request::wwwRoot();
?>
<!DOCTYPE html>
<html>
<head>
<?php /*<BASE href="<?php echo Request::baseUrl();?>">*/?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<?php
	if($this->get('useCSRFToken'))
		echo '<meta name="csrf-token" content="'.$token.'"/>';
?>
<title><?php echo $this->get('page')->title;?>  .:: ADMIN PANEL ::.  </title>
<link rel="shortcut icon" href="<?php echo $wwwRoot;?>i/favicon.png" />
<?php
 echo $res->includeCss();
 echo $res->includeJsByTag(true , false , 'head');
 ?>
</head>
<body>
<?php  echo $this->render($this->get('path') . 'menu.php'); ?>
<div id="header" class="x-hidden">
 <div class="sysVersion"><img src="<?php echo $wwwRoot;?>i/logo-s.png" /><span class="num"><?php echo $this->get('version');?></span></div>
</div>
<?php echo $res->includeJs(true , false); ?>
</body>
</html>

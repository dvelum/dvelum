<?php
if(!defined('DVELUM'))exit;

	header('Content-Type: text/html; charset=utf-8');

	$theme = $this->get('theme');

	$res = Resource::getInstance();
	$res->addJs('/js/app/system/common.js' , -2);
	$res->addJs('/js/app/system/Application.js' , -1);

	$res->addJs('/js/lib/jquery.js', 1 , true , 'head');
	$res->addJs('/js/lang/'.$this->get('lang').'.js', 1 , true , 'head');

	if($this->get('development'))
	    $res->addJs('/js/lib/ext6/build/ext-all-debug.js', 2 , true , 'head');
	else
	    $res->addJs('/js/lib/ext6/build/ext-all.js', 2 , true , 'head');

	$res->addJs('/js/lib/ext6/build/theme-'.$theme.'/theme-'.$theme.'.js', 3 , true , 'head');

	$res->addJs('/js/lib/ext6/build/locale/locale-'.$this->get('lang').'.js', 4 , true , 'head');

	$res->addInlineJs('var developmentMode = '.intval($this->get('development')).';');

    $res->addCss('/js/lib/ext6/build/theme-'.$theme.'/resources/theme-'.$theme.'-all.css' , 1);
    $res->addCss('/css/system/style.css' , 2);
	$res->addCss('/css/system/'.$theme.'/style.css' , 3);


	$token = '';
	if($this->get('useCSRFToken')){
		$csrf = new Security_Csrf();
		$token = $csrf->createToken();
	}

	$menuData = [];
	$modules = $this->modules;
	foreach($modules as $data)
	{
		if(!$data['active'] || !$data['in_menu'] || !isset($this->userModules[$data['id']])){
			continue;
		}
		$menuData[] = [
			'id' => $data['id'],
			'dev' => $data['dev'],
			'url' =>  Request::url(array($this->get('adminPath'),$data['id'])),
			'title'=> $data['title'],
			'icon'=> Request::wwwRoot().$data['icon']
		];
	}
	$menuData[] = [
		'id' => 'logout',
		'dev' => false,
		'url' =>  Request::url([$this->get('adminPath'),'']) . '?logout=1',
		'title'=>Lang::lang()->get('LOGOUT'),
		'icon' => Request::wwwRoot() . 'i/system/icons/logout.png'
	];

	$res->addInlineJs('
		app.menuData = '.json_encode($menuData).';
		app.permissions = Ext.create("app.PermissionsStorage");
		var rights = '.json_encode(User::getInstance()->getPermissions()).';
		app.permissions.setData(rights);
	');

	$wwwRoot = Request::wwwRoot();
?>
<!DOCTYPE html>
<html>
<head>
<?php /*<BASE href="<?php echo Request::baseUrl();?>">*/?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<?php
	if($this->get('useCSRFToken'))
		echo '<meta name="csrf-token" content="'.$token.'"/>';
?>
<title><?php echo $this->get('page')->title;?>  .:: BACK OFFICE PANEL ::.  </title>
<link rel="shortcut icon" href="<?php echo $wwwRoot;?>i/favicon.png" />
<?php
 echo $res->includeCss();
 echo $res->includeJsByTag(true , false , 'head');
 ?>
</head>
<body>
<div id="header" class="x-hidden">
 <div class="sysVersion"><img src="<?php echo $wwwRoot;?>i/logo-s.png" />
	 <span class="num"><?php echo $this->get('version');?></span>
	 <div class="loginInfo"><?php echo Lang::lang()->get('YOU_LOGGED_AS');?>:
	  <span class="name"><?php echo User::getInstance()->getInfo()['name'];?></span>
	  <span class="logout"><a href="<?php echo Request::url([$this->get('adminPath'),'']);?>?logout=1">
	   <img src="<?php echo $wwwRoot;?>i/system/icons/logout.png" title="<?php echo Lang::lang()->get('LOGOUT');?>" height="16" width="16">
	  </a></span>
	 </div>
 </div>
</div>
<?php echo $res->includeJs(true , false); ?>
</body>
</html>

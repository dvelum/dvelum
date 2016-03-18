<?php
    header('Content-Type: text/html; charset=utf-8');

	$theme = $this->get('theme');

	$res = Resource::getInstance();
	$res->addJs('/js/app/system/common.js' , -2);
	$res->addJs('/js/app/system/Desktop.js' , -1);

	$res->addJs('/js/lib/jquery.js', 1 , true , 'head');
	$res->addJs('/js/lang/'.$this->get('lang').'.js', 1 , true , 'head');

	if($this->get('development'))
	    $res->addJs('/js/lib/ext6/build/ext-all-debug.js', 2 , true , 'head');
	else
	    $res->addJs('/js/lib/ext6/build/ext-all.js', 2 , true , 'head');

	$res->addJs('/js/lib/ext6/build/theme-gray/theme-gray.js', 3 , true , 'head');

	$res->addJs('/js/lib/ext6/build/locale/locale-'.$this->get('lang').'.js', 4 , true , 'head');

	$res->addInlineJs('var developmentMode = '.intval($this->get('development')).';');

    $res->addCss('/js/lib/ext6/build/theme-gray/resources/theme-gray-all.css' , 1);
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

		$isLink = false;
		if($data['id'] == 'Designer' || $data['id'] == 'Docs'){
			$isLink = true;
		}

		$menuData[] = [
			'id' => $data['id'],
			'dev' => $data['dev'],
			'url' =>  Request::url(array($this->get('adminPath'),$data['id'])),
			'title'=> $data['title'],
			'icon'=> Request::wwwRoot().$data['icon'],
            'isLink' =>$isLink
		];
	}
	$menuData[] = [
		'id' => 'logout',
		'dev' => false,
		'url' =>  Request::url([$this->get('adminPath'),'']) . '?logout=1',
		'isLink'=>true,
		'title'=>Lang::lang()->get('LOGOUT'),
		'icon' => Request::wwwRoot() . 'i/system/icons/logout.png'
	];

	$res->addInlineJs('
		app.menuData = '.json_encode($menuData).';
		app.permissions = Ext.create("app.PermissionsStorage");
		var rights = '.json_encode(User::getInstance()->getPermissions()).';
		app.permissions.setData(rights);
		app.version = "'.$this->get('version').'"
		app.user = {
			name: "'.User::getInstance()->getInfo()['name'].'"
		}
	');

	$wwwRoot = Request::wwwRoot();
?>
<!DOCTYPE html>
<html>
<head>
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
<?php echo $res->includeJs(true , false); ?>
</body>
</html>

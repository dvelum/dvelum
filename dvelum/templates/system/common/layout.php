<?php
if(!defined('DVELUM'))exit;

header('Content-Type: text/html; charset=utf-8');

$theme = $this->get('theme');
/**
 * @var \Dvelum\Resource $res
 */
$res = \Dvelum\Resource::factory();
$res->addJs('/js/app/system/common.js' , -2);
$res->addJs('/js/app/system/Application.js' , -1);

$res->addJs('/js/lang/'.$this->get('lang').'.js', 1 , true , 'head');

if($this->get('development'))
    $res->addJs('/js/lib/extjs/build/ext-all-debug.js', 2 , true , 'head');
else
    $res->addJs('/js/lib/extjs/build/ext-all.js', 2 , true , 'head');

$res->addJs('/js/lib/extjs/build/classic/theme-'.$theme.'/theme-'.$theme.'.js', 3 , true , 'head');

$res->addJs('/js/lib/extjs/build/classic/locale/locale-'.$this->get('lang').'.js', 4 , true , 'head');

$res->addInlineJs('var developmentMode = '.intval($this->get('development')).';');

$res->addCss('/js/lib/extjs/build/classic/theme-'.$theme.'/resources/theme-'.$theme.'-all.css' , 1);
$res->addCss('/css/system/style.css' , 2);
$res->addCss('/css/system/'.$theme.'/style.css' , 3);


$token = '';
if($this->get('useCSRFToken')){
    $csrf = new \Dvelum\Security\Csrf();
    $token = $csrf->createToken();
}

$menuData = [];
$modules = $this->modules;
$request = \Dvelum\Request::factory();
foreach($modules as $data)
{
    if(!$data['active'] || !$data['in_menu'] || !isset($this->userModules[$data['id']])){
        continue;
    }
    $menuData[] = [
        'id' => $data['id'],
        'dev' => $data['dev'],
        'url' =>  $request->url(array($this->get('adminPath'),$data['id'])),
        'title'=> $data['title'],
        'icon'=> $request->wwwRoot().$data['icon']
    ];
}

$lang = \Dvelum\Lang::lang();

$menuData[] = [
    'id' => 'logout',
    'dev' => false,
    'url' =>  $request->url([$this->get('adminPath'),'']) . 'login/logout',
    'title'=> $lang->get('LOGOUT'),
    'icon' => $request->wwwRoot() . 'i/system/icons/logout.png'
];

$res->addInlineJs('
		app.menuData = '.json_encode($menuData).';
		app.permissions = Ext.create("app.PermissionsStorage");
		var rights = '.json_encode(User::getInstance()->getModuleAcl()->getPermissions()).';
		app.permissions.setData(rights);
	');

$wwwRoot = $request->wwwRoot();
?>
<!DOCTYPE html>
<html>
<head>
    <?php /*<BASE href="<?php echo Request::baseUrl();?>">*/?>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="robots" content="noindex">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <?php
    if($this->get('useCSRFToken'))
        echo '<meta name="csrf-token" content="'.$token.'"/>';
    ?>
    <title><?php echo $this->get('page')->title;?>  .:: BACK OFFICE PANEL ::.  </title>
    <link rel="shortcut icon" href="<?php echo $wwwRoot;?>i/favicon.png" />
    <?php

    if(!empty($this->get('development'))){
        echo $res->includeCss(false);
    }else{
        echo $res->includeCss(true);
    }

    echo $res->includeJsByTag(true , false , 'head');
    ?>
</head>
<body>
<div id="header" class="x-hidden">
    <div class="sysVersion"><img src="<?php echo $wwwRoot;?>i/logo-s.png" />
        <span class="num"><?php echo $this->get('version');?></span>
        <div class="loginInfo"><?php echo $lang->get('YOU_LOGGED_AS');?>:
            <span class="name"><?php echo User::getInstance()->getInfo()['name'];?></span>
            <span class="logout"><a href="<?php echo $request->url([$this->get('adminPath'),'']);?>login/logout">
	   <img src="<?php echo $wwwRoot;?>i/system/icons/logout.png" title="<?php echo $lang->get('LOGOUT');?>" height="16" width="16">
	  </a></span>
        </div>
    </div>
</div>
<?php
    echo $res->includeJsByTag(true , false , 'external');
    //echo $res->includeJs(true , true);
    // PLATFORM DEVELOPMENT

    if(!empty($this->get('development'))){
        echo $res->includeJs(true , true);
    }else{
        echo $res->includeJs(true , false);
    }


?>
</body>
</html>

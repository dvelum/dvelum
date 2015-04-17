<?php
if(!defined('DVELUM'))exit;

	$res = Resource::getInstance();
	$res->addJs('js/app/system/common.js' , -1);
	$token = '';
	if($this->useCSRFToken){
		$csrf = new Security_Csrf();
		$token = $csrf->createToken();
	}

	$res->addJs('/js/lib/jquery.js', -4 , true );
	$res->addJs('/js/lang/'.$this->lang.'.js', -3 , true);

	if($this->get('development'))
		$res->addJs('/js/lib/ext5/build/ext-all-debug.js', -2 , true);
	else
		$res->addJs('/js/lib/ext5/build/ext-all.js', -2, true );

	$res->addJs('/js/lib/ext5/build/packages/ext-theme-gray/build/ext-theme-gray.js', -1 , true );
	$res->addJs('/js/lib/ext5/build/packages/ext-locale/build/ext-locale-'.$this->get('lang').'.js', -1 , true );


$res->addCss('/js/lib/ext5/build/packages/ext-theme-gray/build/resources/ext-theme-gray-all.css' , 1);
$res->addCss('/templates/system/default/css/style.css' , 2);
?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
<BASE href="<?php echo Request::baseUrl();?>">
<?php
	if($this->useCSRFToken)
		echo '<meta name="csrf-token" content="'.$token.'"/>';

 echo $this->resource->includeCss() ,$this->resource->includeJs(false,false) , "\n"?>
</head>
<body>
</body>
</html>
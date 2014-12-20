<?php
if(!defined('DVELUM'))exit;

	$res = Resource::getInstance();
	$res->addJs('js/app/system/common.js' , -1);
	$token = '';
	if($this->useCSRFToken){
		$csrf = new Security_Csrf();
		$token = $csrf->createToken();
	}

	$res->addJs('/js/lib/jquery.js', -2 , true , 'head');

	if($this->development)
	    $res->addJs('/js/lib/extjs4/ext-all-debug.js', -2 , true );
	else
	    $res->addJs('/js/lib/extjs4/ext-all.js', -2 , true );

	$res->addJs('/js/lang/'.$this->lang.'.js', -3 , true);
	$res->addCss('/js/lib/extjs4/resources/css/ext-all-gray.css' , 1);
	$res->addCss('/templates/system/default/css/style.css' , 2);
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<BASE href="<?php echo Request::baseUrl();?>">
<?php
	if($this->useCSRFToken)
		echo '<meta name="csrf-token" content="'.$token.'"/>';

 echo $this->resource->includeCss() , $this->resource->includeJs(false,false) , "\n"?>
</head>
<body>
</body>
</html>
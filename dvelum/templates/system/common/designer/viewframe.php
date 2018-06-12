<?php
if(!defined('DVELUM'))exit;
$res = Resource::getInstance();
$res->addJs('js/app/system/common.js' , -1);
$token = '';
if($this->useCSRFToken){
    $csrf = new Security_Csrf();
    $token = $csrf->createToken();
}

$theme = $this->theme;

$res->addJs('/js/lang/'.$this->lang.'.js', -3 , true);

if($this->get('development'))
    $res->addJs('/js/lib/extjs/build/ext-all-debug.js', -2 , true );
else
    $res->addJs('/js/lib/extjs/build/ext-all.js', -2 , true );

$res->addJs('/js/lib/extjs/build/classic/theme-'.$theme.'/theme-'.$theme.'.js', -1 , true );

$res->addJs('/js/lib/extjs/build/classic/locale/locale-'.$this->get('lang').'.js', -1 , true );

$res->addInlineJs('var developmentMode = '.intval($this->get('development')).';');

$res->addCss('/js/lib/extjs/build/classic/theme-'.$theme.'/resources/theme-'.$theme.'-all.css' , 1);
$res->addCss('/css/system/style.css' , 2);
$res->addCss('/css/system/'.$theme.'/style.css' , 3);

?>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <BASE href="<?php echo Request::baseUrl();?>">
    <?php
    if($this->useCSRFToken)
        echo '<meta name="csrf-token" content="'.$token.'"/>';

    echo $res->includeCss(),  $res->includeJsByTag(true , false , 'external'), $res->includeJs(false,false) , "\n"?>
</head>
<body>
</body>
</html>
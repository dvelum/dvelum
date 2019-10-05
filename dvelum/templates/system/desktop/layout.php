<?php

use Dvelum\Resource;
use Dvelum\Request;

header('Content-Type: text/html; charset=utf-8');

$theme = 'gray';

$res = Resource::factory();
$request = Request::factory();

$res->addJs('/js/app/system/common.js' , -2);
$res->addJs('/js/app/system/Desktop.js' , -1);

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
$res->addCss('/css/system/desktop/style.css' , 3);


$token = '';
if($this->get('useCSRFToken')){
    $csrf = new Security_Csrf();
    $token = $csrf->createToken();
}

$wwwRoot = $request->wwwRoot();
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
<?php
echo $res->includeJsByTag(true , false , 'external');
echo $res->includeJs(true , false);
?>
</body>
</html>

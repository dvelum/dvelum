<?php
$page = $this->get('page');
$resource = \Dvelum\Resource::factory();
$wwwRoot = \Dvelum\Request::factory()->wwwRoot();

$robots = [];
if(isset($page->robots) && is_array($page->robots)){
    $robots = $page->robots;
}

?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $page->html_title;?></title>
    <?php
    /*<BASE href="<?php echo Request::baseUrl();?>">*/
    echo $page->getOgMeta();

    if(isset($page->csrfToken) && !empty($page->csrfToken))
        echo '<meta name="csrf-token" content="'.$page->csrfToken.'"/>';

    if(strlen($page->meta_description))
        echo '	<meta name="DESCRIPTION" content="'.$page->meta_description.'" />'."\n";

    if(strlen($page->meta_keywords))
        echo '	<meta name="KEYWORDS" content="'.$page->meta_keywords.'" />';
    ?>
    <meta name="viewport" content="width=device-width; initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <?php
    if(!empty($robots)){
        echo '<meta name="robots" content="'.implode(',', $robots).'" />';
    }
    if(!empty($page->canonical)){
        echo '<link rel="canonical" href="'.$page->canonical.'"/>';
    }
    ?>
    <link rel="shortcut icon" href="<?php echo $wwwRoot;?>i/favicon.png" />
    <?php  echo $this->resource->includeCss(); ?>
    <?php  echo $this->get('resource')->includeJsByTag(true , false, 'head'); ?>
    <?php echo $this->get('resource')->includeJs(true , false); ?>
</head>
<body id="content">
<?=$page->text;?>
</body>
</html>
<?php
/**
 * @var \Dvelum\Page\Page $page
 */
$page = $this->get('page');
$resource = \Dvelum\Resource::factory();
$wwwRoot = \Dvelum\Request::factory()->wwwRoot();

$robots = $page->getRobots();
$htmlTitle = $page->getHtmlTitle();
$metaDescription = $page->getMetaDescription();
$metaKeywords = $page->getMetaKeywords();
$canonical = $page->getCanonical();
$securityToken = $page->getCsrfToken();

?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width; initial-scale=1.0"/>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <?php

    echo $page->openGraph();

    if (!empty($securityToken)) {
        echo '<meta name="csrf-token" content="' . $securityToken . '"/>' . PHP_EOL;
    }

    if (!empty($metaDescription)) {
        echo '<meta name="description" content="' . $metaDescription . '"/>' . PHP_EOL;
    }

    if (!empty($metaKeywords)) {
        echo '<meta name="keywords" content="' . $metaKeywords . '"/>' . PHP_EOL;
    }

    if (!empty($robots)) {
        echo '<meta name="robots" content="' . $robots . '" />';
    }
    if (!empty($canonical)) {
        echo '<link rel="canonical" href="' . $canonical . '"/>';
    }
    ?>
    <title><?php echo $page->getHtmlTitle(); ?></title>
    <link rel="shortcut icon" href="<?php echo $wwwRoot; ?>i/favicon.png"/>
    <?php echo $resource->includeCss(); ?>
    <?php echo $resource->includeJsByTag(true, false, 'head'); ?>
    <?php echo $resource->includeJs(true, false); ?>
</head>
<body id="content">
    <h1><?php echo $page->getTitle(); ?></h1>
    <?php echo $page->getText(); ?>
</body>
</html>
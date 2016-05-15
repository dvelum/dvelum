<?php
$page = $this->get('page');
$resource = Resource::getInstance();
$resource->addCss('/css/public/main/reset.css' ,0);
$resource->addCss('/css/public/main/style.css' ,100);
$wwwRoot = Request::wwwRoot();

$blockManager = $this->get('blockManager');
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width; initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?php echo $page->html_title;?></title>
    <link rel="shortcut icon" href="<?php echo $wwwRoot;?>i/favicon.png" />
    <?php
    echo $page->getOgMeta();

    if(isset($page->csrfToken) && !empty($page->csrfToken))
        echo '<meta name="csrf-token" content="'.$page->csrfToken.'"/>';

    if(strlen($page->meta_description))
        echo '<meta name="DESCRIPTION" content="'.$page->meta_description.'" />';

    if(strlen($page->meta_keywords))
        echo '<meta name="KEYWORDS" content="'.$page->meta_keywords.'" />';
    ?>
    <?php  echo $this->resource->includeCss(); ?>
    <?php  echo $this->get('resource')->includeJsByTag(true , false, 'head'); ?>
</head>
<body>
<div class="page_wrap">
    <div class="page">
        <?php
          $t = new Template();
            echo $this->renderTemplate(
                'public/default/header.php',
                [
                    'blocks' => $blockManager->getBlocksHtml('top-blocks')
                ]
            );
        ?>

        <div class="content-wrap">
            <?php
            echo $this->renderTemplate(
                'public/default/sidebar.php',
                [
                    'blocks' => $blockManager->getBlocksHtml('right-blocks')
                ]
            );
            ?>

            <div id="content" class="content">
                <?php
                    if(empty($page->func_code)){
                       echo '<h3>'.$page->page_title.'</h3>';
                    }
                ?>
               <?php echo $page->text;?>
            </div>

        </div>
        <?php
        echo $this->renderTemplate(
            'public/default/footer.php',
            [
                'blocks' => $blockManager->getBlocksHtml('bottom-blocks')
            ]
        );
        ?>
    </div><!--end:page-->
</div><!--end:page_wrap-->

<?php echo $this->get('resource')->includeJs(true , true); ?>
</body>
</html>
<?php
$page = $this->get('page');
$resource = Resource::getInstance();
$resource->addCss('/css/public/main/reset.css' ,0);
$resource->addCss('/css/public/main/style.css' ,100);
$wwwRoot = Request::wwwRoot();

/**
 * @var BlockManager $blockManager
 */
$blockManager = $this->get('blockManager');

$layoutCls = '';
$layoutSideLeft = $blockManager->hasBlocks('left-blocks');
$layoutSideRight = $blockManager->hasBlocks('right-blocks');
$sideCls = 'side-';
if($layoutSideLeft){
    $layoutCls.=' left';
    $sideCls.= 'l';
}
if($layoutSideRight){
    $layoutCls.=' right';
    $sideCls.= 'r';
}

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

        <div class="layout-wrap">

            <div class="content-wrap  <?php echo $layoutCls?>">
                <section id="content" class=" content">
                    <?php
                    if(empty($page->func_code)){
                        echo '<h1>'.$page->page_title.'</h1>';
                    }
                    ?>
                    <div class="text"><?php echo $page->text;?></div>
                </section>
            </div>
            <?php
            if($layoutSideLeft){
                echo $this->renderTemplate(
                    'public/default/side_left.php',
                    [
                        'blocks' => $blockManager->getBlocksHtml('left-blocks'),
                        'sideCls' => $sideCls
                    ]
                );
            }

            if($layoutSideRight){
                echo $this->renderTemplate(
                    'public/default/side_right.php',
                    [
                        'blocks' => $blockManager->getBlocksHtml('right-blocks'),
                        'sideCls' => $sideCls
                    ]
                );
            }
            ?>
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
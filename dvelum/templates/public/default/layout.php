<?php
$page = $this->get('page');
$resource = Resource::getInstance();
$resource->addCss('/css/public/main/reset.css' ,0);
$resource->addCss('/css/public/main/style.css' ,100);
$resource->addJs('/js/app/frontend/common.js',10);
$wwwRoot = Request::wwwRoot();

/**
 * @var BlockManager $blockManager
 */
$blockManager = $this->get('blockManager');

$layoutCls = '';
$hasSideLeft = $blockManager->hasBlocks('left-blocks');
$hasSideRight = $blockManager->hasBlocks('right-blocks');

if($hasSideLeft && !$hasSideRight){
    $resource->addCss('/css/public/main/side-left.css' ,101);
}elseif(!$hasSideLeft && $hasSideRight){
    $resource->addCss('/css/public/main/side-right.css' ,101);
}elseif($hasSideLeft && $hasSideRight){
    $resource->addCss('/css/public/main/side-left-right.css' ,101);
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

            <div class="layout">

                <?php
                if($hasSideLeft){
                    echo $this->renderTemplate(
                        'public/default/side_left.php',
                        [
                            'blocks' => $blockManager->getBlocksHtml('left-blocks')
                        ]
                    );
                }
                ?>

                <div class="content-wrap">
                    <section id="content" class=" content">
                        <?php
                        if(empty($page->func_code)){
                            echo '<header><h1>'.$page->page_title.'</h1></header>';
                        }
                        ?>
                        <div class="text"><?php echo $page->text;?></div>
                    </section>
                </div>

                <?php
                if($hasSideRight){
                    echo $this->renderTemplate(
                        'public/default/side_right.php',
                        [
                            'blocks' => $blockManager->getBlocksHtml('right-blocks')
                        ]
                    );
                }
                ?>
            </div>
    </div><!--end:page-->
    <?php
    echo $this->renderTemplate(
        'public/default/footer.php',
        [
            'blocks' => $blockManager->getBlocksHtml('bottom-blocks')
        ]
    );
    ?>
<?php echo $this->get('resource')->includeJs(true , true); ?>
</body>
</html>
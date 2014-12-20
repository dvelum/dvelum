<?php
if(!defined('DVELUM'))exit;
$page = $this->get('page');

$template = new Template();
$template->setProperties(
	array(
		'page' => $page,
		'resource' =>  Resource::getInstance(),
		'topBlocks' => $this->get('blockManager')->getBlocksHtml('topblocks')
	)
);
echo $template->render($this->get('path') . '/header.php');

$blockManager = $this->get('blockManager');
?>
<div id="container">
	<div id="fullwidth">
		 <div class="block_wrap feature_wrap">
			 <div class="bottom elements">
				 <div class="layout-wrap sidebar-right">
					 <div class="content-wrap">
						 <div id="content" class="content">
							<?php echo $page->text;?>
							<div class="content-col-2">
							<?php echo $blockManager->getBlocksHtml('centerleft');?>
							</div>
							<div class="content-col-2">
								<?php echo $blockManager->getBlocksHtml('centerright');?>
							</div>
						 </div>		
						 <div class="sep"></div>			
					 </div>	
					 <div id="sidebar"><?php echo $blockManager->getBlocksHtml('rightblocks');?></div>
				 </div>	
			 </div>
		 </div>
	</div><!--end:fullwidth-->
</div><!--end:container-->
<?php
$template = new Template();
$template->setProperties(
	array(
		'page' => $page,
		'resource' => Resource::getInstance(),
		'bottomBlocks' => $blockManager->getBlocksHtml('bottomblocks'),
		'footerBlocks' =>$blockManager->getBlocksHtml('footerblocks')
	)
);
echo $template->render($this->get('path') . '/footer.php');
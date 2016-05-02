<?php
$page = Page::getInstance();
$template = new Template();
$template->set('page', $page);
$template->set('resource' , Resource::getInstance());
$template->set('topBlocks' , $this->get('blockManager')->getBlocksHtml('topblocks'));
echo $template->render($this->path . '/header.php');
?>
<div id="container">
	<div id="fullwidth">
		 <div class="block_wrap feature_wrap">
			 <div class="bottom elements">
				 <div class="layout-wrap sidebar-right">
					<div class="content-wrap">
						<div id="content" class="content">
						   <h3><?php echo $page->page_title;?></h3>
						   <?php echo $page->text;?>
						</div>
						<div class="sep"></div>
					</div>
					<div id="sidebar">
						<?php echo $this->get('blockManager')->getBlocksHtml('rightblocks');?>
					</div>
				 </div>	
			 </div>
		 </div>
	</div><!--end:fullwidth-->
</div><!--end:container-->
<?php
$template = new Template();
$template->set('page',Page::getInstance());
$template->set('resource' , Resource::getInstance());
$template->set('bottomBlocks' , $this->get('blockManager')->getBlocksHtml('bottomblocks'));
echo $template->render($this->path . '/footer.php');
?>
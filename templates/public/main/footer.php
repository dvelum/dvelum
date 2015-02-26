<?php 
if(!defined('DVELUM'))exit;
$resource = Resource::getInstance();
$resource->addJs('/js/lib/jquery.js' , true , 0);
?>
<div class="block_wrap">
				<footer>
					<div class="block_wrap">
						<nav class="nav bottommenu">
							<?php echo $this->get('bottomBlocks');?>
						</nav>
					</div>
					<div class="first"><?php echo $this->get('footerBlocks');?></div>
					<div class="copyright">Copyright &copy; 2012 Your company name</div>
				</footer><!--end:footer-->
			</div>
		</div><!--end:page-->
    </div><!--end:page_wrap-->
	<?php echo $this->get('resource')->includeJs(true , false); ?>
</body>
</html>
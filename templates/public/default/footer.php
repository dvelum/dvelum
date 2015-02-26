<?php if(!defined('DVELUM'))exit;?>
<div class="block_wrap">
				<footer>
					<div class="block_wrap">
						<nav class="nav bottommenu">

						</nav>
					</div>
					<div class="first"><?php echo $this->get('bottomBlocks');?></div>
					<div class="copyright">Copyright &copy; 2012 Your company name</div>
				</footer><!--end:footer-->
			</div>
		</div><!--end:page-->
    </div><!--end:page_wrap-->
	<?php echo $this->get('resource')->includeJs(true , true); ?>
</body>
</html>
<?php 
if(!defined('DVELUM'))exit;

$page = $this->get('page');
$resource = Resource::getInstance();
$resource->addCss('/templates/public/main/css/reset.css' ,0);
$resource->addCss('/templates/public/main/css/style.css' ,100);
$wwwRoot = Request::wwwRoot();

?>
<!DOCTYPE html>
<html>
<head>
	<title><?php echo $page->html_title;?></title>
	<?php 
	/*<BASE href="<?php echo Request::baseUrl();?>">*/
	$page = $this->get('page');
	
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
	<link rel="shortcut icon" href="<?php echo $wwwRoot;?>i/favicon.png" />
	<?php  echo $this->resource->includeCss(); ?>
	<?php  echo $this->get('resource')->includeJsByTag(true , false, 'head'); ?>
</head>
<body>
	<div id="page_wrap">
    	<div id="page">
			<header id="header">
				<div class="center">
					<nav class="nav topmenu">
						<?php echo $this->get('topBlocks');?>
					</nav>
				</div>
			</header><!--end:header-->

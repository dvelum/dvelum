<?php
if(isset($this->msg))
	$message = '<p>'.$this->msg.'</p>';
else 
	$message = '<h1>Something went wrong.</h1>';

$wwwRoot = Request::wwwRoot();
?>
<html xmlns="http://www.w3.org/1999/xhtml" class="systemPage">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;">
	<title><?php echo strip_tags($message);?></title>
	<link href="<?php echo $wwwRoot;?>css/public/main/error.css" rel="stylesheet" type="text/css">
</head>

<body class="pageBottom">
	<div id="pageBottom" class="bgImage">
		<div class="wrapper">
		  <div class="text3D"><span>Ooops...</span><?php echo $message;?></div>
	<?php 
	  if($this->get('development')){
	    echo '<p align="center" style="font-size:12px;">'.$this->get('error_msg').'<p>';
	  }
	?>
		</div>
	</div>	
</body>
</html>
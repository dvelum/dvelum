<?php
$wwwRoot = $this->get('wwwRoot');
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;"/>
<BASE href="<?php echo Request::baseUrl();?>"/>
<title>Log into administrative panel</title>
<link href="<?php echo $wwwRoot ;?>css/system/login.css" rel="stylesheet" type="text/css"/>
<!--[if IE]>
	<link rel="stylesheet" href="<?php echo $wwwRoot ;?>css/system/style_ieFix.css" type="text/css"/>
<![endif]-->
</head>

<body class="loginPage">
	<div class="loginWrapper">
		<div class="loginLogo"><img src="<?php echo $wwwRoot ;?>i/logo.large.png" alt=""/></div>
		<div class="widget">
			<div class="title"><h6>Admin Panel</h6></div>
			<form action="" id="validate" class="form" method="post">
				<fieldset>
					<div class="formRow">
						<label for="login">Username:</label>
						<div class="loginInput"><input name="ulogin" class="validate[required]" id="login" type="text"/></div>
						<div class="clear"></div>
					</div>
					
					<div class="formRow">
						<label for="pass">Password:</label>
						<div class="loginInput"><input name="upassword" class="validate[required]" id="pass" type="password"/></div>
						<div class="clear"></div>
					</div>
					
					<div class="loginControl">
						<input type="submit" value="LOG IN" class="dv_button LogMeIn"/>
						<div class="clear"></div>
					</div>
				</fieldset>
			</form>
		</div>
	</div>    
	<div id="footer">
		<div class="wrapper">Copyright &copy; 2011- 2012 DVelum team</div>
	</div>
</body>
</html>
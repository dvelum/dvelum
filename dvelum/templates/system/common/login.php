<?php 
if(!defined('DVELUM'))exit;

$wwwRoot = $this->get('wwwRoot');
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;">
    <title>Log into BackOffice panel</title>
    <script type="text/javascript" src="<?php echo $wwwRoot;?>js/app/login.js"></script>
    <link href="<?php echo $wwwRoot;?>css/system/login.css" rel="stylesheet" type="text/css">
    <!--[if IE]>
        <link rel="stylesheet" href="<?php echo $wwwRoot;?>css/system/style_ieFix.css" type="text/css"/>
    <![endif]-->
</head>

<body class="loginPage">
    <div class="loginWrapper">
        <div class="loginLogo"><img src="<?php echo $wwwRoot;?>i/logo.large.png" alt=""></div>
        <div class="widget">
            <div class="title"><h6>BackOffice Panel</h6></div>
            <form action="" id="validate" class="form" method="post">
                <?php
                    if(!empty($this->csrf))
                        echo '<input type="hidden" name="'. $this->csrf['csrfFieldName'] .'" value="'. $this->csrf['csrfToken'] .'"/>';
                ?>
                <fieldset>
                    <div class="formRow">
                        <label for="login">Username:</label>
                        <div class="loginInput"><input name="ulogin" class="validate[required]" id="login" type="text"></div>
                        <div class="clear"></div>
                    </div>

                    <div class="formRow">
                        <label for="pass">Password:</label>
                        <div class="loginInput"><input name="upassword" class="validate[required]" id="pass" type="password"></div>
                        <div class="clear"></div>
                    </div>
<!--					<div class="formRow">
                        <label for="provider">Provider:</label>
                        <div class="loginInput">
                            <select name="uprovider" class="validate[required]" id="provider">
                                <option value="dvelum">Dvelum</option>
                                <option value="ldap">LDAP</option>
                            </select>
                        </div>
                        <div class="clear"></div>
                    </div>
-->
                    <div class="formRow errRow" style="display:none;" id="errorMsg"></div>
                    <div class="loginControl">
                        <input type="button" value="LOG IN" id="loginBtn" class="dv_button LogMeIn"/>
                        <div class="clear"></div>
                    </div>
                </fieldset>
            </form>
        </div>
    </div>
    <div id="footer">
        <div class="wrapper">Copyright &copy; 2011 - <?php echo date('Y');?> DVelum team</div>
    </div>
</body>
</html>
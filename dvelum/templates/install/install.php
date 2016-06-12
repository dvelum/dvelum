<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<title><?php echo $this->dictionary->get('SBI'); ?></title>
		<link type="text/css" href="<?php echo $this->wwwRoot;?>css/install/jquery-ui-1.8.16.custom.css" rel="stylesheet" />
		<link type="text/css" href="<?php echo $this->wwwRoot;?>css/install/style.css" rel="stylesheet" />
		<script type="text/javascript" src="<?php echo $this->wwwRoot;?>js/install/jquery-1.6.2.min.js"></script>
		<script type="text/javascript" src="<?php echo $this->wwwRoot;?>js/install/jquery-ui-1.8.16.custom.min.js"></script>
		<script type="text/javascript" src="<?php echo $this->wwwRoot;?>js/install/jquery.uniform.min.js"></script>
		<script type="text/javascript" src="<?php echo $this->wwwRoot;?>js/install/handlers.js"></script>
		<script>
		var passMsg = "<?php echo $this->dictionary->get('PASS_MISMATCH');?>";
		var admPanel = "<?php echo $this->dictionary->get('ADMIN_PANEL');?>";
		var lang = "<?php echo $this->lang;?>";
		var newLang = lang;
		var passMatch = false;
		var curStep = 0;
		var wwwRoot = "<?php echo $this->wwwRoot;?>install/";
		var url = wwwRoot;
		var appRoot = "<?php echo $this->wwwRoot;?>";
		$(function() {
			$( "#tabs" ).tabs();
			$('#nextBtn').bind('click', showLicenseStep).removeAttr('disabled').removeClass('disabled');
			$('#backBtn').bind('click', backAction).attr('disabled','disabled').addClass('disabled');
			$('#refreshBtn').bind('click', firstCheck);
			$('#checkBtn').bind('click', dbSettings);
			$('#refreshBtn').css('display', 'none');
			
			$('input[name="pass"]').keyup(checkPass);
			$('input[name="pass_confirm"]').keyup(checkPass);
			
			$('div[id^="tabs"]').css('font-size', '14px');

			$('option[value="' + lang + '"]').attr('selected', 'selected');
			$('option[value="<?php echo date_default_timezone_get();?>"]').attr('selected', 'selected');

			$('#langchoose').bind('change' , function() {
				newLang = $("select option:selected").val();
				$.post(url, {
					'action': 'setlang',
					'lang'	: newLang
				}, function(data){
					if (data.success) {
						var date = new Date();
						window.location = url+'?t=' + date.getTime();
					} else {
						alert('An internal error');
					}
				}, 'json');
				
			});
			$("select, .check, .check :checkbox, input:radio, input:file").uniform();
		});
		</script>
	</head>
	<body class="installerPage">
		<div class="installerWrapper">
			<div class="Logo"><img title="DVelum" src="<?php echo $this->wwwRoot;?>i/logo.large.png" /></div>
			<div class="widget">
				<div id="tabs">
					<ul>
						<li class="tabs_0"><a href="#tabs-0"><?php echo $this->dictionary->get('WELCOME'); ?></a></li>
						<li class="tabs_1" style="display: none;"><a href="#tabs-1"><?php echo $this->dictionary->get('LICENSE'); ?></a></li>
						<li class="tabs_2" style="display: none;"><a href="#tabs-2"><?php echo $this->dictionary->get('INITIAL_CHECK'); ?></a></li>
						<li class="tabs_3" style="display: none;"><a href="#tabs-3"><?php echo $this->dictionary->get('DB_SETTINGS'); ?></a></li>
						<li class="tabs_4" style="display: none;"><a href="#tabs-4"><?php echo $this->dictionary->get('DB_CREATING'); ?></a></li>
						<li class="tabs_5" style="display: none;"><a href="#tabs-5"><?php echo $this->dictionary->get('USER_CREATING'); ?></a></li>
						<li class="tabs_6" style="display: none;"><a href="#tabs-6"><?php echo $this->dictionary->get('THANKS'); ?></a></li>
					</ul>
					<div id="tabs-0">
						<?php echo $this->dictionary->get('WELCOME_TO'); ?>
						<form class="form" action="<?php echo $this->url?>" id="velcome">
							<fieldset>
								<div class="formRow">
									<label for="lang"><?php echo $this->dictionary->get('LANGUAGE_CHOOSING'); ?>:</label>
									<select id="langchoose">
										<option value="en" <?php if($this->lang=='en'){echo 'selected';}?>>English</option>
										<option value="ru" <?php if($this->lang=='ru'){echo 'selected';}?>>Русский</option>
									</select>
									<div class="clear"></div>
								</div>
							</fieldset>
						</form>
					</div>
					<div id="tabs-1" style="height: 375px;overflow: auto;">
						<div id="license"><?php echo nl2br(htmlspecialchars($this->license));?></div>
					</div>
					<div id="tabs-2" style="height: 375px;overflow: auto;">
						<div id="checkNote"></div>
						<div id="checkInfo"></div>
					</div>
					<div id="tabs-3" style="padding: 0;">
						<div style="text-align: justify; padding: 14px;">
							<?php echo $this->dictionary->get('DB_SETTINGS_MSG'); ?>:
						</div>
						<form class="form" action="<?php echo $this->url?>" id="dbsettings">
							<fieldset>
								<div class="formRow">
									<label for="host"><?php echo $this->dictionary->get('HOST'), ':', $this->dictionary->get('PORT'); ?></label>
									<div class="installerInput"><input type="text" id="host" class="validate[required]" name="host" value="localhost"/></div><span style="float: left; margin-top: 4px;">&nbsp;:&nbsp;</span>
									<div class="installerInput"><input type="text" id="port" class="validate[required]" name="port" value="3306"/></div>
									<div class="clear"></div>
								</div>
								<div class="formRow">
									<label for="dbname"><?php echo $this->dictionary->get('DB_NAME'); ?>:</label>
									<div class="installerInput"><input type="text" id="dbname" class="validate[required]" name="dbname" /></div>
									<div class="clear"></div>
								</div>
								<div class="formRow">
									<label for="username"><?php echo $this->dictionary->get('USER_NAME'); ?>:</label>
									<div class="installerInput"><input type="text" id="username" class="validate[required]" name="username" /></div>
									<div class="clear"></div>
								</div>
								<div class="formRow">
									<label for="password"><?php echo $this->dictionary->get('PASSWORD'); ?>:</label>
									<div class="installerInput"><input type="password" id="password" class="validate[required]" name="password" /></div>
									<input type="hidden" value="dbcheck" name="action">
									<div class="clear"></div>
								</div>	
									<div class="formRow">
									<label for="username"><?php echo $this->dictionary->get('DB_PREFIX'); ?>:</label>
									<div class="installerInput"><input type="text" id="prefix" class="validate[required]" name="prefix" value="dv_" /></div>
									<div class="clear"></div>
								</div>
								<div class="formRow long">
									<label for="username"><?php echo $this->dictionary->get('INSTALL_DOCS'); ?>:</label>
									<div class="installerInput"><input type="checkbox" id="install_docs"  name="install_docs"/></div>
									<div class="clear"></div>
								</div>
							</fieldset>
						</form>
						<div id="dbCheckMsg"></div>
					</div>
					<div id="tabs-4">
						<?php echo  $this->dictionary->get('DB_CREATING')?>
						<br />
						<div id="dbCreateMsg"><img src="<?php echo $this->wwwRoot;?>i/ajaxload.gif"/></div>
					</div>
					<div id="tabs-5"  style="padding: 0;">
						<form class="form" action="<?php echo $this->url?>" id="userpass">
							<fieldset>
								<div class="formRow">
									<label for="adminpath"><?php echo $this->dictionary->get('ADMINPATH'); ?>:</label>
									<div class="installerInput"><input type="text" id="adminpath" class="validate[required]" name="adminpath" value="backoffice"/></div>
									<div class="clear"></div>
								</div>
								<div class="formRow">
									<label for="user"><?php echo $this->dictionary->get('USER_NAME'); ?>:</label>
									<div class="installerInput"><input type="text" id="user" class="validate[required]" name="user" value="root"/></div>
									<div class="clear"></div>
								</div>
								<div class="formRow">
									<label for="pass"><?php echo $this->dictionary->get('PASSWORD'); ?>:</label>
									<div class="installerInput"><input type="password" id="pass" class="validate[required]" name="pass" /></div>
									<div class="clear"></div>
								</div>
								<div class="formRow">
									<label for="pass_confirm"><?php echo $this->dictionary->get('CONFIRM_PASS'); ?>:</label>
									<div class="installerInput"><input type="password" id="pass_confirm" class="validate[required]" name="pass_confirm" /></div>
									<div class="clear"></div>
								</div>
								<div class="formRow">
									<label for="timezone"><?php echo $this->dictionary->get('TIMEZONE'); ?>:</label>
									<select name="timezone">
										<?php foreach (timezone_identifiers_list() as $v){
											echo '<option value="' . $v . '">' . $v . '</option>';
										}?>
									</select>
								</div>
								<input type="hidden" value="<?php echo $this->lang;?>" name="lang" />
								<input type="hidden" value="setuserpass" name="action" />
							</fieldset>
						</form>
						<div id="userPassMsg"></div>
					</div>
					<div id="tabs-6">
						<?php echo $this->dictionary->get('THANKS_MSG'), ' '; ?> <span id="link"></span>
					</div>
				</div>
				<button class="dv_button" id="nextBtn"><?php echo $this->dictionary->get('NEXT'); ?></button>
				<button class="dv_button" id="refreshBtn"><?php echo $this->dictionary->get('REFRESH'); ?></button>
				<button class="dv_button" id="checkBtn" style="display: none;"><?php echo $this->dictionary->get('CHECK'); ?></button>
				<button class="dv_button" id="backBtn"><?php echo $this->dictionary->get('BACK'); ?></button>
			</div>
		</div>
		<div id="footer">
			<div class="wrapper">Copyright &copy; 2011- <?php echo date('Y'); ?> DVelum team</div>
		</div>
	</body>
</html>

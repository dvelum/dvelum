var tabsCount = 5;
function showTab(index)
{
	for(var i =0; i<=tabsCount; i++){
		$('.tabs_'+i).hide();
	}
	$('.tabs_'+index).show();
	$('#tabs').tabs( 'select' , index);
}


function backAction()
{
	switch (curStep) {
		case 1:
			showLangStep();
			break;
		case 2:
			showTab(1);			
			//$('#backBtn').attr('disabled','disabled').addClass('disabled');
			$('#nextBtn').unbind('click').bind('click', firstCheck).removeAttr('disabled').removeClass('disabled');
			$('#refreshBtn').css('display', 'none');		
			curStep = 1;
			break;
	
		case 3:
			showTab(2);
			
			$('#nextBtn').unbind('click').bind('click', showDbStep).removeAttr('disabled').removeClass('disabled');
			$('#checkBtn').css('display', 'none');
			$('#refreshBtn').css('display', '');
			
			curStep = 2;
			break;
			
		case 4:			
		case 5:
			showTab(3);
			
			$('#dbCreateMsg').html('<img src="'+appRoot+'i/ajaxload.gif"></img>').removeClass('success');
			$('#checkBtn').css('display', '');
			$('#nextBtn').unbind('click').bind('click', createTables).removeAttr('disabled').removeClass('disabled').removeClass('fail');
			
			curStep = 3;
			break;
	}
}

function showLangStep()
{
	curStep = 0;
	showTab(0);
	$('#nextBtn').unbind('click').removeAttr('disabled').removeClass('disabled').bind('click', showLicenseStep);
}
function showLicenseStep()
{
	curStep = 1;
	$('#backBtn').removeAttr('disabled').removeClass('disabled');
	var flag = true;
	
	var btn = $('#nextBtn');
	btn.unbind('click').removeAttr('disabled').removeClass('disabled').bind('click', firstCheck);
	showTab(1);
}

function firstCheck() {
	curStep = 2;
	showTab(1);
	
	$('#refreshBtn').css('display', '');
	$('#backBtn').removeAttr('disabled').removeClass('disabled');
	$.post(url, {
		'action' : 'firstcheck'
	}, function(data){
		if (data.success) {
			showTab(2);
			
			var flag = false;
			
			$('#checkInfo').html('');
			var div = $('<div>').addClass('list');
			$.each(data.data.items, function(){
				d = $('<div>').addClass('block').append($('<div>').addClass('title').html(this.title));
				if (this.success) {
					d.append($('<div>').html('Ok.').addClass('success'));
					if (this.error) {
						d.append($('<div>').html(this.error).addClass('fail'));
					}
				} else {
					flag = true;
					d.append($('<br>'));
					d.append($('<div>').html(this.error).addClass('fail'));
				}
				div.append(d);
			});
			var info = $('<div>').addClass('fail')
						.html(data.data.info)
						.css('border', '1px dashed red')
						.css('margin', '10px 0')
						.css('padding', '5px');
			$('#checkNote').html('').append(info);
			$('#checkInfo').append(div);
			
			var btn = $('#nextBtn');
			if (!flag) {
				btn.unbind('click').removeAttr('disabled').removeClass('disabled').bind('click', showDbStep);
			} else {
				btn.attr('disabled','disabled').addClass('disabled');
			}
		} else {
			alert('An internal error');
		}
	}, 'json');
}
function showDbStep() {
	curStep = 3
	$('#refreshBtn').css('display', 'none');
	showTab(3);
	
	
	$('#nextBtn').unbind('click').attr("disabled","disabled").addClass('disabled');
	$('#checkBtn').css('display', '');
}
function dbSettings() {
	$.post(url, $('#dbsettings').serialize(), function(data){
		if (data.success) {
			var div = $('#dbCheckMsg').html(data.data.msg);
			if (data.data.success) {
				div.removeClass('fail');
				div.addClass('success');
				$('#nextBtn')	.removeAttr('disabled').removeClass('disabled')
								.bind('click', createTables);
			} else {
				div.removeClass('success');
				div.addClass('fail');
				$('#nextBtn').unbind('click').attr("disabled","disabled").addClass('disabled');
			}
		} else {
			alert('An internal error');
		}
	}, 'json');
}
function createTables(){
	curStep = 4;
	showTab(4);
	
	$('#checkBtn').css('display', 'none');
	$('#nextBtn').unbind('click').attr("disabled","disabled").addClass('disabled');
	$('#backBtn').attr("disabled","disabled").addClass('disabled');
	
	$.post(url, {
		'action' : 'createtables'
	}, function(data){
		var div = $('#dbCreateMsg');
		if (data.success) {
			div.removeClass('fail');
			div.addClass('success');
			div.html('<div>'+data.msg+'</div>');
			$('#backBtn').removeAttr('disabled').removeClass('disabled');
			$('#nextBtn').unbind('click').bind('click', showUserPassStep).removeAttr('disabled').removeClass('disabled');
		} else {
			$('#backBtn').removeAttr('disabled').removeClass('disabled');
			div.removeClass('success');
			div.addClass('fail');
			div.html(data.msg);
		}
	}, 'json');
	
}

function showUserPassStep(){
	curStep = 5;
	showTab(5);
	
	$('#checkBtn').css('display', 'none');
	$('#nextBtn').unbind('click').bind('click', savePass);
}

function savePass(){
	if(!passMatch){
		checkPass();
		return false;
	}
	
	$.post(url, $('#userpass').serialize(), function(data){
		var div = $('#link');
		if (data.success) {
			div.removeClass('fail');
			div.html('<a href="'+appRoot+data.data['link']+'">' + admPanel + '</a>');
			$('#nextBtn').remove();
			$('#backBtn').remove();
			showTab(6);
		} else {
			div.removeClass('success');
			div.addClass('fail');
			$('#userPassMsg').html(data.msg).addClass('fail');
		}
	}, 'json');
}

function checkPass(){
	var pass = $('input[name="pass"]');
	var passConfirm = $('input[name="pass_confirm"]');
	
	var msgDiv = $('#userPassMsg');
	
	if(pass.val() != passConfirm.val() || pass.val().length == 0 || passConfirm.val().length == 0){
		pass.addClass('invalid_field');
		passConfirm.addClass('invalid_field');
		msgDiv.addClass('fail');
		msgDiv.html(passMsg);
		passMatch = false;
	}else{
		pass.removeClass('invalid_field');
		passConfirm.removeClass('invalid_field');
		msgDiv.removeClass('fail');
		msgDiv.html('');
		passMatch = true;
	}
}
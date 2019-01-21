
function doLogin() {
    var xhr = new XMLHttpRequest();
    var url = document.location+'/login/login';
    var login = document.getElementById('login').value;
    var pass = document.getElementById('pass').value;
    var params = 'ulogin='+login+'&upassword='+pass;

    document.getElementById('errorMsg').innerHTML = '';
    document.getElementById('errorMsg').style.display = 'none';

    xhr.open('POST', url, true);

    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");

    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            try {
                var ret = JSON.parse(xhr.responseText);
                console.log(ret);
                if (ret.success) {
                    location.reload();
                }else{
                    document.getElementById('errorMsg').innerHTML = ret.msg;
                    document.getElementById('errorMsg').style.display = 'block';
                }
            }catch (e){
                return false;
            }
        }
    }
    xhr.send(params);
}

function setListeners(){
    document.getElementById('loginBtn').onclick = doLogin;
    document.getElementById('login').onkeydown = function(e){
        if(e.keyCode == 13){
            doLogin();
        }
    }
    document.getElementById('pass').onkeydown = function(e){
        if(e.keyCode == 13){
            doLogin();
        }
    }
}

window.onload = setListeners;
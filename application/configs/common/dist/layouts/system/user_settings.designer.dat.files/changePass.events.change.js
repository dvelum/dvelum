var pwd = this.childObjects.settingsForm_pass;
var pwd_confirm = this.childObjects.pwdConfirm;

if(newValue){
  pwd.enable();
  pwd.show();
  pwd_confirm.enable();
  pwd_confirm.show();
}else{
  pwd.hide();
  pwd.disable();
  pwd_confirm.hide();
  pwd_confirm.disable();
}
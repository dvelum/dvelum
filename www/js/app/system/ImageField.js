Ext.define('app.ImageField',{
	extend:'Ext.form.field.Display',
	alias:'widget.imagefield',
	submitValue:false,	
	imgSrc:null,
    setValue: function(value){	
        if (value == undefined || value == null)  {
        	this.imgSrc='';
        	value = '<img src="'+app.wwwRoot+'i/system/empty.gif" alt="' + appLang.IMAGE + '" style="border:1px solid #000000">';
        } else {
        	imgSrc = value;
        	value = '<img src="'+value+'" alt="' + appLang.IMAGE + '" style="border:1px solid #000000">';       
        }
        this.callParent(arguments);
    },
    getValue:function(){
    	return this.imgSrc;
    },
    initValue : function(){
          this.setValue(this.value);
    }
});

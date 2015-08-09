Ext.define('app.ImageField',{
	extend:'Ext.form.field.Display',
	alias:'widget.imagefield',
	submitValue:false,
    prependWebRoot:false,
	imgSrc:null,
    wwwRoot:'/',
    setValue: function(value){
        if (value)  {
            this.imgSrc = value;
            var srcPath = value;
            if(this.prependWebRoot){
                srcPath = this.wwwRoot + value;
            }
            value = '<img src="'+ srcPath +'" alt="' + appLang.IMAGE + '" style="border:1px solid #000000">';
        } else {
            this.imgSrc='';
            value = '<img src="'+this.wwwRoot+'i/system/empty.gif" alt="' + appLang.IMAGE + '" style="border:1px solid #000000">';
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

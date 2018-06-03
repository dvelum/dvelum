this.addDesignerItems();
this.callParent();

Ext.each(this.locales,function(item){
  var field = Ext.create('Ext.form.field.Text',{
    fieldLabel: item,
    name:'lang['+item+']',
    value:''
  });
  this.childObjects.dataForm.add(field);
},this);
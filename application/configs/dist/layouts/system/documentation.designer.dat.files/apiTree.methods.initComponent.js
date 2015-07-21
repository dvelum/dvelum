this.addDesignerItems();

this.store = Ext.create('Ext.data.TreeStore',{
  autoLoad:false,
  proxy:{
    url:app.createUrl([this.controllerUrl + 'apitree']),
	reader:{
      idProperty:"id"
	},
	type:"ajax"
  },
  root:{
    id:0,
    expanded:true,
    text:appLang.ROOT
  },
  fields:[
      {
          name:"id",
          type:"string"
      },{
          name:"name",
          type:"string"
      },{
          name:"parentId",
          type:"string"
      },{
          name:"path",
          type:"string"
      },{
          name:"text",
          type:"string"
      },{
          name:"isDir",
          type:"boolean"
      },{
          name:"hid",
          type:"string"
      }
  ],
});

this.callParent();
  
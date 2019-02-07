Ext.ns('app.orm.validate');

Ext.define('app.orm.validate.Model',{
    extend:'Ext.data.Model',
    fields: [
        {name:'id', type:'string'},
        {name:'title' ,  type:'string'},
        {name:'name' ,  type:'string'},
        {name:'validdb', typr:'boolean'},
        {name:'broken' , type:'boolean'},
        {name:'locked' , type:'boolean'},
        {name:'readonly' , type:'boolean'},
        {name:'distributed', type:'boolean'},
        {name:'shard', type:'string'},
        {name:'shard_title', type:'string'}
    ]
});

Ext.define('app.orm.validate.Window',{
   extend:'Ext.window.Window',
   resizable:true,
   closable:true,
   modal:true,
   layout:'fit',
   width:650,
   height:450,
   objectsStore:null,
   validateQueue:null,
   initComponent:function() {
       var me = this;
       this.validateQueue = [];
       this.dataStore = Ext.create('Ext.data.Store',{
           model:'app.orm.validate.Model',
           autoLoad: false,
           groupField:'validdb'
       });

       this.buttons = [
           {
               text:appLang.CLOSE,
               handler: me.close,
               scope:me
           }
       ];

       this.tbar = [
           {
               iconCls:'refreshIcon',
               tooltip:appLang.REFRESH,
               listeners:{
                   click:{
                       fn:function(){
                           me.validateAllObjects();
                       },
                       scope:me
                   }
               }
           },
           {
               xtype:'button',
               text:appLang.BUILD_ALL,
               tooltip:appLang.BUILD_ALL,
               iconCls:'buildIcon',
               scope:me,
               handler:function(){
                   me.fireEvent('RebuildAllCall',this)
               }
           },'->',
           {
               xtype:'searchpanel',
               store:this.dataStore,
               local:true,
               fieldNames:['title','name']
           }
       ];

       var titleRenderer = function(value, metaData, record){
           if(record.get('external')){
               metaData.tdCls ='color:#0415D0;';
           }

           if(record.get('readonly')){
               value = '<img src="'+app.wwwRoot+'i/system/plock.png" title="'+appLang.DB_READONLY_TOOLTIP+'" height="15"> ' + value;

           }

           if(record.get('locked') && !record.get('readonly')){
               value = '<img src="'+app.wwwRoot+'i/system/locked.png" title="'+appLang.DB_STRUCTURE_LOCKED_TOOLTIP+'" height="15"> ' + value;
           }

           if(record.get('broken')) {
               metaData.style ='background-color:red;';
               value = '<img src="'+app.wwwRoot+'i/system/broken.png" title="'+appLang.BROKEN_LINK+'" height="15">&nbsp; ' + value;
           }
           return value;
       };

       this.dataGrid = Ext.create('Ext.grid.Panel',{
           store:this.dataStore,
           viewConfig:{
             columnLines:true,
             enableTextSelection:true
           },
           bufferedRenderer:false,
           columns:[
               {
                   xtype:'actioncolumn',
                   align:'center',
                   width:40,
                   items:[
                       {
                           tooltip:appLang.REBUILD_DB_TABLE,
                           iconCls:'buildIcon',
                           scope:this,
                           handler:function(grid, rowIndex, colIndex){
                               var record = grid.getStore().getAt(rowIndex);
                               this.fireEvent('rebuildTable' , record.get('name'), record.get('shard'));
                           }
                       }
                   ]
               }, {
                   text:appLang.TITLE,
                   width:200,
                   dataIndex:'title',
                   flex:1,
                   renderer:titleRenderer
               },{
                   text: appLang.OBJECT,
                   dataIndex: 'name',
                   align:'left',
                   width:200
               },{
                   text: appLang.SHARD,
                   dataIndex: 'shard_title',
                   align:'left'
               },{
                   text:appLang.VALID_DB,
                   dataIndex: 'validdb',
                   align:'center',
                   renderer:app.checkboxRenderer
               }
           ],
           features:[
               Ext.create('Ext.grid.feature.Grouping',{
                   groupHeaderTpl: '{name} ({rows.length})',
                   startCollapsed: 0,
                   enableGroupingMenu: 1,
                   hideGroupedHeader:0
               })
           ]
       });

       this.items = [this.dataGrid];


       this.callParent(arguments);

       this.on('show', function(){
           app.checkSize(this);
           Ext.WindowMgr.register(this);
           Ext.WindowMgr.bringToFront(this);
       }, this);
   },
   validateAllObjects:function(){
       this.dataStore.removeAll();

       this.validateQueue = [];
       this.objectsStore.each(function(record){
           this.validateQueue.push(record.get('name'));
       },this);
       this.validateObjects();
   },
    addToQueue:function(name){
      this.validateQueue.push(name);
    },
    validateObjects:function(){
        var me = this;
        if(this.validateQueue.length){
            var object = this.validateQueue.shift();
            Ext.Ajax.request({
                url:  app.crud.orm.Actions.validateRecord,
                method: 'post',
                scope:me,
                params:{
                    object:object
                },
                success: function(response, request) {
                    response =  Ext.JSON.decode(response.responseText);
                    if(response.success){
                        var data = response.data;
                        var itemId = data.name;
                        var index = me.dataStore.findExact('name', data.name);
                        if(index!=-1){
                            var record = me.dataStore.getAt(index);
                            Ext.Object.each(data, function(k, v){
                                record.set(k, v);
                            });
                            record.commit();
                        }else{
                            me.dataStore.add(data);
                        }
                    }
                    me.validateObjects();
                }
            });
        }
    },
   destroy:function(){
       this.dataStore.destroy();
       this.dataGrid.destroy();
       this.callParent(arguments);
   }
});
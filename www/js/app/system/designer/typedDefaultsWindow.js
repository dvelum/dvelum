Ext.define('designer.typedDefaultsModel', {
    extend: 'Ext.data.Model',
    fields: [
        {name:'key' , type:'string'},
        {name:'value', type:'string'},
        {name:'type', type:'string'}
    ],
    idProperty:'key'
});
/**
 * @event dataSaved
 */
Ext.define('designer.typedDefaultsWindow',{
    extend:'Ext.Window',
    title:'defaults',
    dataGrid:null,
    dataStore:null,
    cellEditing:null,
    width:400,
    height:400,
    modal:true,
    closeAction:'destroy',
    initialData:null,
    layout:'fit',

    initComponent:function()
    {
        this.cellEditing = Ext.create('Ext.grid.plugin.CellEditing', {clicksToEdit: 1});

        this.dataStore = Ext.create('Ext.data.Store',{
            model:'designer.typedDefaultsModel'
        });

        this.dataStore.loadData(this.initialData);

        this.tbar = [
            {
                iconCls:'plusIcon',
                tooltip:desLang.add,
                handler:function(){
                    var r = Ext.create('designer.defaultsModel', {
                        key:'',
                        value:''
                    });
                    this.dataStore.insert(0, r);
                    this.cellEditing.startEditByPosition({row: 0, column: 0});
                },
                scope:this
            }
        ];

        this.dataGrid = Ext.create('Ext.grid.Panel',{
            store:this.dataStore,
            scrollable:true,
            frame: false,
            loadMask:true,
            columnLines: true,
            plugins: [this.cellEditing],
            columns:[{
                text:desLang.key,
                dataIndex:'key',
                flex:1,
                editor:{
                    xtype:'textfield'
                }
            },{
                text:desLang.type,
                dataIndex:'type',
                flex:1,
                editor:{
                    xtype:'combobox',
                    displayField:'title',
                    valueField:'id',
                    forceSelection:true,
                    allowBlank:false,
                    autoLoad:false,
                    queryMode:'local',
                    store: Ext.create('Ext.data.ArrayStore', {
                        model:'app.comboStringModel',
                        data:[
                           ['Number','Number'],
                           ['String','String'],
                           ['Object','Object'],
                           ['Boolean','Boolean']
                        ]
                    })
                }
            },{
                text:desLang.value,
                dataIndex:'value',
                flex:1,
                editor:{
                    xtype:'textfield'
                }
            },{
                xtype:'actioncolumn',
                width:25,
                align:'center',
                sortable: false,
                menuDisabled:true,
                items:[
                    {
                        iconCls:'deleteIcon',
                        tooltip:desLang.remove,
                        handler:function(grid , row , col){
                            var store = grid.getStore();
                            store.remove(store.getAt(row));
                        }
                    }
                ]
            }]
        });
        this.items = [this.dataGrid];
        this.buttons = [
            {
                text:desLang.save,
                scope:this,
                handler:this.saveData
            },{
                text:desLang.cancel,
                scope:this,
                handler:this.close
            }
        ];
        this.callParent();
    },
    saveData:function(){
        var s ='';
        var result = [];
        this.dataStore.commitChanges();
        var data = this.dataStore.getData();

        this.dataStore.each(function(item) {
            result.push(item.getData());
        });
        this.fireEvent('dataChanged' ,  Ext.JSON.encode(result));
        this.close();
    }
});
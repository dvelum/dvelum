this.usersTab = Ext.create('appUsersClasses.Users',{
  canEdit:canEdit ,
  canDelete:canDelete
});


this.permissionsPanel = Ext.create('appUsersClasses.Permissions',{
  canEdit:canEdit ,
  canDelete:canDelete,
  region:'center'
});


this.groupsPanel = Ext.create('appUsersClasses.Groups',{
  canEdit:canEdit ,
  canDelete:canDelete,
  region:'west',
  width:250,
  listeners:{
    selectionchange:{
      fn:function(model, selected){
        
        if(selected.length){
          selected = selected[0];
        }else{
          selected = false;
        }

        this.permissionsPanel.groupSelected(selected);
      },
      scope:this
    },
    groupDeleted:{
      fn:function(){
        this.permissionsPanel.groupSelected(false);
      },
      scope:this
    }
  }
});

this.groupsTab = Ext.create('Ext.Panel',{
  layout:'border',
  title:appLang.GROUPS,
  activeTab:0,
  deferredRender:false,
  items:[this.groupsPanel ,this.permissionsPanel]
});

this.removeAll();
this.add([this.usersTab , this.groupsTab]);
this.setActiveTab(0);
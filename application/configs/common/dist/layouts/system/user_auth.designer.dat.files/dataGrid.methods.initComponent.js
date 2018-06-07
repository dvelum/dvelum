
            this.addDesignerItems();
            this.callParent();

            if(!Ext.isEmpty(this.canEdit) && !Ext.isEmpty(this.setCanEdit)){
                this.setCanEdit(this.canEdit);
            }else{
                this.canEdit = false;
            }

            if(!Ext.isEmpty(this.canDelete) && !Ext.isEmpty(this.setCanDelete)){
                this.setCanDelete(this.canDelete);
            }else{
                this.canDelete = false;
            }

            if(!Ext.isEmpty(this.canPublish) && !Ext.isEmpty(this.setCanPublish)){
                this.setCanPublish(this.canPublish);
            }else{
                this.canPublish = false;
            }
        
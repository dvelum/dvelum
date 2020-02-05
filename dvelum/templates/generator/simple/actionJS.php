<?php
    $runNamespace = $this->get('runNamespace');
    $object = $this->get('object');
?>
/*
 * Here you can define application logic
 * To obtain info about current user access rights
 * you can use global scope JS vars canEdit , canDelete , canPublish
 * To access project elements, please use the namespace you defined in the config
 * For example: <?php echo $runNamespace;?>.Panel or Ext.create("<?php echo $this->get('classNamespace');?>.editWindow", {});
 */
Ext.onReady(function(){
    // Init permissions
    app.application.on("projectLoaded",function(module){
        if(Ext.isEmpty(module) || module === "<?php echo $object;?>"){
            if(!Ext.isEmpty(<?php echo $runNamespace;?>.dataGrid)){
                <?php echo $runNamespace;?>.dataGrid.setCanEdit(app.permissions.canEdit("<?php echo $object;?>"));
                <?php echo $runNamespace;?>.dataGrid.setCanDelete(app.permissions.canDelete("<?php echo $object;?>"));
            <?php if($this->get('vc')) { ?>
                <?php echo $runNamespace;?>.dataGrid.setCanPublish(app.permissions.canPublish("<?php echo $object;?>"));
            <?php }?>
                <?php echo $runNamespace;?>.dataGrid.getView().refresh();
            }
        }
    });
});
<?php
    $vc = $this->get('vc');
    $namespace = $this->get('namespace');
?>
var win = Ext.create("<?php echo $namespace;?>.editWindow", {
    dataItemId:id,
    canDelete:this.canDelete,
    <?php if($vc){ echo ' canPublish:this.canPublish,';}?>
    canEdit:this.canEdit
});
win.on("dataSaved",function(){
    this.getStore().load();
    <?php if(!$vc){ echo '  win.close();';}?>
},this);
win.show();
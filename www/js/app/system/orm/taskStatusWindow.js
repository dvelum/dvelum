/**
 * @event failure
 * @param msg
 *
 * @event updateState
 * @param data
 *
 * @event finished
 * @param data
 *
 */
Ext.define('app.crud.orm.taskStatusWindow',{

    extend:'Ext.Window',
    controllerUrl:null,
    extraParams:null,
    width:450,
    height:140,
    modal:true,
    closable:false,
    resizable:false,
    checkInterval:2000,
    progressBar:null,
    layout:'anchor',
    timerId:null,
    bodyPadding:'5px',

   initComponent:function(){

       this.processLabel = Ext.create('Ext.form.field.Display',{
           value:'<div align="center"><b>'+appLang.PROCESSING+'<b></div>',
           anchor:'100%'
       });

       this.progressBar = Ext.create('Ext.ProgressBar',{
           anchor:'100%'
       });

       var me = this;
       this.closeBtn = Ext.create('Ext.Button',{
           text:appLang.CLOSE,
           hidden:true,
           handler:function(){
               me.close();
           }
       });

       this.items = [
           this.processLabel ,
           this.progressBar
       ];

       this.fbar = [this.closeBtn];
       this.callParent();

       var me = this;
       setTimeout(function(){me.checkState();} , this.checkInterval);
   },
   /**
    * Request status info
    */
   checkState:function(){
       var me = this;
       Ext.Ajax.request({
           url: me.controllerUrl,
           method: 'post',
           params:me.extraParams,
           success: function(response, request) {
               response =  Ext.JSON.decode(response.responseText);
               if(response.success){

                  /*
                    statuses from task dictionary
                       0=> 'Undefined',
                       1 => 'Run',
                       2 => 'Sleep',
                       3 => 'Stoped',
                       4 => 'Finished',
                       5 => 'Error'
                   */
                  switch(parseInt(response.data.status)){
                      case 0:
                      case 1:
                      case 2:
                          me.updateState(response.data);
                          if(me.timerId){
                              clearTimeout(me.timerId);
                          }
                          me.timerId = setTimeout(function(){me.checkState();} , me.checkInterval);
                          break;
                      case 3:
                      case 4:
                          me.updateState(response.data);
                          me.fireEvent('finished' , response.data);
                          me.closeBtn.show();
                          me.processLabel.setValue('<div align="center"><b>'+appLang.COMPLETED+'<b></div>');
                          break;
                      case 5:
                          me.fireEvent('failure' , 'Task Error');
                          me.closeBtn.show();
                          me.processLabel.setValue('<div align="center"><b>'+appLang.ERROR+'<b></div>');
                          break;
                  }
               }else{
                  me.fireEvent('failure' , response.msg);
               }
           },
           failure:function(form, action){
               me.fireEvent('failure' , appLang.MSG_LOST_CONNECTION);
           }
       });
   },
   /**
    *  Update progress
    * @param {Object} data
    */
   updateState:function(data)
   {
       var value = data.op_finished / data.op_total;
       this.progressBar.updateProgress(value , data.op_finished + '/'+ data.op_total , true);
       this.fireEvent('updateState' , data);
   }
});
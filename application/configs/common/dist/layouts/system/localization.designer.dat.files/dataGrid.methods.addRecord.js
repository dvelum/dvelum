var store = this.getStore();
var curDictionary = store.proxy.extraParams['dictionary']; 
var localizationList = [];

appLocalizationRun.localesStore.each(function(record){
  localizationList.push(record.get('id'));
});

Ext.create('appLocalizationClasses.addWindow',{
  dictionary:curDictionary,
  locales:localizationList,
  listeners:{
    dataSaved:{
      fn:function(values){
        // Create a model instance
        var index = store.findExact('id',values.key);
        if(index!==-1){
          var record = store.getAt(index);
          record.set('title' , values['lang['+curDictionary.split('/')[0]+']']);
          record.set('sync' , true);
          record.commit();
        }else{    
          var rec = {
              id: values.key,
              key:values.key,
              title: values['lang['+curDictionary.split('/')[0]+']'],
              sync: true,
          };
          store.insert(0, rec);  
       }
      },
      scope:this
    }
  }
}).show();
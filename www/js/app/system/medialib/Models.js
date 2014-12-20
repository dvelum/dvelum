Ext.define('app.medialibFilesModel', {
    extend: 'Ext.data.Model',
    fields: [
              {name:'id',type:'integer'},
	          {name:'type',type:'string'},
	          {name:'url',type:'string'},
	          {name:'thumb',type:'string'},
	          {name:'thumbnail',type:'string'},
	          {name:'title'},
	          {name:'size'},
     	      {name:'srcpath', type:'string'},
     	      {name:'ext',type:'string'},
     	      {name:'path',type:'string'},
     	      {name:'icon', type:'string'}
    ]
});

Ext.define('app.medialibModel', {
    extend: 'Ext.data.Model',
    fields: [
             {name:'id' , type:'integer'},
    	     {name:'thumb' , type:'string'},
       	     {name:'date', type:"date", dateFormat: "Y-m-d H:i:s"},		
       	     {name:'modified',type:'string'},
       	     {name:'title',type:'string'},
       	     {name:'alttext',type:'string'},   	     
     	     {name:'text',type:'string'},	
     	     {name:'caption',type:'string'}, 	
     	     {name:'description',type:'string'}, 	
     	     {name:'size',type:'float'}, 	
     	     {name:'user_id',type:'integer'}, 
     	     {name:'path',type:'string'},
     	     {name:'type',type:'string'},
     	     {name:'user_name',type:'string'},
     	     {name:'ext',type:'string'},
     	     {name:'srcpath', type:'string'},
     	     {name:'thumbnail', type:'string'},
     	     {name:'icon', type:'string'}
    ]
});
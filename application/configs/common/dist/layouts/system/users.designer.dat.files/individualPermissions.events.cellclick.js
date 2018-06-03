var column = cmp .getHeaderCt().getHeaderAtIndex(cellIndex).itemId;

function getGroupValue(field, record){
   return record.get('g_'+field);
}
switch (column) {
    case 'allcol':
    
        var allChecked = me.checkPermissionsCol(record);
        if (!allChecked) {
          
            record.set('view', true);
            record.set('edit', true);
            record.set('delete', true);
            if (record.get('rc')) {
                record.set('publish', true);
                record.set('only_own', true);
            }
        } else {
          
           if(!getGroupValue('view', record)){
            record.set('view', false);
           }
          
           if(!getGroupValue('edit', record)){
            record.set('edit', false);
           }
          
           if(!getGroupValue('delete', record)){
            record.set('delete', false);
           }


            if (record.get('rc')){
              if(!getGroupValue('publish', record)){
                record.set('publish', false);
              }
              if(!getGroupValue('only_own', record)){
                record.set('only_own', false);
              }
            }
        }
        return false;
        break;
    case 'publish':
        if (!record.get('rc')){
            return false;
        }
        if(!getGroupValue(column, record)){
    		record.set(column, !(record.get(column)));
    	}
        break;
   case 'only_own':
     	if (!record.get('rc')){
            return false;
        }
        if(!getGroupValue(column, record)){
    		record.set(column, !(record.get(column)));
    	}
    break;
    
  case 'view':
  case 'edit':
  case 'delete':
    if(!getGroupValue(column, record)){
    	record.set(column, !(record.get(column)));
    }
    break;
}
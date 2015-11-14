var toCheck = ['view', 'edit', 'publish', 'delete','only_own'];
var allChecked = true;

Ext.each(toCheck, function (item) {
    if (item != 'publish' && item!='only_own') {
        if (!record.get(item)) {
            allChecked = false;
        }
    }

    if (item == 'publish' && record.get('rc')) {
        if (!record.get(item)) {
            allChecked = false;
        }
    }
  
  
    if (item == 'only_own' && record.get('rc')) {
        if (!record.get(item)) {
            allChecked = false;
        }
    }
}, this);

return allChecked;
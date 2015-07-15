var toCheck = ['view', 'edit', 'publish', 'delete'];
var allChecked = true;

Ext.each(toCheck, function (item) {
    if (item != 'publish') {
        if (!record.get(item)) {
            allChecked = false;
        }
    }

    if (item == 'publish' && record.get('rc')) {
        if (!record.get(item)) {
            allChecked = false;
        }
    }
}, this);

return allChecked;
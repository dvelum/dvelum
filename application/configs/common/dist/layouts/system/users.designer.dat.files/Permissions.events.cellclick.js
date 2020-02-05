
var column = cmp .getHeaderCt().getHeaderAtIndex(cellIndex).itemId;

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
            record.set('view', false);
            record.set('edit', false);
            record.set('delete', false);

            if (record.get('rc')) {
                record.set('publish', false);
                record.set('only_own', false);
            }
        }
        return false;
        break;
    case 'publish':
        if (!record.get('rc'))
            return false;
        break;
   case 'only_own':
     	if (!record.get('rc'))
            return false;
    break;
}
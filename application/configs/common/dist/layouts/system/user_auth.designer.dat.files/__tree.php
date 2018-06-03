<?php return array (
  '_Component_' => 
  array (
    'id' => '_Component_',
    'parent' => false,
    'data' => '_Component_.config.php',
    'order' => 0,
  ),
  '_Layout_' => 
  array (
    'id' => '_Layout_',
    'parent' => false,
    'data' => '_Layout_.config.php',
    'order' => 1,
  ),
  'User_AuthModel' => 
  array (
    'id' => 'User_AuthModel',
    'parent' => '_Component_',
    'data' => 'User_AuthModel.config.php',
    'order' => 0,
  ),
  'dataGrid' => 
  array (
    'id' => 'dataGrid',
    'parent' => '_Component_',
    'data' => 'dataGrid.config.php',
    'order' => 1,
  ),
  'editWindow' => 
  array (
    'id' => 'editWindow',
    'parent' => '_Component_',
    'data' => 'editWindow.config.php',
    'order' => 2,
  ),
  'dataStore' => 
  array (
    'id' => 'dataStore',
    'parent' => '_Layout_',
    'data' => 'dataStore.config.php',
    'order' => 0,
  ),
  'dataGrid_instance' => 
  array (
    'id' => 'dataGrid_instance',
    'parent' => '_Layout_',
    'data' => 'dataGrid_instance.config.php',
    'order' => 1,
  ),
  'dataGrid__docked' => 
  array (
    'id' => 'dataGrid__docked',
    'parent' => 'dataGrid',
    'data' => 'dataGrid__docked.config.php',
    'order' => 0,
  ),
  'filters' => 
  array (
    'id' => 'filters',
    'parent' => 'dataGrid__docked',
    'data' => 'filters.config.php',
    'order' => 0,
  ),
  'editWindow_configTab' => 
  array (
    'id' => 'editWindow_configTab',
    'parent' => 'editWindow',
    'data' => 'editWindow_configTab.config.php',
    'order' => 1,
  ),
  'editWindow_generalTab' => 
  array (
    'id' => 'editWindow_generalTab',
    'parent' => 'editWindow',
    'data' => 'editWindow_generalTab.config.php',
    'order' => 0,
  ),
  'editWindow_config' => 
  array (
    'id' => 'editWindow_config',
    'parent' => 'editWindow_configTab',
    'data' => 'editWindow_config.config.php',
    'order' => 0,
  ),
  'editWindow_configTab__docked' => 
  array (
    'id' => 'editWindow_configTab__docked',
    'parent' => 'editWindow_configTab',
    'data' => 'editWindow_configTab__docked.config.php',
    'order' => 1,
  ),
  'editWindow_user' => 
  array (
    'id' => 'editWindow_user',
    'parent' => 'editWindow_generalTab',
    'data' => 'editWindow_user.config.php',
    'order' => 0,
  ),
  'editWindow_type' => 
  array (
    'id' => 'editWindow_type',
    'parent' => 'editWindow_generalTab',
    'data' => 'editWindow_type.config.php',
    'order' => 1,
  ),
  'addButton' => 
  array (
    'id' => 'addButton',
    'parent' => 'filters',
    'data' => 'addButton.config.php',
    'order' => 0,
  ),
  'sep1' => 
  array (
    'id' => 'sep1',
    'parent' => 'filters',
    'data' => 'sep1.config.php',
    'order' => 1,
  ),
); 
<?php return array (
  '_Layout_' => 
  array (
    'id' => '_Layout_',
    'parent' => false,
    'data' => '_Layout_.config.php',
    'order' => -500,
  ),
  '_Component_' => 
  array (
    'id' => '_Component_',
    'parent' => false,
    'data' => '_Component_.config.php',
    'order' => -1000,
  ),
  'dataStoreModel' => 
  array (
    'id' => 'dataStoreModel',
    'parent' => '_Component_',
    'data' => 'dataStoreModel.config.php',
    'order' => 1,
  ),
  'dataGrid' => 
  array (
    'id' => 'dataGrid',
    'parent' => '_Component_',
    'data' => 'dataGrid.config.php',
    'order' => 2,
  ),
  'dataStore' => 
  array (
    'id' => 'dataStore',
    'parent' => '_Component_',
    'data' => 'dataStore.config.php',
    'order' => 0,
  ),
  'mainPanel' => 
  array (
    'id' => 'mainPanel',
    'parent' => '_Layout_',
    'data' => 'mainPanel.config.php',
    'order' => 0,
  ),
  'dataGrid__docked' => 
  array (
    'id' => 'dataGrid__docked',
    'parent' => 'dataGrid',
    'data' => 'dataGrid__docked.config.php',
    'order' => false,
  ),
); 
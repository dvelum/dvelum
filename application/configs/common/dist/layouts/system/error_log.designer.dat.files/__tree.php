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
  'dataStore' => 
  array (
    'id' => 'dataStore',
    'parent' => '_Component_',
    'data' => 'dataStore.config.php',
    'order' => 0,
  ),
  'dataGrid' => 
  array (
    'id' => 'dataGrid',
    'parent' => '_Component_',
    'data' => 'dataGrid.config.php',
    'order' => 2,
  ),
  'dataStoreModel' => 
  array (
    'id' => 'dataStoreModel',
    'parent' => '_Component_',
    'data' => 'dataStoreModel.config.php',
    'order' => 1,
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
    'order' => 0,
  ),
  'logTools' => 
  array (
    'id' => 'logTools',
    'parent' => 'dataGrid__docked',
    'data' => 'logTools.config.php',
    'order' => 0,
  ),
  'dateFilterLabel' => 
  array (
    'id' => 'dateFilterLabel',
    'parent' => 'logTools',
    'data' => 'dateFilterLabel.config.php',
    'order' => 0,
  ),
  'dateFilter' => 
  array (
    'id' => 'dateFilter',
    'parent' => 'logTools',
    'data' => 'dateFilter.config.php',
    'order' => 1,
  ),
); 
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
  'mainPanel' => 
  array (
    'id' => 'mainPanel',
    'parent' => '_Component_',
    'data' => 'mainPanel.config.php',
    'order' => 0,
  ),
  'changesStore' => 
  array (
    'id' => 'changesStore',
    'parent' => '_Layout_',
    'data' => 'changesStore.config.php',
    'order' => 1,
  ),
  'mainPanelInstance' => 
  array (
    'id' => 'mainPanelInstance',
    'parent' => '_Layout_',
    'data' => 'mainPanelInstance.config.php',
    'order' => 3,
  ),
  'dataStore' => 
  array (
    'id' => 'dataStore',
    'parent' => '_Layout_',
    'data' => 'dataStore.config.php',
    'order' => 0,
  ),
  'objectsStore' => 
  array (
    'id' => 'objectsStore',
    'parent' => '_Layout_',
    'data' => 'objectsStore.config.php',
    'order' => 2,
  ),
  'changesGrid__docked' => 
  array (
    'id' => 'changesGrid__docked',
    'parent' => 'changesGrid',
    'data' => 'changesGrid__docked.config.php',
    'order' => 0,
  ),
  'dataGrid__docked' => 
  array (
    'id' => 'dataGrid__docked',
    'parent' => 'dataGrid',
    'data' => 'dataGrid__docked.config.php',
    'order' => 0,
  ),
  'tools' => 
  array (
    'id' => 'tools',
    'parent' => 'dataGrid__docked',
    'data' => 'tools.config.php',
    'order' => 0,
  ),
  'changesGrid' => 
  array (
    'id' => 'changesGrid',
    'parent' => 'mainPanel',
    'data' => 'changesGrid.config.php',
    'order' => 2,
  ),
  'mainPanel__docked' => 
  array (
    'id' => 'mainPanel__docked',
    'parent' => 'mainPanel',
    'data' => 'mainPanel__docked.config.php',
    'order' => 0,
  ),
  'dataGrid' => 
  array (
    'id' => 'dataGrid',
    'parent' => 'mainPanel',
    'data' => 'dataGrid.config.php',
    'order' => 1,
  ),
  'dateFilterLabel' => 
  array (
    'id' => 'dateFilterLabel',
    'parent' => 'tools',
    'data' => 'dateFilterLabel.config.php',
    'order' => 0,
  ),
  'objectFilterLabel' => 
  array (
    'id' => 'objectFilterLabel',
    'parent' => 'tools',
    'data' => 'objectFilterLabel.config.php',
    'order' => 6,
  ),
  'userFilterLabel' => 
  array (
    'id' => 'userFilterLabel',
    'parent' => 'tools',
    'data' => 'userFilterLabel.config.php',
    'order' => 2,
  ),
  'operationFilterLabel' => 
  array (
    'id' => 'operationFilterLabel',
    'parent' => 'tools',
    'data' => 'operationFilterLabel.config.php',
    'order' => 4,
  ),
  'dateFilter' => 
  array (
    'id' => 'dateFilter',
    'parent' => 'tools',
    'data' => 'dateFilter.config.php',
    'order' => 1,
  ),
  'objectFilter' => 
  array (
    'id' => 'objectFilter',
    'parent' => 'tools',
    'data' => 'objectFilter.config.php',
    'order' => 7,
  ),
  'userFilter' => 
  array (
    'id' => 'userFilter',
    'parent' => 'tools',
    'data' => 'userFilter.config.php',
    'order' => 3,
  ),
  'operationFilter' => 
  array (
    'id' => 'operationFilter',
    'parent' => 'tools',
    'data' => 'operationFilter.config.php',
    'order' => 5,
  ),
); 
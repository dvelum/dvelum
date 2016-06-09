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
  'repoBrowserWindow' => 
  array (
    'id' => 'repoBrowserWindow',
    'parent' => '_Component_',
    'data' => 'repoBrowserWindow.config.php',
    'order' => 1,
  ),
  'dataGrid' => 
  array (
    'id' => 'dataGrid',
    'parent' => '_Component_',
    'data' => 'dataGrid.config.php',
    'order' => 0,
  ),
  'mainPanel' => 
  array (
    'id' => 'mainPanel',
    'parent' => '_Layout_',
    'data' => 'mainPanel.config.php',
    'order' => 3,
  ),
  'dataStore' => 
  array (
    'id' => 'dataStore',
    'parent' => '_Layout_',
    'data' => 'dataStore.config.php',
    'order' => 0,
  ),
  'repoStore' => 
  array (
    'id' => 'repoStore',
    'parent' => '_Layout_',
    'data' => 'repoStore.config.php',
    'order' => 1,
  ),
  'repoItemsStore' => 
  array (
    'id' => 'repoItemsStore',
    'parent' => '_Layout_',
    'data' => 'repoItemsStore.config.php',
    'order' => 2,
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
  'rbItemsGrid__docked' => 
  array (
    'id' => 'rbItemsGrid__docked',
    'parent' => 'rbItemsGrid',
    'data' => 'rbItemsGrid__docked.config.php',
    'order' => 0,
  ),
  'rbRepoSelect' => 
  array (
    'id' => 'rbRepoSelect',
    'parent' => 'rbTools',
    'data' => 'rbRepoSelect.config.php',
    'order' => 1,
  ),
  'rbRepoSelectLabel' => 
  array (
    'id' => 'rbRepoSelectLabel',
    'parent' => 'rbTools',
    'data' => 'rbRepoSelectLabel.config.php',
    'order' => 0,
  ),
  'repoBrowserWindow__docked' => 
  array (
    'id' => 'repoBrowserWindow__docked',
    'parent' => 'repoBrowserWindow',
    'data' => 'repoBrowserWindow__docked.config.php',
    'order' => 0,
  ),
  'rbItemsGrid' => 
  array (
    'id' => 'rbItemsGrid',
    'parent' => 'repoBrowserWindow',
    'data' => 'rbItemsGrid.config.php',
    'order' => 1,
  ),
  'rbTools' => 
  array (
    'id' => 'rbTools',
    'parent' => 'repoBrowserWindow__docked',
    'data' => 'rbTools.config.php',
    'order' => 0,
  ),
  'refreshButton' => 
  array (
    'id' => 'refreshButton',
    'parent' => 'tools',
    'data' => 'refreshButton.config.php',
    'order' => 0,
  ),
  'downloadsButton' => 
  array (
    'id' => 'downloadsButton',
    'parent' => 'tools',
    'data' => 'downloadsButton.config.php',
    'order' => 1,
  ),
); 
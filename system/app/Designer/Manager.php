<?php
/*
* DVelum project http://code.google.com/p/dvelum/ , http://dvelum.net
* Copyright (C) 2011-2013  Kirill A Egorov
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
/**
 * Designer Factory Wrapper
 */
class Designer_Manager
{
  /**
   * Application configuration
   * @var Config_Abstract
   */
  protected $_appConfig;
  /**
   * Designer configuration
   * @var Config_Abstract
   */
  protected $_designerConfig; 
  
  public function __construct(Config_Abstract $appConfig)
  {
     $this->_appConfig = $appConfig;
     $this->_designerConfig = Config::factory(Config::File_Array , $appConfig->get('configs') . 'designer.php');
  }
  
  /**
   * Render Designer project
   * @param string $projectFile - file path
   * @param string $renderTo - optional, default false (html tag id)
   */
  public function renderProject($projectFile , $renderTo = false)
  {
     $replaces = $this->getReplaceConfig();
     Designer_Factory::runProject($projectFile , $this->_designerConfig , $replaces , $renderTo);
  }
  
  /**
   * Get configuration of code templates (for replacing)
   * @return array
   */
  public function getReplaceConfig()
  {
     $templates =  $this->_designerConfig->get('templates');
     return array(
        array(
                        'tpl' => $templates['wwwroot'],
                        'value' => $this->_appConfig->get('wwwroot')
        ),
        array(
                        'tpl' => $templates['adminpath'],
                        'value' => $this->_appConfig->get('adminPath')
        ),
        array(
                        'tpl' => $templates['urldelimiter'],
                        'value' => $this->_appConfig->get('urlDelimiter')
        )
     );
  }
}
/*
 * DVelum Auto generated controller
 */
declare(strict_types=1);

namespace <?php echo $this->get('controller_namespace')?>;

use Dvelum\App\Backend;

class Controller extends Backend\Ui\Controller
{
   /**
    * Fields for grid
    * @var array $listFields
    */
    protected $listFields = <?php echo $this->get('listFields')?>;
   /**
    * Show link title for fields (grid)
    * @var array $listLinks
    */
    protected $listLinks = <?php echo $this->get('listLinks')?>;
   /**
    * User can view lists of additional ORM objects
    * @var array $canViewObjects
    */
    protected $canViewObjects = <?php echo $this->get('canViewObjects')?>;
   /**
    * Controller module for permissions check
    * @return string
    */
    public function getModuleName() : string {
        return '<?php echo $this->get('moduleName')?>';
    }
   /**
    * Controller code edit Orm Object
    * @return string
    */
    public function getObjectName() : string {
        return '<?php echo $this->get('objectName')?>';
    }
}
<?php
/**
 * Abstract class for event managing
 * @author Kirill A Egorov kirill.a.egorov@gmail.com
 * @copyright Copyright (C) 2012  Kirill A Egorov,
 * DVelum project http://code.google.com/p/dvelum/ , http://dvelum.net
 * @license General Public License version 3
 */
abstract class Db_Object_Event_Manager{
	const BEFORE_ADD = 'onBeforeAdd';
	const BEFORE_UPDATE = 'onBeforeUpdate';
	const BEFORE_DELETE = 'onBeforeDelete';
	const BEFORE_UNPUBLISH = 'onBeforeUnpublish';
	const BEFORE_PUBLISH = 'onBeforePublish';
	const BEFORE_ADD_VERSION = 'onBeforeAddVersion';
	const AFTER_ADD = 'onAfterAdd';
	const AFTER_ADD_VERSION = 'onAfterAddVersion';
	const AFTER_UPDATE = 'onAfterUpdate';
	const AFTER_DELETE = 'onAfterDelete';
	const AFTER_UNPUBLISH = 'onAfterUnpublish';
	const AFTER_PUBLISH = 'onAfterPublish';
	const AFTER_UPDATE_BEFORE_COMMIT = 'onAfterUpdateBeforeCommit';
	/**
	 * Find and run event triggers
	 * Note that onBeforeDelete and onAfterDelete events provide "SpacialCase" empty Db_Object
	 * id property exists
	 * @param string $code  (action constant)
	 * @param Db_Object $object
	 */
	abstract public function fireEvent($code , Db_Object $object);
}
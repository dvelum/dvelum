<?php
/**
 *  DVelum project https://github.com/dvelum/dvelum
 *  Copyright (C) 2011-2017  Kirill Yegorov
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace Dvelum\Orm\Record\Event;

use Dvelum\Orm;

/**
 * Abstract class for event managing
 * @author Kirill A Egorov kirill.a.egorov@gmail.com
 * @copyright Copyright (C) 2012  Kirill A Egorov,
 * DVelum project https://github.com/dvelum/dvelum , http://dvelum.net
 * @license General Public License version 3
 */
abstract class Manager
{
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
    const AFTER_INSERT_BEFORE_COMMIT = 'onAfterInsertBeforeCommit';
    const AFTER_DELETE_BEFORE_COMMIT = 'onAfterDeleteBeforeCommit';

	/**
	 * Find and run event triggers
	 * Note that onBeforeDelete and onAfterDelete events provide "SpacialCase" empty Db_Object
	 * id property exists
	 * @param string $code  (action constant)
	 * @param Orm\RecordInterface $object
	 */
	abstract public function fireEvent(string $code , Orm\RecordInterface $object);
}
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
declare(strict_types=1);

namespace Dvelum\App\Form\Adapter;

use Dvelum\App\Form\Adapter;
use Dvelum\App\Form;

class Page extends Adapter\Orm\Record
{
    public function validateRequest(): bool
    {
        if (!parent::validateRequest()) {
            return false;
        }

        $posted = $this->request->postArray();

        if (isset($posted['blocks']) && strlen($posted['blocks'])) {
            $posted['blocks'] = serialize(json_decode($posted['blocks'], true));
        } else {
            $posted['blocks'] = serialize([]);
        }

        try {
            $this->object->set('blocks', $posted['blocks']);
        } catch (\Exception $e) {
            $this->errors[] = new Form\Error($this->lang->get('INVALID_VALUE'), null, 'block_error');
            return false;
        }
        return true;
    }
}

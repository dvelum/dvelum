<?php
/**
 *  DVelum project https://github.com/dvelum/dvelum
 *  Copyright (C) 2011-2019  Kirill Yegorov
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

namespace Dvelum\Orm\Record;

use Dvelum\Orm\RecordInterface;

class ErrorMessage
{
    static public function factory()
    {
        static $instance;
        if (empty($instance)) {
            $instance = new static();
        }
        return $instance;
    }

    public function cantRead(RecordInterface $record): string
    {
        return 'You do not have permission to view data in this object [' . $record->getName() . ':' . $record->getId() . '].';
    }

    public function cantEdit(RecordInterface $record): string
    {
        return 'You do not have permission to edit data in this object [' . $record->getName() . ':' . $record->getId() . '].';
    }

    public function cantDelete(RecordInterface $record): string
    {
        return 'You do not have permission to delete this object [' . $record->getName() . ':' . $record->getId() . '].';
    }

    public function cantPublish(RecordInterface $record): string
    {
        return 'You do not have permission to publish object [' . $record->getName() . '].';
    }

    public function cantCreate(RecordInterface $record): string
    {
        return 'You do not have permission to create object [' . $record->getName() . '].';
    }

    public function readOnly(RecordInterface $record): string
    {
        return 'ORM :: cannot save readonly object ['. $record->getName(). ':' . $record->getId() . '].';
    }

    public function emptyFields(RecordInterface $record, array $fields) : string
    {
        return 'ORM :: Fields can not be empty. ['.$record->getName(). ':' . $record->getId() . ' '.implode(',', $fields).']';
    }

    public function uniqueValue(string $field, $value) : string
    {
        if(is_array($value) || is_object($value)){
            $value = json_encode($value);
        }
        return 'The Field value should be unique '.$field . ':' . (string) $value;
    }

    public function cantLoadVersion(RecordInterface $record, int $vers) : string
    {
        return 'Cannot load version for ' . $record->getName() . ':' . $record->getId() . '. v:' . $vers;
    }

    public function cantLoadVersionIncompatible(RecordInterface $record, int $vers, string  $errors) : string
    {
        return 'Cannot load version data ' . $record->getName() . ':' . $record->getId() . '. v:' . $vers . '. This version contains incompatible data. ' . $errors;
    }
}
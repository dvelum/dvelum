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

namespace Dvelum\App\Backend\Localization;

use Dvelum\App\Backend;
use Dvelum\Filter;
use Dvelum\Request;
use Dvelum\Response;

/**
 * Class Controller
 * @package Dvelum\App\Backend\Localization
 */
class Controller extends Backend\Controller
{
    /**
     * @var Manager
     */
    protected $manager;

    public function __construct(Request $request, Response $response)
    {
        parent::__construct($request, $response);
        $this->manager = new Manager($this->appConfig);
    }

    public function getModule(): string
    {
        return 'Localization';
    }

    public function getObjectName(): string
    {
        return '';
    }

    /**
     * Get list of language dictionaries
     */
    public function langListAction()
    {
        $this->response->success($this->getLangs(false));
    }

    /**
     * Get list of  system locales
     */
    public function localesListAction()
    {
        $this->response->success($this->getLangs(true));
    }

    /**
     * @param bool $onlyMain
     * @return array
     */
    protected function getLangs(bool $onlyMain): array
    {
        $langs = $this->manager->getLangs($onlyMain);
        $result = [];

        foreach ($langs as $lang) {
            $result[] = ['id' => $lang];
        }

        return $result;
    }

    /**
     * Get localization dictionary content
     */
    public function localisationAction()
    {
        $dictionary = $this->request->post('dictionary', Filter::FILTER_CLEANED_STR, false);

        if ($dictionary === false) {
            $this->response->error($this->lang->get('WRONG_REQUEST'));
            return;
        }

        $data = $this->manager->getLocalization($dictionary);
        $this->response->success($data);
    }

    /**
     * Rebuild localization index
     */
    public function rebuildIndexAction()
    {
        try {
            $this->manager->rebuildAllIndexes();
            $this->response->success();
        } catch (\Exception $e) {
            $this->response->error($e->getMessage());
            return;
        }
    }

    /**
     * Add dictionary record
     */
    public function addRecordAction()
    {
        if (!$this->checkCanEdit()) {
            return;
        }

        $dictionary = $this->request->post('dictionary', Filter::FILTER_CLEANED_STR, false);
        $key = $this->request->post('key', Filter::FILTER_CLEANED_STR, false);
        $lang = $this->request->post('lang', Filter::FILTER_ARRAY, false);

        if ($dictionary === false || empty($dictionary) || $key === false || $lang === false) {
            $this->response->error($this->lang->get('WRONG_REQUEST'));
            return;
        }

        try {
            $this->manager->addRecord($dictionary, $key, $lang);
            $this->compileLangAction();
        } catch (\Exception $e) {
            $this->response->error($e->getMessage());
            return;
        }
    }

    /**
     * Remove dictionary record
     */
    public function removeRecordAction()
    {
        if (!$this->checkCanEdit()) {
            return;
        }
        $dictionary = $this->request->post('dictionary', Filter::FILTER_CLEANED_STR, false);
        $id = $this->request->post('id', Filter::FILTER_CLEANED_STR, false);

        if ($dictionary === false || empty($dictionary) || $id === false) {
            $this->response->error($this->lang->get('WRONG_REQUEST'));
            return;
        }
        try {
            $this->manager->removeRecord($dictionary, $id);
            $this->compileLangAction();
        } catch (\Exception $e) {
            $this->response->error($e->getMessage());
            return;
        }
    }

    /**
     * Update dictionary record
     */
    public function updateRecordsAction()
    {
        if (!$this->checkCanEdit()) {
            return;
        }

        $dictionary = $this->request->post('dictionary', Filter::FILTER_CLEANED_STR, false);
        $data = $this->request->post('data', Filter::FILTER_RAW, false);

        if ($dictionary === false || empty($dictionary) || $data === false) {
            $this->response->error($this->lang->get('WRONG_REQUEST'));
            return;
        }

        $data = json_decode($data, true);

        try {
            $this->manager->updateRecords($dictionary, $data);
            $this->compileLangAction();
        } catch (\Exception $e) {
            $this->response->error($e->getMessage());
            return;
        }
    }

    /**
     * Create sub dictionary
     */
    public function createSubAction()
    {
        $name = $this->request->post('name', Filter::FILTER_ALPHANUM, false);

        if (empty($name)) {
            $this->response->error($this->lang->get('INVALID_VALUE_FOR_FIELD') . ' ' . $this->lang->get('DICTIONARY_NAME'));
            return;
        }

        if ($this->manager->dictionaryExists($name)) {
            $this->response->error($this->lang->get('DICTIONARY_EXISTS'));
            return;
        }

        try {
            $this->manager->createDictionary($name);
        } catch (\Exception $e) {
            $this->response->error($e->getMessage());
            return;
        }
        $this->response->success();
    }

    /**
     * Rebuild lang files
     */
    public function compileLangAction()
    {
        if (!$this->checkCanEdit()) {
            return;
        }

        $langManager = new Manager($this->appConfig);
        try {
            $langManager->compileLangFiles();
            $this->response->success();
        } catch (\Exception $e) {
            $this->response->error($e->getMessage());
        }
    }
}
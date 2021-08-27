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

namespace Dvelum\Externals\Client;

use Composer\Console\Application;
use Dvelum\Externals\ClientInterface;
use Dvelum\File;
use Dvelum\Lang\Dictionary as Lang;
use Dvelum\Config\ConfigInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;

/**
 * Add-ons repository client
 */
class Composer implements ClientInterface
{
    /**
     * @var ConfigInterface
     */
    protected $config;
    /**
     * @var string
     */
    protected $language = 'en';
    /**
     * @var Lang
     */
    protected $lang;
    /**
     * @var Lang
     */
    protected $appLang;
    /**
     * @var string $tmpDir
     */
    protected $tmpDir;

    protected $actions = [
        'list' => 'https://packagist.org/packages/list.json',
        'info' => 'https://repo.packagist.org/packages/'
    ];

    public function __construct(ConfigInterface $repoConfig)
    {
        $this->config = $repoConfig;
        $this->appLang = \Dvelum\Lang::lang();
    }

    /**
     * Set tmp dir for downloads
     * @param string $dir
     */
    public function setTmpDir(string $dir): void
    {
        $this->tmpDir = $dir;
    }

    /**
     * Set client language
     * @param $lang
     */
    public function setLanguage(string $lang): void
    {
        $this->language = $lang;
    }

    /**
     * set localization dictionary
     * @param Lang $lang
     */
    public function setLocalization(Lang $lang): void
    {
        $this->lang = $lang;
    }

    /**
     * Get add-ons list
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function getList(array $params): array
    {
        $requestUrl = $this->actions['list'] . '?vendor=' . $this->config->get('vendor');

        try {
            $data = json_decode(file_get_contents($requestUrl), true);
        } catch (\Throwable $e) {
            return [];
        }

        if (!isset($data['packageNames'])) {
            return [];
        }

        $result = [];
        foreach ($data['packageNames'] as $pack) {
            $itemUrl = $this->actions['info'] . $pack . '.json';
            try {
                $info = json_decode(file_get_contents($itemUrl), true);
            } catch (\Throwable $e) {
                return [];
            }
            if (!isset($info['package']) || $info['package']['type'] != 'dvelum3-module') {
                continue;
            }
            $info = $info['package'];

            $lastVersion = '';
            $date = '';
            arsort($info['versions']);
            foreach ($info['versions'] as $number => $item) {
                if (strpos($number, 'dev') === false) {
                    $lastVersion = $number;
                    $date = date('Y-m-d H:i:s', strtotime($item['time']));
                    break;
                }
            }

            $result[] = [
                'code' => $pack,
                'title' => $info['description'],
                'downloads' => $info['downloads']['total'],
                'number' => $lastVersion,
                'date' => $date
            ];
        }

        return $result;
    }

    /**
     * Get dist file download url
     * @param string $package
     * @param string $version
     * @return string|null
     */
    protected function getDownloadUrl(string $package, string $version): ?string
    {
        $itemUrl = $this->actions['info'] . $package . '.json';
        try {
            $info = json_decode(file_get_contents($itemUrl), true);
        } catch (\Throwable $e) {
            return null;
        }

        if (!isset($info['package']) || $info['package']['type'] != 'dvelum-module' || !isset($info['package']['versions'][$version])) {
            return null;
        }

        //return $info['package']['versions'][$version]['dist']['url'];
        // https://github.com/dvelum/module-articles/archive/2.0.3.zip
        return $info['package']['repository'] . '/archive/' . $version . '.zip';
    }

    private function initComposer()
    {
        set_time_limit(0);
        putenv('COMPOSER_HOME=./temp/.composer');
    }

    /**
     * Download add-on
     * @param string $app
     * @param string $version
     * @return bool
     * @throws \Exception
     */
    public function download(string $app): bool
    {
        $this->initComposer();

        $stream = fopen('php://temp', 'w+');
        $output = new StreamOutput($stream);
        $application = new Application();
        $application->setAutoExit(false);
        $code = $application->run(new ArrayInput(['command' => 'require', 'packages' => [$app]]), $output);
        $res = stream_get_contents($stream);

        if ($code !== 0) {
            throw new \Exception('Cant download package. ' . $app . ' ' . $res);
        }
        return true;
    }

    /**
     * @param string $app
     * @return bool
     */
    public function remove(string $app): bool
    {
        $this->initComposer();

        $stream = fopen('php://temp', 'w+');
        $output = new StreamOutput($stream);
        $application = new Application();
        $application->setAutoExit(false);
        $code = $application->run(new ArrayInput(['command' => 'remove', 'packages' => [$app]]), $output);
        $res = stream_get_contents($stream);

        if ($code !== 0) {
            throw new \Exception('Cant remove composer package. ' . $app . ' ' . $res);
        }
        return true;
    }
}
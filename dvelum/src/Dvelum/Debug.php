<?php
/*
 * DVelum project https://github.com/dvelum/dvelum , http://dvelum.net Copyright
 * (C) 2011-2012 Kirill A Egorov This program is free software: you can
 * redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version. This program is distributed
 * in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details. You should have received
 * a copy of the GNU General Public License along with this program. If not, see
 * <http://www.gnu.org/licenses/>.
 */
declare(strict_types=1);

namespace Dvelum;

class Debug
{
    /**
     * Script startup time
     * @var float
     */
    protected static $scriptStartTime = false;
    /**
     * Database profiler
     * @var array $dbProfilers
     */
    static protected $dbProfilers = [];
    static protected $timers = [];
    static protected $loadedClasses = [];
    static protected $loadedConfigs = [];
    static protected $cacheCores = [];

    static public function setCacheCores(array $data)
    {
        self::$cacheCores = $data;
    }

    static public function setScriptStartTime($time)
    {
        self::$scriptStartTime = $time;
    }

    static public function addDbProfiler(\Zend\Db\Adapter\Profiler\ProfilerInterface $profiler)
    {
        self::$dbProfilers[] = $profiler;
    }

    static public function setLoadedClasses(array $data)
    {
        self::$loadedClasses = $data;
    }

    static public function setLoadedConfigs(array $data)
    {
        self::$loadedConfigs = $data;
    }

    /**
     * Get debug information
     * @param array $options
     * @return string  - html formated results
     */
    static public function getStats(array $options)
    {
        $options = array_merge(
            array(
                'cache' => true,
                'sql' => false,
                'autoloader' => false,
                'configs' => false,
                'includes' => false
            ),
            $options
        );

        $str = '';

        if (self::$scriptStartTime) {
            $str .= '<b>Time:</b> ' . number_format((microtime(true) - self::$scriptStartTime), 5) . "sec.<br>\n";
        }

        $str .= '<b>Memory:</b> ' . number_format((memory_get_usage() / (1024 * 1024)), 3) . "mb<br>\n"
            . '<b>Memory peak:</b> ' . number_format((memory_get_peak_usage() / (1024 * 1024)), 3) . "mb<br>\n"
            . '<b>Includes:</b> ' . count(get_included_files()) . "<br>\n"
            . '<b>Autoloaded:</b> ' . count(self::$loadedClasses) . "<br>\n"
            . '<b>Config files:</b> ' . count(self::$loadedConfigs) . "<br>\n";

        if (!empty(self::$dbProfilers)) {
            $str .= self::getQueryProfiles($options);
        }


        if ($options['configs']) {
            $str .= "<b>Configs (" . count(self::$loadedConfigs) . "):</b>\n<br> " . implode("\n\t <br>",
                    self::$loadedConfigs) . '<br>';
        }

        if ($options['autoloader']) {
            $str .= "<b>Autoloaded (" . count(self::$loadedClasses) . "):</b>\n<br> " . implode("\n\t <br>",
                    self::$loadedClasses) . '<br>';
        }

        if ($options['includes']) {
            $str .= "<b>Includes (" . count(get_included_files()) . "):</b>\n<br> " . implode("\n\t <br>",
                    get_included_files());
        }


        if (!empty(self::$cacheCores) && self::$cacheCores && $options['cache']) {
            $body = '';
            $globalCount = array('load' => 0, 'save' => 0, 'remove' => 0, 'total' => 0);
            $globalTotal = 0;

            foreach (self::$cacheCores as $name => $cacheCore) {
                if (!$cacheCore) {
                    continue;
                }

                $count = $cacheCore->getOperationsStat();

                $count['total'] = $count['load'] + $count['save'] + $count['remove'];

                $globalCount['load'] += $count['load'];
                $globalCount['save'] += $count['save'];
                $globalCount['remove'] += $count['remove'];
                $globalCount['total'] += $count['total'];

                $body .= '
                    <tr align="right">
                        <td align="left" >' . $name . '</td>
                        <td>' . $count['load'] . '</td>
                        <td>' . $count['save'] . '</td>
                        <td>' . $count['remove'] . '</td>
                        <td style="border-left:2px solid #000000;">' . $count['total'] . '</td>
                    </tr>';

            }

            $body .= '
                    <tr align="right" style="border-top:2px solid #000000;">
                        <td align="left" >Total</td>
                        <td>' . $globalCount['load'] . '</td>
                        <td>' . $globalCount['save'] . '</td>
                        <td>' . $globalCount['remove'] . '</td>
                        <td style="border-left:2px solid #000000;">' . $globalCount['total'] . '</td>
                    </tr>';

            $str .= '<div style=" padding:1px;"> <center><b>Cache</b></center>
                <table cellpadding="2" cellspacing="2" border="1" style="font-size:10px;">
                    <tr style="background-color:#cccccc;font-weight:bold;">
                        <td>Name</td>
                        <td>Load</td>
                        <td>Save</td>
                        <td>Remove</td>
                        <td style="border-left:2px solid #000000;">Total</td>
                    </tr>
                    ' . $body . '
                </table>
             </div>';
        }


        return '<div id="debugPanel" style="position:fixed;font-size:12px;left:10px;bottom:10px;overflow:auto;max-height:300px;padding:5px;background-color:#ffffff;z-index:1000;border:1px solid #cccccc;">' . $str . ' <center><a href="javascript:void(0)" onClick="document.getElementById(\'debugPanel\').style.display = \'none\'">close</a></center></div>';
    }

    /**
     * Start timer
     * @param string $name
     */
    static public function startTimer($name)
    {
        self::$timers[$name] = array(
            'start' => microtime(true),
            'stop' => 0
        );
    }

    /**
     * Stop timer
     * @param string $name
     * @return float time elapsed
     */
    static public function stopTimer($name)
    {
        if (!isset(self::$timers[$name])) {
            return 0;
        }

        self::$timers[$name]['stop'] = microtime(true);
        return self::$timers[$name]['stop'] - self::$timers[$name]['start'];
    }

    /**
     * Get time
     * @param string $timer
     * @return float time elapsed
     */
    static public function getTimerTime($timer)
    {
        if (!isset(self::$timers[$timer])) {
            return 0;
        }

        if (!self::$timers[$timer]['stop']) {
            return self::stopTimer($timer);
        }

        self::$timers[$timer]['stop'] = microtime(true);
        return self::$timers[$timer]['stop'] - self::$timers[$timer]['start'];
    }

    static protected function getQueryProfiles($options)
    {
        $str = '';

        $totalCount = 0;
        $totalTime = 0;
        $profiles = [];

        foreach (self::$dbProfilers as $prof) {
            $totalCount += count($prof->getProfiles());
            //$totalTime += $prof->getTotalElapsedSecs();
            $prof = $prof->getProfiles();
            if (!empty($prof)) {
                foreach ($prof as $item) {
                    $profiles[] = $item;
                    $totalTime += $item['elapse'];
                }
            }
        }


        $str .= '<b>Queries:</b> ' . $totalCount . '<br>' . '<b>Queries time:</b> ' . number_format($totalTime,
                5) . 'sec.<br>';
        if ($options['sql']) {
            if (!empty($profiles)) {
                foreach ($profiles as $queryProfile) {
                    $str .= '<span style="color:blue;font-size: 11px;">' . number_format($queryProfile['elapse'],
                            5) . 's. </span><span style="font-size: 11px;color:green;">' . $queryProfile['sql'] . "</span><br>\n";
                }
            }
        }
        $str .= "<br>\n";

        return $str;
    }

}
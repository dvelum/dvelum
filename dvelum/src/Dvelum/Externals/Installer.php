<?php

namespace Dvelum\Externals;

use Dvelum\Config\ConfigInterface;

abstract class Installer
{
    protected $errors = [];
    /**
     * Run module post-install actions
     * @param ConfigInterface $applicationConfig
     * @param ConfigInterface $moduleConfig
     * @return bool - success flag
     */
    abstract public function install(ConfigInterface $applicationConfig, ConfigInterface $moduleConfig);

    /**
     * Run module post-uninstall actions
     * @param ConfigInterface $applicationConfig
     * @param ConfigInterface $moduleConfig
     * @return bool - success flag
     */
    abstract public function uninstall(ConfigInterface $applicationConfig, ConfigInterface $moduleConfig);

    /**
     * Get installation errors
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
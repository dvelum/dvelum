<?php
if(!defined('DVELUM'))exit;

echo $this->renderTemplate('system/common/layout.php', $this->getData(), $this->useCache);
<?php

namespace Dvelum\App\Module;

class Permissions
{
    public $view = false;
    public $edit = false;
    public $publish = false;
    public $delete = false;
    // ORm compatible field name
    public $only_own = false;
    public $module = '';

    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            foreach ($data as $k => $v) {
                $this->{$k} = $v;
            }
        }
    }

    /**
     * @return bool
     */
    public function canView(): bool
    {
        return $this->view;
    }

    /**
     * @return bool
     */
    public function canEdit(): bool
    {
        return $this->edit;
    }

    /**
     * @return bool
     */
    public function canPublish(): bool
    {
        return $this->publish;
    }

    /**
     * @return bool
     */
    public function isOnlyOwn(): bool
    {
        return $this->only_own;
    }

    /**
     * @return string
     */
    public function getModule(): string
    {
        return $this->module;
    }
}

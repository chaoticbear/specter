<?php
namespace Specter;

class App
{
    protected $settings = [];

    public function __construct(array $settings = [])
    {
        $this->settings = $settings;
        $this->defaults();
    }

    public function defaults()
    {
        if (!isset($this->settings['appPath'])) {
            $this->settings['appPath'] = '../app/';
        }
        if (!isset($this->settings['viewPath'])) {
            $this->settings['viewPath'] = '../views/';
        }
        if (!isset($this->settings['webBase'])) {
            $this->settings['webBase'] = '/';
        }
    }

    public function get($name)
    {
        if (isset($this->settings[$name])) {
            return $this->settings[$name];
        } else {
            return null;
        }
    }

    public function set($name, $value)
    {
        $this->settings[$name] = $value;
    }
}

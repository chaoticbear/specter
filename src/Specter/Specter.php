<?php
namespace Specter;

class Specter
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
        if (!isset($this->settings['dbs'])) {
            $this->settings['dbs'] = [
                'db' => [
                    'dsn' => 'mysql:host=localhost;dbname=specter',
                    'user' => 'specter',
                    'pass' => 'haunt',
                ]
            ];
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

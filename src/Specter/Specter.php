<?php
namespace Specter;

use Dotenv\Dotenv;
use Specter\View;
use Specter\DB;

class Specter
{
    protected $settings = [];
    protected $db;

    public function __construct(array $settings = [])
    {
        //TODO cache settings.
        if (!empty($settings)) {
            $this->settings = $settings;
        } else {
            $configPaths = [
                './',
                '../',
            ];
            foreach ($configPaths as $path) {
                $file = $path . 'config.php';
                if (is_file($file)) {
                    $dotenv = new Dotenv($path);
                    $dotenv->overload();
                    $this->settings = include $file;
                    $this->settings['configFile'] = $file;
                    $dotenv = null;
                    break;
                }
            }
        }
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

    protected function routes()
    {
        $paths = [
            './',
            '../',
        ];
        foreach ($paths as $path) {
            $file = $path . 'routes.php';
            if (is_file($file)) {
                return include $file;
            }
        }

    }

    protected function page404()
    {
        $this->errorPage('404');
    }

    protected function page405()
    {
        $this->errorPage('405');
    }

    private function errorPage($type = '404')
    {
        http_response_code($type);
        $view = new View($this);
        die($view->read($type.'.php'));
    }

    protected function route()
    {
        //TODO cache this.
        $dispatcher = FastRoute\simpleDispatcher($this->routes());

        // Fetch method and URI from somewhere
        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];

        // Strip query string (?foo=bar) and decode URI
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);

        $routeInfo = $dispatcher->dispatch($httpMethod, $uri);
        switch ($routeInfo[0]) {
        case FastRoute\Dispatcher::NOT_FOUND:
            $this->page404();
            break;
        case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
            $this->page405();
            break;
        case FastRoute\Dispatcher::FOUND:
            $handler = $routeInfo[1];
            $params = $routeInfo[2];
            $parts = explode('@', $handler);
            if(
                isset($parts[0])
                && isset($parts[1])
                && class_exists('\\App\\Controllers\\' . $parts[0])
                && method_exists('\\App\\Controllers\\' . $parts[0], $parts[1])
            ) {
                $class = '\\App\\Controllers\\' . $parts[0];
                $method = $parts[1];
                $controller = new $class($this, $params);
                echo $controller->$method();
            } else {
                $this->page404();
            }
            break;
        }
    }

    public function haunt()
    {
        session_start();
        $this->db = new DB($this);
        $this->route();
        /*
        set_exception_handler('uncaught_exception_handler');
        function uncaught_exception_handler($e) {
          ob_end_clean(); //dump out remaining buffered text
          $vars['message']=$e;
          die(View::do_fetch(APP_PATH.'errors/exception_uncaught.php',$vars));
        }
        function custom_error($msg='') {
          $vars['msg']=$msg;
          die(View::do_fetch(APP_PATH.'errors/custom_error.php',$vars));
        }
         */
    }
}

<?php
namespace Specter;

use Dotenv\Dotenv;
use Specter\View;
use Specter\DB;

class Specter
{
    protected $settings = [];

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
        if (!isset($this->settings['configPath'])) {
            $this->settings['configPath'] = '../config.php';
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

    public function haunt()
    {
        session_start();
        $db = new DB($this);
        $pdo = $db->pdo();
        foreach($pdo->query('SELECT * FROM dataSrc') as $row) {
            print_r($row);
        }

        $dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
            $r->addRoute('GET', '/', 'Main@index');
        });

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
                http_response_code(404);
                $view = new View($this);
                echo $view->read('404.php');
                break;
            case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $routeInfo[1];
                http_response_code(405);
                $view = new View($this);
                echo $view->read('405.php');
                break;
            case FastRoute\Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];
                $controller = new \App\Controllers\Main($this);
                echo $controller->index();
                break;
        }

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

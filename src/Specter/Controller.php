<?php
namespace Specter;

abstract class Controller
{
    protected $controllerPath='../app/controllers/';
    protected $webFolder='/';
    protected $requestUriParts=array();
    protected $controller;
    protected $action;
    protected $params=array();

    function __construct(
        $controllerPath,
        $webFolder,
        $defaultController,
        $defaultAction
    )
    {
        $this->controllerPath=$controllerPath;
        $this->webFolder=$webFolder;
        $this->controller=$defaultController;
        $this->action=$defaultAction;
        $this->explodeHttpRequest()->parseHttpRequest()->routeRequest();
    }

    function explodeHttpRequest()
    {
        $requri = $_SERVER['REQUEST_URI'];
        if (strpos($requri,$this->webFolder)===0) {
            $requri=substr($requri,strlen($this->webFolder));
        }
        $this->requestUriParts = $requri ? explode('/',$requri) : array();
        return $this;
    }

    function parseHttpRequest()
    {
        $this->params = array();
        $p = $this->requestUriParts;
        if (isset($p[0]) && $p[0] && $p[0][0]!='?') {
            $this->controller = $p[0];
        }
        if (isset($p[1]) && $p[1] && $p[1][0]!='?') {
            $this->action = $p[1];
        }
        if (isset($p[2])) {
            $this->params = array_slice($p,2);
        }
        return $this;
    }

    function routeRequest()
    {
        $controllerfile = $this->controllerPath . $this->controller . '/' .
            $this->action . '.php';
        if (
            !preg_match('#^[A-Za-z0-9_-]+$#', $this->controller)
            or !file_exists($controllerfile)
        ) {
            $this->requestNotFound('Controller file not found: ' .
                $controllerfile);
        }
        $function = '_' . $this->action;
        if (
            !preg_match('#^[A-Za-z_][A-Za-z0-9_-]*$#', $function)
            or function_exists($function)
        ) {
            $this->requestNotFound('Invalid function name: '.$function);
        }
        require($controllerfile);
        if (!function_exists($function)) {
            $this->requestNotFound('Function not found: '.$function);
        }
        call_user_func_array($function,$this->params);
        return $this;
    }

    function requestNotFound($msg='')
    {
        header("HTTP/1.0 404 Not Found");
        die(
            '<html><head><title>404 Not Found</title></head>' .
            '<body><h1>Not Found</h1><p>'.$msg.'</p>' .
            '<p>The requested URL was not found on this server.</p>' .
            '<p>Please go <a href="javascript: history.back(1)">back</a>' .
            ' and try again.</p></body></html>'
        );
    }
}

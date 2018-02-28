<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-02-28
 * Time: 下午 12:21
 */

namespace common\base;
use Phalcon\Di;
use Phalcon\Loader,
    Phalcon\Di\FactoryDefault,
    Phalcon\Mvc\View\Engine\Volt,
    Phalcon\Mvc\View;



class Web extends Application
{
    public static function run(string $publicDir, $arg = []){
        try {
            parent::init($publicDir);
            $di = self::di();
            /** 启动palcon Mvc 应用 */
            $phalconApp = new \Phalcon\Mvc\Application($di);
            $phalconApp->handle()->send();

        }catch (\Exception $e){
            echo $e->getMessage();
        }
    }

    /**
     * Phalcon core configuration
     * @return \Phalcon\Di
     */
    protected static function di():Di
    {
        // set controller dir
        $loader = new Loader();
        $loader->registerDirs([
            self::$dirs['controller']
        ]);
        $loader->register();


        $di = new FactoryDefault();

        self::setView($di);

        // load app router configuration
        self::setRouter($di);


        return $di;
    }

    private static function setView(Di &$di){
        $viewDir = self::$dirs['view']  ;

        // set view dir
        $view = new View();
        $view->setViewsDir($viewDir  );

        // set view engine
        $volt = new Volt( $view , $di);
        $voltDir = self::$dirs['volt'];
        if(!is_dir($voltDir)) {
            mkdir($voltDir, 0777,true);
        }
        // set volt cache dir
        $volt->setOptions([
            "compiledPath" => $voltDir .'/'
        ]);
        // set volt file suffix
        $view->registerEngines([
            ".volt" => $volt
        ]);
        $di->set("view", $view);
    }
    private static function setRouter(Di &$di){
        $appRouterPath = self::$dirs['app'] . '/Router.php';
        if(is_readable($appRouterPath)){
            $routerFunc =  self::$appName . '\\Router::setting';
            $routerFunc($di);
        }
    }
}
<?php
namespace common\base;

use Phalcon\Config\Adapter\Ini as ConfigIni,
    Phalcon\Di\FactoryDefault,
    Phalcon\Loader,
    Phalcon\Mvc\View\Engine\Volt,
    Phalcon\Mvc\View;
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-02-27
 * Time: 上午 10:35
 */
class Application
{
    /** @var  $config \Phalcon\Config\Adapter\Ini */
    private static $config;
    private static $dirs;
    private static $appName;

    public static function run(string $publicDir){
        self::setDirs($publicDir);
        try {
            self::loadConfig();
            $composerAutoloadPath = self::$dirs['vendor'] . '/autoload.php';
            if(is_readable($composerAutoloadPath)){
                require $composerAutoloadPath;
            }
            $di = self::di();
            /** 启动palcon Mvc 应用 */
            $phalconApp = new \Phalcon\Mvc\Application($di);
            $phalconApp->handle()->send();


        }catch (\Exception $e){
            echo $e->getMessage();
        }
    }

    /**
     * Set the basic path.
     * @param string $publicDir
     */
    private static function setDirs(string $publicDir) {
        $appDir     = dirname($publicDir);
        $appDirName = basename($appDir);
        $rootDir    = dirname($appDir);
        $outputDir  = $rootDir . '/output/' . $appDirName;

        self::$appName = $appDirName;

        $dirs = [
            'app'        => $appDir,
            'root'       => $rootDir,
            'public'     => $publicDir,
            'view'       => $appDir . '/view',
            'config'     => $appDir . '/config',
            'model'      => $appDir . '/model',
            'controller' => $appDir . '/controller',

            'output' => $outputDir ,
            'Cache'  => $outputDir . '/cache',
            'log'    => $outputDir . '/log',
            'volt'   => $outputDir . '/volt',

            'vendor' => $rootDir . '/vendor'
        ];
        self::$dirs = $dirs;
    }

    /**
     * Load configuration information.
     */
    private static function loadConfig (){
        $configDir      = self::$dirs['config'];
        $configPath     = $configDir . '/config.ini';
        $configDevPath  = $configDir . '/config.dev.ini';

        $config = new ConfigIni($configPath);
        if(is_readable($configDevPath)){
            $configDev = new ConfigIni($configDevPath);
            $config->merge($configDev);
        }
        self::$config = $config;
    }

    /**
     * Phalcon core configuration
     * @return \Phalcon\Di
     */
    private static function di(){
        // set controller dir
        $loader = new Loader();
        $loader->registerDirs([
            self::$dirs['controller']
        ]);
        $loader->register();


        $di = new FactoryDefault();

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

        // set db
        $config = self::$config->get('database')->toArray();
        $dbClass = 'Phalcon\Db\Adapter\Pdo\\' . $config['adapter'];
        unset($config['adapter']);
        $di->setShared('DB', new $dbClass($config));


        // load app router configuration
        $appRouterPath = self::$dirs['app'] . '/Router.php';
        if(is_readable($appRouterPath)){
            $routerFunc =  self::$appName . '\\Router::setting';
            $routerFunc($di);
        }

        return $di;
    }

    public static function getDirs():array             {return self::$dirs;   }
    public static function getConfig():ConfigIni       {return self::$config; }
    public static function getApplicationName():string {return self::$appName;}
}
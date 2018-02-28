<?php
namespace common\base;

use Phalcon\Config\Adapter\Ini as ConfigIni;
use Phalcon\Di;
use Phalcon\Di\FactoryDefault\Cli as CliDI;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-02-27
 * Time: 上午 10:35
 */
abstract class Application
{
    /** @var  $config \Phalcon\Config\Adapter\Ini */
    protected static $config;
    protected static $dirs;
    protected static $appName;

    protected static abstract function run(string $publicDirOrCliPath , $arg=[]);
    protected static abstract function di():Di;

    protected static function init(string $publicDirOrCliPath){
        self::setDirs($publicDirOrCliPath);
        self::loadConfig();
        $composerAutoloadPath = self::$dirs['vendor'] . '/autoload.php';
        if(is_readable($composerAutoloadPath)){
            require $composerAutoloadPath;
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
            'view'       => $appDir . '/view',
            'config'     => $appDir . '/config',
            'model'      => $appDir . '/model',
            'controller' => $appDir . '/controller',
            'task'       => $appDir . '/task',

            'public'     => $appDir . '/public',
            'stylesheets'=> $appDir . '/stylesheets',
            'javascripts'=> $appDir . '/javascripts',
            'images'     => $appDir . '/images',
            'fonts'      => $appDir . '/fonts',

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
     * set DB
     * @param Di $di
     */
    protected static function setDB(Di &$di){
        $config = self::$config->get('database')->toArray();
        $dbClass = 'Phalcon\Db\Adapter\Pdo\\' . $config['adapter'];
        unset($config['adapter']);
        $di->setShared('DB', new $dbClass($config));
    }

    public static function getDirs():array             {return self::$dirs;   }
    public static function getConfig():ConfigIni       {return self::$config; }
    public static function getApplicationName():string {return self::$appName;}
}
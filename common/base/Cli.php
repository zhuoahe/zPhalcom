<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-02-28
 * Time: 下午 12:17
 */

namespace common\base;
use Phalcon\Di;
use Phalcon\Di\FactoryDefault\Cli as CliDI;
use Phalcon\Cli\Console as ConsoleApp;
use Phalcon\Loader;

class Cli extends Application
{
    public static function run(string $cliPath, $args=[]){
        try {
            parent::init($cliPath);
            $di = self::di();
            /** 启动palcon Cli 应用 */
            $console = new ConsoleApp();

            $console->setDI($di);
            $arguments = [];

            foreach ($args as $k => $arg) {
                if ($k === 1) {
                    $arguments['task'] = $arg;
                } elseif ($k === 2) {
                    $arguments['action'] = $arg;
                } elseif ($k >= 3) {
                    $arguments['params'][] = $arg;
                }
            }

            try {
                // Handle incoming arguments
                $console->handle($arguments);
            } catch (\Phalcon\Exception $e) {
                // Do Phalcon related stuff here
                // ..
                fwrite(STDERR, $e->getMessage() . PHP_EOL);
                exit(1);
            } catch (\Throwable $throwable) {
                fwrite(STDERR, $throwable->getMessage() . PHP_EOL);
                exit(1);
            } catch (\Exception $exception) {
                fwrite(STDERR, $exception->getMessage() . PHP_EOL);
                exit(1);
            }

        }catch (\Exception $e){
            echo $e->getMessage();
        }
    }

    protected static function di():Di
    {
        $loader = new Loader();
        $loader->registerDirs([self::$dirs['task']]);
        $loader->register();
        $di = new CliDI();
        //self::setDB($di);
        return $di;
    }

}
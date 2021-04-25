<?php

declare(strict_types=1);

namespace Engelsystem\Helpers;

use Engelsystem\Config\Config;
use Engelsystem\Container\ServiceProvider;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\Dumper\ContextProvider\CliContextProvider;
use Symfony\Component\VarDumper\Dumper\ContextProvider\SourceContextProvider;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use Symfony\Component\VarDumper\Dumper\ServerDumper;
use Symfony\Component\VarDumper\VarDumper;

class DumpServerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $app = $this->app;

        /** @var Config $config */
        $config = $app->get('config');
        // setup var dump server to use for easier debugging
        $varDumpServerConfig = $config->get('var_dump_server');

        if (
            !$varDumpServerConfig['enable']
            || $config->get('environment') !== 'development'
            || !class_exists(ServerDumper::class)
        ) {
            return;
        }

        $dumper = new ServerDumper(
            'tcp://' . $varDumpServerConfig['host'] . ':' . $varDumpServerConfig['port'],
            in_array(PHP_SAPI, ['cli', 'phpdbg']) ? $app->get(CliDumper::class) : $app->get(HtmlDumper::class),
            [
                'cli'    => new CliContextProvider(),
                'source' => new SourceContextProvider(),
            ]
        );

        $cloner = $app->get(VarCloner::class);

        VarDumper::setHandler(
            // @codeCoverageIgnoreStart
            static function ($var) use ($cloner, $dumper) {
                $dumper->dump($cloner->cloneVar($var));
            }
            // @codeCoverageIgnoreEnd
        );
    }
}

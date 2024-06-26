<?php

declare(strict_types=1);

namespace App;

use Neu;
use Neu\Component\DependencyInjection\ContainerBuilder;
use Neu\Component\DependencyInjection\ContainerBuilderInterface;
use Neu\Component\DependencyInjection\Project;

use Revolt\EventLoop;
use function Neu\Framework\entrypoint;

require_once __DIR__ . '/../vendor/autoload.php';

define('AMP_DEBUG', true);
\putenv('AMP_DEBUG=1');
define('REVOLT_DRIVER_DEBUG_TRACE', true);
\putenv('REVOLT_DRIVER_DEBUG_TRACE=1');

entrypoint(static function(Project $project): ContainerBuilderInterface {
    $builder = ContainerBuilder::create($project);

    $builder->addExtensions([
        new Neu\Component\Configuration\DependencyInjection\ConfigurationExtension(),
        new Neu\Bridge\Monolog\DependencyInjection\MonologExtension(),
        new Neu\Component\Advisory\DependencyInjection\AdvisoryExtension(),
        new Neu\Component\Console\DependencyInjection\ConsoleExtension(),
        new Neu\Component\EventDispatcher\DependencyInjection\EventDispatcherExtension(),
        new Neu\Component\Cache\DependencyInjection\CacheExtension(),
        new Neu\Component\Broadcast\DependencyInjection\BroadcastExtension(),
        new Neu\Component\Http\Message\DependencyInjection\MessageExtension(),
        new Neu\Component\Http\Recovery\DependencyInjection\RecoveryExtension(),
        new Neu\Component\Http\Router\DependencyInjection\RouterExtension(),
        new Neu\Component\Http\Runtime\DependencyInjection\RuntimeExtension(),
        new Neu\Component\Http\Server\DependencyInjection\ServerExtension(),
        new Neu\Component\Http\Session\DependencyInjection\SessionExtension(),
        new Neu\Bridge\Twig\DependencyInjection\TwigExtension(),
        new Neu\Component\Database\DependencyInjection\DatabaseExtension(),
    ]);

    return $builder;
});


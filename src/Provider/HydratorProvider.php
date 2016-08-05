<?php

namespace Hydrator\Provider;

use Hydrator\Hydrator;
use Hydrator\HydratorEvents;
use Hydrator\HydratorItemMap;
use Hydrator\HydratorScheme;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Api\EventListenerProviderInterface;
use Silex\Application;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Yaml\Parser as YamlParser;

class HydratorProvider implements ServiceProviderInterface, EventListenerProviderInterface
{
    /**
     * @param Container $app
     */
    public function register(Container $app)
    {
        $app['hydrator.scheme'] = function () use ($app) {
            $scheme = new HydratorScheme();
            $app['dispatcher']->dispatch(HydratorEvents::INIT, new GenericEvent(function ($path, $filename) use ($scheme) {
                $locator = new FileLocator($path);
                $path = $locator->locate($filename);

                $config = (new YamlParser())->parse(file_get_contents($path));
                $scheme->addScheme($config);
            }));

            return $scheme;
        };

        $app['hydrator.item.map'] = $app->protect(function ($scheme) use ($app) {
            $map = HydratorItemMap::buildMap($app['hydrator.scheme'], $scheme);

            return array_map(function($item) {
                return $item->toArray();
            }, $map);
        });

        $app['hydrator.factory'] = $app->protect(function ($schemaName) use ($app) {
            $scheme = $app['hydrator.scheme']->getScheme($schemaName);
            $map = $app['hydrator.item.map']($scheme, $app['hydrator.scheme']);

            return (new Hydrator($map))->setApp($app);
        });
    }

    /**
     * @param Container                $app
     * @param EventDispatcherInterface $dispatcher
     */
    public function subscribe(Container $app, EventDispatcherInterface $dispatcher)
    {
        $app['dispatcher']->addListener(HydratorEvents::INIT, function (GenericEvent $event) {
            $registerScheme = $event->getSubject();
            $registerScheme(__DIR__ . '/../Resources/config/', 'scheme.yml');
        });
    }
}
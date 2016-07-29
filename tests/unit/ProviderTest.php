<?php

namespace Hydrator;

use Hydrator\Provider\HydratorProvider;
use Monolog\Logger;
use Silex\Application;
use Silex\Provider\MonologServiceProvider;
use Silex\WebTestCase;
use Symfony\Component\Debug\ErrorHandler;
use Symfony\Component\Debug\ExceptionHandler;

class ProviderTest extends WebTestCase
{
    protected $expectedScheme = [
        'id' => [
            'Hydrator\Strategy\EntityIdStrategy' => '_id'
        ],
        'groupId' => 'group_id',
        'externalId' => 'external_id',
        'booleanType' => [
            'Hydrator\Strategy\BooleanStrategy' => 'boolean_type'
        ],
        'datetime' => [
            'Hydrator\Strategy\DateTimeStrategy' => 'datetime'
        ],
        'floatType' => [
            'Hydrator\Strategy\FloatStrategy' => 'float_type'
        ],
        'numberType' => [
            'Hydrator\Strategy\IntegerStrategy' => 'number_type'
        ],
        'methodType' => 'callMe',
        'objectType' => 'object_type',
        'sub' => [
            '~subScheme' => 'inner'
        ],
        'subArray' => [
            '~subScheme[]' => 'innerArray'
        ]
    ];

    protected $expectedSubScheme = [
        'Telephone' => 'tel'
    ];

    /**
     * @return Application
     */
    public function createApplication()
    {
        $this->app = new Application();

        ErrorHandler::register();
        ExceptionHandler::register(true);

        $this->app->register(new MonologServiceProvider(), [
            'monolog.name' => 'APP',
            'monolog.logfile' => __DIR__ . '/../../runtime/logs/error.log',
            'monolog.level' => Logger::INFO
        ]);
        $this->app->register(new HydratorProvider());
        $this->app->boot();
        return $this->app;
    }

    public function testHydratorScheme()
    {
        /** @var HydratorScheme $scheme */
        $scheme = $this->app['hydrator.scheme'];
        $this->assertInstanceOf(HydratorScheme::class, $scheme);

        $result = $scheme->getScheme('test');
        $this->assertEquals([
            'scheme' => $this->expectedScheme
        ], $result);

        $result = $scheme->getAllScheme();
        $this->assertEquals([
            'test' => [
                'scheme' => $this->expectedScheme
            ],
            'subScheme' => [
                'scheme' => $this->expectedSubScheme
            ]
        ], $result);
    }

    public function testHydrator()
    {
        /** @var Hydrator $hydrator */
        $hydrator = $this->app['hydrator.factory']('test');
        $this->assertInstanceOf(Hydrator::class, $hydrator);

        $this->assertInternalType('array', $hydrator->getScheme());
        $this->assertEquals($this->expectedScheme, $hydrator->getScheme());
    }
}

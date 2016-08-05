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

    public function testHydratorItemMap()
    {
        $scheme = $this->app['hydrator.scheme']->getScheme('test');
        $mapConfig = $this->app['hydrator.item.map']($scheme);

        $expected = [
            [
                'dirtyName' => 'id',
                'clearName' => '_id',
                'strategyClassName' => 'Hydrator\Strategy\EntityIdStrategy',
                'hasChildren' => false,
                'hasManyChildren' => false,
                'children' => false
            ],
            [
                'dirtyName' => 'groupId',
                'clearName' => 'group_id',
                'strategyClassName' => 'Hydrator\Strategy\DefaultStrategy',
                'hasChildren' => false,
                'hasManyChildren' => false,
                'children' => false
            ],
            [
                'dirtyName' => 'externalId',
                'clearName' => 'external_id',
                'strategyClassName' => 'Hydrator\Strategy\DefaultStrategy',
                'hasChildren' => false,
                'hasManyChildren' => false,
                'children' => false
            ],
            [
                'dirtyName' => 'booleanType',
                'clearName' => 'boolean_type',
                'strategyClassName' => 'Hydrator\Strategy\BooleanStrategy',
                'hasChildren' => false,
                'hasManyChildren' => false,
                'children' => false
            ],
            [
                'dirtyName' => 'datetime',
                'clearName' => 'datetime',
                'strategyClassName' => 'Hydrator\Strategy\DateTimeStrategy',
                'hasChildren' => false,
                'hasManyChildren' => false,
                'children' => false
            ],
            [
                'dirtyName' => 'floatType',
                'clearName' => 'float_type',
                'strategyClassName' => 'Hydrator\Strategy\FloatStrategy',
                'hasChildren' => false,
                'hasManyChildren' => false,
                'children' => false
            ],
            [
                'dirtyName' => 'numberType',
                'clearName' => 'number_type',
                'strategyClassName' => 'Hydrator\Strategy\IntegerStrategy',
                'hasChildren' => false,
                'hasManyChildren' => false,
                'children' => false
            ],
            [
                'dirtyName' => 'methodType',
                'clearName' => 'callMe',
                'strategyClassName' => 'Hydrator\Strategy\DefaultStrategy',
                'hasChildren' => false,
                'hasManyChildren' => false,
                'children' => false
            ],
            [
                'dirtyName' => 'objectType',
                'clearName' => 'object_type',
                'strategyClassName' => 'Hydrator\Strategy\DefaultStrategy',
                'hasChildren' => false,
                'hasManyChildren' => false,
                'children' => false
            ],
            [
                'dirtyName' => 'sub',
                'clearName' => 'inner',
                'strategyClassName' => false,
                'hasChildren' => true,
                'hasManyChildren' => false,
                'children' => [
                    [
                        'dirtyName' => 'Telephone',
                        'clearName' => 'tel',
                        'strategyClassName' => 'Hydrator\Strategy\DefaultStrategy',
                        'hasChildren' => false,
                        'hasManyChildren' => false,
                        'children' => false
                    ]
                ]
            ],
            [
                'dirtyName' => 'subArray',
                'clearName' => 'innerArray',
                'strategyClassName' => false,
                'hasChildren' => true,
                'hasManyChildren' => true,
                'children' => [
                    [
                        'dirtyName' => 'Telephone',
                        'clearName' => 'tel',
                        'strategyClassName' => 'Hydrator\Strategy\DefaultStrategy',
                        'hasChildren' => false,
                        'hasManyChildren' => false,
                        'children' => false
                    ]
                ]
            ]
        ];

        $this->assertEquals($expected, $mapConfig);
    }

    public function testHydrator()
    {
        /** @var Hydrator $hydrator */
        $hydrator = $this->app['hydrator.factory']('test');
        $this->assertInstanceOf(Hydrator::class, $hydrator);
    }
}

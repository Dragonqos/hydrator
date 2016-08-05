<?php

namespace Hydrator;

use Silex\Application;

class ExtractNamesTest extends ProviderTest
{
    public function testNameExtract()
    {
        /** @var Hydrator $hydrator */
        $hydrator = $this->app['hydrator.factory']('test');
        $result = $hydrator->extract([
            'group_id' => '123',
            'inner' => ['tel' => '123'],
            'innerArray' => [
                ['tel' => '123'],
                ['tel' => '123']
            ]
        ], ['groupId', 'sub', 'subArray']);
        $this->assertEquals(['groupId' => '123', 'sub' => ['Telephone' => 123], 'subArray' => [['Telephone' => 123], ['Telephone' => 123]]], $result);
    }

    public function testNameHydrate()
    {
        /** @var Hydrator $hydrator */
        $hydrator = $this->app['hydrator.factory']('test');
        $result = $hydrator->hydrate([
            'groupId' => '123',
            'sub' => ['Telephone' => '123'],
            'subArray' => [
                ['Telephone' => '123'],
                ['Telephone' => '123']
            ]
        ]);

        $this->assertEquals(['group_id' => '123', 'inner' => ['tel' => 123], 'innerArray' => [['tel' => 123], ['tel' => 123]]], $result);
    }
}

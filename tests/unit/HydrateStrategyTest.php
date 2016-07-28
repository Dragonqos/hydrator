<?php

namespace Hydrator;

use MongoDB\BSON\UTCDatetime;
use Silex\Application;

class HydrateStrategyTest extends ProviderTest
{
    public function testHydratorHydrate()
    {
        /** @var Hydrator $hydrator */
        $hydrator = $this->app['hydrator.factory']('test');
        $result = $hydrator->hydrate(['group_id' => '123', 'external_id' => 123, 'boolean_type' => 1], ['groupId', 'externalId', 'booleanType']);
        $this->assertEquals([
            'groupId' => '123',
            'externalId' => 123,
            'booleanType' => 1
        ], $result);
        $this->assertInternalType('string', $result['groupId']);
        $this->assertInternalType('integer', $result['externalId']);
        $this->assertInternalType('integer', $result['booleanType']);

        $result = $hydrator->hydrate(['group_id' => '123', 'external_id' => 123, 'boolean_type' => 1]);
        $this->assertEquals([
            'groupId' => '123',
            'externalId' => 123,
            'booleanType' => 1,
            'id' => null,
            'datetime' => null,
            'floatType' => null,
            'numberType' => null,
            'methodType' => null,
            'objectType' => null,
            'sub' => null,
            'subArray' => null
        ], $result);
    }

    public function testHydrateOne()
    {
        /** @var Hydrator $hydrator */
        $hydrator = $this->app['hydrator.factory']('test');

        list($key, $value) = $hydrator->hydrateOne(['number_type' => '123'], 'numberType');

        $this->assertEquals('numberType', $key);
        $this->assertEquals(123, $value);
    }

    public function testHydrateSub()
    {
        /** @var Hydrator $hydrator */
        $hydrator = $this->app['hydrator.factory']('test');
        $result = $hydrator->hydrate(['_id' => 123, 'inner' => ['tel' => '+380']], ['id', 'sub']);
        $this->assertEquals(['id' => 123, 'sub' => ['Telephone' => '+380']], $result);
    }

    public function testHydrateSubArray()
    {
        /** @var Hydrator $hydrator */
        $hydrator = $this->app['hydrator.factory']('test');

        $result = $hydrator->hydrate(['_id' => 123, 'innerArray' => [['tel' => '+7'], ['tel' => '+380']]], ['id', 'subArray']);
        $this->assertEquals(['id' => 123, 'subArray' => [['Telephone' => '+7'], ['Telephone' => '+380']]], $result);
    }

    public function testHydrateBooleanType()
    {
        /** @var Hydrator $hydrator */
        $hydrator = $this->app['hydrator.factory']('test');
        $result = $hydrator->hydrate(['boolean_type' => true], ['booleanType']);
        $this->assertEquals(['booleanType' => 1], $result);

        $result = $hydrator->hydrate(['boolean_type' => '1'], ['booleanType']);
        $this->assertEquals(['booleanType' => 1], $result);

        $result = $hydrator->hydrate(['boolean_type' => 2], ['booleanType']);
        $this->assertEquals(['booleanType' => 1], $result);

        $result = $hydrator->hydrate(['boolean_type' => 0], ['booleanType']);
        $this->assertEquals(['booleanType' => 0], $result);

        $result = $hydrator->hydrate(['boolean_type' => false], ['booleanType']);
        $this->assertEquals(['booleanType' => 0], $result);

        $result = $hydrator->hydrate(['boolean_type' => 'false'], ['booleanType']);
        $this->assertEquals(['booleanType' => 0], $result);
    }

    public function testHydrateDateTime()
    {
        $timeStamp  = 1469636398;
        $timeString = '2016-07-27 16:19:58';

        /** @var Hydrator $hydrator */
        $hydrator = $this->app['hydrator.factory']('test');

        $result = $hydrator->hydrate(['datetime' => \DateTime::createFromFormat('Y-m-d H:i:s', $timeString)]);
        $this->assertArrayHasKey('datetime', $result);
        $this->assertInternalType('int', $result['datetime']);
        $this->assertEquals($timeStamp, $result['datetime']);

        $result = $hydrator->hydrate(['datetime' => new UTCDatetime($timeStamp * 1000)]);
        $this->assertArrayHasKey('datetime', $result);
        $this->assertInternalType('int', $result['datetime']);
        $this->assertEquals($timeStamp, $result['datetime']);

        $result = $hydrator->hydrate(['datetime' => $timeStamp]);
        $this->assertArrayHasKey('datetime', $result);
        $this->assertInternalType('int', $result['datetime']);
        $this->assertEquals($timeStamp, $result['datetime']);

        $result = $hydrator->hydrate(['datetime' => $timeString]);
        $this->assertArrayHasKey('datetime', $result);
        $this->assertInternalType('int', $result['datetime']);
        $this->assertEquals($timeStamp, $result['datetime']);
    }

    public function testHydrateFloatType()
    {
        /** @var Hydrator $hydrator */
        $hydrator = $this->app['hydrator.factory']('test');

        $result = $hydrator->hydrate(['float_type' => 1]);
        $this->assertArrayHasKey('floatType', $result);
        $this->assertInternalType('float', $result['floatType']);
        $this->assertEquals(1.0, $result['floatType']);

        $result = $hydrator->hydrate(['float_type' => 2.1]);
        $this->assertArrayHasKey('floatType', $result);
        $this->assertInternalType('float', $result['floatType']);
        $this->assertEquals(2.1, $result['floatType']);

        $result = $hydrator->hydrate(['float_type' => '2.1']);
        $this->assertArrayHasKey('floatType', $result);
        $this->assertInternalType('float', $result['floatType']);
        $this->assertEquals(2.1, $result['floatType']);

        $result = $hydrator->hydrate(['float_type' => '2.1999999']);
        $this->assertArrayHasKey('floatType', $result);
        $this->assertInternalType('float', $result['floatType']);
        $this->assertEquals(2.1999999, $result['floatType']);

        $result = $hydrator->hydrate(['float_type' => 'test']);
        $this->assertArrayHasKey('floatType', $result);
        $this->assertInternalType('float', $result['floatType']);
        $this->assertEquals(0.0, $result['floatType']);
    }

    public function testHydrateEntityIdType()
    {
        /** @var Hydrator $hydrator */
        $hydrator = $this->app['hydrator.factory']('test');

        $result = $hydrator->hydrate(['_id' => 1]);
        $this->assertArrayHasKey('id', $result);
        $this->assertInternalType('integer', $result['id']);
        $this->assertEquals(1, $result['id']);

        $result = $hydrator->hydrate(['_id' => '1']);
        $this->assertArrayHasKey('id', $result);
        $this->assertInternalType('string', $result['id']);
        $this->assertEquals('1', $result['id']);

        $result = $hydrator->hydrate(['_id' => 'customid']);
        $this->assertArrayHasKey('id', $result);
        $this->assertInternalType('string', $result['id']);
        $this->assertEquals('customid', $result['id']);
    }

    public function testHydrateNumberType()
    {
        /** @var Hydrator $hydrator */
        $hydrator = $this->app['hydrator.factory']('test');

        $result = $hydrator->hydrate(['number_type' => 1]);
        $this->assertArrayHasKey('numberType', $result);
        $this->assertInternalType('integer', $result['numberType']);
        $this->assertEquals(1, $result['numberType']);

        $result = $hydrator->hydrate(['number_type' => '1']);
        $this->assertArrayHasKey('numberType', $result);
        $this->assertInternalType('integer', $result['numberType']);
        $this->assertEquals(1, $result['numberType']);

        $result = $hydrator->hydrate(['number_type' => '1.1']);
        $this->assertArrayHasKey('numberType', $result);
        $this->assertInternalType('integer', $result['numberType']);
        $this->assertEquals(1, $result['numberType']);

        $result = $hydrator->hydrate(['number_type' => '33x']);
        $this->assertArrayHasKey('numberType', $result);
        $this->assertInternalType('integer', $result['numberType']);
        $this->assertEquals(33, $result['numberType']);
    }

    public function testHydrateDefaultType()
    {
        /** @var Hydrator $hydrator */
        $hydrator = $this->app['hydrator.factory']('test');

        $result = $hydrator->hydrate(['object_type' => (object) ['key' => 'value']]);
        $this->assertInternalType('array', $result['objectType']);
        $this->assertEquals(['key' => 'value'], $result['objectType']);

        $result = $hydrator->hydrate(['object_type' => (array) ['key' => 'value']]);
        $this->assertInternalType('array', $result['objectType']);
        $this->assertEquals(['key' => 'value'], $result['objectType']);

        $result = $hydrator->hydrate(['object_type' => 'string']);
        $this->assertInternalType('string', $result['objectType']);
        $this->assertEquals('string', $result['objectType']);
    }

    public function testMethodStrategy()
    {
        /** @var Hydrator $hydrator */
        $hydrator = $this->app['hydrator.factory']('test');

        $result = $hydrator->hydrate(new HydrateObject());
        $this->assertInternalType('string', $result['methodType']);
        $this->assertEquals('success', $result['methodType']);
    }
}

class HydrateObject {

    public $_id = 1;

    /**
     * @return string
     */
    public function callMe()
    {
        return 'success';
    }
}

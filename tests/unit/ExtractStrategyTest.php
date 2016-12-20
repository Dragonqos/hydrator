<?php

namespace Hydrator;

use MongoDB\BSON\UTCDatetime;
use Silex\Application;

class ExtractStrategyTest extends ProviderTest
{
    public function testHydratorExtract()
    {
        /** @var Hydrator $hydrator */
        $hydrator = $this->app['hydrator.factory']->build('test');
        $result = $hydrator->extract(['group_id' => '123', 'external_id' => 123, 'boolean_type' => 1], ['groupId', 'externalId', 'booleanType']);
        $this->assertEquals([
            'groupId' => '123',
            'externalId' => 123,
            'booleanType' => 1
        ], $result);
        $this->assertInternalType('string', $result['groupId']);
        $this->assertInternalType('integer', $result['externalId']);
        $this->assertInternalType('integer', $result['booleanType']);

        $result = $hydrator->extract(['group_id' => '123', 'external_id' => 123, 'boolean_type' => 1]);

        $this->assertEquals([
            'groupId' => '123',
            'externalId' => 123,
            'booleanType' => 1,
            'id' => null,
            'datetime' => false,
            'floatType' => 0.0,
            'numberType' => 0,
            'methodType' => null,
            'objectType' => null,
            'sub' => null,
            'subArray' => []
        ], $result);
    }

    public function testExtractSub()
    {
        /** @var Hydrator $hydrator */
        $hydrator = $this->app['hydrator.factory']->build('test');
        $result = $hydrator->extract(['_id' => 123, 'inner' => ['tel' => '+380']], ['id', 'sub']);
        $this->assertEquals(['id' => 123, 'sub' => ['Telephone' => '+380']], $result);
    }

    public function testExtractSubArray()
    {
        /** @var Hydrator $hydrator */
        $hydrator = $this->app['hydrator.factory']->build('test');

        $result = $hydrator->extract(['_id' => 123, 'innerArray' => [['tel' => '+7'], ['tel' => '+380']]], ['id', 'subArray']);
        $this->assertEquals(['id' => 123, 'subArray' => [['Telephone' => '+7'], ['Telephone' => '+380']]], $result);
    }

    public function testExtractBooleanType()
    {
        /** @var Hydrator $hydrator */
        $hydrator = $this->app['hydrator.factory']->build('test');
        $result = $hydrator->extract(['boolean_type' => true], ['booleanType']);
        $this->assertEquals(['booleanType' => 1], $result);

        $result = $hydrator->extract(['boolean_type' => '1'], ['booleanType']);
        $this->assertEquals(['booleanType' => 1], $result);

        $result = $hydrator->extract(['boolean_type' => 2], ['booleanType']);
        $this->assertEquals(['booleanType' => 1], $result);

        $result = $hydrator->extract(['boolean_type' => 0], ['booleanType']);
        $this->assertEquals(['booleanType' => 0], $result);

        $result = $hydrator->extract(['boolean_type' => false], ['booleanType']);
        $this->assertEquals(['booleanType' => 0], $result);

        $result = $hydrator->extract(['boolean_type' => 'false'], ['booleanType']);
        $this->assertEquals(['booleanType' => 0], $result);
    }

    public function testExtractDateTime()
    {
        $timeStamp  = 1469636398;
        $timeString = '2016-07-27 16:19:58';

        /** @var Hydrator $hydrator */
        $hydrator = $this->app['hydrator.factory']->build('test');

        $result = $hydrator->extract(['datetime' => \DateTime::createFromFormat('Y-m-d H:i:s', $timeString)]);
        $this->assertArrayHasKey('datetime', $result);
        $this->assertInternalType('int', $result['datetime']);
        $this->assertEquals($timeStamp, $result['datetime']);

        $result = $hydrator->extract(['datetime' => new UTCDatetime($timeStamp * 1000)]);
        $this->assertArrayHasKey('datetime', $result);
        $this->assertInternalType('int', $result['datetime']);
        $this->assertEquals($timeStamp, $result['datetime']);

        $result = $hydrator->extract(['datetime' => $timeStamp]);
        $this->assertArrayHasKey('datetime', $result);
        $this->assertInternalType('int', $result['datetime']);
        $this->assertEquals($timeStamp, $result['datetime']);

        $result = $hydrator->extract(['datetime' => $timeString]);
        $this->assertArrayHasKey('datetime', $result);
        $this->assertInternalType('int', $result['datetime']);
        $this->assertEquals($timeStamp, $result['datetime']);
    }

    public function testExtractFloatType()
    {
        /** @var Hydrator $hydrator */
        $hydrator = $this->app['hydrator.factory']->build('test');

        $result = $hydrator->extract(['float_type' => 1]);
        $this->assertArrayHasKey('floatType', $result);
        $this->assertInternalType('float', $result['floatType']);
        $this->assertEquals(1.0, $result['floatType']);

        $result = $hydrator->extract(['float_type' => 2.1]);
        $this->assertArrayHasKey('floatType', $result);
        $this->assertInternalType('float', $result['floatType']);
        $this->assertEquals(2.1, $result['floatType']);

        $result = $hydrator->extract(['float_type' => '2.1']);
        $this->assertArrayHasKey('floatType', $result);
        $this->assertInternalType('float', $result['floatType']);
        $this->assertEquals(2.1, $result['floatType']);

        $result = $hydrator->extract(['float_type' => '2.1999999']);
        $this->assertArrayHasKey('floatType', $result);
        $this->assertInternalType('float', $result['floatType']);
        $this->assertEquals(2.1999999, $result['floatType']);

        $result = $hydrator->extract(['float_type' => 'test']);
        $this->assertArrayHasKey('floatType', $result);
        $this->assertInternalType('float', $result['floatType']);
        $this->assertEquals(0.0, $result['floatType']);
    }

    public function testExtractEntityIdType()
    {
        /** @var Hydrator $hydrator */
        $hydrator = $this->app['hydrator.factory']->build('test');

        $result = $hydrator->extract(['_id' => 1]);
        $this->assertArrayHasKey('id', $result);
        $this->assertInternalType('integer', $result['id']);
        $this->assertEquals(1, $result['id']);

        $result = $hydrator->extract(['_id' => '1']);
        $this->assertArrayHasKey('id', $result);
        $this->assertInternalType('string', $result['id']);
        $this->assertEquals('1', $result['id']);

        $result = $hydrator->extract(['_id' => 'customid']);
        $this->assertArrayHasKey('id', $result);
        $this->assertInternalType('string', $result['id']);
        $this->assertEquals('customid', $result['id']);
    }

    public function testExtractNumberType()
    {
        /** @var Hydrator $hydrator */
        $hydrator = $this->app['hydrator.factory']->build('test');

        $result = $hydrator->extract(['number_type' => 1]);
        $this->assertArrayHasKey('numberType', $result);
        $this->assertInternalType('integer', $result['numberType']);
        $this->assertEquals(1, $result['numberType']);

        $result = $hydrator->extract(['number_type' => '1']);
        $this->assertArrayHasKey('numberType', $result);
        $this->assertInternalType('integer', $result['numberType']);
        $this->assertEquals(1, $result['numberType']);

        $result = $hydrator->extract(['number_type' => '1.1']);
        $this->assertArrayHasKey('numberType', $result);
        $this->assertInternalType('integer', $result['numberType']);
        $this->assertEquals(1, $result['numberType']);

        $result = $hydrator->extract(['number_type' => '33x']);
        $this->assertArrayHasKey('numberType', $result);
        $this->assertInternalType('integer', $result['numberType']);
        $this->assertEquals(33, $result['numberType']);
    }

    public function testExtractDefaultType()
    {
        /** @var Hydrator $hydrator */
        $hydrator = $this->app['hydrator.factory']->build('test');

        $result = $hydrator->extract(['object_type' => (object) ['key' => 'value']]);
        $this->assertInternalType('array', $result['objectType']);
        $this->assertEquals(['key' => 'value'], $result['objectType']);

        $result = $hydrator->extract(['object_type' => (array) ['key' => 'value']]);
        $this->assertInternalType('array', $result['objectType']);
        $this->assertEquals(['key' => 'value'], $result['objectType']);

        $result = $hydrator->extract(['object_type' => 'string']);
        $this->assertInternalType('string', $result['objectType']);
        $this->assertEquals('string', $result['objectType']);
    }

    public function testMethodStrategy()
    {
        /** @var Hydrator $hydrator */
        $hydrator = $this->app['hydrator.factory']->build('test');

        $result = $hydrator->extract(new ExtractObject());
        $this->assertInternalType('string', $result['methodType']);
        $this->assertEquals('success', $result['methodType']);
    }

    public function testEmptyFieldsToReturn()
    {
        /** @var Hydrator $hydrator */
        $hydrator = $this->app['hydrator.factory']->build('test');
        $result = $hydrator->extract(['group_id' => '123', 'external_id' => 123, 'boolean_type' => 1], []);

        $this->assertEquals([
            'groupId' => '123',
            'externalId' => 123,
            'booleanType' => 1,
            'id' => null,
            'datetime' => false,
            'floatType' => 0.0,
            'numberType' => 0,
            'methodType' => null,
            'objectType' => null,
            'sub' => null,
            'subArray' => []
        ], $result);
    }

    public function testExtractValue()
    {
        $hydrator = $this->app['hydrator.factory']->build('test');
        $result = $hydrator->extractValue('datetime', new \DateTime());

        $this->assertInternalType('int', $result);

        $hydrator = $this->app['hydrator.factory']->build('test');
        $result = $hydrator->extractValue('inner.tel', '234');

        $this->assertInternalType('int', $result);
    }

    public function testDottedKeys()
    {
        $hydrator = $this->app['hydrator.factory']->build('test2');
        $result = $hydrator->extract([
            'one' => [
                'key' => 123
            ],
            'two' => [
                'key' => 123
            ],
            'three' => 'subschemeValue'
        ], []);

        $this->assertEquals([
            'groupId' => '123',
            'externalId' => 123,
            'sub' => [
                'subScheme' => 'subschemeValue'
            ]
        ], $result);

    }
}

class ExtractObject {

    public $_id = 1;

    /**
     * @return string
     */
    public function callMe()
    {
        return 'success';
    }
}

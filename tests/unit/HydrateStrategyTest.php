<?php

namespace Hydrator;

use Silex\Application;

class HydrateStrategyTest extends ProviderTest
{
    public function testHydratorHydrate()
    {
        /** @var Hydrator $hydrator */
        $hydrator = $this->app['hydrator.factory']->build('test');
        $result = $hydrator->hydrate(['groupId' => '123', 'externalId' => 123, 'booleanType' => 1]);
        $this->assertEquals(['group_id' => '123', 'external_id' => 123, 'boolean_type' => true], $result);
    }

    public function testHydrateToObject()
    {
        /** @var Hydrator $hydrator */
        $hydrator = $this->app['hydrator.factory']->build('test');

        $stdClass = new \stdClass();
        $result = $hydrator->hydrate(['groupId' => '123', 'externalId' => 123, 'booleanType' => 1], $stdClass);
        $this->assertEquals((object) ['group_id' => '123', 'external_id' => 123, 'boolean_type' => true], $result);

        $result = $hydrator->hydrate(['groupId' => '123', 'externalId' => 123, 'booleanType' => 1], 'stdClass');
        $this->assertEquals((object) ['group_id' => '123', 'external_id' => 123, 'boolean_type' => true], $result);
    }

    public function testHydrateSub()
    {
        /** @var Hydrator $hydrator */
        $hydrator = $this->app['hydrator.factory']->build('test');

        $result = $hydrator->hydrate(['id' => 123, 'sub' => ['Telephone' => '+380']]);
        $this->assertEquals(['_id' => 123, 'inner' => ['tel' => '+380']], $result);
    }

    public function testHydrateSubArray()
    {
        /** @var Hydrator $hydrator */
        $hydrator = $this->app['hydrator.factory']->build('test');

        $result = $hydrator->hydrate(['id' => 123, 'subArray' => [['Telephone' => '+7'], ['Telephone' => '+380']]]);
        $this->assertEquals(['_id' => 123, 'innerArray' => [['tel' => '+7'], ['tel' => '+380']]], $result);
    }

    public function testHydrateBooleanType()
    {
        /** @var Hydrator $hydrator */
        $hydrator = $this->app['hydrator.factory']->build('test');
        $result = $hydrator->hydrate(['booleanType' => 'true']);
        $this->assertEquals(['boolean_type' => true], $result);

        $result = $hydrator->hydrate(['booleanType' => 2]);
        $this->assertEquals(['boolean_type' => true], $result);

        $result = $hydrator->hydrate(['booleanType' => 'false']);
        $this->assertEquals(['boolean_type' => false], $result);

        $result = $hydrator->hydrate(['booleanType' => '0']);
        $this->assertEquals(['boolean_type' => false], $result);
    }

    public function testHydrateDateTime()
    {
        $timeStamp = 1469636398;
        $timeString = '2016-07-27 16:19:58';
        $dateString = '2016-07-27';

        /** @var Hydrator $hydrator */
        $hydrator = $this->app['hydrator.factory']->build('test');
        $result = $hydrator->hydrate(['datetime' => $timeStamp]);
        $this->assertArrayHasKey('datetime', $result);
        $this->assertInternalType('object', $result['datetime']);
        $this->assertInstanceOf(\DateTime::class, $result['datetime']);
        /** @var \DateTime $datetime */
        $datetime = $result['datetime'];
        $this->assertEquals($datetime->getTimestamp(), $timeStamp);


        $result = $hydrator->hydrate(['datetime' => $timeString]);
        $this->assertArrayHasKey('datetime', $result);
        $this->assertInternalType('object', $result['datetime']);
        $this->assertInstanceOf(\DateTime::class, $result['datetime']);
        /** @var \DateTime $datetime */
        $datetime = $result['datetime'];
        $this->assertEquals($datetime->getTimestamp(), $timeStamp);


        $result = $hydrator->hydrate(['datetime' => $dateString]);
        $this->assertArrayHasKey('datetime', $result);
        $this->assertInternalType('object', $result['datetime']);
        $this->assertInstanceOf(\DateTime::class, $result['datetime']);
        /** @var \DateTime $datetime */
        $datetime = $result['datetime'];
        $this->assertEquals($datetime->format('Y-m-d H:i:s'), '2016-07-27 00:00:00');
    }

    public function testHydrateFloatType()
    {
        /** @var Hydrator $hydrator */
        $hydrator = $this->app['hydrator.factory']->build('test');
        $result = $hydrator->hydrate(['floatType' => 1]);
        $this->assertEquals(['float_type' => 1.0], $result);
        $this->assertInternalType('float', $result['float_type']);

        $result = $hydrator->hydrate(['floatType' => 2.1]);
        $this->assertEquals(['float_type' => 2.1], $result);
        $this->assertInternalType('float', $result['float_type']);

        $result = $hydrator->hydrate(['floatType' => '2.1']);
        $this->assertEquals(['float_type' => 2.1], $result);
        $this->assertInternalType('float', $result['float_type']);

        $result = $hydrator->hydrate(['floatType' => '2.1999999']);
        $this->assertEquals(['float_type' => 2.1999999], $result);
        $this->assertInternalType('float', $result['float_type']);

        $result = $hydrator->hydrate(['floatType' => 'test']);
        $this->assertEquals(['float_type' => 0], $result);
        $this->assertInternalType('float', $result['float_type']);
    }

    public function testHydrateEntityIdType()
    {
        /** @var Hydrator $hydrator */
        $hydrator = $this->app['hydrator.factory']->build('test');
        $result = $hydrator->hydrate(['id' => 1]);
        $this->assertEquals(['_id' => 1], $result);
        $this->assertInternalType('integer', $result['_id']);

        $result = $hydrator->hydrate(['id' => '1']);
        $this->assertEquals(['_id' => '1'], $result);
        $this->assertInternalType('string', $result['_id']);

        $result = $hydrator->hydrate(['id' => 'customid']);
        $this->assertEquals(['_id' => 'customid'], $result);
        $this->assertInternalType('string', $result['_id']);
    }

    public function testHydrateNumberType()
    {
        /** @var Hydrator $hydrator */
        $hydrator = $this->app['hydrator.factory']->build('test');
        $result = $hydrator->hydrate(['numberType' => 1]);
        $this->assertEquals(['number_type' => 1], $result);
        $this->assertInternalType('integer', $result['number_type']);

        $result = $hydrator->hydrate(['numberType' => '1']);
        $this->assertEquals(['number_type' => 1], $result);
        $this->assertInternalType('integer', $result['number_type']);

        $result = $hydrator->hydrate(['numberType' => '1.1']);
        $this->assertEquals(['number_type' => 1], $result);
        $this->assertInternalType('integer', $result['number_type']);

        $result = $hydrator->hydrate(['numberType' => '33x']);
        $this->assertEquals(['number_type' => 33], $result);
        $this->assertInternalType('integer', $result['number_type']);
    }

    public function testHydrateMethodType()
    {
        /** @var Hydrator $hydrator */
        $hydrator = $this->app['hydrator.factory']->build('test');
        $result = $hydrator->hydrate(['methodType' => 'test']);
        $this->assertInternalType('string', $result['callMe']);
        $this->assertEquals('test', $result['callMe']);
    }

    public function testHydrateDefaultType()
    {
        /** @var Hydrator $hydrator */
        $hydrator = $this->app['hydrator.factory']->build('test');
        $result = $hydrator->hydrate(['objectType' => (object)['key' => 'value']]);
        $this->assertInternalType('object', $result['object_type']);
        $this->assertInstanceOf(\stdClass::class, $result['object_type']);
        $this->assertEquals('value', $result['object_type']->key);
    }

    public function testHydrateValue()
    {
        $hydrator = $this->app['hydrator.factory']->build('test');
        $result = $hydrator->hydrateValue('datetime', '1470930276');

        $this->assertInstanceOf(\DateTime::class, $result);

        $hydrator = $this->app['hydrator.factory']->build('test');
        $result = $hydrator->hydrateValue('sub.Telephone', '234');

        $this->assertInternalType('int', $result);
    }

    public function testDottedKeys()
    {
        $hydrator = $this->app['hydrator.factory']->build('test2');
        $result = $hydrator->hydrate([
            'groupId' => '123',
            'externalId' => 123,
            'sub' => [
                'subScheme' => 'subschemeValue'
            ]
        ], []);

        $this->assertEquals([
            'one' => [
                'key' => 123
            ],
            'two' => [
                'key' => 123
            ],
            'three' => 'subschemeValue'
        ], $result);
    }
}

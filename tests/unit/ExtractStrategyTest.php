<?php

namespace Hydrator;

use Silex\Application;

class ExtractStrategyTest extends ProviderTest
{
    public function testHydratorExtract()
    {
        /** @var Hydrator $hydrator */
        $hydrator = $this->app['hydrator.factory']('test');
        $result = $hydrator->extract(['groupId' => '123', 'externalId' => 123, 'booleanType' => 1]);
        $this->assertEquals(['group_id' => '123', 'external_id' => 123, 'boolean_type' => true], $result);
    }

    public function testExtractName()
    {
        /** @var Hydrator $hydrator */
        $hydrator = $this->app['hydrator.factory']('test');

        $key = $hydrator->extractName('numberType');

        $this->assertEquals('number_type', $key);
    }

    public function testExtractValue()
    {
        /** @var Hydrator $hydrator */
        $hydrator = $this->app['hydrator.factory']('test');

        $value = $hydrator->extractValue('numberType', '123');
        $this->assertEquals(123, $value);
    }

    public function testExtractSub()
    {
        /** @var Hydrator $hydrator */
        $hydrator = $this->app['hydrator.factory']('test');

        $result = $hydrator->extract(['id' => 123, 'sub' => ['Telephone' => '+380']]);
        $this->assertEquals(['_id' => 123, 'inner' => ['tel' => '+380']], $result);
    }

    public function testExtractSubArray()
    {
        /** @var Hydrator $hydrator */
        $hydrator = $this->app['hydrator.factory']('test');

        $result = $hydrator->extract(['id' => 123, 'subArray' => [['Telephone' => '+7'], ['Telephone' => '+380']]]);
        $this->assertEquals(['_id' => 123, 'innerArray' => [['tel' => '+7'], ['tel' => '+380']]], $result);
    }

    public function testExtractBooleanType()
    {
        /** @var Hydrator $hydrator */
        $hydrator = $this->app['hydrator.factory']('test');
        $result = $hydrator->extract(['booleanType' => 'true']);
        $this->assertEquals(['boolean_type' => true], $result);

        $result = $hydrator->extract(['booleanType' => 2]);
        $this->assertEquals(['boolean_type' => true], $result);

        $result = $hydrator->extract(['booleanType' => 'false']);
        $this->assertEquals(['boolean_type' => false], $result);

        $result = $hydrator->extract(['booleanType' => '0']);
        $this->assertEquals(['boolean_type' => false], $result);
    }

    public function testExtractDateTime()
    {
        $timeStamp  = 1469636398;
        $timeString = '2016-07-27 16:19:58';
        $dateString = '2016-07-27';

        /** @var Hydrator $hydrator */
        $hydrator = $this->app['hydrator.factory']('test');
        $result = $hydrator->extract(['datetime' => $timeStamp]);
        $this->assertArrayHasKey('datetime', $result);
        $this->assertInternalType('object', $result['datetime']);
        $this->assertInstanceOf(\DateTime::class, $result['datetime']);
        /** @var \DateTime $datetime */
        $datetime = $result['datetime'];
        $this->assertEquals($datetime->getTimestamp(), $timeStamp);


        $result = $hydrator->extract(['datetime' => $timeString]);
        $this->assertArrayHasKey('datetime', $result);
        $this->assertInternalType('object', $result['datetime']);
        $this->assertInstanceOf(\DateTime::class, $result['datetime']);
        /** @var \DateTime $datetime */
        $datetime = $result['datetime'];
        $this->assertEquals($datetime->getTimestamp(), $timeStamp);


        $result = $hydrator->extract(['datetime' => $dateString]);
        $this->assertArrayHasKey('datetime', $result);
        $this->assertInternalType('object', $result['datetime']);
        $this->assertInstanceOf(\DateTime::class, $result['datetime']);
        /** @var \DateTime $datetime */
        $datetime = $result['datetime'];
        $this->assertEquals($datetime->format('Y-m-d H:i:s'), '2016-07-27 00:00:00');
    }

    public function testExtractFloatType()
    {
        /** @var Hydrator $hydrator */
        $hydrator = $this->app['hydrator.factory']('test');
        $result = $hydrator->extract(['floatType' => 1]);
        $this->assertEquals(['float_type' => 1.0], $result);
        $this->assertInternalType('float', $result['float_type']);

        $result = $hydrator->extract(['floatType' => 2.1]);
        $this->assertEquals(['float_type' => 2.1], $result);
        $this->assertInternalType('float', $result['float_type']);

        $result = $hydrator->extract(['floatType' => '2.1']);
        $this->assertEquals(['float_type' => 2.1], $result);
        $this->assertInternalType('float', $result['float_type']);

        $result = $hydrator->extract(['floatType' => '2.1999999']);
        $this->assertEquals(['float_type' => 2.1999999], $result);
        $this->assertInternalType('float', $result['float_type']);

        $result = $hydrator->extract(['floatType' => 'test']);
        $this->assertEquals(['float_type' => 0], $result);
        $this->assertInternalType('float', $result['float_type']);
    }

    public function testExtractEntityIdType()
    {
        /** @var Hydrator $hydrator */
        $hydrator = $this->app['hydrator.factory']('test');
        $result = $hydrator->extract(['id' => 1]);
        $this->assertEquals(['_id' => 1], $result);
        $this->assertInternalType('integer', $result['_id']);

        $result = $hydrator->extract(['id' => '1']);
        $this->assertEquals(['_id' => '1'], $result);
        $this->assertInternalType('string', $result['_id']);

        $result = $hydrator->extract(['id' => 'customid']);
        $this->assertEquals(['_id' => 'customid'], $result);
        $this->assertInternalType('string', $result['_id']);
    }

    public function testExtractNumberType()
    {
        /** @var Hydrator $hydrator */
        $hydrator = $this->app['hydrator.factory']('test');
        $result = $hydrator->extract(['numberType' => 1]);
        $this->assertEquals(['number_type' => 1], $result);
        $this->assertInternalType('integer', $result['number_type']);

        $result = $hydrator->extract(['numberType' => '1']);
        $this->assertEquals(['number_type' => 1], $result);
        $this->assertInternalType('integer', $result['number_type']);

        $result = $hydrator->extract(['numberType' => '1.1']);
        $this->assertEquals(['number_type' => 1], $result);
        $this->assertInternalType('integer', $result['number_type']);

        $result = $hydrator->extract(['numberType' => '33x']);
        $this->assertEquals(['number_type' => 33], $result);
        $this->assertInternalType('integer', $result['number_type']);
    }

    public function testExtractMethodType()
    {
        /** @var Hydrator $hydrator */
        $hydrator = $this->app['hydrator.factory']('test');
        $result = $hydrator->extract(['methodType' => 'test']);
        $this->assertInternalType('string', $result['callMe']);
        $this->assertEquals('test', $result['callMe']);
    }

    public function testExtractDefaultType()
    {
        /** @var Hydrator $hydrator */
        $hydrator = $this->app['hydrator.factory']('test');
        $result = $hydrator->extract(['objectType' => (object) ['key' => 'value']]);
        $this->assertInternalType('object', $result['object_type']);
        $this->assertInstanceOf(\stdClass::class, $result['object_type']);
        $this->assertEquals('value', $result['object_type']->key);
    }
}

<?php

namespace Hydrator;

class DottedNamesTest extends ProviderTest
{
    public function testNameExtract()
    {
        /** @var Hydrator $hydrator */
        $hydrator = $this->app['hydrator.factory']->build('test');

        $name = $hydrator->extractName('inner');
        $this->assertEquals('sub', $name);

        $name = $hydrator->extractName('inner.tel');
        $this->assertEquals('sub.Telephone', $name);

        $name = $hydrator->extractName('innerArray.0.tel');
        $this->assertEquals('subArray.0.Telephone', $name);

        $name = $hydrator->extractName('innerArray.tel');
        $this->assertEquals('subArray.Telephone', $name);

        $name = $hydrator->extractName('innerArray.0.tels');
        $this->assertEquals(false, $name);
    }

    public function testNameHydrate()
    {
        /** @var Hydrator $hydrator */
        $hydrator = $this->app['hydrator.factory']->build('test');

        $name = $hydrator->hydrateName('sub');
        $this->assertEquals('inner', $name);

        $name = $hydrator->hydrateName('sub.Telephone');
        $this->assertEquals('inner.tel', $name);

        $name = $hydrator->hydrateName('subArray.0.Telephone');
        $this->assertEquals('innerArray.0.tel', $name);

        $name = $hydrator->hydrateName('subArray.Telephone');
        $this->assertEquals('innerArray.tel', $name);

        $name = $hydrator->hydrateName('subArray.0.Telephon');
        $this->assertEquals(false, $name);
    }
}

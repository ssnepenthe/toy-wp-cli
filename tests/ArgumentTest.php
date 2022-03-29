<?php

declare(strict_types=1);

namespace ApheleiaCli\Tests;

use ApheleiaCli\Argument;
use PHPUnit\Framework\TestCase;

class ArgumentTest extends TestCase
{
    public function testGetSynopsis()
    {
        $argument = new Argument('some-name');

        $this->assertSame([
            'type' => 'positional',
            'name' => 'some-name',
            'optional' => false,
            'repeating' => false,
        ], $argument->getSynopsis());
    }

    public function testGetSynopsisWithNonDefaultSettings()
    {
        $argument = new Argument('some-name');

        $argument->setDefault('Apple');
        $argument->setDescription('Just a fruit');
        $argument->setOptional(true);
        $argument->setOptions('one', 'two', 'three');
        $argument->setRepeating(true);

        $this->assertSame([
            'type' => 'positional',
            'name' => 'some-name',
            'optional' => true,
            'repeating' => true,
            'description' => 'Just a fruit',
            'default' => 'Apple',
            'options' => ['one', 'two', 'three'],
        ], $argument->getSynopsis());
    }
}

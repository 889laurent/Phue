<?php
/**
 * Phue: Philips Hue PHP Client
 *
 * @author    Michael Squires <sqmk@php.net>
 * @copyright Copyright (c) 2012 Michael K. Squires
 * @license   http://github.com/sqmk/Phue/wiki/License
 */

namespace Phue\Test;

use Phue\Client;
use Phue\Rule;

/**
 * Tests for Phue\Rule
 */
class RuleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Set up
     *
     * @covers \Phue\Rule::__construct
     */
    public function setUp()
    {
        // Mock client
        $this->mockClient = $this->getMock(
            '\Phue\Client',
            ['sendCommand'],
            ['127.0.0.1']
        );

        // Build stub attributes
        $this->attributes = (object) [
            'name'           => 'Wall Switch Rule',
            'lasttriggered'  => '2013-10-17T01:23:20',
            'creationtime'   => '2013-10-10T21:11:45',
            'timestriggered' => 27,
            'owner'          => '78H56B12BA',
            'status'         => 'enabled',
        ];

        // Create rule object
        $this->rule = new Rule(4, $this->attributes, $this->mockClient);
    }

    /**
     * Test: Getting Id
     *
     * @covers \Phue\Rule::getId
     */
    public function testGetId()
    {
        $this->assertEquals(
            4,
            $this->rule->getId()
        );
    }

    /**
     * Test: Getting name
     *
     * @covers \Phue\Rule::getName
     */
    public function testGetName()
    {
        $this->assertEquals(
            $this->attributes->name,
            $this->rule->getName()
        );
    }

    /**
     * Test: Getting last triggered time
     *
     * @covers \Phue\Rule::getLastTriggeredTime
     */
    public function testGetLastTriggeredTime()
    {
        $this->assertEquals(
            $this->attributes->lasttriggered,
            $this->rule->getLastTriggeredTime()
        );
    }

    /**
     * Test: Getting creation time
     *
     * @covers \Phue\Rule::getCreationTime
     */
    public function testGetCreationTime()
    {
        $this->assertEquals(
            $this->attributes->creationtime,
            $this->rule->getCreationTime()
        );
    }

    /**
     * Test: Getting triggered count
     *
     * @covers \Phue\Rule::getTriggeredCount
     */
    public function testGetTriggeredCount()
    {
        $this->assertEquals(
            $this->attributes->timestriggered,
            $this->rule->getTriggeredCount()
        );
    }

    /**
     * Test: Get owner
     *
     * @covers \Phue\Rule::getOwner
     */
    public function testGetOwner()
    {
        $this->assertEquals(
            $this->attributes->owner,
            $this->rule->getOwner()
        );
    }

    /**
     * Test: Is enabled?
     *
     * @covers \Phue\Rule::isEnabled
     */
    public function testIsEnabled()
    {
        return $this->assertTrue(
            $this->rule->isEnabled()
        );
    }

    /**
     * Test: toString
     *
     * @covers \Phue\Rule::__toString
     */
    public function testToString()
    {
        $this->assertEquals(
            $this->rule->getId(),
            (string) $this->rule
        );
    }
}

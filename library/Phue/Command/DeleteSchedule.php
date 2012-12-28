<?php
/**
 * Phue: Philips Hue PHP Client
 *
 * @author    Michael Squires <sqmk@php.net>
 * @copyright Copyright (c) 2012 Michael K. Squires
 * @license   http://github.com/sqmk/Phue/wiki/License
 * @package   Phue
 */

namespace Phue\Command;

use Phue\Client;
use Phue\Transport\TransportInterface;
use Phue\Command\CommandInterface;

/**
 * Delete schedule command
 *
 * @category Phue
 * @package  Phue
 */
class DeleteSchedule implements CommandInterface
{
    /**
     * Schedule Id
     * 
     * @var string
     */
    protected $scheduleId;

    /**
     * Constructs a command
     *
     * @param mixed $schedule Schedule Id or Schedule object
     */
    public function __construct($schedule)
    {
        $this->scheduleId = (string) $schedule;
    }

    /**
     * Send command
     *
     * @param Client $client Phue Client
     *
     * @return void
     */
    public function send(Client $client)
    {
        $client->getTransport()->sendRequest(
            "{$client->getUsername()}/schedules/{$this->scheduleId}",
            TransportInterface::METHOD_DELETE
        );
    }
}

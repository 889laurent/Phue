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
use Phue\Command\CommandInterface;
use Phue\Schedule;

/**
 * Get schedules command
 *
 * @category Phue
 * @package  Phue
 */
class GetSchedules implements CommandInterface
{
    /**
     * Send command
     *
     * @param Client $client Phue Client
     *
     * @return array List of Schedule objects
     */
    public function send(Client $client)
    {
        // Get response
        $response = $client->getTransport()->sendRequest(
            $client->getUsername()
        );

        // Return empty list if no schedules
        if (!isset($response->schedules)) {
            return [];
        }

        $schedules = [];

        foreach ($response->schedules as $scheduleId => $details) {
            $schedules[$scheduleId] = new Schedule($scheduleId, $details, $client);
        }

        return $schedules;
    }
}

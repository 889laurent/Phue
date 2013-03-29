<?php
/**
 * Phue: Philips Hue PHP Client
 *
 * @author    Michael Squires <sqmk@php.net>
 * @copyright Copyright (c) 2012 Michael K. Squires
 * @license   http://github.com/sqmk/Phue/wiki/License
 */

namespace Phue\Command;

use Phue\Client;
use Phue\Transport\TransportInterface;

/**
 * Authenticate command
 */
class Authenticate implements CommandInterface
{
    /**
     * Send command
     *
     * @param Client $client Phue Client
     *
     * @return stdClass Authentication response
     */
    public function send(Client $client)
    {
        // Get response
        $response = $client->getTransport()->sendRequest(
            '',
            TransportInterface::METHOD_POST,
            $this->buildRequestData($client)
        );

        return $response;
    }

    /**
     * Build request data
     *
     * @param Client $client Phue client
     *
     * @return stdClass Request data object
     */
    protected function buildRequestData(Client $client)
    {
        return (object) [
            'username'   => $client->getUsername(),
            'devicetype' => $client::CLIENT_NAME
        ];
    }
}

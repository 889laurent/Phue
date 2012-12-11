<?php
/**
 * Phue: Philips Hue PHP Client
 *
 * @author    Michael Squires <sqmk@php.net>
 * @copyright Copyright (c) 2012 Michael K. Squires
 * @license   http://github.com/sqmk/Phue/wiki/License
 * @package   Phue
 */

namespace Phue\Transport;

use Phue\Client;
use Phue\Command\CommandInterface;
use Phue\Transport\TransportInterface;

/**
 * Http transport
 *
 * @category Phue
 * @package  Phue
 */
class Http implements TransportInterface
{
    /**
     * Phue Client
     *
     * @var Client
     */
    protected $client = null;

    /**
     * Curl connection
     *
     * @var resource Curl resource
     */
    protected $connection = null;

    /**
     * Exception map
     *
     * @var array
     */
    protected static $exceptionMap = [
        0   => 'Phue\Transport\Exception\BridgeException',
        1   => 'Phue\Transport\Exception\AuthorizationException',
        2   => 'Phue\Transport\Exception\InvalidBodyException',
        3   => 'Phue\Transport\Exception\ResourceException',
        4   => 'Phue\Transport\Exception\MethodException',
        5   => 'Phue\Transport\Exception\InvalidParameterException',
        6   => 'Phue\Transport\Exception\ParameterUnavailableException',
        7   => 'Phue\Transport\Exception\InvalidValueException',
        101 => 'Phue\Transport\Exception\LinkButtonException',
        301 => 'Phue\Transport\Exception\GroupTableFullException',
        901 => 'Phue\Transport\Exception\ThrottleException',
    ];

    /**
     * Construct Http transport
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Open connection
     *
     * @return void
     */
    protected function open()
    {
        // Don't continue if connection already set
        if ($this->connection !== null) {
            return;
        }

        $this->connection = curl_init();
    }

    /**
     * Send request
     *
     * @param string   $path   API path
     * @param string   $method Request method
     * @param stdClass $data   Post data
     *
     * @return void
     */
    public function sendRequest($path, $method = self::METHOD_GET, \stdClass $data = null)
    {
        // Build base URL
        $url = 'http://' . $this->client->getHost() . '/api/';

        // Add path to base URL
        $url .= $path;

        // Initialize connection
        $this->open();

        // Set connection options
        curl_setopt($this->connection, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($this->connection, CURLOPT_URL, $url);
        curl_setopt($this->connection, CURLOPT_HEADER, false);
        curl_setopt($this->connection, CURLOPT_RETURNTRANSFER, true);

        if ($data) {
            curl_setopt($this->connection, CURLOPT_POSTFIELDS, json_encode($data));
        }

        // Get results and status
        $results     = curl_exec($this->connection);
        $status      = curl_getinfo($this->connection, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($this->connection, CURLINFO_CONTENT_TYPE);

        // Close connection
        $this->close();

        // Throw connection exception if status code isn't 200
        if ($status != 200 && $contentType != 'application/json') {
            throw new ConnectionException("Connection failure");
        }

        // Parse results into json
        $jsonResults = json_decode($results);

        // Get first element in array if it's an array response
        if (is_array($jsonResults)) {
            $jsonResults = $jsonResults[0];
        }

        // Get success object only if available
        if (isset($jsonResults->success)) {
            $jsonResults = $jsonResults->success;
        }

        // Get error type
        if (isset($jsonResults->error)) {
            $this->throwExceptionByType(
                $jsonResults->error->type,
                $jsonResults->error->description
            );
        }

        return $jsonResults;
    }

    /**
     * Close connection
     *
     * @return void
     */
    protected function close()
    {
        // Don't continue if no connection
        if ($this->connection === null) {
            return;
        }

        curl_close($this->connection);

        $this->connection = null;
    }

    /**
     * Throw exception by type
     *
     * @param string $type        Error type
     * @param string $description Description of error
     *
     * @return void
     */
    public function throwExceptionByType($type, $description)
    {
        // Determine exception
        $exceptionClass = isset(static::$exceptionMap[$type])
                        ? static::$exceptionMap[$type]
                        : static::$exceptionMap[0];

        throw new $exceptionClass($description, $type);
    }

    /**
     * Destruct Http transport
     *
     * @return void
     */
    public function __destruct()
    {
        $this->close();
    }
}

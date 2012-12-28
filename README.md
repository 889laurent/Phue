## Phue - Philips Hue PHP Client

Master: [![CI Status](https://secure.travis-ci.org/sqmk/Phue.png?branch=master)](http://travis-ci.org/sqmk/Phue)

Phue is a PHP 5.4 client used to connect to and manage the Philips Hue lighting system.

It is currently registered with Packagist so that this library can easily be included in other projects. For example, one may want to bundle this library with Zend Framework or Symfony to build their own front-end for the Hue system.

The client has the ability to make full use of the Hue's API, including:
* Authenticating
* Updating the bridge
* Finding new lights
* Getting and managing lights
* Creating, updating, and deleting groups
* Creating and deleting schedules

## Installing Phue

The Phue library is available in Packagist. You'll want to include ```sqmk/Phue``` as a dependency in your project using composer. If you are not familiar with composer, check it out here: [Composer](http://getcomposer.org)

You can also use this library without composer. The library directory is ```library```. You'll want to map your namespace ```Phue``` to this directory in your autoloader of choice.

The scripts in ```bin``` are dependent on composer's class/namespace mapper within ```vendor```. You'll need to ```composer install``` from root directory of this repo to get those working.

After all the packages are installed, include composer's generated autoloader. The autoloader is ```vendor/autoload.php```. An example of including this from the root directory of this repository:

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$client = new \Phue\Client('10.0.1.1', 'your.username');
```

## Using the client

For all examples, it is assumed that the autoloader is included somewhere in your PHP app.

To start, you'll need to instantiate a new Phue Client object. You'll need the IP of your bridge, and an authenticated key/username. If you don't know the IP of your bridge or haven't authenticated a user, you can use the helper scripts documented at the end of the README.

Here's how to instantiate a client object:

```php
<?php

$client = new \Phue\Client('10.0.1.31', 'sqmk');
```

### Testing connection and authorization

You can issue a Ping command to the bridge to test making a request to it. If a ConnectionException exception is thrown, then there is a problem with talking to the bridge:

```php
try {
	$client->sendCommand(
		new \Phue\Command\Ping
	);
} catch (\Phue\Transport\Exception\ConnectionException $e) {
	echo 'There was a problem accessing the bridge';
}
```

Once you have determined you can make requests to the bridge, you can test if the username you provided is authenticated.

```php
$isAuthenticated = $client->sendCommand(
	new \Phue\Command\IsAuthorized
);

echo $isAuthenticated
   ? 'You are authenticated!'
   : 'You are not authenticated!';
```

If the username provided is not authenticated, you can use the convenience script to authenticate, which is documented later in this README. Or, you can use the Authenticate command to do it yourself.

```php
// Push the bridge's link button prior to running this
try {
	$client->sendCommand(
		new \Phue\Command\Authenticate
	);

	echo "{$client->getUsername()} is now authenticated";
} catch (\Phue\Transport\Exception\LinkButtonException $e) {
	echo 'The link button was not pressed!';
}
```

After the user is authenticated, you won't have to authenticate again unless you reset the bridge!

## Managing lights

Now that you have an authorized user, you can start managing the lights with the client.

There are a couple of ways to retrieve the list of lights that are registered with the bridge. You can use the helper method available from the client, or by manually issuing a command to the client. These commands return an array of ```\Phue\Light``` objects:

```php
// From the client
foreach ($client->getLights() as $lightId => $light) {
	echo "Id #{$lightId} - {$light->getName()}", "\n";
}

// Or from command
$lights = $client->sendCommand(
	new \Phue\Command\GetLights
);

foreach ($lights as $lightId => $light) {
	echo "Id #{$lightId} - {$light->getName()}", "\n";
}
```

You can also retrieve a single light. You can either dereference from the list of lights via ```getLights``` from the client, or pass in a manual command to the client:

```php
// Retrieve light of id 3 from convenience method
$light = $client->getLights()[3];

echo $light->getName(), "\n";

// Manually send command to get light of id 3
$light = $client->sendCommand(
	new \Phue\Command\GetLightById(3)
);

echo $light->getName(), "\n";
```

Don't have any lights, or need to register a new bulb? The ```StartLightScan``` command and the ```GetNewLights``` command can be used to help registering new lights.  You can see how these commands are used by looking at the ```bin/phue-light-finder``` script, which is documented at the end of this README.

Now that you can retrieve ```\Phue\Light``` objects, you can start manipulating them with the client. Here are a few examples of how to show and change a light's properties:

```php
// Get a specific light
$light = $client->getLights()[3];

// Retrieving light properties:
echo $light->getId(), "\n",
     $light->getName(), "\n",
     $light->getType(), "\n",
     $light->getModelId(), "\n",
     $light->getSoftwareVersion(), "\n",
     $light->isOn(), "\n",
     $light->getAlert(), "\n",
     $light->getBrightness(), "\n",
     $light->getHue(), "\n",
     $light->getSaturation(), "\n",
     $light->getXY()['x'], "\n",
     $light->getXY()['y'], "\n",
     $light->getColorTemp(), "\n",
     $light->getColorMode(), "\n";

// Setting name
$light->setName('Living Room #1');

// Setting on/off state (true|false)
$light->setOn(true);

// Setting alert (select|lselect)
$light->setAlert('lselect');

// Setting brightness (0 for no light, 254 for max brightness)
$light->setBrightness(254);

// Set hue (0 to 65535), pairs with saturation, changes color mode to 'hs'
$light->setHue(56000);

// Set saturation (0 min, 255 max), pairs with hue, changes color mode to 'hs'
$light->setSaturation(255);

// Set xy, CIE 1931 color space (from 0.0 to 1.0 for both x and y)
// Changes color mode to 'xy'
$light->setXY(0.25, 0.5);

// Set color temp (154 min, 500 max), changes color mode to 'ct'
$light->setColorTemp(300);
```

Each *set* method above issues a single request to the bridge. In order to update multiple attributes of a light with a single request, the ```SetLightState``` command should be used manually. You also get access to the *transition time* option with the command.

```
// Retrieve light
$light = $client->getLights()[3];

// Setting the brightness, hue, and saturation at the same time
$command = new \Phue\Command\SetLightState($light);
$command->brightness(200)
        ->hue(0)
        ->saturation(255);

// Transition time (in seconds).
// 0 for "snapping" change
// Any other value for gradual change between current and new state
$command->transitionTime(3);

// Send the command
$client->sendCommand(
    $command
);

```


## Example/convenience scripts

There are a few scripts included in this package which serve as both convenience and further examples of using the client.

### Finding your Bridge

Included in this package is ```bin/phue-bridge-finder```, a script that will help find your Philips Hue bridges on your network. When plugging in your bridge into your router with an internet connection, the bridge will phone home to Philips *meethue* servers. The bridge will periodically send its assigned network IP and MAC address to *meethue*. Philips *meethue* service allows iPhone and Android apps to pull a list of the bridges directly from their servers by matching IPs originating from your requesting devices and bridges. ```bin/phue-bridge-finder``` uses same technique.

Prior to running this script, make sure your bridge is powered up and linked to your router. All lights should be lit up on the bridge.

Here's how to run this script:
```
$ ./bin/phue-bridge-finder
```

Assuming all goes well, you'll get results like this:
```
Philips Hue Bridge Finder

Checking meethue.com if the bridge has phoned home:
  Request succeeded

Number of bridges found: 1
	Bridge #1
		ID: 001788fffe09dddd
		Internal IP Address: 10.0.1.31
		MAC Address: 00:17:88:09:dd:dd
```

The internal IP address(es) listed in the results is what you need for the Phue client.

If the script provided doesn't find your bridge, or if you don't have internet connection on your network, I have created a wiki page that describes a few other convenient ways of finding it: [Finding Philips Hue bridge on network](/sqmk/Phue/wiki/Finding-Philips-Hue-bridge-on-network).

### Authentication

To test connectivity and authenticate with the bridge, you can use ```bin/phue-authenticate```. The script uses the Phue library to make requests and receive responses from the Philips Hue bridge.

At this point, you should be ready to authenticate with the bridge. The bridge expects a 32 character hash as a username to authenticate with, but you can feed in any string into the client and it will automatically hash for you. This is much easier to remember than the hash!

Here's how to run the script for authenticating:

```
$ ./bin/phue-authenticate 10.0.1.31 your.username
```

If the connection is ok, you will get a response similar to this:

```
! - Username your.username doesn't appear to be 32 character hash (A-F, 0-9)
! - Using this for username instead: af8caecf12655838d10fa92d86d09e82

Testing connection to bridge at 10.0.1.31
Response appears OK!

Attempting to authenticate (af8caecf12655838d10fa92d86d09e82):
Press the Bridge's button!
Waiting.....
```

The ```phue-authenticate``` script will attempt to authenticate with the bridge once every second. The bridge's connection button (the big round lit up one) needs to be pressed while the script runs. If the button is pressed during execution of the script, you should get a response like this:

```
! - Username your.username doesn't appear to be 32 character hash (A-F, 0-9)
! - Using this for username instead: af8caecf12655838d10fa92d86d09e82

Testing connection to bridge at 10.0.1.31
Response appears OK!

Attempting to authenticate (af8caecf12655838d10fa92d86d09e82):
Press the Bridge's button!
Waiting......
Authentication for user your.username was successful!
```

From then on, you should be able to use the final username for interacting with the Philips Hue bridge!

### Scanning / registering new lights

Now that you have tested connection and authentication to the bridge, you can now register your lights using the Phue client.

Another convenience script has been created to demonstrate how to use Phue to get the bridge to start scanning for and retrieving new lights. This script is ```phue-light-finder```, and it is also located in the ```bin``` directory.

You can pass the same arguments for ```phue-light-finder``` as you did with ```phue-authenticate```. Here's how to use the script:

```
$ ./bin/phue-light-finder 10.0.1.31 your.username
```

Example results are as follows:

```
! - Username your.username doesn't appear to be 32 character hash (A-F, 0-9)
! - Using this for username instead: af8caecf12655838d10fa92d86d09e82

Testing connection to bridge at 10.0.1.31
Response appears OK!

Scanning for lights. Turn at least one light off, then on...
Found: Light #1, Hue Lamp 1
Found: Light #2, Hue Lamp 2
Found: Light #3, Hue Lamp 3
Done scanning
```

Now that you have found and registered new lights with the bridge, you can now manage the lights! If you happen to add additional Hue lights to your home, you can use the Phue client and/or this script to invoke scanning and retrieving them.

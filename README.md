# Rubix Model Server
High-performance standalone model servers bring your [Rubix ML](https://github.com/RubixML/RubixML) estimators live into production quickly and effortlessly.

## Installation
Install Rubix Server using [Composer](https://getcomposer.org/):

```sh
$ composer require rubix/server
```

## Requirements
- [PHP](https://php.net/manual/en/install.php) 7.1.3 or above

#### Optional
- [Igbinary extension](https://github.com/igbinary/igbinary) for fast binary message serialization

## Documentation

### Table of Contents
- [Getting Started](#getting-started)
- [Servers](#servers)
	- [REST Server](#rest-server)
	- [RPC Server](#rpc-server)
- [Clients](#clients)
	- [RPC Client](#rpc-client)
- [Messages](#messages)
	- [Commands](#commands)
		- [Predict](#predict)
		- [Proba](#proba)
		- [Rank](#rank)
		- [Query Model](#query-model)
		- [Server Status](#server-status)
	- [Responses](#responses)
- [Http Middleware](#http-middeware)
	- [Shared Token Authenticator](#shared-token-authenticator)

---
### Getting Started
Once you've trained an estimator in Rubix ML, the next step is to use it to make predictions. If the model is going to used to make predictions in real time (as opposed to offline) then you'll need to make it availble to clients through a *server*. Rubix model servers expose your Rubix ML estimators as standalone services (such as REST and RPC) that can be queried in a live production environment. The library also provides an object oriented client API for executing *commands* on the server from your applications.

---
### Servers
Server objects are standalone server implementations built on top of React PHP, an event-driven system that makes it possible to serve thousands of concurrent requests at once.

To boot up a server, pass a trained estimator to the `serve()` method:
```php
public function serve(Estimator $estimator) : void
```

**Example**

```php
use Rubix\Server\RESTServer;
use Rubix\ML\Classifiers\KNearestNeighbors;

$estimator = new KNearestNeighbors(3);

// Train learner

$server = new RESTServer('127.0.0.1', 8888);

$server->serve($estimator);
```

The server will stay running until the process is terminated.

> **Note**: It is a good practice to use a process monitor such as [Supervisor](http://supervisord.org/) to start and autorestart the server in case there is a failure.

### REST Server
A standalone Json over HTTP and secure HTTP server exposing a [REST](https://en.wikipedia.org/wiki/Representational_state_transfer) (Representational State Transfer) API. 

**Parameters:**

| # | Param | Default | Type | Description |
|--|--|--|--|--|
| 1 | host | '127.0.0.1' | string | The host address to bind the server to. |
| 2 | port | 8888 | int | The network port to run the HTTP services on. |
| 3 | cert | None | string | The path to the certificate used to authenticate and encrypt the HTTP channel. |
| 4 | middleware | None | array | The HTTP middleware stack to run on each request. |

**HTTP Routes:**

| Method | URI | Json Params | Description |
|--|--|--|--|
| GET | /model | | Query information about the model. |
| POST | /model/predictions | `samples` | Return the predictions given by the model. |
| POST | /model/probabilities | `samples` | Predict the probabilities of each outcome. |
| POST | /model/scores | `samples` | Assign an anomaly score to each sample. |
| GET | /server/status | | Query the status of the server. |

**Example**

```php
use Rubix\Server\RESTServer;
use Rubix\Server\Http\Middleware\SharedTokenAuthenticator;

$server = new RESTServer('127.0.0.1', 4443, '/cert.pem', [
    new SharedTokenAuthenticator('secret'),
]);
```

### RPC Server
A lightweight [Remote Procedure Call](https://en.wikipedia.org/wiki/Remote_procedure_call) (RPC) server over HTTP and HTTPS that responds to serialized messages called [Commands](#commands).

**Parameters:**

| # | Param | Default | Type | Description |
|--|--|--|--|--|
| 1 | host | '127.0.0.1' | string | The host address to bind the server to. |
| 2 | port | 8888 | int | The network port to run the HTTP services on. |
| 3 | cert | None | string | The path to the certificate used to authenticate and encrypt the HTTP channel. |
| 4 | middleware | None | array | The HTTP middleware stack to run on each request. |
| 5 | serializer | Json | object | The message serializer. |

**Example**

```php
use Rubix\Server\RPCServer;
use Rubix\Server\Http\Middleware\SharedTokenAuthenticator;
use Rubix\Server\Serializers\Binary;

$server = new RPCServer('127.0.0.1', 4443, '/cert.pem', [
    new SharedTokenAuthenticator('secret'),
], new Binary());
```

---
### Clients
Clients allow you to communicate with a server over the wire using a user friendly object-oriented interface. Each client is capable of sending *commands* to the backend server with the `send()` method while handling all of the networking under the hood.

To send a Command and return a Response object:
```php
public send(Command $command) : Response
```

**Example:**

```php
use Rubix\Server\RPCClient;
use Rubix\Server\Commands\Predict;

$client = new RPCClient('127.0.0.1', 8888);

$predictions = $client->send(new Predict($samples));
```

### RPC Client
The RPC Client allows you to communicate with a [RPC Server](#rpc-server) over HTTP or Secure HTTP (HTTPS).

**Parameters:**

| # | Param | Default | Type | Description |
|--|--|--|--|--|
| 1 | host | '127.0.0.1' | string | The address of the server. |
| 2 | port | 8888 | int | The network port that the HTTP server is running on. |
| 3 | secure | false | bool | Should we use an encrypted HTTP channel (HTTPS)?. |
| 4 | headers | Auto | array | The HTTP headers to send along with each request. |
| 5 | serializer | Json | object | The message serializer. |
| 6 | timeout | INF | float | The number of seconds to wait before retrying. |
| 7 | retries | 2 | int | The number of retries before giving up. |
| 8 | delay | 0.3 | float | The delay in seconds between retries. |

**Example:**

```php
use Rubix\Server\RPCClient;
use Rubix\Server\Serializers\Binary;

$client = new RPCClient('127.0.0.1', 8888, false, [
    'Authorization' => 'secret',
], new Binary(), 2.5, 3, 0.5);
```

---
### Messages
Messages are containers for the data that flow accross the network between clients and model servers. They provide an object oriented interface to making requests and receiving responses through client/server interaction. There are two types of messages to consider in Rubix Server - *commands* and *responses*. Commands signal an action to be performed by the server and are instantiated by the user and sent by the client API. Responses are returned by the server and contain the data that was sent back as a result of a command.

To build a Message from an associative array:
```php
public static function fromArray() : self
```

To return the Message payload as an associative array:
```php
public function asArray() : array
```

> **Note**: Message objects use magic getters that allow you to access the payload data as if they were public properties of the message instance.

### Commands
Commands are messages sent by clients and used internally by servers to transport data over the wire and direct the server to execute a remote procedure. They should contain all the data needed by the server to execute the request. The result of a command is a [Response](#responses) object that contains the data sent back from the server.

### Predict
Return the predictions of the samples provided from the model running on the server.

**Parameters:**

| # | Param | Default | Type | Description |
|--|--|--|--|--|
| 1 | samples | | array | The unknown samples to predict. |

**Example:**

```php
use Rubix\Server\Commands\Predict;

$command = new Predict($samples);
```

### Proba
Return the probabilistic predictions from an underlying probabilistic model.

**Parameters:**
| # | Param | Default | Type | Description |
|--|--|--|--|--|
| 1 | samples | | array | The unknown samples to predict. |

**Example:**
```php
use Rubix\Server\Commands\Proba;

$command = new Proba($samples);
```

### Rank
Apply an arbitrary unnormalized scoring function over the the samples.

**Parameters:**

| # | Param | Default | Type | Description |
|--|--|--|--|--|
| 1 | samples | | array | The unknown samples to predict. |

**Example:**

```php
use Rubix\Server\Commands\Rank;

$command = new Rank($samples);
```

### Query Model
Query the status of the current model being served.

**Parameters:**

This command does not have any parameters.

**Example:**
```php
use Rubix\Server\Commands\QueryModel;

$command = new QueryModel();
```

### Server Status
Return statistics regarding the server status such as uptime, requests per minute, and memory usage.

**Parameters:**

This command does not have any parameters.

**Example:**

```php
use Rubix\Server\Commands\ServerStatus;

$command = new ServerStatus();
```

### Responses
Response objects are those returned as a result of an executed [Command](#commands). They contain all the data being sent back from the server.

---
### HTTP Middleware
HTTP middleware are objects that process incoming HTTP requests before they are handled by a controller.

### Shared Token Authenticator
Authenticates incoming requests using a shared key that is kept secret between the client and server.

> **Note**: This strategy is only secure over an encrypted channel such as HTTPS with SSL or TLS.

**Parameters:**

| # | Param | Default | Type | Description |
|--|--|--|--|--|
| 1 | token | | string | The shared secret key (token) required to authenticate every request. |

**Example:**

```php
use Rubix\Server\Http\Middleware\SharedTokenAuthenticator;

$middleware = new SharedTokenAuthenticator('secret');
```

---
## Testing
Rubix utilizes a combination of static analysis and unit tests for quality assurance and to reduce the number of bugs. Rubix provides three [Composer](https://getcomposer.org/) scripts that can be run from the root directory to automate the testing process.

To run static analysis:
```sh
$ composer analyze
```

To run the style checker:
```sh
$ composer check
```

To run the unit tests:
```sh
$ composer test
```

---
## License
[MIT](https://github.com/RubixML/Server/blob/master/LICENSE.md)
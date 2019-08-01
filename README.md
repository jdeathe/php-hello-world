# PHP "Hello, world!" Web App

A simple web app for demonstration / testing implementation in a container based environment such as [Docker](https://www.docker.com/) or [rkt](https://coreos.com/rkt/).

## Running with Docker Compose

### Prerequisites

- [Docker](https://docs.docker.com/engine/installation/).
- [Docker Compose](https://docs.docker.com/compose/install/).
- Host ports: 80, 443, 9022.
- SSH with a default private/public key pair.

### Recommended

- Chrome [Xdebug Helper](https://chrome.google.com/webstore/detail/xdebug-helper/eadndfjplgieldjbigjakmdgkmoaaaoc).

### Configure

Copy `.env.example` to `.env` and change values to suite.

**Note:** It is recommended to set your own username and default SSH public key.

### Bring Up the Services

Build the `httpd` service and run all required services in the background.

#### Build

```
$ docker-compose build
```

#### Run

```
$ docker-compose up -d
```

The app will be available on the docker host. i.e http://localhost/ if running Docker on the local host.

### Rebuilding the Services

After making a change the httpd service's image needs to be rebuilt.

**Note:** If rebuilding the image is undesirable, another option is to mount the local path's into the container by uncommenting the applicable volumes in the [docker-compose.yml](docker-compose.yml) file.

```
$ docker-compose up -d --build
```

### Bring Down the Services

Tear down the services when not in use.

```
$ docker-compose down
```

## Alternative Services

The default Apache PHP environment for the httpd service is Apache 2.2 / PHP 5.3 (Prefork) - to run the services with alternative images you need to specify both the base and override docker-compose configuration files.

### Bringing Up the Apache 2.2 / PHP 5.3 (FastCGI) Services

```
$ docker-compose -f docker-compose.yml -f docker-compose-v1.yml up -d --build
```

### Bringing Up the Apache 2.4 / PHP 5.4 (FastCGI) Services

```
$ docker-compose -f docker-compose.yml -f docker-compose-v4.yml up -d --build
```

### Bringing Up the Apache 2.4 / PHP 7.2 (PHP-FPM) Services

```
$ docker-compose -f docker-compose.yml -f docker-compose-v3.yml up -d --build
```

## Xdebug debugging

### IDE Key

Set the appropriate xdebug.idekey configuration value using the `DBGP_IDEKEY` environment variable in the `.env` file. For the PHPStorm IDE the value should be `PHPSTORM`, for Atom the value should be `xdebug-atom`. If using the recommended [Xdebug Helper](https://chrome.google.com/webstore/detail/xdebug-helper/eadndfjplgieldjbigjakmdgkmoaaaoc) Chrome extension, set the same IDE Key value in the extensions options.

### Port Forwarding

Xdebug is configured with `xdebug.remote_host = localhost` and `xdebug.remote_port = 9127` so SSH can be used to forward the docker host's local port `9127` to port `9000` on the host running the IDE.

The following command connects to the httpd container on port `9022` and forwards the remote port `9127` to `localhost` on local port `9000` - the `-f` and `-N` options are used to run it in the background and forward ports only. If your IDE is configured to listen on a port other than `9000` you would need to modify the last, (hostport), value.

```
$ ssh -p 9022 -fNR localhost:9127:localhost:9000 localhost
```

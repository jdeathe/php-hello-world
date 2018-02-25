<?php
namespace jdeathe\PhpHelloWorld;

use jdeathe\PhpHelloWorld\Http\Request;
use jdeathe\PhpHelloWorld\Http\Session;
use jdeathe\PhpHelloWorld\Output\Html;
use jdeathe\PhpHelloWorld\Settings\IniSettings;
use jdeathe\PhpHelloWorld\Collections\JsonFileCollection;
use jdeathe\PhpHelloWorld\Collections\NavigationBar;

require_once 'Http/Request.php';
require_once 'Http/Session.php';
require_once 'Output/Html.php';
require_once 'Settings/IniSettings.php';
require_once 'Collections/JsonFileCollection.php';
require_once 'Collections/NavigationBar.php';

$request = new Request(
    $_SERVER
);

$dateTimeUtc = new \DateTime(
    null,
    new \DateTimeZone(
        'UTC'
    )
);

if (
    ini_get(
        'session.save_handler'
    ) == 'memcached' &&
    ! empty(
        substr_count(
            ini_get(
                'session.save_path'
            ),
            ','
        )
    )
) {
    ini_set(
        'memcached.sess_binary',
        'On'
    );
    ini_set(
        'memcached.sess_consistent_hash',
        'On'
    );
    ini_set(
        'memcached.sess_number_of_replicas',
        (string) substr_count(
            ini_get(
                'session.save_path'
            ),
            ','
        )
    );
    ini_set(
        'memcached.sess_remove_failed',
        '1'
    );
}

$session = new Session();
$session->setName(
    'php-hello-world'
)
->set(
    'visitCount',
    (int) $session->get(
        'visitCount'
    ) + 1
)
->set(
    'visitDateTime',
    $dateTimeUtc->format(
        \DateTime::ATOM
    )
)
->close();

$viewSettings = new IniSettings(
    sprintf(
        '../etc/views/%s.ini',
        basename(
            __FILE__,
            '.php'
        )
    )
);

$navbar = NavigationBar::create(new JsonFileCollection(
    '../etc/collections/navbar-item.json'
));
$navbarItems = $navbar->getAll();
?>
<!DOCTYPE html>
<html lang="en" class="session">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title><?php Html::printEncoded($viewSettings->get('title', 'PHP "Hello, world!"')); ?></title>
        <link rel="stylesheet" href="/assets/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha256-LA89z+k9fjgMKQ/kq4OO2Mrf8VltYml/VES+Rg0fh20=" crossorigin="anonymous">
        <link rel="stylesheet" href="/assets/css/main.min.css">
    </head>
    <body>
        <nav class="navbar navbar-expand-lg navbar-dark fixed-top bg-dark">
            <div class="container">
                <a class="navbar-brand" href="/"><?php Html::printEncoded($viewSettings->get('project_name', 'PHP "Hello, world!"')); ?></a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
<?php
    if (!empty($navbarItems)) {
?>
                <div class="collapse navbar-collapse" id="navbar">
                    <ul class="navbar-nav mr-auto">
<?php
        foreach ($navbarItems as $navbarItem) {
            $activeItem = $navbar->isActiveItem($navbarItem);
?>
                        <li class="nav-item<?php print $activeItem ? ' active' : ''; ?>"><<?php print $activeItem ? 'span' : 'a'; ?> class="nav-link" href="<?php Html::printEncoded($navbarItem->url); ?>"><?php Html::printEncoded($navbarItem->label) . print $activeItem ? '<span class="sr-only"> (current)</span>' : ''; ?></<?php print $activeItem ? 'span' : 'a'; ?>></li>
<?php
        }
?>
                    </ul>
                </div>
<?php
    }
?>
            </div>
        </nav>
        <div class="container">
<?php
    if ($request->isTlsTerminated()) {
?>
            <div class="alert alert-info"><?php Html::printEncoded($viewSettings->get('alert_tls_terminated', 'SSL/TLS termination has been carried out upstream.')); ?></div>
<?php
    }
?>
            <h1><?php Html::printEncoded($viewSettings->get('heading')); ?></h1>
            <div class="table-responsive">
                <table class="table">
                    <tbody>
                        <tr>
                            <th>Save handler</th>
                            <td><?php Html::printEncoded(ini_get('session.save_handler')); ?></td>
                        </tr>
                        <tr>
                            <th>Save path</th>
                            <td><?php Html::printEncoded(ini_get('session.save_path')); ?></td>
                        </tr>
                        <tr>
                            <th>ID</th>
                            <td><?php Html::printEncoded($session->getId()); ?></td>
                        </tr>
                        <tr>
                            <th>Name</th>
                            <td><?php Html::printEncoded($session->getName()); ?></td>
                        </tr>
                        <tr>
                            <th>Visit count</th>
                            <td><?php Html::printEncoded($session->get('visitCount')); ?></td>
                        </tr>
                        <tr>
                            <th>Visit time (UTC)</th>
                            <td><?php Html::printEncoded($session->get('visitDateTime')); ?></td>
                        </tr>
                        <tr>
                            <th>Cookie</th>
                            <td><?php array_key_exists($session->getName(), $_COOKIE) ? Html::printEncoded($_COOKIE[$session->getName()]) : null; ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <script src="/assets/jquery/3.2.1/jquery.slim.min.js" integrity="sha256-k2WSCIexGzOj3Euiig+TlR8gA0EmPjuc79OEeY5L45g=" crossorigin="anonymous"></script>
        <script src="/assets/popper.js/1.12.9/umd/popper.min.js" integrity="sha256-pS96pU17yq+gVu4KBQJi38VpSuKN7otMrDQprzf/DWY=" crossorigin="anonymous"></script>
        <script src="/assets/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha256-5+02zu5UULQkO7w1GIr6vftCgMfFdZcAHeDtFnKZsBs=" crossorigin="anonymous"></script>
    </body>
</html>
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

if (
    version_compare(
        PHP_VERSION,
        '5.5.2',
        '>='
    )
) {
    ini_set(
        'session.use_strict_mode',
        '1'
    );
}

$viewSettings = new IniSettings(
    sprintf(
        '../etc/views/%s.ini',
        basename(
            __FILE__,
            '.php'
        )
    )
);

$request = new Request(
    $_SERVER
);

$session = new Session();
$session->setName(
    'php-hello-world'
);

if (
    $session->isExpired()
) {
    if (
        ! $session->invalidate()
    ){
        $session->destroy();
        header(
            sprintf(
                '%s %s Session Terminated',
                $request->getServerParams()['SERVER_PROTOCOL'],
                403
            ),
            true,
            403
        );
        exit;
    }
    $session->restart();
}

$dateTime = new \DateTime(
    null,
    new \DateTimeZone(
        'UTC'
    )
);

$session
    ->setBucket(
        'visits'
    )
    ->set(
        'count',
        (int) $session->get(
            'count'
        ) + 1
    )
    ->set(
        'last',
        $dateTime->format(
            \DateTime::ATOM
        )
    )
;

if(
    ! $session->has(
        'first'
    )
) {
    $session->set(
        'first',
        $dateTime->format(
            \DateTime::ATOM
        )
    );
}

// Test Session methods
if (
    array_key_exists(
        'action',
        $_GET
    )
) {
    switch (
        $_GET['action']
    ) {
        case 'clear':
            $session->clear();
            break;
        case 'migrate':
            $session->migrate(
                false,
                15
            );
            break;
    }
}

// Test Session flash
if (
    array_key_exists(
        'flash',
        $_GET
    )
) {
    $alert = new \stdClass();

    switch (
        $_GET['flash']
    ) {
        case '0':
        case 'emerg':
        case '1':
        case 'alert':
        case '2':
        case 'crit':
        case '3':
        case 'error':
            $alert->context = 'danger';
            $alert->dismissable = false;
            $alert->message = $viewSettings->get(
                'alert_error_message'
            );
            break;
        case '4':
        case 'warning':
            $alert->context = 'warning';
            $alert->dismissable = true;
            $alert->message = $viewSettings->get(
                'alert_warning_message'
            );
            break;
        case '5':
        case 'notice':
            $alert->context = 'success';
            $alert->dismissable = true;
            $alert->message = $viewSettings->get(
                'alert_notice_message'
            );
            break;
        case '6':
        case 'info':
        case '7':
        case 'debug':
        default:
            $alert->context = 'info';
            $alert->dismissable = true;
            $alert->message = $viewSettings->get(
                'alert_info_message'
            );
            break;
    }

    $session
        ->setBucket($session::BUCKET_FLASH)
        ->set(
            'alert',
            $alert
        )
    ;
}

// Commit session
$session
    ->restoreBucket()
    ->save()
;

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
    if (!$session->setBucket($session::BUCKET_FLASH)->isEmpty()
        && $session->setBucket($session::BUCKET_FLASH)->has('alert')
        && property_exists($session->setBucket($session::BUCKET_FLASH)->get('alert'), 'context')
        && property_exists($session->setBucket($session::BUCKET_FLASH)->get('alert'), 'message')
        && property_exists($session->setBucket($session::BUCKET_FLASH)->get('alert'), 'dismissable')
    ) {
?>
            <div class="alert alert-<?php Html::printEncoded($session->setBucket($session::BUCKET_FLASH)->get('alert')->context); ?><?php $session->setBucket($session::BUCKET_FLASH)->get('alert')->dismissable ? print ' alert-dismissible fade show' : null; ?>">
                <?php Html::printEncoded($session->setBucket($session::BUCKET_FLASH)->get('alert')->message) . PHP_EOL; ?>
<?php
        if ($session->setBucket($session::BUCKET_FLASH)->get('alert')->dismissable === true) {
?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
<?php
        }
?>
            </div>
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
                    </tbody>
                </table>
            </div>
<?php
    if (!empty($session->getAllBuckets())) {
?>
            <h2>Bucket Contents</h2>
<?php
        foreach ($session->getAllBuckets() as $bucket) {
            if (!$session->setBucket($bucket)->isEmpty()) {
?>
            <div class="table-responsive">
                <table class="table table-sm">
                    <caption><?php Html::printEncoded($bucket); ?></caption>
                    <thead>
                        <tr>
                            <th>Key</th>
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody>
<?php
                foreach ($session->getAll() as $key => $value) {
?>
                        <tr>
                            <td><?php Html::printEncoded($key); ?></td>
                            <td><?php Html::printEncoded(print_r($value, true)); ?></td>

                        </tr>
<?php
                }
?>
                    </tbody>
                </table>
            </div>
<?php
            }
        }
    }
?>
        </div>
        <script src="/assets/jquery/3.2.1/jquery.slim.min.js" integrity="sha256-k2WSCIexGzOj3Euiig+TlR8gA0EmPjuc79OEeY5L45g=" crossorigin="anonymous"></script>
        <script src="/assets/popper.js/1.12.9/umd/popper.min.js" integrity="sha256-pS96pU17yq+gVu4KBQJi38VpSuKN7otMrDQprzf/DWY=" crossorigin="anonymous"></script>
        <script src="/assets/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha256-5+02zu5UULQkO7w1GIr6vftCgMfFdZcAHeDtFnKZsBs=" crossorigin="anonymous"></script>
    </body>
</html>
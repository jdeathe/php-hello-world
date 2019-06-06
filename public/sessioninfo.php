<?php
namespace jdeathe\PhpHelloWorld;

use jdeathe\PhpHelloWorld\Alerts\Alerts;
use jdeathe\PhpHelloWorld\Alerts\BootstrapAlert as Alert;
use jdeathe\PhpHelloWorld\Collections\JsonFileCollection;
use jdeathe\PhpHelloWorld\Collections\NavigationBar;
use jdeathe\PhpHelloWorld\Http\Request;
use jdeathe\PhpHelloWorld\Http\Session;
use jdeathe\PhpHelloWorld\Output\Html;
use jdeathe\PhpHelloWorld\Settings\IniSettings;

require_once 'Alerts/Alerts.php';
require_once 'Alerts/BootstrapAlert.php';
require_once 'Collections/JsonFileCollection.php';
require_once 'Collections/NavigationBar.php';
require_once 'Http/Request.php';
require_once 'Http/Session.php';
require_once 'Output/Html.php';
require_once 'Settings/IniSettings.php';

$request = new Request(
    $_SERVER
);

// Session creation and validation
$session = Session::create()->setName(
    ini_get(
        'session.name'
    )
);
if (
    $session->isExpired()
) {
    if (
        ! $session->invalidate()
    ) {
        $session->destroy();
        header(
            sprintf(
                '%s %s Session Terminated',
                $_SERVER['SERVER_PROTOCOL'],
                403
            ),
            true,
            403
        );
        exit;
    }
    $session->restart();
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

// Session visits
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
$session->restoreBucket();

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
    // Limit to valid level
    $alertLevel = (int) $_GET['flash'];
    if (
        $alertLevel < Alert::LEVEL_EMERG ||
        $alertLevel > Alert::LEVEL_DEBUG
    ) {
        $alertLevel = Alert::LEVEL_ERR;
    }
    // Create Alert for level
    $alert = Alert::create()->setLevel(
        $alertLevel
    );
    switch (
        $alert->getLevel()
    ) {
        case Alert::LEVEL_EMERG:
        case Alert::LEVEL_ALERT:
        case Alert::LEVEL_CRIT:
        case Alert::LEVEL_ERR:
            $alert
                ->setMessage(
                    $viewSettings->get(
                        'alert_error_message'
                    )
                )
            ;
            break;
        case Alert::LEVEL_WARNING:
            $alert
                ->setDismissible(
                    true
                )
                ->setMessage(
                    $viewSettings->get(
                        'alert_warning_message'
                    )
                )
            ;
            break;
        case Alert::LEVEL_NOTICE:
            $alert
                ->setDismissible(
                    true
                )
                ->setMessage(
                    $viewSettings->get(
                        'alert_notice_message'
                    )
                )
            ;
            break;
        case Alert::LEVEL_INFO:
        case Alert::LEVEL_DEBUG:
        default:
            $alert
                ->setDismissible(
                    true
                )
                ->setMessage(
                    $viewSettings->get(
                        'alert_info_message'
                    )
                )
            ;
            break;
    }
    // Add Alert to Alerts and store in Session flash
    $session
        ->setBucket(
            $session::BUCKET_FLASH_WRITE
        )
        ->set(
            'alerts',
            Alerts::create(
                $alert
            )
            ->add(
                $alert
            )
        )
        ->restoreBucket()
        ->save()
    ;
    // Redirect to show Alerts in Session flash
    header(
        sprintf(
            'Location: %s',
            $_SERVER['SCRIPT_NAME']
        ),
        true,
        302
    );
    exit;
}
// Commit session
$session->save();

// Alerts
$alerts = Alerts::create(
    Alert::create()
);
if (
    $request->isTlsTerminated()
) {
    $alerts->add(
        Alert::create()
            ->setLevel(
                Alert::LEVEL_INFO
            )
            ->setMessage(
                $viewSettings->get(
                    'alert_tls_terminated',
                    'SSL/TLS termination has been carried out upstream.'
                )
            )
    );
}
array_map(
    function($alert) use ($alerts) {
        $alerts->add(
            $alert
        );
    },
    $session
        ->setBucket(
            $session::BUCKET_FLASH_READ
        )
        ->get(
            'alerts',
            Alerts::create(
                Alert::create()
            )
        )
        ->getAll()
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
        <link rel="stylesheet" href="/assets/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha256-eSi1q2PG6J7g7ib17yAaWMcrr5GrtohYChqibrV7PBE=" crossorigin="anonymous">
        <link rel="stylesheet" href="/assets/css/main.min.css">
    </head>
    <body>
        <nav class="navbar navbar-expand-lg navbar-dark fixed-top bg-dark">
            <div class="container">
                <a class="navbar-brand"<?php print $navbar->isHomePage() ? '' : ' href="/"'; ?>><?php Html::printEncoded($viewSettings->get('project_name', 'PHP "Hello, world!"')); ?></a>
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
    foreach ($alerts->getAll() as $alert) {
?>
            <div class="alert alert-<?php Html::printEncoded($alert->getLabel()); ?><?php $alert->getDismissible() ? print ' alert-dismissible fade show' : null; ?>">
                <?php Html::printEncoded($alert->getMessage()) . PHP_EOL; ?>
<?php
        if ($alert->getDismissible() === true) {
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
    if ($session->hasBuckets()) {
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
        <script src="/assets/jquery/3.3.1/jquery.slim.min.js" integrity="sha256-3edrmyuQ0w65f8gfBsqowzjJe2iM6n0nKciPUp8y+7E=" crossorigin="anonymous"></script>
        <script src="/assets/popper.js/1.14.3/umd/popper.min.js" integrity="sha256-98vAGjEDGN79TjHkYWVD4s87rvWkdWLHPs5MC3FvFX4=" crossorigin="anonymous"></script>
        <script src="/assets/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha256-VsEqElsCHSGmnmHXGQzvoWjWwoznFSZc6hs7ARLRacQ=" crossorigin="anonymous"></script>
    </body>
</html>
<?php
namespace jdeathe\PhpHelloWorld;

use jdeathe\PhpHelloWorld\Alerts\Alerts;
use jdeathe\PhpHelloWorld\Alerts\BootstrapAlert as Alert;
use jdeathe\PhpHelloWorld\Collections\JsonFileCollection;
use jdeathe\PhpHelloWorld\Collections\NavigationBar;
use jdeathe\PhpHelloWorld\Http\Request;
use jdeathe\PhpHelloWorld\Output\Html;
use jdeathe\PhpHelloWorld\Output\Info;
use jdeathe\PhpHelloWorld\Settings\IniSettings;

require_once 'Alerts/Alerts.php';
require_once 'Alerts/BootstrapAlert.php';
require_once 'Collections/JsonFileCollection.php';
require_once 'Collections/NavigationBar.php';
require_once 'Http/Request.php';
require_once 'Output/Html.php';
require_once 'Output/Info.php';
require_once 'Settings/IniSettings.php';

$request = new Request(
    $_SERVER
);

$viewSettings = new IniSettings(
    sprintf(
        '../etc/views/%s.ini',
        basename(
            __FILE__,
            '.php'
        )
    )
);

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

$navbar = NavigationBar::create(new JsonFileCollection(
    '../etc/collections/navbar-item.json'
));
$navbarItems = $navbar->getAll();

header(
    sprintf(
        'Cache-Control: %s',
        'no-store, must-revalidate'
    ),
    true
);
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title><?php Html::printEncoded($viewSettings->get('title', 'PHP "Hello, world!"')); ?></title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha256-LA89z+k9fjgMKQ/kq4OO2Mrf8VltYml/VES+Rg0fh20=" crossorigin="anonymous">
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
            <div class="table-responsive">
<?php
    Info::php();
?>
            </div>
        </div>
        <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha256-k2WSCIexGzOj3Euiig+TlR8gA0EmPjuc79OEeY5L45g=" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha256-pS96pU17yq+gVu4KBQJi38VpSuKN7otMrDQprzf/DWY=" crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha256-5+02zu5UULQkO7w1GIr6vftCgMfFdZcAHeDtFnKZsBs=" crossorigin="anonymous"></script>
    </body>
</html>
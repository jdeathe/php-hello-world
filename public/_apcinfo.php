<?php
namespace jdeathe\PhpHelloWorld;

use jdeathe\PhpHelloWorld\Http\Request;
use jdeathe\PhpHelloWorld\Output\Html;
use jdeathe\PhpHelloWorld\Output\Info;
use jdeathe\PhpHelloWorld\Settings\IniSettings;
use jdeathe\PhpHelloWorld\Collections\JsonFileCollection;
use jdeathe\PhpHelloWorld\Collections\NavigationBar;

require_once 'Http/Request.php';
require_once 'Output/Html.php';
require_once 'Output/Info.php';
require_once 'Settings/IniSettings.php';
require_once 'Collections/JsonFileCollection.php';
require_once 'Collections/NavigationBar.php';

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

$navbarItems = NavigationBar::create(new JsonFileCollection(
    '../etc/collections/navbar-item.json'
))->getAll();
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="/favicon.ico">
    <title><?php Html::printEncoded($viewSettings->get('title', 'PHP "Hello, world!"')); ?></title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha256-916EbMg70RQy9LHiGkXzG8hSg9EdNy97GazNG/aiY1w=" crossorigin="anonymous">
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <link rel="stylesheet" href="/assets/css/main.min.css">
  </head>
  <body>
    <nav class="navbar navbar-default navbar-fixed-top navbar-inverse">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="/"><?php Html::printEncoded($viewSettings->get('project_name', 'PHP "Hello, world!"')); ?></a>
        </div>
<?php
    if (!empty($navbarItems)) {
?>
        <div id="navbar" class="navbar-collapse collapse">
          <ul class="nav navbar-nav">
<?php
        foreach ($navbarItems as $navbarItem) {
            $activeItem = $navbarItem->url == $_SERVER['REQUEST_URI']
                ? true
                : false
            ;
?>
            <li<?php print $activeItem ? ' class="active"' : ''; ?>><a href="<?php Html::printEncoded($navbarItem->url); ?>"><?php Html::printEncoded($navbarItem->label) . print $activeItem ? '<span class="sr-only"> (current)</span>' : ''; ?></a></li>
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
    <div class="container-flow">
<?php
    if ($request->isTlsTerminated()) {
?>
      <div class="alert alert-info"><?php Html::printEncoded($viewSettings->get('alert_tls_terminated', 'SSL/TLS termination has been carried out upstream.')); ?></div>
<?php
    }
?>
      <div class="embed-flow">
        <iframe src="/_apc.php" frameborder="0" width="100%" height="100%"></iframe>
      </div>
    </div>
    <script src="https://code.jquery.com/jquery-1.12.4.min.js" integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ=" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha256-U5ZEeKfGNOja007MMD3YBI0A3OSZOQbeG6z2f2Y0hu8=" crossorigin="anonymous"></script>
  </body>
</html>
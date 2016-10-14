<?php
namespace jdeathe\PhpHelloWorld;

use jdeathe\PhpHelloWorld\Output\Html;
use jdeathe\PhpHelloWorld\Settings\IniSettings;

require_once 'Output/Html.php';
require_once 'Settings/IniSettings.php';

$viewSettings = new IniSettings(
    sprintf(
        '../etc/views/%s.ini',
        basename(
            __FILE__,
            '.php'
        )
    )
);
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="/favicon.ico">
    <title><?php Html::printEncoded($viewSettings->get('title', 'PHP Hello World')); ?></title>

    <!-- Bootstrap -->
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

    <!-- Optional theme -->
    <!-- <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous"> -->

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="//oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="//oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <style type="text/css">
      html, body {background-color: #333; height: 100%; font-family: sans-serif;}
      body {margin: 0; padding-top: 50px; color: #fff; box-shadow: inset 0 0 100px rgba(0,0,0,.5);}
      .hello-banner {padding: 40px 15px; text-align: center; background-color: #222; border-radius: 6px;}
      .hello-banner h1 {font-size: 63px;}
      .hello-banner p {margin-bottom: 15px; font-size: 21px; font-weight: 200;}
    </style>
  </head>
  <body>
    <div class="container">
<?php
  // Example method to detect SSL/TLS offloaded requests
  if (array_key_exists('SERVER_PORT', $_SERVER) &&
      $_SERVER['SERVER_PORT'] === '8443' &&
      array_key_exists('HTTP_X_FORWARDED_PROTO', $_SERVER) &&
      $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https'
  ) {
      $_SERVER['HTTPS'] = 'on';
?>
      <div class="alert alert-info"><?php Html::printEncoded($viewSettings->get('alert_tls_terminated', 'TLS termination has been carried out on the load balancer.')); ?></div>
<?php
  }
?>
      <div class="hello-banner">
        <h1><?php Html::printEncoded($viewSettings->get('heading', 'Hello, World!')); ?></h1>
        <p><?php Html::printfEncoded($viewSettings->get('description'), array(PHP_SAPI)); ?></p>
        <p class="lead">
<?php
  if (realpath(
      __DIR__ . "/_phpinfo.php"
  )) {
?>
          <a href="/_phpinfo.php" class="btn btn-lg btn-primary">PHP info</a>
<?php
  }
  if (extension_loaded('apc') &&
      realpath(
        __DIR__ . "/_apc.php"
      )
  ) {
?>
          <a href="/_apcinfo.php" class="btn btn-lg btn-default">APC info</a>
<?php
  }
  if (array_key_exists('SERVER_SOFTWARE', $_SERVER) &&
      strpos($_SERVER['SERVER_SOFTWARE'], 'Apache') === 0 &&
      array_key_exists('REMOTE_ADDR', $_SERVER) &&
      $_SERVER['REMOTE_ADDR'] === '127.0.0.1'
  ) {
?>
          <a href="/server-status" class="btn btn-lg btn-default">Apache status</a>
<?php
  }
  if (PHP_SAPI === 'fpm-fcgi' &&
      array_key_exists('REMOTE_ADDR', $_SERVER) &&
      $_SERVER['REMOTE_ADDR'] === '127.0.0.1'
  ) {
?>
          <a href="/status?full" class="btn btn-lg btn-default">PHP-FPM status</a>
<?php
  }
?>
        </p>
      </div>
    </div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <!-- Latest compiled and minified JavaScript -->
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
  </body>
</html>
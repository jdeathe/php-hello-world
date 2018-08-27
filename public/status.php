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

Html::printEncoded(
    $viewSettings->get(
        'status_success',
        'OK'
    )
);

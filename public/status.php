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

header(
    sprintf(
        'Cache-Control: %s',
        'no-cache'
    ),
    true
);

$statusCode = function_exists(
        'http_response_code'
    )
    ? (int) http_response_code()
    : (int) $_SERVER['REDIRECT_STATUS']
;
if (
    $statusCode === 503
) {
    Html::printEncoded(
        $viewSettings->get(
            'status_unavailable',
            'Service Unavailable'
        )
    );
} elseif (
    $statusCode >= 500
) {
    Html::printEncoded(
        $viewSettings->get(
            'status_error',
            'Internal Server Error'
        )
    );
} else {
    Html::printEncoded(
        $viewSettings->get(
            'status_success',
            'OK'
        )
    );
}

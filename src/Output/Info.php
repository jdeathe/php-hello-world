<?php
namespace jdeathe\PhpHelloWorld\Output;

class Info
{
    /**
     * @var string
     */
    private static $phpinfo = '';

    /**
     * Output phpinfo HTML table contents.
     *
     * @return string HTML phpinfo table.
     */
    public static function php()
    {
        $matches = array();

        ob_start();
        phpinfo();
        preg_match(
            '~<body[^>]*>(.*)</body>~ims',
            ob_get_contents(),
            $matches
        );
        ob_end_clean();

        if (isset($matches[1]) && is_string($matches[1])) {
            self::$phpinfo = preg_replace(
                array(
                    '~<font~',
                    '~</font>~',
                    '~<table.*>~',
                    '~<tr class="h"><th>~',
                    '~</th></tr>~',
                    '~</table>~'
                ),
                array(
                    '<span',
                    '</span>',
                    '<table class="table table-sm">',
                    '<thead><tr><th class="col-xs-4">',
                    '</th></tr></thead><tbody>',
                    '</tbody></table>'
                ),
                $matches[1]
            );
        }

        print self::$phpinfo;
    }
}
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
        ob_start();
        phpinfo();
        $htmlDom = \DOMDocument::loadHTML(
            ob_get_contents()
        );
        ob_end_clean();

        foreach ($htmlDom->getElementsByTagName('body')->item(0)->childNodes as $childNode) {
            self::$phpinfo .= $htmlDom->saveHTML($childNode);
        }

        self::$phpinfo = preg_replace(
            array(
                '~<font~',
                '~</font>~',
                '~border="0" cellpadding="3"~',
                '~<tr class="h"><th>~',
                '~</th></tr>~',
                '~</table>~'
            ),
            array(
                '<span',
                '</span>',
                'class="table table-condensed table-hover"',
                '<thead><tr><th>',
                '</th></tr></thead><tbody>',
                '</tbody></table>'
            ),
            self::$phpinfo
        );

        print self::$phpinfo;
    }
}
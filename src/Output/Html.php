<?php
namespace jdeathe\PhpHelloWorld\Output;

class Html
{
    /**
     * Output an HTML encoded string.
     *
     * @param string $value String to be HTML encoded.
     * @param string $default The default string to use when value is empty.
     * @return void
     */
    public static function printEncoded($value = '', $default = '')
    {
        print htmlspecialchars(
            (string) $value !== ''
                ? (string) $value
                : (string) $default,
            ENT_QUOTES,
            'UTF-8'
        );
    }

    /**
     * Output an sprintf formatted HTML encoded string.
     *
     * @param string $format Format string to be HTML encoded.
     * @param array $args Array of arguments to apply to the format template.
     * @param string $default The default string to use when value is empty.
     * @return void
     */
    public static function printfEncoded($format = '%s', $args = array(), $default = '')
    {
        self::printEncoded(
            vsprintf(
                $format,
                $args
            ),
            $default
        );
    }
}
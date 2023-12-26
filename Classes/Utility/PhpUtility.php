<?php

namespace JambageCom\Div2007\Utility;

class PhpUtility
{
    // Function to get the client IP address
    public static function getClientIp()
    {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        } elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        } else {
            $ipaddress = false;
        }

        return $ipaddress;
    }

    public static function php_is_secure($code)
    {
        $result = true;
        $foundSystem = stripos($code, 'system');
        if ($foundSystem !== false) {
            $result = false;
        }

        return $result;
    }

    /**
     * Check the syntax of some PHP code.
     *
     * @param string $code PHP code to check
     *
     * @return bool|array if FALSE, then check was successful, otherwise an array(message,line) of errors is returned
     */
    public static function php_syntax_error($code, $addPhp = true): array|bool
    {
        if (!defined('CR')) {
            define('CR', "\r");
        }
        if (!defined('LF')) {
            define('LF', "\n");
        }
        if (!defined('CRLF')) {
            define('CRLF', "\r\n");
        }
        $braces = 0;
        $inString = 0;
        if ($addPhp) {
            $code = '<?php ' . $code;
        }

        foreach (\PhpToken::tokenize('<?php ' . $code) as $token) {
            if (is_array($token)) {
                switch ($token[0]) {
                    case T_CURLY_OPEN:
                    case T_DOLLAR_OPEN_CURLY_BRACES:
                    case T_START_HEREDOC: ++$inString;
                        break;
                    case T_END_HEREDOC:   --$inString;
                        break;
                }
            } elseif ($inString & 1) {
                switch ($token) {
                    case '`': case '\'':
                    case '"': --$inString;
                        break;
                }
            } else {
                switch ($token) {
                    case '`': case '\'':
                    case '"': ++$inString;
                        break;
                    case '{': ++$braces;
                        break;
                    case '}':
                        if ($inString) {
                            --$inString;
                        } else {
                            --$braces;
                            if ($braces < 0) {
                                break 2;
                            }
                        }
                        break;
                }
            }
        }
        $inString = @ini_set('log_errors', false);
        $token = @ini_set('display_errors', true);
        ob_start();
        $code = substr($code, strlen('<?php '));

        $braces || $code = "if(0) {{$code}\n}";
        if (eval($code) === false) {
            if ($braces) {
                $braces = PHP_INT_MAX;
            } else {
                str_contains($code, CR) && $code = strtr(str_replace(CRLF, LF, $code), CR, LF);
                $braces = substr_count($code, LF);
            }
            $code = ob_get_clean();
            $code = strip_tags($code);
            if (preg_match("'syntax error, (.+) in .+ on line (\d+)$'s", $code, $code)) {
                $code[2] = (int)$code[2];
                $code = $code[2] <= $braces
                    ? [$code[1], $code[2]]
                    : ['unexpected $end' . substr($code[1], 14), $braces];
            } else {
                $code = ['syntax error', 0];
            }
        } else {
            ob_end_clean();
            $code = false;
        }
        @ini_set('display_errors', $token);
        @ini_set('log_errors', $inString);

        return $code;
    }
}

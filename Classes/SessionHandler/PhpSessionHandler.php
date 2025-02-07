<?php

namespace JambageCom\Div2007\SessionHandler;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * PHP session handling utility.
 *
 * @author Bernhard Kraft <kraftb@think-open.at>
 * @copyright 2018
 */
class PhpSessionHandler extends AbstractSessionHandler implements SessionHandlerInterface
{
    /**
     * Constructor for session handling class.
     */
    public function __construct()
    {
        if (basename($_SERVER['PHP_SELF']) !== 'phpunit') {
            session_start();
        }
    }

    /**
     * Set session data.
     */
    public function setSessionData(array $data): void
    {
        if (!is_array($data)) {
            $data = [];
        }
        $sessionKey = $this->getSessionKey();
        $_SESSION[$sessionKey] = $data;
    }

    /**
     * Get session data.
     *
     * @return data The session data
     */
    public function getSessionData($subKey = '')
    {
        $data = [];
        $result = '';

        $sessionKey = $this->getSessionKey();
        if (
            isset($_SESSION[$sessionKey]) &&
            is_array($_SESSION[$sessionKey])
        ) {
            $data = $_SESSION[$sessionKey];
        }

        if (
            $subKey != '' &&
            is_array($data) &&
            isset($data[$subKey])
        ) {
            $result = $data[$subKey];
        } elseif (
            $subKey == '' &&
            is_array($data)
        ) {
            $result = $data;
        }

        return $result;
    }
}

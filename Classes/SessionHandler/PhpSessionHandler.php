<?php

declare(strict_types=1);

namespace JambageCom\Div2007\SessionHandler;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

/**
 * PHP session handling utility.
 * Optimized for PHP 8.2+ and TYPO3 v13/v14.
 */
class PhpSessionHandler extends AbstractSessionHandler implements SessionHandlerInterface
{
    /**
     * Constructor for session handling class.
     */
    public function __construct()
    {
        // Sicherer Check, ob eine native PHP-Session bereits gestartet wurde (verhindert PHPUnit-Konflikte)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Set session data.
     */
    public function setSessionData(array $data): void
    {
        $sessionKey = $this->getSessionKey();
        $_SESSION[$sessionKey] = $data;
    }

    /**
     * Get session data.
     *
     * @param string $subKey
     * @return mixed The session data (array or string)
     */
    public function getSessionData(string $subKey = ''): mixed
    {
        $sessionKey = $this->getSessionKey();

        $data = [];
        if (isset($_SESSION[$sessionKey]) && is_array($_SESSION[$sessionKey])) {
            $data = $_SESSION[$sessionKey];
        }

        if ($subKey !== '') {
            return $data[$subKey] ?? '';
        }

        return $data;
    }
}

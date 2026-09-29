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

use JambageCom\Div2007\Constants\Extension;

/**
 * Abstract session handling base class.
 * Optimized for PHP 8.2+ and TYPO3 v13/v14.
 */
abstract class AbstractSessionHandler
{
    /**
     * The session variable key.
     * Overwrite this with your own session key (e.g., tx_myextension).
     */
    protected string $sessionKey = Extension::KEY;

    /**
     * Get session key.
     */
    public function getSessionKey(): string
    {
        return $this->sessionKey;
    }

    /**
     * Set session key.
     */
    public function setSessionKey(string $key): void
    {
        $this->sessionKey = $key;
    }

    /**
     * Get session data.
     *
     * @param string $subKey
     * @return mixed The session data (array or string)
     */
    abstract public function getSessionData(string $subKey = ''): mixed;

    /**
     * Set session data.
     */
    abstract public function setSessionData(array $data): void;
}

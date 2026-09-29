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
 * Interface definition for session handling class.
 * Optimized for PHP 8.2+ and TYPO3 v13/v14.
 */
interface SessionHandlerInterface
{
    /**
     * Get session key.
     */
    public function getSessionKey(): string;

    /**
     * Set session key.
     */
    public function setSessionKey(string $key): void;

    /**
     * Get session data.
     *
     * @param string $subKey
     * @return mixed The session data (array or string)
     */
    public function getSessionData(string $subKey = ''): mixed;

    /**
     * Set session data.
     */
    public function setSessionData(array $data): void;
}

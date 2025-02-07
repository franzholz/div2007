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
 * Abstract session handling base class.
 *
 * @author Bernhard Kraft <kraftb@think-open.at>
 * @copyright 2016
 */
abstract class AbstractSessionHandler
{
    /**
     * The session variable key. Overwrite this with your own session key which should be the tx_(extensionkey) where the extension key is without underline characters.
     *
     * @var string
     */
    protected $sessionKey = DIV2007_EXT;

    /**
     * Get session key.
     *
     * @return data The session data
     */
    public function getSessionKey(): string
    {
        return $this->sessionKey;
    }

    /**
     * Get session key.
     *
     * @return data The session data for the captcha extension
     */
    public function setSessionKey($key): void
    {
        $this->sessionKey = $key;
    }

    /**
     * Get session data.
     *
     * @return data The session data
     */
    abstract public function getSessionData($subKey = '');

    /**
     * Set session data.
     */
    abstract public function setSessionData(array $data): void;
}

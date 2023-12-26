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
 * Interface definition for session handling class.
 *
 * @author Bernhard Kraft <kraftb@think-open.at>
 * @copyright 2018
 */
interface SessionHandlerInterface
{
    /**
     * Get session key.
     *
     * @return data The session data
     */
    public function getSessionKey();

    /**
     * Get session key.
     *
     * @return data The session data for the captcha extension
     */
    public function setSessionKey($key);

    /**
     * Get session data.
     *
     * @return data The session data for the captcha extension
     */
    public function getSessionData();

    /**
     * Set session data.
     */
    public function setSessionData($data);
}

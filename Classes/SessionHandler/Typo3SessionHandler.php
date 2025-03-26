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
 * TYPO3 session handling utility.
 *
 * @author Bernhard Kraft <kraftb@think-open.at>
 * @copyright 2018
 */

use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;


class Typo3SessionHandler extends AbstractSessionHandler implements SessionHandlerInterface
{
    /**
     * The session variable key. You must overwrite this class or use the setSessionKey method to make it working.
     *
     * @var string
     */
    protected $sessionKey = self::class;

    /**
     * An "fe_user" object instance. Required for session access.
     *
     * @var \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication
     */
    protected $frontendUser;

    /**
     * Constructor for session handling class.
     *
     * Usage requirement:
     *
     * An object of this class must be generated once in the MiddleWare of the extension
     * to which your derived class belongs to.
     */
    public function __construct(
        ?FrontendUserAuthentication $frontendUser = null,
        $setCookie = true, // Unused
    )
    {
        if (basename($_SERVER['PHP_SELF']) !== 'phpunit') {
            if (isset($frontendUser)) {
                $this->frontendUser = $frontendUser;
            }

            if (empty($this->frontendUser)) {
                throw new \RuntimeException('Extension ' . DIV2007_EXT . ' Typo3SessionHandler: Empty attribute frontend.user' . ' ', 1612216764);
            }
            $session = null;
            try {
                $session = $this->frontendUser->getSession();
            } catch (TypeError $e) {
             // continue
            }
            if (empty($session)) {
                throw new \RuntimeException('Extension ' . DIV2007_EXT . ' Typo3SessionHandler: The frontend.user session must not be empty. A MiddleWare must be used which has created once its derived class.' . ' ', 1738760876);
            }
        }
    }

    /**
     * Set session data.
     */
    public function setSessionData(array $data): void
    {
        if (
            true ||
            // TODO: Check if cookies are allowed
            empty($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][DIV2007_EXT]['checkCookieSet'])
        ) {
            $sessionKey = $this->getSessionKey();
            $this->frontendUser->setAndSaveSessionData($sessionKey, $data);
        }

    }

    /**
     * Get session data.
     *
     * @return data ... The session data
     */
    public function getSessionData($subKey = '')
    {
        $result = '';
        $sessionKey = $this->getSessionKey();
        $data = $this->frontendUser->getSessionData($sessionKey);

        if (is_array($data)) {
            if (
                $subKey != '' &&
                isset($data[$subKey])
            ) {
                $result = $data[$subKey];
            } elseif (
                $subKey == '' &&
                is_array($data)
            ) {
                $result = $data;
            }
        } else if ($subKey == '') {
            $result = [];
        }

        return $result;
    }
}

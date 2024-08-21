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
     */
    public function __construct($setCookie = true)
    {
        if (basename($_SERVER['PHP_SELF']) !== 'phpunit') {
            $this->frontendUser = $GLOBALS['TYPO3_REQUEST']->getAttribute('frontend.user');

            if (empty($this->frontendUser)) {
                throw new \RuntimeException('Extension ' . DIV2007_EXT . ' Typo3SessionHandler: Empty attribute frontend.user' . $detail . ' ', 1612216764);
            }

            if ($setCookie) {
                $this->allowCookie();
            }
        }
    }

    public function allowCookie(): void
    {
        $vars = get_class_vars(get_class($this->frontendUser));

        if (isset($vars['dontSetCookie'])) {
            $this->frontendUser->dontSetCookie = false;
        }
    }

    /**
     * Set session data.
     */
    public function setSessionData($data): void
    {
        if (
            empty($GLOBALS['TYPO3_CONF_VARS']['FE']['maxSessionDataSize']) ||
            empty($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][DIV2007_EXT]['checkCookieSet']) ||
            $this->frontendUser->isCookieSet()
        ) {
            if (!is_array($data)) {
                $data = [];
            }
            $sessionKey = $this->getSessionKey();
            $this->frontendUser->setAndSaveSessionData($sessionKey, $data);
        }
    }

    /**
     * Get session data.
     *
     * @return array data The session data
     */
    public function getSessionData($subKey = '')
    {
        $result = [];
        $sessionKey = $this->getSessionKey();
        $data = $this->frontendUser->getSessionData($sessionKey);

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

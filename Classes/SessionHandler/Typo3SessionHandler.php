<?php

declare(strict_types=1);

namespace JambageCom\Div2007\SessionHandler;

use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Core\Session\UserSession;

class Typo3SessionHandler extends AbstractSessionHandler implements SessionHandlerInterface
{
    protected string $sessionKey = self::class;
    protected ?FrontendUserAuthentication $frontendUser = null;
    protected ?UserSession $userSession = null;

    /**
     * Constructor for TYPO3 13 and v14.
     * Allows an empty user object to prevent exceptions during early bootstrapping.
     */
    public function __construct(
        ?FrontendUserAuthentication $frontendUser = null,
        bool $setCookie = true
    ) {
        if ($frontendUser !== null) {
            $this->frontendUser = $frontendUser;

            // TYPO3 13/14 API: Access the UserSession object safely via property or method
            $this->userSession = method_exists($frontendUser, 'getSession')
                ? $frontendUser->getSession()
                : ($frontendUser->user ?? null);
        }
    }

    /**
     * Set session data.
     */
    public function setSessionData(array $data): void
    {
        // If no user or session is initialized, we cannot store anything
        if ($this->userSession === null || $this->frontendUser === null) {
            return;
        }

        $sessionKey = $this->getSessionKey();

        // Modernized call for UserSession state updates
        $this->userSession->set($sessionKey, $data);
        $this->frontendUser->storeSessionData();
    }

    /**
     * Get session data.
     */
    public function getSessionData(string $subKey = ''): mixed
    {
        // If no session exists (yet), return an empty fallback structure
        if ($this->userSession === null) {
            return $subKey === '' ? [] : '';
        }

        $sessionKey = $this->getSessionKey();
        $data = $this->userSession->get($sessionKey);

        if (is_array($data)) {
            if ($subKey !== '' && isset($data[$subKey])) {
                return $data[$subKey];
            }
            if ($subKey === '') {
                return $data;
            }
        }

        return $subKey === '' ? [] : '';
    }
}

<?php

namespace JambageCom\Div2007\Middleware;

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

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Routing\SiteMatcher;
use TYPO3\CMS\Core\Routing\SiteRouteResult;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;



/**
 * Stores the original request for an Ajax call before processing a request for the TYPO3 Frontend.
 *
 */
class StoreRequest implements MiddlewareInterface
{
    /**
     * Hook to store the current request
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // This is a safety net, see RequestHandler for how this is validated.
        $request = $request->withAttribute('_originalGetParameters', $_GET);
        if ($request->getMethod() === 'POST') {
            $request = $request->withAttribute('_originalPostParameters', $_POST);
        }
        $typo3Version = GeneralUtility::makeInstance(Typo3Version::class);
        $version = $typo3Version->getVersion();

        if (
            version_compare($version, '12.0.0', '>=')
        ) {
            $container = GeneralUtility::getContainer();
            $contextFactory = $container->get(\TYPO3\CMS\Core\Routing\RequestContextFactory::class);
            $matcher = GeneralUtility::makeInstance(
                SiteMatcher::class,
                GeneralUtility::makeInstance(SiteFinder::class),
                $contextFactory
            );
        } else {        
            $matcher = GeneralUtility::makeInstance(
                SiteMatcher::class,
                GeneralUtility::makeInstance(SiteFinder::class)
            );
        }

        /** @var SiteRouteResult $routeResult */
        $routeResult = $matcher->matchRequest($request);
        $extendedRequest = $request->withAttribute('site', $routeResult->getSite());
        $extendedRequest = $extendedRequest->withAttribute('language', $routeResult->getLanguage());
        $extendedRequest = $extendedRequest->withAttribute('routing', $routeResult);

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['div2007']['TYPO3_REQUEST'] = $extendedRequest;

        return $handler->handle($request);
    }
}

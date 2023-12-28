<?php

use JambageCom\Div2007\Middleware\StoreRequest;
return [
    'frontend' => [
        'jambagecom/div2007/preprocessing' => [
            'target' => StoreRequest::class,
            'description' => 'The Ajax feature needs the original request object in order to determine the page id out of the speaking url',
            'before' => [
                'typo3/cms-frontend/site',
            ],
        ],
    ],
];

<?php

return [
    'frontend' => [
        'jambagecom/cms-frontend/preprocessing' => [
            'target' => \JambageCom\Div2007\Middleware\StoreRequest::class,
            'description' => 'The Ajax feature needs the original request object in order to determine the page id out of the speaking url',
            'after' => [
                'typo3/cms-core/normalized-params-attribute',
            ],
            'before' => [
                'typo3/cms-frontend/eid'
            ]
        ]
    ]
];


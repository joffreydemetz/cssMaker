<?php
$basePath = realpath(__DIR__ . '/') . DIRECTORY_SEPARATOR . 'less' . DIRECTORY_SEPARATOR;

return [
    'fonts' => [
        'lato/regular',
    ],

    'glyphicons' => [
        'glyphicons' => [
            'music',
            'heart',
            'heart-empty',
            // 'inexistant',
        ],
        'halflings' => [
            'cog',
            'file',
        ],
        'filetypes' => [],
        'social' => [],
    ],

    'flags' => [],

    'less' => [
        'variables' => [
            $basePath . 'core' . DIRECTORY_SEPARATOR . 'variables.yml',
        ],
        'mixins' => [
            $basePath . 'core' . DIRECTORY_SEPARATOR . 'mixins.less',
            $basePath . 'core' . DIRECTORY_SEPARATOR . 'mixins' . DIRECTORY_SEPARATOR . 'gradients.less',
            $basePath . 'core' . DIRECTORY_SEPARATOR . 'mixins' . DIRECTORY_SEPARATOR . 'icons.less',
            $basePath . 'core' . DIRECTORY_SEPARATOR . 'mixins' . DIRECTORY_SEPARATOR . 'lists.less',
            $basePath . 'core' . DIRECTORY_SEPARATOR . 'mixins' . DIRECTORY_SEPARATOR . 'vendored.less',
        ],
        'normalize' => [
            $basePath . 'normalize' . DIRECTORY_SEPARATOR . 'necolas.less',
            $basePath . 'normalize' . DIRECTORY_SEPARATOR . 'sindresorhus.less',
        ],
        'structure' => [
            $basePath . 'core' . DIRECTORY_SEPARATOR . 'structure.less',
        ],
        'mobile' => [
            $basePath . 'core' . DIRECTORY_SEPARATOR . 'mobile.less',
        ],
        'screen' => [
            $basePath . 'core' . DIRECTORY_SEPARATOR . 'screen.less',
        ],
        'queries' => [
            $basePath . 'core' . DIRECTORY_SEPARATOR . 'queries.less',
        ],
        'print' => [
            $basePath . 'normalize' . DIRECTORY_SEPARATOR . 'print.less',
            $basePath . 'core' . DIRECTORY_SEPARATOR . 'print.less',
        ],
    ],
];

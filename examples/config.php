<?php
$basePath = realpath(__DIR__ . '/');

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
            $basePath . '/less/core/variables.yml',
        ],
        'mixins' => [
            $basePath . '/less/core/mixins.less',
            $basePath . '/less/core/mixins/gradients.less',
            $basePath . '/less/core/mixins/icons.less',
            $basePath . '/less/core/mixins/lists.less',
            $basePath . '/less/core/mixins/vendored.less',
        ],
        'normalize' => [
            $basePath . '/less/normalize/necolas.less',
            $basePath . '/less/normalize/sindresorhus.less',
        ],
        'structure' => [
            $basePath . '/less/core/structure.less',
        ],
        'mobile' => [
            $basePath . '/less/core/mobile.less',
        ],
        'screen' => [
            $basePath . '/less/core/screen.less',
        ],
        'queries' => [
            $basePath . '/less/core/queries.less',
        ],
        'print' => [
            $basePath . '/less/normalize/print.less',
            $basePath . '/less/core/print.less',
        ],
    ],
];

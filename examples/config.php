<?php
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
            CSSMAKER_BASEPATH . '/less/core/variables.yml',
        ],
        'mixins' => [
            CSSMAKER_BASEPATH . '/less/core/mixins.less',
            CSSMAKER_BASEPATH . '/less/core/mixins/gradients.less',
            CSSMAKER_BASEPATH . '/less/core/mixins/icons.less',
            CSSMAKER_BASEPATH . '/less/core/mixins/lists.less',
            CSSMAKER_BASEPATH . '/less/core/mixins/vendored.less',
        ],
        'normalize' => [
            CSSMAKER_BASEPATH . '/less/normalize/necolas.less',
            CSSMAKER_BASEPATH . '/less/normalize/sindresorhus.less',
        ],
        'structure' => [
            CSSMAKER_BASEPATH . '/less/core/structure.less',
        ],
        'mobile' => [
            CSSMAKER_BASEPATH . '/less/core/mobile.less',
        ],
        'screen' => [
            CSSMAKER_BASEPATH . '/less/core/screen.less',
        ],
        'queries' => [
            CSSMAKER_BASEPATH . '/less/core/queries.less',
        ],
        'print' => [
            CSSMAKER_BASEPATH . '/less/normalize/print.less',
            CSSMAKER_BASEPATH . '/less/core/print.less',
        ],
    ],
];

<?php

return [
    'tokens' => [
        'slate' => ['hex' => '#64748b', 'rgb' => '100, 116, 139'],
        'orange' => ['hex' => '#f97316', 'rgb' => '249, 115, 22'],
        'cyan' => ['hex' => '#06b6d4', 'rgb' => '6, 182, 212'],
        'lime' => ['hex' => '#84cc16', 'rgb' => '132, 204, 22'],
        'blue' => ['hex' => '#3b82f6', 'rgb' => '59, 130, 246'],
        'amber' => ['hex' => '#f59e0b', 'rgb' => '245, 158, 11'],
        'red' => ['hex' => '#ef4444', 'rgb' => '239, 68, 68'],
        'emerald' => ['hex' => '#10b981', 'rgb' => '16, 185, 129'],
        'violet' => ['hex' => '#8b5cf6', 'rgb' => '139, 92, 246'],
        'teal' => ['hex' => '#14b8a6', 'rgb' => '20, 184, 166'],
        'fuchsia' => ['hex' => '#d946ef', 'rgb' => '217, 70, 239'],
    ],

    'status' => [
        'default' => 'slate',
        'rules' => [
            ['needles' => ['backlog'], 'color' => 'slate'],
            ['needles' => ['todo', 'to_do', 'to-do'], 'color' => 'slate'],
            ['needles' => ['doing', 'inprogress', 'in_progress', 'wip', 'progress'], 'color' => 'slate'],
            ['needles' => ['done', 'closed', 'complete', 'resolved'], 'color' => 'slate'],
        ],
    ],

    'priority' => [
        'default' => 'amber',
        'rules' => [
            ['needles' => ['low'], 'color' => 'blue'],
            [
                'needles' => ['medium', 'normal'],
                'color' => 'amber',
                'style' => [
                    'font_weight' => 500,
                    'border_width' => '1px',
                    'border_alpha' => 0.5,
                ],
            ],
            ['needles' => ['high'], 'color' => 'orange'],
            ['needles' => ['urgent', 'critical', 'blocker'], 'color' => 'red'],
        ],
    ],

    'category' => [
        'default' => 'slate',
        'rules' => [
            ['needles' => ['bug', 'defect', 'error', 'fix'], 'color' => 'red'],
            ['needles' => ['feature', 'story', 'enhancement'], 'color' => 'violet'],
            ['needles' => ['support', 'help', 'ticket'], 'color' => 'teal'],
            ['needles' => ['task', 'maintenance', 'ops', 'devops', 'infra'], 'color' => 'slate'],
            ['needles' => ['qa', 'test'], 'color' => 'emerald'],
            ['needles' => ['doc', 'documentation'], 'color' => 'blue'],
            ['needles' => ['research', 'spike'], 'color' => 'cyan'],
            ['needles' => ['meeting', 'call'], 'color' => 'amber'],
            ['needles' => ['billing', 'invoice', 'payment', 'finance'], 'color' => 'fuchsia'],
        ],
    ],
];

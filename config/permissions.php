<?php

return [

    'permissions' => [
        ['name' => 'Dashboard View',         'code' => 'dashboard.view',     'module' => 'dashboard'],
        ['name' => 'Reports View',           'code' => 'reports.view',       'module' => 'reports'],

        ['name' => 'Planning Manage',        'code' => 'planning.manage',    'module' => 'planning'],
        ['name' => 'Planning View',          'code' => 'planning.view',      'module' => 'planning'],

        ['name' => 'Risk Manage',            'code' => 'risk.manage',        'module' => 'risk'],
        ['name' => 'Risk View',              'code' => 'risk.view',          'module' => 'risk'],

        ['name' => 'OGB Manage',             'code' => 'ogb.manage',         'module' => 'ogb'],
        ['name' => 'OGB View',               'code' => 'ogb.view',           'module' => 'ogb'],

        ['name' => 'HSSE Manage',            'code' => 'hsse.manage',        'module' => 'hsse'],
        ['name' => 'HSSE View',              'code' => 'hsse.view',          'module' => 'hsse'],
        ['name' => 'HSSE Target',            'code' => 'hsse.target',        'module' => 'hsse'],

        ['name' => 'Compliance Manage',      'code' => 'compliance.manage',  'module' => 'compliance'],
        ['name' => 'Compliance View',        'code' => 'compliance.view',    'module' => 'compliance'],

        ['name' => 'Master Data Manage',     'code' => 'masterdata.manage',  'module' => 'master'],
        ['name' => 'Users Manage',           'code' => 'users.manage',       'module' => 'master'],
    ],

    'roles' => [
        'admin' => [
            'dashboard.view',
            'reports.view',
            'planning.manage', 'planning.view',
            'risk.manage', 'risk.view',
            'ogb.manage', 'ogb.view',
            'hsse.manage', 'hsse.view', 'hsse.target',
            'compliance.manage', 'compliance.view',
            'masterdata.manage',
            'users.manage',
        ],
        'manager' => [
            'dashboard.view',
            'reports.view',
            'planning.manage', 'planning.view',
            'risk.manage', 'risk.view',
            'ogb.manage', 'ogb.view',
            'hsse.manage', 'hsse.view', 'hsse.target',
            'compliance.manage', 'compliance.view',
        ],
        'editor' => [
            'dashboard.view',
            'planning.manage', 'planning.view',
            'risk.manage', 'risk.view',
            'ogb.manage', 'ogb.view',
            'hsse.manage', 'hsse.view',
            'compliance.manage', 'compliance.view',
        ],
        'viewer' => [
            'dashboard.view',
            'planning.view',
            'risk.view',
            'ogb.view',
            'hsse.view',
            'compliance.view',
        ],
    ],
];
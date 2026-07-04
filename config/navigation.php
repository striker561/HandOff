<?php

return [

    /*
    |--------------------------------------------------------------------------
    | App Sidebar Navigation
    |--------------------------------------------------------------------------
    |
    | Each variant maps to the shell layout (agency = admin, portal = client).
    | Add new features here — no need to edit sidebar Blade partials.
    |
    | Item keys:
    | - label: translation string
    | - icon: Flux icon name
    | - route: named route (enables wire:navigate + href)
    | - active: route pattern for :current (defaults to route name)
    | - badge: optional badge label (e.g. "Soon")
    | - disabled: placeholder items without a route yet
    |
    | Group keys:
    | - heading: translation string
    | - expandable: optional, shows collapsible group
    | - icon: group icon when expandable
    | - items: list of nav items
    |
    */

    'agency' => [
        'groups' => [
            [
                'heading' => 'Workspace',
                'items' => [
                    [
                        'label' => 'Dashboard',
                        'icon' => 'home',
                        'route' => 'dashboard',
                        'active' => 'dashboard',
                    ],
                ],
            ],
            [
                'heading' => 'Agency',
                'expandable' => true,
                'icon' => 'building-office-2',
                'items' => [
                    [
                        'label' => 'Clients',
                        'icon' => 'users',
                        'badge' => 'Soon',
                        'disabled' => true,
                    ],
                    [
                        'label' => 'Projects',
                        'icon' => 'folder',
                        'badge' => 'Soon',
                        'disabled' => true,
                    ],
                ],
            ],
        ],
    ],

    'portal' => [
        'groups' => [
            [
                'heading' => 'Workspace',
                'items' => [
                    [
                        'label' => 'Dashboard',
                        'icon' => 'home',
                        'route' => 'dashboard',
                        'active' => 'dashboard',
                    ],
                ],
            ],
            [
                'heading' => 'Projects',
                'items' => [
                    [
                        'label' => 'My projects',
                        'icon' => 'folder',
                        'badge' => 'Soon',
                        'disabled' => true,
                    ],
                ],
            ],
        ],
    ],

];

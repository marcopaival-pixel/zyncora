<?php

return [
    'components' => [
        'pagination' => [
            'overview' => 'A mostrar :first a :last de :total resultados',
            'fields' => [
                'records_per_page' => [
                    'label' => 'Registos por página',
                ],
            ],
            'actions' => [
                'go_to_page' => [
                    'label' => 'Ir para a página :page',
                ],
                'next' => [
                    'label' => 'Seguinte',
                ],
                'previous' => [
                    'label' => 'Anterior',
                ],
            ],
        ],
    ],
    'actions' => [
        'create' => [
            'single' => [
                'label' => 'Criar :label',
            ],
            'modal' => [
                'heading' => 'Criar :label',
            ],
        ],
        'edit' => [
            'single' => [
                'label' => 'Editar :label',
            ],
        ],
        'delete' => [
            'single' => [
                'label' => 'Eliminar',
            ],
        ],
    ],
];

<?php

return [
    'ImageOptions' => [
        'width' => [
            'label' => "Largeur",
            'type' => "text",
        ],
        'height' => [
            'label' => "hauteur",
            'type' => "text",
        ],
        'crop' => [
            'label' => "Comment ajuster l'image ?",
            'type' => 'dropdown',
            'options' => [
                'fill' => 'fill : ajuster',
                'crop' => 'crop : couper',
                'scale' => 'Scale : deformer',
                'pad' => 'Pad : adapter',
                'fit' => 'fit : tenir',
            ],
        ],
        'gravity' => [
            'label' => "Gravity",
            'type' => 'dropdown',
            'options' => [
                'center' => 'center',
                'face' => 'face',
                'faces' => 'faces',
                'north_west' => 'north_west',
                'north	' => 'north',
                'north_east' => 'north_east',
                'west' => 'west',
                'center' => 'center',
                'east' => 'east',
                'south_west' => 'south_west',
                'south' => 'south',
                'south_east' => 'south_east',
                'west' => 'west',
            ],
        ],
    ],
];

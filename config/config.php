<?php

return [
    'ImageOptions' => [
        'width' => [
            'label' => "Largeur",
            'type' => "text",
            'span' => 'left',
        ],
        'height' => [
            'label' => "hauteur",
            'type' => "text",
            'span' => 'right',
        ],
        'crop' => [
            'label' => "Comment ajuster l'image ?",
            'type' => 'dropdown',
            'span' => 'left',
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
            'span' => 'right',
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

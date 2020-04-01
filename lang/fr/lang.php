<?php

return [
    'menu' => [
        'label' => 'Montage photos',
        'description' => "Gestion des montages photos pour les models de l'application",
        'settings' => "Optiosn cloudis",
        'settings_description' => "Réglage des valeurs par defaut",
        'settings_category' => 'Wakaari Modèle',
        'settings_category_options' => 'Wakaari Options',
    ],
    'settings' => [
        'category' => 'Cloudi',
        'label' => "Options Cloudi",
        'cloudinary_path' => 'Chemin cloudinary',
        'unknown' => "Image de remplacement pour les assets manquant",
        'unknown_com' => "Pour remplacer l'image vous devez choisir une image avec un nom different.",
        'primary_color' => "Couleur primaire",
        'secondary_color' => "Couleur secondaire",
    ],
    'montage' => [
        'name' => 'Nom du montage',
        'slug' => 'Slug',
        'active' => 'Actif ?',
        'auto_create' => 'Création automatique',
        'data_source' => 'Source des données',
        'cloudi_path' => 'Chemin sur cloudi',
        'options' => 'Options',
        'src' => 'Source du montage',
        'data_source_placeholder' => '--Choisissez une source--',
        'use_files' => "Utiliser des fichiers pour le montage",
    ],
    'popup' => [
        'title' => 'Montage cloudis',
    ],
    'formwidget' => [
        'title' => 'Montage cloudis',
        'checking' => 'Vérifier les montages',
        'checking_indicator' => 'Vérification en cours',
    ],

];

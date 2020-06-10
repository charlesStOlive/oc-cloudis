<?php

return [
    'menu' => [
        'label' => 'Montage photos',
        'description' => "Gestion des montages photos pour les models de l'application",
        'settings' => "Options Images",
        'settings_description' => "Réglage des valeurs par defaut ( images, couleurs, image manquante)",
        'settings_category' => 'Wakaari Modèle',
        'settings_category_options' => 'Wakaari Options',
    ],
    'settings' => [
        'category' => 'Cloudi',
        'label' => "Options Cloudi",
        'cloudinary_path' => 'Chemin cloudinary',
        'logo' => 'Logo',
        'logo_com' => 'Cette image sera disponible dans word, excel, etc.',
        'unknown' => "Image manquante",
        'unknown_com' => "Image de remplacement pour les assets manquant",
        'primary_color' => "Couleur primaire",
        'secondary_color' => "Couleur secondaire",
        'police_1' => "Police pour les montages",
        'police_1_com' => "Code à utiliser : {nom_du_chemin}:police:police1",
        'police_2' => "Police 2 pour les montages",
        'police_2_com' => "Code à utiliser : {nom_du_chemin}:police:police2",
        'police_3' => "Police 3 pour les montages",
        'police_3_com' => "Code à utiliser : {nom_du_chemin}:police:police3",

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

<?php

return [
    /*
    |--------------------------------------------------------------------------
    | OFA Default Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains default configuration for OFA features. Admins can
    | publish this config and modify as needed.
    |
    */

    'enabled' => true,

    'branding' => [
        'panel_name' => 'Dark Coder',
        'powered_by' => 'Pterodactyl®',
        'copyright' => '© Dark Coder (Amrit Yadav). All Rights Reserved.',
    ],

    'features' => [
        'plugin_installer' => true,
        'mod_installer' => true,
        'world_manager' => true,
        'recycle_bin' => true,
        'ai_suggestions' => false, // admin can enable
    ],
];

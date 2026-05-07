<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Asset bundle for Recipe and Event views
 */
class RecipeEventAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $css = [
        // Load framework and shared assets first, sidebar next, then page-specific overrides last
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
        'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css',
        'css/sidebar.css',
        'css/recipe-event.css',
        'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css',
    ];

    public $js = [
        'js/event-location-autocomplete.js',
        'https://cdn.jsdelivr.net/npm/flatpickr',
    ];

    public $depends = [
        'yii\\web\\YiiAsset',
        'yii\\bootstrap5\\BootstrapAsset',
        'yii\\bootstrap5\\BootstrapPluginAsset',
    ];
}

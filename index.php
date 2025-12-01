<?php

use Kirby\Cms\App;
use Kirby\Data\Yaml;

App::plugin('tobimori/color-palette', [
    'fields' => [
        'color-palette' => [
            'extends'  => 'radio',
            'props'    => [
                'options' => function ($options = []) {
                    return $options;
                },
                'display' => function ($display = 'single') {
                    return $display;
                },
                'size' => function ($size = 'medium') {
                    return $size;
                },
                'unselect' => function ($unselect = false) {
                    return $unselect;
                },
                'default' => function ($default = false) {
                    return $default;
                },
                'limit' => function ($limit = 10) {
                    return $limit;
                },
                'value' => function ($value = null) {
                    if ($value === false) {
                        $value = null;
                    };
                    $yaml = Yaml::decode($value);
                    return count($yaml) ? $yaml : $value;
                },
            ],
            'computed' => [
                'uri' => function () {
                    return $this->model()->uri();
                },
                'parent' => function () {
                    return $this->model()->apiUrl(true);
                },
                'options' => function () {
                    $options = $this->options;

                    if ($options == 'query') {
                        return $this->getOptions();
                    }

                    return $options;
                },
                'default' => function () {
                    return $this->default;
                },
                'value' => function () {
                    return $this->value;
                }
            ],
        ],
    ],
    'translations' => [
        'en' => require_once __DIR__ . '/languages/en.php',
        'de' => require_once __DIR__ . '/languages/de.php',
        'fr' => require_once __DIR__ . '/languages/fr.php',
    ],
]);

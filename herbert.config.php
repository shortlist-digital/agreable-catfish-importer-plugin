<?php


return [

    /**
     * The Herbert version constraint.
     */
    'constraint' => '~0.9.9',

    /**
     * Auto-load all required files.
     */
    'requires' => [
      __DIR__ . '/app/custom-fields/acf.php',
      __DIR__ . '/app/hooks.php'
    ],

    /**
     * The tables to manage.
     */
    'tables' => [
    ],

    /**
     * Activate
     */
    'activators' => [
        __DIR__ . '/app/activate.php'
    ],

    /**
     * Activate
     */
    'deactivators' => [],

    /**
     * The shortcodes to auto-load.
     */
    'shortcodes' => [],

    /**
     * The widgets to auto-load.
     */
    'widgets' => [],

    /**
     * The widgets to auto-load.
     */
    'enqueue' => [
        __DIR__ . '/app/enqueue.php'
    ],

    /**
     * The routes to auto-load.
     */
    'routes' => [
        'AgreableCatfishImporterPlugin' => __DIR__ . '/app/routes.php'
    ],

    /**
     * The panels to auto-load.
     */
    'panels' => [
        'AgreableCatfishImporterPlugin' => __DIR__ . '/app/panels.php'
    ],

    /**
     * The API to auto-load.
     */
    'apis' => [
        'AgreableCatfishImporterPlugin' => __DIR__ . '/app/api.php'
    ],

    /**
     * The view paths to register.
     *
     * E.G: 'AgreableCatfishImporterPlugin' => __DIR__ . '/views'
     * can be referenced via @AgreableCatfishImporterPlugin/
     * when rendering a view in twig.
     */
    'views' => [
        'AgreableCatfishImporterPlugin' => __DIR__ . '/resources/views'
    ],

    /**
     * The view globals.
     */
    'viewGlobals' => [

    ],

    /**
     * The asset path.
     */
    'assets' => '/resources/assets/'

];

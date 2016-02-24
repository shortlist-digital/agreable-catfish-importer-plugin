<?php namespace AgreableCatfishImporterPlugin;

/** @var \Herbert\Framework\Panel $panel */

$options_page_name = 'acf-options';

if( function_exists('register_field_group') ):

register_field_group(array (
  'key' => 'group_catfish_importer_plugin',
  'title' => 'Catfish Importer Credentials',
  'fields' => array (
    array (
      'key' => 'apple_news_url',
      'label' => 'Apple News Main URL',
      'name' => 'apple_news_url',
      'type' => 'url',
      'instructions' => 'Please put in either http://www.stylist.co.uk/ or http://www.shortlist.com/',
      'required' => 0,
      'conditional_logic' => 0,
      'wrapper' => array (
        'width' => '',
        'class' => '',
        'id' => '',
      ),
      'default_value' => '',
      'placeholder' => '',
      'prepend' => '',
      'append' => '',
      'maxlength' => '',
      'readonly' => 0,
      'disabled' => 0,
    )
  ),
  'location' => array (
    array (
      array (
        'param' => 'options_page',
        'operator' => '==',
        'value' => $options_page_name,
      ),
    ),
  ),
  'menu_order' => 10,
  'position' => 'normal',
  'style' => 'default',
  'label_placement' => 'top',
  'instruction_placement' => 'label',
  'hide_on_screen' => '',
));

endif;

$panel->add([
  'type'   => 'panel',
  'as'     => 'mainPanel',
  'title'  => 'Catfish Importer',
  'rename' => 'Recent imports',
  'slug'   => 'catfish-importer-index',
  'icon'   => 'dashicons-download',
  'uses'   => __NAMESPACE__ . '\Controllers\AdminController@index'
]);

$panel->add([
  'type'   => 'sub-panel',
  'parent' => 'mainPanel',
  'as'     => 'syncPanel',
  'title'  => 'Sync',
  'slug'   => 'sync',
  'uses'   => __NAMESPACE__ . '\Controllers\AdminController@sync'
]);


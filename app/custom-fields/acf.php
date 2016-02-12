<?php

if( function_exists('acf_add_local_field_group') ):

$field['group'] = 'article_basic_group';
$field['parent'] = 'article_basic_group';
$field['key'] = 'article_catfish-importer_imported';
$field['name'] = 'catfish-importer_imported';
$field['label'] = 'Catfish imported';
$field['type'] = 'true_false';
$field['default_value'] = 0;
$field['layout'] = 'vertical';

$field['instructions'] = 'Is this post imported from Catfish?';
$field['message'] = '';

acf_add_local_field_group(array (
  'key' => 'article_catfish-importer_group',
  'title' => 'Catfish Importer',
  'fields' => array (
    array (
      'key' => 'article_catfish-importer_imported',
      'label' => 'Imported',
      'name' => 'catfish-importer_imported',
      'type' => 'true_false',
      'instructions' => 'Is this post imported from Catfish?',
      'required' => 0,
      'conditional_logic' => 0,
      'wrapper' => array (
        'width' => '33%',
        'class' => '',
        'id' => '',
      ),
      'message' => '',
      'default_value' => 0,
    ),
    array (
      'key' => 'article_catfish-importer_date-updated',
      'label' => 'Updated',
      'name' => 'catfish-importer_date-updated',
      'type' => 'date_time_picker',
      'instructions' => 'When did the importer update this post?',
      'required' => 0,
      'conditional_logic' => 0,
      'wrapper' => array (
        'width' => '33%',
        'class' => '',
        'id' => '',
      ),
      'show_date' => 'true',
      'date_format' => 'yy-m-d',
      'time_format' => 'h:mm tt',
      'show_week_number' => 'false',
      'picker' => 'slider',
      'save_as_timestamp' => 'true',
      'get_as_timestamp' => 'true',
    ),
    array (
      'key' => 'article_catfish-importer_url',
      'label' => 'URL',
      'name' => 'catfish-importer_url',
      'type' => 'url',
      'instructions' => 'The URL from imported from',
      'required' => 0,
      'conditional_logic' => 0,
      'wrapper' => array (
        'width' => '33%',
        'class' => '',
        'id' => '',
      ),
      'default_value' => '',
      'placeholder' => '',
    ),

  ),
  'location' => array (
    array (
      array (
        'param' => 'post_type',
        'operator' => '==',
        'value' => 'post',
      ),
    ),
  ),
  'menu_order' => 0,
  'position' => 'normal',
  'style' => 'default',
  'label_placement' => 'top',
  'instruction_placement' => 'label',
  'hide_on_screen' => '',
));

endif;

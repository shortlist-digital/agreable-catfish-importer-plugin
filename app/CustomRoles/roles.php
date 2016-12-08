<?php
add_action('admin_init', function() {
$role_id = 'catfish_editor';
if (!get_role($role_id)) {
    // Add Catfish editor role
    add_role($role_id,
      'Catfish Editor',
      array(
        'read' => true,
        'edit_posts' => true,
        'delete_posts' => true,
        'publish_posts' => true,
        'upload_files' => true,
      )
    );
  }
  $role = get_role($role_id);
  $role->add_cap('manage_options');
});

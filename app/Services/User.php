<?php
namespace AgreableCatfishImporterPlugin\Services;

class User {

  public static function checkUserByEmail($email) {
    $user_id = false;
    $user = get_user_by('email', $email);
    if ($user) {
      $user_id = $user->ID;
    }
    return $user_id;
  }

  public static function insertCatfishUser($object) {
    $user_data = array(
      'user_login' => $object->slug,
      'user_nicename' => $object->slug,
      'user_email' => $object->emailAddress,
      'display_name' => $object->name,
      'description' => $object->biography,
      'role' => 'editor',
      'user_pass' => null
    );
    $user_id = wp_insert_user( $user_data ) ;
    return $user_id;
  }

}

<?php
/**
    * Plugin Name: tappd-functions
    * Plugin URI: https://www.tappd.co.za/
    * Description: Default.
    * Version: 0.2
    * Author: Jason Waugh
    * Author URI: none
    */

// Global Vars --------------------------------------------------------------------------------------------------
$POST_TYPE = "Users";
// $PUBLISHED_SLUGS = array(
//     "users",
//     "sign-up"
// );
$timezone = new DateTimeZone( 'Africa/Johannesburg' );

// Web hook Initializers ------------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------------------------------

// Initial card URI redirect
add_action('init', 'is_user_registered');

// Sign up Form profile picture upload ajax request
Add_action('wp_ajax_update_pp', 'update_pp');
Add_action('wp_ajax_nopriv_update_pp', 'update_pp');
// Elementor Sign up page form submition processing
add_action( 'template_redirect', 'my_logged_in_redirect' );

function my_logged_in_redirect() {

	if ( is_user_logged_in() && is_page( 12 ) )
    {
        wp_redirect( get_permalink( 32 ) );
        die;
    }

}

add_action( 'elementor_pro/forms/new_record' , function ( $record, $handler ){

    $form_name = $record->get_form_settings( 'form_name' );

	if ( 'Form Testing' !== $form_name ) {
		return;
	}

	$raw_fields = $record->get( 'fields' );
	$fields = [];
	foreach ( $raw_fields as $id => $field ) {
		$fields[ $id ] = $field['value'];
	}

    $error = update_post_object( $fields );

    if ($error == 0)
    {
        $handler->add_error_message( "User Registration Failed" );
    }else {

        $redirect_url = get_site_url() . "/users/" . $fields['userid'] . "/";
        $redirect_to = $record->replace_setting_shortcodes( $redirect_url );
        nocache_headers();
        $handler->add_response_data( 'redirect_url', $redirect_to );
    }

}, 10, 2 );

// -------------------------------------------------------------------------------------------------------------------
//
function get_current_slug(){
    return explode("/", $_SERVER['REQUEST_URI'] )[1];
}

function redirect ( $slug ) {
    wp_redirect ( get_site_url().$slug );
    exit();
}

function get_post_object( $user_id ){
    $post_object = get_page_by_path($user_id, 'OBJECT', "Users");
    if( $post_object === null ){
        nocache_headers();
        wp_safe_redirect( get_site_url() , 302);
        exit();
    }
    return $post_object;
}
add_action( 'user_register', 'myplugin_registration_save', 10, 1 );

function myplugin_registration_save( $user_id ) {
  echo "Got here to save";
  $timezone = new DateTimeZone( 'Africa/Johannesburg' );
  $log_timestamp_var = wp_date("d-m-Y H:i:s", null, $timezone );
  $log_message_var = "[ $log_timestamp_var ] function call : myplugin_registration_save() ----------------------- \n";
  $log_message_var = $log_message_var . "[ $log_timestamp_var ] [INFO] Request user ID  : $user_id \n";


  $all_meta_for_user = array_map( function( $a ){ return $a[0]; }, get_user_meta( $user_id ) );
  foreach($all_meta_for_user as $key => $val) {
    $log_message_var = $log_message_var . "[ $log_timestamp_var ] [INFO] $key : $val \n";
  }
  add_user_meta( $user_id, 'userid', $user_id_meta);
  $tel = $all_meta_for_user['contact_number'];
  update_user_meta( $user_id, 'tel_url', "tel:$tel");
  $mail = $all_meta_for_user['email'];
  update_user_meta( $user_id, 'emailcommand', "mailto:$mail");
  $wa = $all_meta_for_user['contact_number'];
  $waw = str_replace("0", "+27", $wa);
  update_user_meta( $user_id, 'whatsapp_url', "https://https://api.whatsapp.com/send?phone=$waw");


  echo $all_meta_for_user['nickname'];
  $username_meta = str_replace(" ", "-" , $all_meta_for_user['nickname']);
  //$log_message_var = $log_message_var . "[ $log_timestamp_var ] function call : myplugin_registration_save() : user meta Username retrieved -> $userMeta  \n";


  // //$log_message_var = $log_message_var . "[ $log_timestamp_var ] [INFO] Request URI : $uri_landing \n";
  // $username_meta = get_user_meta($user_id , 'field_633ea502ba68b' , true);
  if( ! $username_meta == "" && ! $username_meta == NULL ):
    $uri = get_site_url() . "/author" . "/" . $username_meta;
    $log_message_var = $log_message_var . "[ $log_timestamp_var ] [INFO] myplugin_registration_save : condition met : Username registered and set $user_id \n";
    $log_message_var = $log_message_var . "[ $log_timestamp_var ] [INFO] myplugin_registration_save : forwarding to $uri \n";
    log_to_console($log_message_var, "myplugin_registration_save" );
    nocache_headers();
    wp_safe_redirect( $uri , 302);
    exit();
  endif;

  log_to_console($log_message_var, "myplugin_registration_save" );

}

function is_user_registered(){
    $timezone = new DateTimeZone( 'Africa/Johannesburg' );
    $current_preg_match_regex = "/\/user\/[0-9]+/";
    $uri_landing = $_SERVER['REQUEST_URI'];
    // validate uri --
    $log_timestamp_var = wp_date("d-m-Y H:i:s", null, $timezone );
    $log_message_var = "[ $log_timestamp_var ] function call : is_user_registered() ----------------------- \n";
    $log_message_var = $log_message_var . "[ $log_timestamp_var ] [INFO] Request URI : $uri_landing \n";


    if ( preg_match($current_preg_match_regex, $_SERVER['REQUEST_URI']) )
    {
        $log_timestamp_var = wp_date("d-m-Y H:i:s", null, $timezone );
        $log_message_var = $log_message_var . "[ $log_timestamp_var ] is_user_registered() : condition met : preg_match( $current_preg_match_regex ) = TRUE \n";

        $user_id = explode("/", $_SERVER['REQUEST_URI'] )[2];


        // $post_object = get_post_object( $user_id );
        // if ( !get_post_meta($post_object->ID, 'registered', true) )
        // {
        //     nocache_headers();
        //     wp_safe_redirect( get_site_url()."/wp-login.php?action=register&userName=".$user_id , 302);
        //     exit();
        // }
        // $id_int = (int)$user_id;
        // $user = get_user_by("userid", $user_id);
        // var_dump($user);
        // WP_User_Query arguments
// The search term
      $search_term = $user_id;

// WP_User_Query arguments
        $args = array (
            'role' => 'author',
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key'     => 'userid',
                    'value'   => $search_term,
                    'compare' => '='
                )
            )
        );

        // Create the WP_User_Query object
        $wp_user_query = new WP_User_Query($args);

        // Get the results
        $authors = $wp_user_query->get_results();
        if(!empty($authors))
        {
          $theID = $authors[0]->get('ID');

          $user = get_user_by('id' , $theID );
        }else{
          $user = FALSE;
        }




        if ( $user == FALSE )
        {
          $log_timestamp_var = wp_date("d-m-Y H:i:s", null, $timezone );
          $log_message_var = $log_message_var . "[ $log_timestamp_var ] is_user_registered() : condition met : get_user_by = FALSE : User not registered \n";
          $log_message_var = $log_message_var . "[ $log_timestamp_var ] function being called : wp_safe_redirect ( https://tappd.co.za/wp-login.php?action=register&cardID= $user_id) \n";

          log_to_console($log_message_var, "is_user_registered");
          nocache_headers();
          wp_safe_redirect( get_site_url()."/wp-login.php?action=register&cardID=".$user_id , 302);
          exit();
        }else{
          $log_timestamp_var = wp_date("d-m-Y H:i:s", null, $timezone );
          $log_message_var = $log_message_var . "[ $log_timestamp_var ] is_user_registered() : condition met : get_user_by = TRUE : User IS registered \n";

          $all_meta_for_user = array_map( function( $a ){ return $a[0]; }, get_user_meta( $theID ) );


          $username_meta = str_replace(" ", "-" , $all_meta_for_user['nickname']);
          //$log_message_var = $log_message_var . "[ $log_timestamp_var ] function call : myplugin_registration_save() : user meta Username retrieved -> $userMeta  \n";


          // //$log_message_var = $log_message_var . "[ $log_timestamp_var ] [INFO] Request URI : $uri_landing \n";
          // $username_meta = get_user_meta($user_id , 'field_633ea502ba68b' , true);
          if( ! $username_meta == "" && ! $username_meta == NULL ):
            $uri = get_site_url() . "/author" . "/" . $username_meta;
            nocache_headers();
            wp_safe_redirect( $uri , 302);
            exit();
          else:
            $uri = get_site_url();
            nocache_headers();
            wp_safe_redirect( $uri , 302);
            exit();
          endif;

        }

    }else{
      $otherPregMatch = "/\/wp-login\.php\?action=register&cardID=[0-9]+/";
      $id_value = explode("=", $_SERVER['REQUEST_URI'] )[2];

      if(preg_match($otherPregMatch, $_SERVER['REQUEST_URI']))
      {
        echo "<script type='text/javascript'> window.onload = function () { document.getElementById('acf-field_63e36a9bac409').value = '$id_value'; document.getElementById('acf-field_63e36a9bac409').readOnly
          = true; document.getElementById('acf-field_63e36a9bac409').type = 'hidden'; document.getElementById('acf-field_636104e6d1e2d').type = 'hidden'; document.getElementById('acf-field_636104e6d1e2d').type = 'hidden';
          document.getElementById('acf-field_63610505d1e2e').style.display = 'none'; document.getElementById('acf-field_63610505d1e2e').type = 'hidden';
          document.getElementById('acf-field_63610517d1e2f').style.display = 'none'; document.getElementById('acf-field_63610517d1e2f').type = 'hidden';
          document.getElementById('acf-field_636c0df63d5a1').style.display = 'none'; document.getElementById('acf-field_636c0df63d5a1').type = 'hidden'; }</script>";
      }
      $log_timestamp_var = wp_date("d-m-Y H:i:s", null, $timezone );
      $log_message_var = $log_message_var . "[ $log_timestamp_var ] is_user_registered() : condition met : preg_match( $current_preg_match_regex ) = FALSE \n";

    }

    log_to_console($log_message_var , "is_user_registered");

    // if ( preg_match("/\/sign-up\/\?userid=[0-9]+/", $_SERVER['REQUEST_URI']) )
    // {
    //     $user_id = str_replace( "/sign-up/?userid=" , "" , $_SERVER['REQUEST_URI'] );
    //     $post_object = get_post_object( $user_id );
    //     if ( get_post_meta($post_object->ID, 'registered', true) )
    //     {
    //         nocache_headers();
    //         wp_safe_redirect( get_site_url()."/users/".$user_id , 302);
    //         exit();
    //     }
    // }
}

// Check that post_id is set





function log_to_console($string_message , $function_name)
{
  // = $_SERVER['DOCUMENT_ROOT'] . '/wp-content/uploads/custom_dev_logs';
  $timezone = new DateTimeZone( 'Africa/Johannesburg' );
  $log_timestamp_var = wp_date("d-m-Y H:i:s", null, $timezone );
  $adjusted_path = $_SERVER['DOCUMENT_ROOT'] . "/wp-content/plugins/functions/is_user_registered_log_file.txt";
  // Check that the doc-folder exists
  //echo "To See full Log details of custom plugin look in : $adjusted_path";
  if( file_exists( $adjusted_path ) ):
    $current = file_get_contents($adjusted_path);
    if ( ! $current ):
      echo "failed to open log file";
    else:
      $new = $current . "\n\n" . $string_message;
      if( ! file_put_contents($adjusted_path, $new) ):
        echo "failed write";
      endif;
    endif;
  endif; // if( file_exists( $docs_folder . '/post' . $post_id ) ):




  // $file = fopen("dev_test_log_file_$log_timestamp_var.txt", "w")
  // Open the file to get existing content
  // $current = file_get_contents($file);
  // Append a new person to the file
  // Write the contents back to the file


}

// add_filter( 'registration_redirect', 'my_redirect_home' );
// function my_redirect_home( $registration_redirect ) {
// 	$user = wp_get_current_user();
//   nocache_headers();
//   wp_safe_redirect( get_site_url()."/author/".$user->name , 302);
//   exit();
// }

function update_post_object( $fields ){
    $post_object = get_post_object( $fields['userid'] );


    // TODO -----------------------------------------------------------
    // Needs to be refactored
    // Form ID's need to be the same as custom field ID's for this

    // $field_ids = array(
    //     "name",
    //     "contact_number",
    //     "email", // ETC
    // );

    // foreach ($field_ids as $id){
    //     update_post_meta($post_object->ID, $id, $fields[$id]);
    // }
    // -------------------------------------------------------------------

    update_post_meta($post_object->ID, 'title', $fields['testtile']);
    update_post_meta($post_object->ID, 'name', $fields['testname']);
    update_post_meta($post_object->ID, 'contact_number', $fields['testcontact']);
    update_post_meta($post_object->ID, 'email', $fields['testemail']);
    update_post_meta($post_object->ID, 'registered', 1);
    $counter = 0;
    $registered = get_post_meta($post_object->ID, 'registered', true);
    while ( !$registered && $counter < 5 )
    {
        sleep(2);
        $registered = get_post_meta($post_object->ID, 'registered', true);
        $counter = $counter + 1;
    }
    return $registered;
}

function update_pp(){
    $attachment = $_REQUEST['attachment'];

    $url = $_REQUEST['id'];
    $post_id = str_replace( "https://tappd.co.za/sign-up/?userid=" , "" , $url );



    $post_object = get_page_by_path($post_id, 'OBJECT', 'Users');


    $upload_dir = wp_upload_dir();
    $upload_path = str_replace( '/', DIRECTORY_SEPARATOR, $upload_dir['path'] ) . DIRECTORY_SEPARATOR;
    $image_parts = explode(";base64,",$attachment);
    $decoded = base64_decode($image_parts[1]);
    $filename = '{$post_id}.png';
    $hashed_filename = md5( $filename . microtime() ) . '_' . $filename;
    $image_upload = file_put_contents( $upload_path . $hashed_filename, $decoded );
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    $file             = array();
    $file['error']    = '';
    $file['tmp_name'] = $upload_path . $hashed_filename;
    $file['name']     = $hashed_filename;
    $file['type']     = 'image/jpeg';
    $file['size']     = filesize( $upload_path . $hashed_filename );
    $file_return = wp_handle_sideload( $file, array( 'test_form' => false ) );
    $filename = $file_return['file'];
    $attachment = array(
                        'post_mime_type' => $file_return['type'],
                        'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
                        'post_content' => '',
                        'post_status' => 'inherit',
                        'guid' => $wp_upload_dir['url'] . '/' . basename($filename)
                        );
    $attach_id = wp_insert_attachment( $attachment, $filename );
    update_post_meta($post_object->ID, 'headshot', $attach_id);
}
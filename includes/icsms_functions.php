<?php
/*
 * Script name: icsms_functions.php
 * Plugin: istiesms
 * Plugin URI:  http://www.istiecool.nl/ic-plugins/istie-sms/
 * Author: Istiecool
 * Author URI:  http://www.istiecool.nl 
 *
*/
 

/*--- extra javascripts + css -----------------------------------------------------------*/	
function icsms_extra_scripts()  
{  
    // Register the script like this for a plugin:  
    wp_register_script( 'icsms_java', plugins_url( '/js/icsms_javascript.js', __FILE__ ) ); 
	//wp_register_script( 'icsms_javachars', plugins_url( '/js/calcCharactersLeft.js', __FILE__ ) ); 
	wp_register_style('icsms_css', plugins_url( '/css/icsms_style.css', __FILE__ ), false, '1.0', 'screen');	
    // or register the script like this for a theme:  
    //wp_register_script( 'icsms_css', get_template_directory_uri() . '/icsms_javascript.js' );  
    // For either a plugin or a theme, you can then enqueue the script:  
    wp_enqueue_script( 'icsms_java' );  
	//wp_enqueue_script( 'icsms_javachars' );    
	wp_enqueue_script('jquery-ui-datepicker');
	wp_enqueue_style( 'icsms_css' );  
}  
add_action( 'wp_enqueue_scripts', 'icsms_extra_scripts' ); //load for frontend
add_action( 'admin_enqueue_scripts', 'icsms_extra_scripts' ); //load for backend

/*--- Istiecool Unique ID Generator------------------------------------------------------*/
function icsms_IDgenerator()
{
    $karakters = array_merge(range('a','z'),range('A','Z'),range(0,9));
    shuffle($karakters);
    $karakters = implode('',$karakters);
    return substr($karakters,0,12);
}

/*--- Istiecool pointer (info schermen)--------------------------------------------------*/
add_action( 'admin_enqueue_scripts', 'icsms_admin_pointer_header' );
function icsms_admin_pointer_header() {
    wp_enqueue_style( 'wp-pointer' );
    wp_enqueue_script( 'wp-pointer' );
    add_action( 'admin_print_footer_scripts', 'icsms_admin_pointers_footer' );
}
function icsms_admin_pointers_check() {
   $admin_pointers = icsms_admin_pointers();
   foreach ( $admin_pointers as $pointer => $array ) {
      if ( $array['active'] )
         return true;
   }
}
function icsms_admin_pointers_footer() {
   $admin_pointers = icsms_admin_pointers();
   ?>
<script type="text/javascript">
/* <![CDATA[ */
( function($) {
   <?php
   foreach ( $admin_pointers as $pointer => $array ) {
      if ( $array['active'] ) {
         ?>
         $( '<?php echo $array['anchor_id']; ?>' ).pointer( {
            content: '<?php echo $array['content']; ?>',
            position: {
            edge: '<?php echo $array['edge']; ?>',
            align: '<?php echo $array['align']; ?>'
         },
            close: function() {
               $.post( ajaxurl, {
                  pointer: '<?php echo $pointer; ?>',
                  action: 'dismiss-wp-pointer'
               } );
            }
         } ).pointer( 'open' );
         <?php
      }
   }
   ?>
} )(jQuery);
/* ]]> */
</script>
   <?php
}

function icsms_admin_pointers() {
   $dismissed = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );
   $version = '1_0'; // replace all periods in 1.0 with an underscore
   $prefix = 'icsms_admin_pointers' . $version . '_';

   $new_pointer_content = '<h3>' . __( 'IstieSMS | Info', 'istie_sms' ) . '</h3>';
   $new_pointer_content .= '<p>' . __( 'Lees de extra informatie en verander de instellingen voor je website.', 'istie_sms' ) . '</p>';

   return array(
      $prefix . 'new_items' => array(
         'content' => $new_pointer_content,
         'anchor_id' => '#toplevel_page_istiesms-info',
         'edge' => 'top',
         'align' => 'left',
         'active' => ( ! in_array( $prefix . 'new_items', $dismissed ) )
      ),
   );
}

//Reset a WP pointer by appending ?reset-pointer=POINTER_ID to an admin URL.
add_action( 'admin_init', 'icsms_reset_pointer' );
function icsms_reset_pointer() {
	if ( ! isset( $_REQUEST['reset-pointer'] ) )
			return;
	$meta = (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true );
	$meta = str_replace( $_REQUEST['reset-pointer'], '', $meta );
	$meta = str_replace( ',,', ',', $meta );
	$meta = trim( $meta, ',' );
	update_user_meta( get_current_user_id(), 'dismissed_wp_pointers', $meta );
}



/*--- show/edit user profile ------------------------------------------------------------*/
function icsms_edit_userprofile( $user ) {
echo '<hr><h3>Persoonlijke SMS Gegevens</h3>';
	// ---------- show credits
	$sms_credits = get_the_author_meta( 'icsms_user_credits', $user->ID );
	if (empty($sms_credits) || $sms_credits <= 0 ) {
		echo '<div class="error fade">';
		if ( current_user_can( 'manage_options' ) ) {	
			echo '<h3>'. __('Deze gebruiker', 'istie_sms') .' (<i>'. $user->user_login .'</i>) '. __('heeft geen credits om sms-berichten te versturen.', 'istie_sms') .'</h3>';
		} else {
			echo '<h3>'. __('Je hebt geen credits om sms-berichten te versturen. <a href="admin.php?page=istiesms/sms_credits.php">Credits kopen</a>', 'istie_sms') .'</h3>';
		}
		echo '</div>';
	} else {
		echo '';
		if ( current_user_can( 'manage_options' ) ) {	
			echo '<div class="updated"><h3>'. __('Deze gebruiker heeft nog', 'istie_sms') .' <b>'.$sms_credits.'</b> sms-credits.</h3></div>';
		} else {
			if ($sms_credits <= 3 ) {
				echo '<div class="updated"><h3>'. __('Je hebt nog maar', 'istie_sms') .' <b>'.$sms_credits.'</b> '. __('credits om sms-berichten te versturen. <a href="admin.php?page=istiesms/sms_credits.php">Credits kopen</a>', 'istie_sms') .'</h3></div>';
			}
		}		
		echo '';
	}
?>
<table class="form-table">
<tr>
<th><label for="icsms_user_afz"><?php _e('SMS afzender', 'istie_sms'); ?></label></th>
<td>
<input type="text" name="icsms_user_afz" id="icsms_user_afz" maxlength="11" value="<?php echo esc_attr( get_the_author_meta( 'icsms_user_afz', $user->ID ) ); ?>" class="regular-text" /><br />
<span class="description"><?php _e('Vul je naam of 06-nummer in. Dit wordt gebruikt als standaard afzender van je sms-berichten. (max. 11 tekens)', 'istie_sms'); ?></span>
</td>
</tr>
<?php
	if ( current_user_can( 'manage_options' ) ) {	
?>
		<tr>
			<th><label for="icsms_user_credits"><?php _e('Beschikbare credits', 'istie_sms'); ?></label></th>
			<td>
			<input type="text" name="icsms_user_credits" id="icsms_user_credits" value="<?php echo esc_attr( get_the_author_meta( 'icsms_user_credits', $user->ID ) ); ?>" class="regular-text" /><br />
			<span class="description"><?php _e('Het aantal credits welke je kunt gebruiken om sms-berichten te versturen (1 SMS = 1 credit).', 'istie_sms'); ?></span>
			</td>
		</tr>
<?php
	} else {
?>
		<tr>
			<th><label for="icsms_user_credits"><?php _e('Beschikbare Credits', 'istie_sms'); ?></label></th>
			<td><strong>
			<?php 
			
			if (!empty($sms_credits) && $sms_credits > 0 ) {
				echo esc_attr( the_author_meta( 'icsms_user_credits', $user->ID ) ) . ' credits';
			} else {
				echo '<span class="error fade">'. __('Je hebt geen credits om sms-berichten te versturen. <a href="admin.php?page=istiesms/sms_credits.php">Credits kopen</a>', 'istie_sms') .'</span>';
			}
			?>
			</strong><br />
			<span class="description"><?php _e('Het aantal credits welke je kunt gebruiken om sms-berichten te versturen (1 SMS = 1 credit).', 'istie_sms'); ?></span>
			</td>
		</tr>
<?php
	}
?>	
</table>
<hr>
<?php 

}
add_action( 'show_user_profile', 'icsms_edit_userprofile' );
add_action( 'edit_user_profile', 'icsms_edit_userprofile' );


/*--- save user profile -----------------------------------------------------------------*/
function icsms_save_userprofile( $user_id ) {

if ( !current_user_can( 'edit_user', $user_id ) ) { return false; }

update_user_meta( $user_id, 'icsms_user_afz', $_POST['icsms_user_afz'] );
update_user_meta( $user_id, 'icsms_user_credits', $_POST['icsms_user_credits'] );

}
add_action( 'personal_options_update', 'icsms_save_userprofile' );
add_action( 'edit_user_profile_update', 'icsms_save_userprofile' );


/*--- give new user xx crdits on registration -------------------------------------------*/
function icsms_creditgift_newuser_($user_id) {
    global $wpdb;
	$smscredits_usergift = get_option( 'icsms_credits_usergift');
	if(!empty($smscredits_usergift)) { 
		update_user_meta( $user_id, 'icsms_user_credits', $smscredits_usergift );
	}
}
add_action( 'user_register', 'icsms_creditgift_newuser_');

?>
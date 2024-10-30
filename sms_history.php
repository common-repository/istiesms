<?php
/*
 * Script name: sms_history.php
 * Plugin: istiesms
 * Plugin URI:  http://www.istiecool.nl/ic-plugins/istie-sms/
 * Author: Istiecool
 * Author URI:  http://www.istiecool.nl 
 *
*/

//--- load extra wp-shit for metaboxes --------------------------------------------------*/
wp_enqueue_script('common');
wp_enqueue_script('wp-lists');
wp_enqueue_script('postbox');
// remember locations + open/closed
wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false );
wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false );
?>
<script>
jQuery(document).ready( function($) {
	// close postboxes that should be closed
	jQuery('.if-js-closed').removeClass('if-js-closed').addClass('closed');
	// postboxes
	<?php
	global $wp_version;
	if(version_compare($wp_version,"2.7-alpha", "<")){
		echo "add_postbox_toggles('istiesms-history');"; //For WP2.6 and below
	}
	else{
		echo "postboxes.add_postbox_toggles('istiesms-history');"; //For WP2.7 and above
	}
	?>
	// close all boxes when page is loaded
    //jQuery("[id^=icsms_meta_box]").each(function() {
    //    if(!jQuery(this).hasClass("closed")) {
    //        jQuery(this).addClass("closed");
    //    }
    //});
});
</script>

<?php

//--- Istiecool titel -------------------------------------------------------------------*/
?>
<div class="wrap">
<div class="ic_infowrap">
<a href="http://www.istiecool.nl" target="_blank"><img src="<?php echo ''. WP_PLUGIN_URL .'/istiesms/images/Istiecool.nl_64x64.png'; ?>" class="istielogo" alt="istiecool" width="50" height="50"></a>
	<div class="ic_infotext">
		<div class="icon32" id="icon-index"><br /></div>
		<h3><strong><?php _e('Overzicht', 'istie_sms'); ?></strong></h3>
		<div class="ic_border"></div>
		<?php _e('Op deze pagina staat een overzicht van de sms-berichten die je al hebt verstuurd en van de berichten die in de toekomst zullen worden verstuurd (geagendeerd).', 'istie_sms'); ?><br />
		<?php _e('Berichten kunnen niet meer worden veranderd.', 'istie_sms'); ?>
		<br /><br />	
	</div>
</div>
<br />

<?php

/*--- Algemeen --------------------------------------------------------------------------*/
$future_title = '<div class="icon16" id="icon-edit-comments"></div>';
$future_title .= '<strong>'. __('Geagendeerde sms-berichten', 'istie_sms') .'</strong>';
function icsms_future_meta_box() {
	// get user info
	global $current_user;
	$sms_credits = get_the_author_meta( 'icsms_user_credits', $current_user->ID );
	$sms_usermail = get_the_author_meta( 'user_email', $current_user->ID );
	$sms_username = get_the_author_meta( 'user_login', $current_user->ID );
	$sms_username = strtolower($sms_username);	
	// mollie login
	$icsms_mollie_username = get_option( 'icsms_mollie_username');
	$icsms_mollie_passwd =  base64_decode(get_option( 'icsms_mollie_passwd'));		
	// get db info
	// add database pointer
	global $wpdb;
	$ic_smscustomers = $wpdb->prefix . 'ic_smscustomers';
	$ic_smscontacts = $wpdb->prefix . 'ic_smscontacts';
	$ic_smsmessages = $wpdb->prefix . 'ic_smsmessages';
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	//dbDelta($customers);
	// ------ geagendeerde berichten
	$active_result = $wpdb->get_results("SELECT * FROM ". $ic_smsmessages ." WHERE email = '". $sms_usermail ."' ORDER BY sms_delivery ASC ");
	// ------ TEL de geagendeerde berichten
	//$activecount = 0;
	//	foreach($active_result as $message) {
	//		if (strtotime($message->sms_delivery) > time()) {
	//			echo 'future: '.$message->mid.' <br>';
	//		} 
	//	}
	// ------ vraag klant id op
	//$cust_id = db_query_range("SELECT {*} FROM {'$ic_smscustomers'} WHERE {email} = {'$sms_usermail'} ORDER BY {id} ASC", 0,1);
	//	while ($node_id = db_fetch_object($cust_id)) {
	//		$id_customer = $node_id->id;
	//		$sms_credits = $node_id->credits;
	//	}

	//check if delmessage form = submitted
	if(isset($_POST['delmessage_form']) && $_POST['delmessage_form'] == 'DELETE') {
		$sms_id = $_POST['sms_id'];	
		$credits_costs = $_POST['sms_costs'];
		//$delete_id = "".$customer_id."".$sms_id ."";
		$delete_id = $sms_id;	
		$message = $sms_id;				
		
		// check mollie api
		require_once(ABSPATH . 'wp-content/plugins/istiesms/includes/class.mollie.php');
		$sms = new mollie();
		// Set Mollie.nl username and password
		$sms->setLogin($icsms_mollie_username, $icsms_mollie_passwd);
		// Add reference (needed for delivery reports)
		$sms->setReference($delete_id);	
		// DELETE the SMS Message
		$sms->deleteSMS($message);		
		
		
		if ($sms->getSuccess()) {
			// delete form db
			$delete_futuremessage = $wpdb->query( "DELETE FROM ". $ic_smsmessages ." WHERE sms_id = '". $sms_id ."' AND email = '". $sms_usermail ."' LIMIT 1 ");
			dbDelta($delete_futuremessage);		
			// update credits
			$old_credits = get_the_author_meta( 'icsms_user_credits', $current_user->ID );
			$new_credits = $old_credits + $credits_costs;
			update_user_meta( $current_user->ID, 'icsms_user_credits', $new_credits );
			// screen message
			if($credits_costs == 1) {
				$credits_costs_text = 'Er wordt '. $credits_costs .' credit teruggeschreven naar je account.';
			} else {
				$credits_costs_text = 'Er worden '. $credits_costs .' credits teruggeschreven naar je account.';
			}	
			?>
			<div class="updated">
			<p><?php esc_html_e('SMS bericht verwijderd. ', 'istie_sms'); ?><br/>
			<?php esc_html_e($credits_costs_text, 'istie_sms'); ?>
			<strong><?php esc_html_e('(Nieuw credit totaal: '.$new_credits.')', 'istie_sms'); ?></strong><br/>
			<?php esc_html_e('Vernieuw de pagina om de berichtenlijst te actualiseren of wacht enkele seconden.', 'istie_sms'); ?>
			</p>
			<meta http-equiv="refresh" content="5;url=">
			</div>
			<?php	
		} else {
			// screen message on failure		
			?>
			<div class="error">
			<p><?php esc_html_e('Het verwijderen van de SMS is niet gelukt!', 'istie_sms'); ?><br/>
			<?php esc_html_e('Errorcode: '. $sms->getResultCode() .'', 'istie_sms'); ?><br/>
			<?php
			if($sms->getResultMessage() == 'No message(s) found.') {
				esc_html_e('Errorbericht: Bericht niet gevonden', 'istie_sms');
			} else {
				esc_html_e('Errorbericht: '. $sms->getResultMessage() .'', 'istie_sms');
			}
			?>
			</p>
			</div>
			<?php	
		} // end mollie			
	} // end if delete

?>
	<?php _e('Overzicht van de geagendeerde sms-berichten, klik op verwijder om het bericht niet te versturen. De creditkosten worden automatisch teruggeschreven op je account.', 'istie_sms'); ?><br />
	<br/>
<table id="active" class="tablefuture" border="0" cellpadding="1" cellspacing="0">
  <tbody>
	<tr class="table_text">
		<td colspan="5"><?php _e('<strong>Geagendeerde sms-berichten</strong> (deze staan klaar om verstuurd te worden)', 'istie_sms'); ?></td>
		<td align="right" colspan="2"></td>
	</tr>
		<tr class="table_head">
		<td width="100px"><b><?php _e('Datum', 'istie_sms'); ?></b></td>
		<td width="50px"><b><?php _e('Tijd', 'istie_sms'); ?></b></td>
		<td width="100px"><b><?php _e('Afzender', 'istie_sms'); ?></b></td>
		<td width="100px"><b><?php _e('Ontvanger', 'istie_sms'); ?></b></td>
		<td width="550px"><b><?php _e('sms-bericht', 'istie_sms'); ?></b></td>
		<td width="50px"><b><?php _e('Kosten', 'istie_sms'); ?></b></td>
		<td width="50px" align="right"><font color="red"><b><?php _e('Verwijder', 'istie_sms'); ?></b></font></td>
	</tr>
<?php
	$coloractive="1"; // Define $color=1
	foreach($active_result as $message) {
		// get only message in future (time = blogs current local time)
		if (strtotime($message->sms_delivery) > strtotime(current_time('mysql')) ) {
			// create different row colors
			if($coloractive==1){
				echo '<tr class="row_odd">';
				$coloractive="2";
			} else {
				echo '<tr class="row_even">';
				$coloractive="1";
			}
			// show future messages
			echo '<td valign="top">'. $message->delivery_date .'</td>';
			echo '<td valign="top">'. $message->delivery_time .'</td>';
			echo '<td valign="top">'. $message->originator .'</td>';
			echo '<td valign="top">'. $message->recipient_number .'</td>';
			echo '<td valign="top">'. $message->SMS_message .'</td>';
			echo '<td valign="top" align="right">'. $message->credits .'</td>';
			echo '<td valign="top" align="right">';
			//echo "<a href=\"?del_activemessage&sms_id=".$message->sms_id ." \" onClick=\"return confirm('Weet je zeker dat je dit bericht wilt verwijderen?');\">\n";
			// create del message form
			echo '<form name="icsms_form1" method="post" action="">';
			echo '<input type="hidden" name="delmessage_form" value="DELETE">';
			echo '<input type="hidden" name="sms_id" value="'. $message->sms_id .'">';
			echo '<input type="hidden" name="sms_costs" value="'. $message->credits .'">';
			// check for demo login
			if ($sms_username == "demo") {
				echo "<a href=\"\" onClick=\"return alert('In de demo versie kun je geen bericht verwijderen?');\"><img src=\"". WP_PLUGIN_URL . "/istiesms/images/dialog-no-3.png\" alt=\"delete\" title=\"Verwijder dit bericht\"/></a>\n";
			} else {
				echo "<input type=\"image\" name=\"Submit\" class=\"imgbutton\" src=\"". WP_PLUGIN_URL . "/istiesms/images/dialog-no-3.png\" alt=\"delete\" title=\"Verwijder dit bericht\" onClick=\"return confirm('Weet je zeker dat je dit bericht wilt verwijderen?');\" />";				
			}
			echo '</form>';
			echo '</td>';
			echo '</tr>';		
		} //end future delivery
	} //end foreach
?>	
  </tbody>
</table>
	
<?php
}
// add metabox
add_meta_box('icsms_meta_box_future', $future_title, 'icsms_future_meta_box', 'istiesms-history', 'advanced', 'default');

/*--- Update --------------------------------------------------------------------------*/
$send_title = '<div class="icon16" id="icon-plugins"></div>';
$send_title .= '<strong>'. __('Verstuurde sms-berichten', 'istie_sms') .'</strong>';
function icsms_send_meta_box() {
	// get user info
	global $current_user;
	$sms_credits = get_the_author_meta( 'icsms_user_credits', $current_user->ID );
	$sms_usermail = get_the_author_meta( 'user_email', $current_user->ID );
	$sms_username = get_the_author_meta( 'user_login', $current_user->ID );	
	$sms_username = strtolower($sms_username);	
	// get db info
	global $wpdb;
	$ic_smscustomers = $wpdb->prefix . 'ic_smscustomers';
	$ic_smscontacts = $wpdb->prefix . 'ic_smscontacts';
	$ic_smsmessages = $wpdb->prefix . 'ic_smsmessages';
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	// get all sms messages
	$active_result = $wpdb->get_results("SELECT * FROM ". $ic_smsmessages ." WHERE email = '". $sms_usermail ."' ORDER BY sms_delivery DESC ");
	//check if delmessage form = submitted
	if(isset($_POST['delpastmessage_form']) && $_POST['delpastmessage_form'] == 'DELETEPAST') {
		$sms_id = $_POST['sms_id'];
		// delete form db
		$delete_pastmessage = $wpdb->query( "DELETE FROM ". $ic_smsmessages ." WHERE sms_id = '". $sms_id ."' AND email = '". $sms_usermail ."' LIMIT 1 ");
		dbDelta($delete_pastmessage);		
		// update screen message
		?>
		<div class="updated">
		<p><?php _e('SMS bericht verwijderd.', 'istie_sms'); ?><br/>
		<?php esc_html_e('Vernieuw de pagina om de berichtenlijst te actualiseren of wacht enkele seconden.', 'istie_sms'); ?></p>
		<meta http-equiv="refresh" content="5;url=">
		</div>
		<?php	
	}	
?>
	<br/>
<table id="past" class="tablepast" border="0" cellpadding="1" cellspacing="0">
  <tbody>
	<tr class="table_text">
		<td colspan="5"><?php _e('<strong>Reeds verstuurde sms-berichten</strong> (meest recente bovenaan)', 'istie_sms'); ?></td>
		<td align="right" colspan="2"></td>
	</tr>
		<tr class="table_head">
		<td width="100px"><b><?php _e('Datum', 'istie_sms'); ?></b></td>
		<td width="50px"><b><?php _e('Tijd', 'istie_sms'); ?></b></td>
		<td width="100px"><b><?php _e('Afzender', 'istie_sms'); ?></b></td>
		<td width="100px"><b><?php _e('Ontvanger', 'istie_sms'); ?></b></td>
		<td width="550px"><b><?php _e('sms-bericht', 'istie_sms'); ?></b></td>
		<td width="50px"><b><?php _e('Kosten', 'istie_sms'); ?></b></td>
		<td width="50px" align="right"><font color="red"><b><?php _e('Verwijder', 'istie_sms'); ?></b></font></td>
	</tr>
<?php
	$coloractive="1"; // Define $color=1
	foreach($active_result as $message) {
	//while($active_result) {
		// get only message in the past (time = blogs current local time)
		if (strtotime($message->sms_delivery) < strtotime(current_time('mysql')) ) {
			// create different row colors
			if($coloractive==1){
				echo "<tr class=\"row_odd\">";
				$coloractive="2";
			} else {
				echo "<tr class=\"row_even\">";
				$coloractive="1";
			}
			// show past messages
			echo '<td valign="top">'. $message->delivery_date .'</td>';
			echo '<td valign="top">'. $message->delivery_time .'</td>';
			echo '<td valign="top">'. $message->originator .'</td>';
			echo '<td valign="top">'. $message->recipient_number .'</td>';
			echo '<td valign="top">'. $message->SMS_message .'</td>';
			echo '<td valign="top" align="right">'. $message->credits .'</td>';
			echo '<td valign="top" align="right">';
			// create del message form
			echo '<form name="icsms_form1" method="post" action="">';
			echo '<input type="hidden" name="delpastmessage_form" value="DELETEPAST">';
			echo '<input type="hidden" name="sms_id" value="'. $message->sms_id .'">';
			// check for demo login
			if ($sms_username == "demo") {
				echo "<a href=\"\" onClick=\"return alert('In de demo versie kun je geen bericht verwijderen?');\"><img src=\"". WP_PLUGIN_URL . "/istiesms/images/dialog-no-3.png\" alt=\"delete\" title=\"Verwijder dit bericht\"/></a>\n";
			} else {
				echo "<input type=\"image\" name=\"Submit\" class=\"imgbutton\" src=\"". WP_PLUGIN_URL . "/istiesms/images/dialog-no-3.png\" alt=\"delete\" title=\"Verwijder dit bericht\" onClick=\"return confirm('Weet je zeker dat je dit bericht wilt verwijderen?');\" />";
			}
			echo '</form>';
			echo '</td>';			
			echo '</tr>';		
		} //end future delivery
	} //end foreach
?>	
  </tbody>
</table>
<?php
}
// add metabox
add_meta_box('icsms_meta_box_send', $send_title, 'icsms_send_meta_box', 'istiesms-history', 'advanced', 'default');

//--- add all different metaboxes to this page ------------------------------------------*/
do_meta_boxes('istiesms-history','advanced',null);

echo '</div>'; //End of wrap class
?>

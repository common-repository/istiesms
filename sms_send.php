<?php
/*
 * Script name: sms_send.php
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
		echo "add_postbox_toggles('istiesms-send');"; //For WP2.6 and below
	} else {
		echo "postboxes.add_postbox_toggles('istiesms-send');"; //For WP2.7 and above
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
		<h3><strong><?php _e('Versturen van sms-berichten', 'istie_sms'); ?></strong></h3>
		<div class="ic_border"></div>
		<?php _e('Vul het onderstaand formulier in en je SMS wordt verstuurd op de door jou opgegeven tijd en datum.', 'istie_sms'); ?>
		<?php _e('Het versturen van 1 sms-bericht kost 1 credit. De credits worden, bij succesvolle verzending, automatisch van je account afgeschreven.', 'istie_sms'); ?>
		<?php _e('Alle geplande en verzonden berichten kun je bekijken op de <a href="admin.php?page=istiesms/sms_history.php">overzichtspagina</a>.', 'istie_sms'); ?><br/>
		<br/>
	</div>
</div>
<br />

<?php
/*--- Algemeen --------------------------------------------------------------------------*/
	// ---------- show credits
	$sms_credits = get_the_author_meta( 'icsms_user_credits', $current_user->ID );
	$credits = ''; // set var for undefined notice php
	if (empty($sms_credits)) {
		$credits .= __('Je hebt geen credits om sms-berichten te versturen. <a href="">Credits kopen</a>', 'istie_sms');
	} else {
		$credits .= __('Je hebt nog <b>'.$sms_credits.'</b> credits.', 'istie_sms');
	}

$sendsms_title = '<div class="icon16" id="icon-edit-comments"></div>';
$sendsms_title .= '<strong>'. __('Stuur SMS', 'istie_sms') .'</strong>';
$sendsms_title .= '<span style="font-size:14px; margin-left: 15px;">';
$sendsms_title .= '<small>'.$credits.'</small></span>';

function icsms_send_meta_box() {

add_action( 'show_user_profile', 'icsms_send_meta_box' );
	// get user info
	global $current_user;
	$sms_sendname = get_user_meta( $current_user->ID, 'icsms_user_afz', true );
	$sms_mobile = ''; // set var for undefined notice php
	$sms_credits = get_the_author_meta( 'icsms_user_credits', $current_user->ID );
	$sms_usermail = get_the_author_meta( 'user_email', $current_user->ID );
	$sms_username = get_the_author_meta( 'user_login', $current_user->ID );
	$sms_username = strtolower($sms_username);
	// get admin info
	$icsms_admin_username = get_option( 'icsms_admin_username'); 	//verplicht
	$icsms_admin_email = get_option( 'icsms_admin_email');			//verplicht
	$icsms_admin_bankrek = get_option( 'icsms_admin_bankrek');		//verplicht
	$icsms_admin_banktnv = get_option( 'icsms_admin_banktnv');		//verplicht
	$icsms_admin_bankname = get_option( 'icsms_admin_bankname');	//optioneel
	$icsms_admin_bankplace = get_option( 'icsms_admin_bankplace');	//optioneel
	// mollie login
	$icsms_mollie_username = get_option( 'icsms_mollie_username');
	$icsms_mollie_passwd = base64_decode(get_option( 'icsms_mollie_passwd'));	
	// get db info
	global $wpdb;
	$ic_smscustomers = $wpdb->prefix . 'ic_smscustomers';
	$ic_smscontacts = $wpdb->prefix . 'ic_smscontacts';
	$ic_smsmessages = $wpdb->prefix . 'ic_smsmessages';
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');	
	
	
	
	//check if delmessage form = submitted
	if(isset($_POST['sendsms_form']) && $_POST['sendsms_form'] == 'SEND') {
		// get values
		$originator = $_POST['originator'];
		$originator = esc_html($originator);
		$recipient_number = $_POST['recipient_number'];
		$recipient_number = "00316".$recipient_number."";
		$message = $_POST['message'];
		$message = str_replace("'","-",$message);
		$message = str_replace("\n"," ",$message);
		$message = esc_html($message);
		//$message = urlencode($message);
		$date = $_POST['date'];
		$dates = explode("-", $date);
		$datum = "".$dates[2]."".$dates[1]."".$dates[0]."";
		$hour = $_POST['hour'];
		$min = $_POST['min'];
		$del_date = $date;
		$del_time = "".$hour.":".$min."";
		$sec = '00';		
		$deliverydate = "".$datum."".$hour."".$min."".$sec."";
		if (strlen($message) <= 160) {$credits_costs = 1;  $type = "normal"; }
		elseif ((strlen($message) > 160) && (strlen($message) <= 306)) {$credits_costs = 2;  $type = "long";}
		elseif ((strlen($message) > 306) && (strlen($message) <= 459)) {$credits_costs = 3;  $type = "long"; }
		elseif ((strlen($message) > 459)) {$credits_costs = 4;  $type = "long"; }
		//$sms_id = uniqid($sms_code);
		$sms_id = uniqid( icsms_IDgenerator() );
		
		// prepare error list
		unset($smssend_errors);	
		//if ($sms_credits <= 0) {
		//  $errors[] = "Je hebt niet genoeg credits om eem sms te versturen<br/>Klik <a href=\"sms_credits.php\">hier<a> om nieuwe credits te kopen.";
		//}		
		if ($originator == "") {
		  $smssend_errors[] = __('Naam van afzender is verplicht.', 'istie_sms');
		}		
		//if ( count($originator > 12) ) {
		if ( strlen($originator) > 11)  {
		  $smssend_errors[] = __('Naam van de afzender is te lang. (max 11 tekens)', 'istie_sms');
		}		
		if ($recipient_number == "") {
		  $smssend_errors[] = __('Geen ontvanger opgegeven.', 'istie_sms');
		}
		if(!is_numeric($recipient_number)) {
		  $smssend_errors[] = __('Bij ontvanger kun je alleen cijfers invullen (het 06-nummer van de ontvanger).', 'istie_sms');
		}		
		if (strlen($recipient_number) != 13) {
		  $smssend_errors[] = __('Telefoonnummer is te kort.', 'istie_sms');
		}		
		if ($message == "") {
		  $smssend_errors[] = __('Geen sms bericht geschreven.', 'istie_sms');
		}	
		if(strtotime($deliverydate) < strtotime(current_time('mysql')) ) {		
			$smssend_errors[] = __('De verzendtijd van het bericht ligt in het verleden. Pas de datum of de tijd aan.', 'istie_sms');
		}
		if ($datum == "") {
		  $smssend_errors[] = __('Geen datum opgegeven.', 'istie_sms');
		}
		if(!is_numeric($datum) && $datum != '') {
		  $smssend_errors[] = __('Bij de datum kun je alleen cijfers invullen.', 'istie_sms');
		}	
		if(!is_numeric($deliverydate)) {
		  $smssend_errors[] = __('Er gaat iets mis met het invullen van de datum of de tijd.', 'istie_sms');
		}		
		if (($deliverydate != "") && (strlen($deliverydate) != "14")) {
		  $smssend_errors[] = __('Er gaat iets mis met het invullen van de datum of de tijd.', 'istie_sms');
		}		
		if ($hour == "") {
		  $smssend_errors[] = __('Tijd is niet compleet ingevuld. (uren)', 'istie_sms');
		}
		if(!is_numeric($hour)) {
		  $smssend_errors[] = __('Bij de tijd (uren) kun je alleen cijfers invullen.', 'istie_sms');
		}
		if (($hour != "") && (strlen($hour) != "2")) {
		  $smssend_errors[] = __('Uren zijn te kort ingevuld. (gebruik 0 in notatie bijv: 09:05)', 'istie_sms');
		}		
		if ($min == "") {
		  $smssend_errors[] = __('Tijd is niet compleet ingevuld. (min)', 'istie_sms');
		}
		if(!is_numeric($min)) {
		  $smssend_errors[] = __('Bij de tijd (minuten) kun je alleen cijfers invullen.', 'istie_sms');
		}
		if (($min != "") && (strlen($min) != "2")) {
		  $smssend_errors[] = __('Minuten zijn te kort ingevuld. (gebruik 0 in notatie bijv: 09:05)', 'istie_sms');
		}				
		if (empty($icsms_admin_username) || empty($icsms_admin_email) || empty($icsms_admin_bankrek) || empty($icsms_admin_banktnv)  ) {
		  $smssend_errors[] = __('De SMS module is nog niet geactiveerd. Neem contact op met de beheerder van de site.', 'istie_sms');
		}
		if ($sms_username == 'demo') {	
			$smssend_errors[] = __('In de demo versie kun je geen sms-bericht verzenden!', 'istie_sms');
		}
		
		if (empty($smssend_errors)) { //&& ($user->name != "Demo" )) {

			require_once(ABSPATH . 'wp-content/plugins/istiesms/includes/class.mollie.php');
			// or php4: require('classes/class.mollie-php4.php');
			$sms = new mollie();
			// Choose SMS gateway (Basic = gateway 2) (Business+ = gateway 1)
			$sms->setGateway(2);
			// Set Mollie.nl username and password
			$sms->setLogin($icsms_mollie_username, $icsms_mollie_passwd);
			// Set originator
			$sms->setOriginator($originator);
			// Add recipient(s)
			$sms->addRecipients($recipient_number);
			// Add reference (needed for delivery reports)
			//$sms->setReference(''.$id_customer.''.$deliverydate.'');
			$sms->setReference(''.$sms_id.'');
			// $sms->setReference('1234');
			// Set Message type
			$sms->setType($type);	
			// Set Delivery Date
			$sms->setDeliveryDate($deliverydate);			
			// Send the SMS Message
			$sms->sendSMS($message);		
		
			//Mollie api succes
			if ($sms->getSuccess()) {
		
				// add message to db
				//$wpdb->query( "DELETE FROM ". $ic_smsmessages ." WHERE sms_id = '". $sms_id ."' AND email = '". $sms_usermail ."' LIMIT 1 ");
				
				// write message in SQL
				$update_messages = $wpdb->query("INSERT INTO ". $ic_smsmessages ." (`mid`,`timestamp`,`userlocaldate`,`userlocaltime`,`loginname`,`email`,`originator`,`recipient_name`,`recipient_number`,`SMS_message`,`delivery_date`,`delivery_time`,`sms_delivery`,`sms_id`,`credits`) VALUES ('','".strftime("%Y-%m-%d, %H:%M:%S")."','".strftime("%d %B %Y")."','".strftime("%H:%M")."','".$sms_username."','".$sms_usermail."','".$originator."','recipient_name','".$recipient_number."','".$message."','".$del_date."','".$del_time."','".$deliverydate."','".$sms_id."','".$credits_costs."')");
				//require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
				dbDelta($update_messages);					
				
				// and update credits
				$old_credits = get_the_author_meta( 'icsms_user_credits', $current_user->ID );
				$new_credits = $old_credits - $credits_costs;
				update_user_meta( $current_user->ID, 'icsms_user_credits', $new_credits );		
			
				// screen message on succes		
				?>
				<div class="updated">
				<p><?php esc_html_e('SMS bericht verstuurd. ', 'istie_sms'); ?><br/>
				<?php //esc_html_e($credits_costs_text, 'istie_sms'); ?>
				<strong><?php esc_html_e('(Nieuw credit totaal: '.$new_credits.')', 'istie_sms'); ?></strong></p>
				<meta http-equiv="refresh" content="5;url=">
				</div>
				<?php
			} else {
				// screen message on failure		
				?>
				<div class="error">
				<p><?php esc_html_e('Het versturen van de SMS is niet gelukt!', 'istie_sms'); ?><br/>
				<?php esc_html_e('Errorcode: '. $sms->getResultCode() .'', 'istie_sms'); ?><br/>
				<?php esc_html_e('Errorbericht: '. $sms->getResultMessage() .'', 'istie_sms'); ?></p>
				</div>
				<?php		
			} // end mollie
		} // end errorcheck
	} //end send formcheck	
	
?>

<script type="text/javascript">
jQuery(document).ready(function() {
    jQuery('#WPdatePicker').datepicker({
        dateFormat : 'dd-mm-yy'
    });
});
</script>
<?php /*--- load extra javascripts for character count ----------------------------------*/	?>
<script type="text/javascript" src="<?php echo WP_PLUGIN_URL; ?>/istiesms/includes/js/calcCharactersLeft.js"></script>
<!--<script type="text/javascript" src="<?php echo WP_PLUGIN_URL; ?>/istiesms/includes/js/jquery.min.js"></script>-->
	<?php _e('Ook voor sms-berichten die op een later tijdstip verzonden moeten worden, wordt direct 1 credit van je account afgeschreven.', 'istie_sms'); ?><br />
	<?php _e('Op de <a href="admin.php?page=istiesms/sms_credits.php">bestelpagina</a> staat een prijsoverzicht en kun je nieuwe credits aanvragen bij de beheerder van <?php echo home_url(); ?>.', 'istie_sms'); ?><br/>
<?php	
	
	if (!empty($sms_credits) && ($sms_credits > 0)) {
		echo "<div class=\"messages\">";
		// create message form
		echo '<form name="icsms_form1" method="post" action="" enctype="multipart/form-data" >';
		echo '<input type="hidden" name="sendsms_form" value="SEND">';
		
		if (!empty($smssend_errors)) {
			echo '<div class="error">';
			echo '<p>'. __('Je bent onderstaande vergeten of hebt het verkeerd ingevuld:', 'istie_sms') .'</p>';
			echo '<span class="order_error"><ul>';
			foreach ($smssend_errors as $f) {
				echo '<li>' . $f . '</li>';
			}
			echo '</ul></span></div>';
		}		
		$timezone_format = _x('l j F Y, G:i', 'timezone date format');
		echo '<p align="right"><b>'. __('Huidige datum en tijd:', 'istie_sms') .'</b> '. date_i18n($timezone_format) .'&nbsp;&nbsp; </p>';
		echo '<table border="0" cellpadding="1" cellspacing="5" width="100%" class="notable"><tbody>';
		echo '<tr>';
		echo '<td valign="top" colspan="3">';
		//echo '<hr style="height: 2px; width: 100%;" color="#E4E4E4" noshade="noshade" align="center" />';
		echo '</td></tr><tr>';
		echo '<td valign="top"><b>'. __('Je Naam', 'istie_sms') .'</b></td><td></td>';
		echo '<td valign="top" align="left"><input type="text" name="originator" class="input" size="25" maxlength="14" value="';
		if (isset($_POST['originator'])) {
			echo $_POST['originator'];
		} else {
			echo substr($sms_sendname, 0, 11);
		}
		echo '" /> <br/><small>'. __('Vul hier je naam of 06-nummer in. Dit is dan de afzender van het SMS-bericht.', 'istie_sms') .'</small></td>';
		echo '</tr><tr>';
		echo '<td valign="top"><b>'. __('Ontvanger', 'istie_sms') .'</b></td><td valign="top" align="right">00316</td>';
		echo '<td valign="top" align="left"><input type="text" name="recipient_number" class="input" size="25" maxlength="8" value="';
		if (isset($_POST['recipient_number'])) {
			echo $_POST['recipient_number'];
		} else {
			echo $sms_mobile;
		}
		echo '" /> <br/><small>'. __('Vul hier het nummer van de ontvanger in zonder landcode en 06 (Dus: 06-12345678 als: 12345678)', 'istie_sms') .'</small></td>';
		echo '</tr><tr>';
		
		echo '<td valign="top"><b>'. __('SMS bericht', 'istie_sms') .'</b></td><td></td>';
		echo '<td valign="top">';
		echo '<textarea name="message" id="CharsLeft" cols="85" rows="7" wrap="virtual" maxlength="612" >';
		if (isset($_POST['message'])) { 
			echo $_POST['message'];
		}
		echo '</textarea>';
		
		echo '<span id="CharCountLabel1"> </span>';
		echo '  <br /><small>'. __('Typ je SMS-bericht (160 tekens = 1 sms-bericht, max. 4 sms-berichten)', 'istie_sms') .'</small></td>';
		
		
		echo '</tr><tr>';
		echo '<td valign="top"><br/><b>'. __('Datum & Tijd', 'istie_sms') .'</b></td><td></td>';
		echo '<td><br/><small>'. __('Kies de juiste datum en de exacte tijd wanneer je bericht moet worden verzonden.', 'istie_sms') .'</small><br/>';
		echo '<input type="text" name="date" id="WPdatePicker" class="WPdatePicker" readonly="true" value="';
		if (isset($_POST['date'])) { 
			echo $_POST['date'];
		}		
		echo '" />';
		//echo '<img style="bottom:0px;" src="'. WP_PLUGIN_URL .'/istiesms/images/office-calendar-2.png" alt="date">';
		
		echo '  </td>';
		echo '</tr><tr>';
		echo '<td valign="top"><b></b></td><td></td>';
		echo '<td valign="top" align="left">';
		echo '<input type="text" name="hour" class="input" size="2" maxlength="2" value="';
		$hour = date('H',current_time('timestamp',0));
		$min = date('i',current_time('timestamp',0));
		//set minutes always + 5min in future
		$min = $min+5;
		if($min > 59) { 
			$min = $min-60; 
			$hour = $hour+1; 
			if($hour < 10) {
				$hour = '0'. $hour. '';
			}
		}
		if($min < 10) { $min = '0'. $min. ''; }
		
		if (isset($_POST['hour'])) {
			echo $_POST['hour'];
		} else {
			echo $hour;
		}
		echo '" />&nbsp;:&nbsp;';
		echo '<input type="text" name="min" class="input" size="2" maxlength="2" value="';
		if (isset($_POST['min'])) {
			echo $_POST['min'];
		} else {
			echo $min;
		}
		echo '" />';	
		echo ' <small>'. __('Vul de tijd in 24-uurs formaat in (bijv. 21:05).', 'istie_sms') .'</small>';
		echo '<br/></td>';
		echo '</tr><tr>';	
		
		echo '<td valign="top" colspan="3">';
		echo '<hr style="height: 2px; width: 100%;" color="#E4E4E4" noshade="noshade" align="center" /><br/><center>';		
		
		echo '<input type="submit" class="button-primary" name="send" value="Verzenden" ';
		if ($sms_username == 'demo') {
			echo "onClick=\"return alert('In de demo versie kun je geen SMS-bericht verzenden!');\"/>";
		} else {
			echo "onClick=\"return confirm('Klik op OK om je SMS-bericht te verzenden, of klik op Annuleren om dit venster te sluiten.');\">";
		}
		echo '&nbsp;&nbsp;&nbsp;';
		//echo '<input type="reset" class="button" name="reset" value="Reset">&nbsp;';
		echo '</td></tr>';	
		echo '<tr>';
		echo '<td valign="top" colspan="3">';
		echo '<b>'. __('N.B.', 'istie_sms') .'</b><br/>'. __('Door op de knop verzenden te klikken wordt er meteen een sms-bericht verstuurd. ', 'istie_sms') .' '. __('Het is mogelijk om sms-berichten die in je <a href="admin.php?page=istiesms/sms_history.php">overzicht</a> staan te annuleren of te verwijderen. Voor het verwijderen van berichten die nog niet zijn verstuurd krijg je natuurlijk ook weer 1 credit terug.', 'istie_sms') .'';
		echo '</td></tr>';
		echo '</tr></tbody></table></form>';
		echo '</div>';
		
	}	
}
// add metabox
add_meta_box('icsms_meta_box_send', $sendsms_title, 'icsms_send_meta_box', 'istiesms-send', 'advanced', 'default');


//--- add all different metaboxes to this page ------------------------------------------*/
do_meta_boxes('istiesms-send','advanced',null);

echo '</div>'; //End of wrap class
?>

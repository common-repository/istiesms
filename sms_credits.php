<?php
/*
 * Script name: sms_credits.php
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
		echo "add_postbox_toggles('istiesms-credits');"; //For WP2.6 and below
	}
	else{
		echo "postboxes.add_postbox_toggles('istiesms-credits');"; //For WP2.7 and above
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
		<h3><strong>Credits</strong></h3>
		<div class="ic_border"></div>
		<?php _e('Credits heb je nodig om sms-berichten te versturen of te agenderen. Het versturen van 1 sms-bericht kost je 1 credit. Bij Prijslijst kun je zien wat de kosten zijn. Ga naar Credits kopen om deze aan te schaffen, als je betaling binnen is worden deze op je account bijgeschreven. Je kunt dan direct je eigen sms-berichten versturen.', 'istie_sms'); ?> <br/>
		<br /><br />	
	</div>
</div>
<br />

<?php
/*--- Algemeen --------------------------------------------------------------------------*/
$info_title = '<div class="icon16" id="icon-edit-comments"></div>';
$info_title .= '<strong>'. __('Credits bijkopen.', 'istie_sms') .'</strong>';
function icsms_buy_meta_box() {
	// get user info
	global $current_user;
	//wp_get_current_user();
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
	// ---------- show credits
	if (empty($sms_credits) || $sms_credits <= 0 ) {
		echo '<div class="error fade">';
		echo '<p>';
		echo __('Je hebt geen credits om sms-berichten te versturen. <a href="">Credits kopen</a><br/>', 'istie_sms');
		echo '</p>';
		echo '</div>';
	} else {
		echo '<div class="updated">';
		echo '<p>';
		echo __('Je hebt nog <b>'.$sms_credits.'</b> credits.', 'istie_sms');
		echo '</p>';	
		echo '</div>';
	}	
	_e('Kies het juiste aantal credits en klik op Bestel. Er wordt meteen een aanvraag voor nieuwe credits gedaan.', 'istie_sms');
	// check is form is submitted
	if(isset($_POST['icsms_order_smscredits']) && $_POST['icsms_order_smscredits'] == 'ORDER') {
		// read the code submitted by form
		$post_sms_credits = $_POST[ 'icsms_credits'];
		// get prices credits
		$smscredits_10x = get_option( 'icsms_credits_10x');
		$smscredits_20x = get_option( 'icsms_credits_20x');
		$smscredits_50x = get_option( 'icsms_credits_50x');
		if ($post_sms_credits == 10) { $sms_amount = $smscredits_10x; }
		if ($post_sms_credits == 20) { $sms_amount = $smscredits_20x; }
		if ($post_sms_credits == 50) { $sms_amount = $smscredits_50x; }
		$new_credits = $sms_credits + $post_sms_credits;		
		$icsms_payment_method = $_POST[ 'icsms_payment'];
		// prepare error list
		unset($smsorder_errors);	
		if ($post_sms_credits == '') {
		  $smsorder_errors[] = __('Het aantal credits is niet duidelijk', 'istie_sms');
		}		
		if ($icsms_payment_method == '') {
		  $smsorder_errors[] = __('De betalingswijze is niet duidelijk', 'istie_sms');
		}		
		if (empty($icsms_admin_username) || empty($icsms_admin_email) || empty($icsms_admin_bankrek) || empty($icsms_admin_banktnv)  ) {
		  $smsorder_errors[] = __('De SMS module is nog niet geactiveerd. Neem contact op met de beheerder van de site', 'istie_sms');
		}
		if ($sms_username == 'demo') {	
			$smsorder_errors[] = __('In de demo versie kun je geen credits bestellen!', 'istie_sms');
		}		
		// Are there any errors?
		if (empty($smsorder_errors)) {
			// update screen message
			?>
			<div class="updated">
			<p><?php _e( 'Er zijn <strong>'. $post_sms_credits .'</strong> nieuwe credits besteld, een bevestigings email met betalingsinstructie is verstuurd naar <strong>'. $sms_usermail .'</strong>. Als de betaling binnen is worden je credits bijgeschreven op je account.', 'istie_sms'); ?></strong></p>
			</div>
			<?php		
			// Send mail to user :
			$cust_email = $sms_usermail;
			$cust_subject = "Bestelling: ".$post_sms_credits." SMS-credits besteld bij ". home_url() ." ";
			$cust_message = "".strftime("%d %B %Y, %H:%M")."\n\n\nHallo ".$sms_username.",\n\nJe hebt ".$post_sms_credits." SMS-credits besteld bij ". home_url() ."\n\nMaak ".$sms_amount." euro over op bankrekening: ".$icsms_admin_bankrek."\nT.n.v. ".$icsms_admin_banktnv."\n".$icsms_admin_bankname." ".$icsms_admin_bankplace."\n\nAls de betaling binnen is worden je SMS-credits zo snel mogelijk bijgeschreven bij je account.\n\n\n==== DISCLAIMER====================================================\nDe informatie opgenomen in dit bericht kan vertrouwelijk zijn en\nis uitsluitend bestemd voor de geadresseerde. Indien u dit bericht\nonterecht ontvangt, wordt u verzocht de inhoud niet te gebruiken en\nde afzender direct te informeren door het bericht te retourneren.\n===================================================================\n\n";
			//$user_header  = 'MIME-Version: 1.0' . "\r\n";
			//$user_header .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			$cust_header = 'From: '.$icsms_admin_username.' <'.$icsms_admin_email.'>' . "\r\n";
			$cust_header .=	'Reply-To: '.$icsms_admin_email.'' . "\r\n";			
			$cust_header .=	'X-Mailer: PHP/' . phpversion() . '' . "\r\n";	
			@mail($cust_email, $cust_subject, $cust_message, $cust_header);			
			
			// mail to admin
			$admin_subject = "Order: ".$post_sms_credits." SMS-jes from ".$sms_usermail."";
			$admin_message = "".strftime("%d %B %Y, %H:%M")."\n\n\nOverzicht bestelling:\n\nAantal credits besteld: ".$post_sms_credits."\nHuidige credits: ".$sms_credits."\nTotaal (incl. nieuwe): ".$new_credits."\nBedrag: ".$sms_amount."\nLoginnaam: ".$sms_username."\nEmail: ".$sms_usermail."\n\n". home_url() ."\n\n";			
			$admin_header = 'From: '.$sms_username.' <'.$sms_usermail.'>' . "\r\n" .
			'Reply-To: '.$icsms_admin_email.'' . "\r\n" .
			'X-Mailer: PHP/' . phpversion();
				
			@mail($icsms_admin_email, $admin_subject, $admin_message, $admin_header);	
		// there are some errors so display them
		} else {		
			echo '<div class="error">';
			echo __('<p>Er ging iets mis met bestellen, probeer het later nog eens.</p>', 'istie_sms');
			echo '<span class="order_error"><ul>';
			foreach ($smsorder_errors as $f) {
				echo '<li>' . $f . '</li>';
			}
			echo '</ul></span></div>';
		}
	}	
	//Start order form
	?>
	<form name="icsms_form" method="post" action="">
	<input type="hidden" name="icsms_order_smscredits" value="ORDER">
	  <table class="form-table">
		<tr>
			<td><label for="icsms_credits"><?php _e('Aantal:', 'istie_sms'); ?></label></td>
			<td><select class="input" id="icsms_credits" name="icsms_credits">
				<option value="10" SELECTED>10 credits &nbsp;</option>
				<option value="20">20 credits &nbsp;</option>
				<option value="50">50 credits &nbsp;</option>
				</select></td>
		</tr>
		<tr>
			<td valign="top"><?php _e('Betalingsmethode', 'istie_sms'); ?></td>
			<td>
			<input type="radio" name="icsms_payment" id="icsms_payment" checked value="Vooruitbetaling"> <?php _e('Vooruitbetaling', 'istie_sms'); ?><br/>
			<input type="radio" name="icsms_payment" id="icsms_payment" disabled value="Online">&nbsp;<?php _e('Online (Wellicht in nabije toekomst mogelijk)', 'istie_sms'); ?><br/>
			</td>
		</tr>
		<tr>
			<td colspan="2" align="center">
			<hr style="height: 2px; width: 100%;" color="#E4E4E4" noshade="noshade" align="center" /><br/>
			
			<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Bestel'); ?>" onClick="return confirm('Klik op OK om de credits te bestellen, of klik op Annuleren om dit venster te sluiten.');" /></td>
		</tr>
		
	  </table>
	</form>
	<b><?php _e('N.B.', 'istie_sms'); ?></b><br/>
	<?php _e('Door op de knop Bestel te klikken wordt er een email verstuurd naar het door jou opgegeven mailadres (', 'istie_sms'); ?> 
	<?php echo esc_attr( get_the_author_meta( 'user_email', $current_user->ID ) ); ?>
	<?php _e(') met daarin verdere uitleg en betalingsgegevens. Als de betaling binnen is worden de aangevraagde credits op je account bijgeschreven.', 'istie_sms'); ?>
<?php


}
add_action( 'show_user_profile', 'icsms_buy_meta_box' );
add_action( 'edit_user_profile', 'icsms_buy_meta_box' );
// add metabox
add_meta_box('icsms_meta_box_buy', $info_title, 'icsms_buy_meta_box', 'istiesms-credits', 'advanced', 'default');

/*--- Update --------------------------------------------------------------------------*/
$update_title = '<div class="icon16" id="icon-plugins"></div>';
$update_title .= '<strong>'. __('Prijslijst', 'istie_sms') .'</strong>';
function icsms_pricelist_meta_box() {
	$smscredits_10x = get_option( 'icsms_credits_10x');
	$smscredits_20x = get_option( 'icsms_credits_20x');
	$smscredits_50x = get_option( 'icsms_credits_50x');
	$sms1credit_10 = $smscredits_10x / 10;
	$sms1credit_20 = $smscredits_20x / 20;
	$sms1credit_50 = $smscredits_50x / 50;
?>
	<span class="pricelist" title="Prijslijst.">
	<ul>
	<li>10 credits = &euro; <?php echo $smscredits_10x; ?> &nbsp;&nbsp;<i>
	(<?php echo round($sms1credit_10, 2); ?> ct/sms)</i>
	<li>25 credits = &euro; <?php echo $smscredits_20x; ?> &nbsp;&nbsp;<i>
	(<?php echo round($sms1credit_20, 2); ?> ct/sms)</i>
	<li>50 credits = &euro; <?php echo $smscredits_50x; ?> &nbsp;&nbsp;<i>
	(<?php echo round($sms1credit_50, 2); ?> ct/sms)</i>
	</ul>
	<?php _e('Prijzen van meer credits op aanvraag.', 'istie_sms'); ?>
	</span>
<?php
}
// add metabox
add_meta_box('icsms_meta_box_pricelist', $update_title, 'icsms_pricelist_meta_box', 'istiesms-credits', 'advanced', 'default');

//--- add all different metaboxes to this page ------------------------------------------*/
do_meta_boxes('istiesms-credits','advanced',null);

echo '</div>'; //End of wrap class
?>

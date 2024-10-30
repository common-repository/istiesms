<?php
/*
 * Script name: sms_options.php
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
		echo "add_postbox_toggles('sms_options');"; //For WP2.6 and below
	}
	else{
		echo "postboxes.add_postbox_toggles('sms_options');"; //For WP2.7 and above
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
//--- Starting --------------------------------------------------------------------------*/
?>
<div class="wrap">
<div class="ic_infowrap">
<a href="http://www.istiecool.nl" target="_blank"><img src="<?php echo ''. WP_PLUGIN_URL .'/istiesms/images/Istiecool.nl_64x64.png'; ?>" class="istielogo" alt="istiecool" width="50" height="50"></a>
	<div class="ic_infotext">
		<?php $version = get_option( 'icsms_version'); ?>
		<div class="icon32" id="icon-index"><br /></div>
		<h3><strong><?php echo __( 'IstieSMS instellingen', 'istie_sms' ); ?></strong><span style="font-size:14px; margin-left: 15px;"><small>versie: <?php echo $version; ?></small></span></h3>
		<div class="ic_border"></div>
		<?php _e('Zorg dat alle onderstaande instellingen goed zijn ingevuld om gebruikers zelf sms-berichten te laten sturen of te agenderen. Vul bij Credits prijslijst eventueel in hoeveel credits een gebruiker krijgt als welkomst kadootje na zijn registratie.', 'istie_sms'); ?><br/>
		<br/>
	</div>
</div>


<?php
/*--- Messagebird.com instellingen ------------------------------------------------------------*/
$mollie_title = '<div class="icon16" id="icon-options-general"></div>';
$mollie_title .= __('API Instellingen voor Messagebird', 'istie_sms');
//$mollie_title .= '<span style="font-size:14px; margin-left: 15px;">';
//$mollie_title .= '<small>()</small></span>';

function icsms_mollie_meta_box() {
?>
<!--<div class="icsms_admin_wrap">-->
	<?php _e('Onderstaande instellingen zijn de gebruikersnaam en wachtwoord welke je ook gebruikt om in te loggen bij <a href="http://www.messagebird.com" target="_blank">Messagebird.com</a> Om werkelijk sms-berichten te versturen via deze plugin is het dus vereist om een account bij Messagebird te hebben.', 'istie_sms'); ?><br/>
<?php	
	$icsms_mollie_username = get_option( 'icsms_mollie_username');
	$icsms_mollie_passwd = base64_decode(get_option( 'icsms_mollie_passwd'));
	// check is form is submitted
	if(isset($_POST['mollie_form_submit']) && $_POST['mollie_form_submit'] == 'MOLLIE') {
		// read the code submitted by form
		$icsms_mollie_username = $_POST[ 'icsms_mollie_username'];
		$icsms_mollie_passwd = base64_encode($_POST[ 'icsms_mollie_passwd']);
		// save to db
		update_option( 'icsms_mollie_username', $icsms_mollie_username);
		update_option( 'icsms_mollie_passwd', $icsms_mollie_passwd);
		// update screen message
		?>
		<div class="updated">
		<p><strong><?php _e('API Instellingen bewaard', 'istie_sms'); ?></strong></p>
		</div>
		<?php	
	}
	//settings form
	?>
	<form name="icsms_form1" method="post" action="">
	<input type="hidden" name="mollie_form_submit" value="MOLLIE">
	  <table>
		<tr>
			<td><label for="icsms_mollie_username"><?php _e('Gebruikersnaam:', 'istie_sms'); ?></label></td>
			<td><input type="text" id="icsms_mollie_username" name="icsms_mollie_username" value="<?php echo $icsms_mollie_username; ?>" size="25"></td>
		</tr>
		<tr>
			<td><label for="icsms_mollie_passwd"><?php _e('Wachtwoord:', 'istie_sms'); ?></label></td>
			<td><input type="password" id="icsms_mollie_passwd" name="icsms_mollie_passwd" value="<?php echo $icsms_mollie_passwd; ?>" size="25"></td>
		</tr>
		<tr>
			<td colspan="2" align="right"><input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Bewaren', 'istie_sms'); ?>" /></td>
		</tr>
	  </table>
	</form>
<!--</div>-->
<?php
}
//-- add_meta_box('id', 'title', 'callback', 'page', 'context', 'priority'); --//
add_meta_box('icsms_meta_box_mollie', $mollie_title, 'icsms_mollie_meta_box', 'sms-options', 'normal', 'sorted');
//do_meta_boxes('istiesms','advanced',null);


/*--- Kosten SMS Credits ------------------------------------------------------------*/
$credits_title = '<div class="icon16" id="icon-tools"></div>';
$credits_title .=  __('Credits prijslijst', 'istie_sms');
//$credits_title .= '<span style="font-size:14px; margin-left: 15px;">';
//$credits_title .= '<small>()</small></span>';

function icsms_pricelist_meta_box() {
?>
<!--<div class="icsms_admin_wrap">-->
	<?php _e('Gebruikers kunnen 10, 20, of 50 credits kopen. Prijzen van credits voor de gebruikers van deze site kun je zelf bepalen door onderstaande te veranderen.', 'istie_sms'); ?><br/>
<?php	
	$smscredits_10x = get_option( 'icsms_credits_10x');
	$smscredits_20x = get_option( 'icsms_credits_20x');
	$smscredits_50x = get_option( 'icsms_credits_50x');
	$smscredits_usergift = get_option( 'icsms_credits_usergift');
	if(empty($smscredits_usergift)) { $smscredits_usergift = '0'; }
	$sms1credit_10 = $smscredits_10x / 10;
	$sms1credit_20 = $smscredits_20x / 20;
	$sms1credit_50 = $smscredits_50x / 50;
	// check is form is submitted
	if(isset($_POST['credit_form_submit']) && $_POST['credit_form_submit'] == 'CREDIT') {
		// read the code submitted by form
		$smscredits_10x = $_POST[ 'smscredits_10x'];
		$smscredits_20x = $_POST[ 'smscredits_20x'];
		$smscredits_50x = $_POST[ 'smscredits_50x'];
		$smscredits_usergift = $_POST[ 'smscredits_usergift'];
		$sms1credit_10 = $smscredits_10x / 10;
		$sms1credit_20 = $smscredits_20x / 20;
		$sms1credit_50 = $smscredits_50x / 50;	
		// prepare error list
		unset($errors);	
		if( $smscredits_10x < 0 || $sms1credit_20 < 0  || $sms1credit_50 < 0 || $smscredits_usergift < 0  ) {
		  $errors[] = __('Je geen negatieve waarde invoeren.', 'istie_sms');
		}
		if( !is_numeric($smscredits_10x) || !is_numeric($sms1credit_20) || !is_numeric($sms1credit_50) ) {
		  $errors[] = __('Je kunt alleen numerieke waarden invoeren.', 'istie_sms');
		}					
		if( !is_numeric($smscredits_usergift) && !empty($smscredits_usergift) ) {
			$errors[] = __('Je kunt alleen numerieke waarden invoeren.', 'istie_sms');
		}
		if (empty($errors)) {		
			// save to db
			update_option( 'icsms_credits_10x', $smscredits_10x);
			update_option( 'icsms_credits_20x', $smscredits_20x);
			update_option( 'icsms_credits_50x', $smscredits_50x);
			update_option( 'icsms_credits_usergift', $smscredits_usergift);
			// update screen message
			?>
			<div class="updated">
			<p><strong><?php _e('Credit prijzen worden bewaard ', 'istie_sms'); ?></strong><small><?php //_e('(De pagina wordt ververst om de kosten te zien)', 'istie_sms'); ?></small></p>
			<!--<meta http-equiv="refresh" content="3;url=">-->
			</div>
			<?php
		} else {
			?>
			<div class="error">
			<p><strong><?php _e('Je bent onderstaande vergeten of hebt het verkeerd ingevuld:', 'istie_sms'); ?></strong></p>
			<span class="order_error"><ul>
			<?php 
			foreach ($errors as $f) {
				echo '<li>' . $f . '</li>';
			}
			?>
			</ul></span>
			</div>
			<?php		
		}

		
	}
	//settings form
	?>
	<form name="icsms_form1" method="post" action="">
	<input type="hidden" name="credit_form_submit" value="CREDIT">
	  <table>
		<tr>
			<td><label for="smscredits_10x"><?php _e('Kosten voor:', 'istie_sms'); ?> <strong>10</strong> credits: </label></td>
			<td><input type="text" id="smscredits_10x" name="smscredits_10x" value="<?php echo $smscredits_10x; ?>" size="10"><span class="description">
			<?php _e(' (Kosten per credit = &euro; '. number_format(round($sms1credit_10, 2), 2, '.', '') .') '); ?></span></td>
		</tr>
		<tr>
			<td><label for="smscredits_20x"><?php _e('Kosten voor:', 'istie_sms'); ?> <strong>20</strong> credits: </label></td>
			<td><input type="text" id="smscredits_20x" name="smscredits_20x" value="<?php echo $smscredits_20x; ?>" size="10"><span class="description">
			<?php _e(' (Kosten per credit = &euro; '. number_format(round($sms1credit_20, 2), 2, '.', '') .')'); ?></span></td>
		</tr>
		<tr>
			<td><label for="smscredits_50x"><?php _e('Kosten voor:', 'istie_sms'); ?> <strong>50</strong> credits: </label></td>
			<td><input type="text" id="smscredits_50x" name="smscredits_50x" value="<?php echo $smscredits_50x; ?>" size="10"><span class="description">
			<?php _e(' (Kosten per credit = &euro; '. number_format(round($sms1credit_50, 2), 2, '.', '') .') '); ?></span></td>
		</tr>	
		<tr>
			<td><label for="smscredits_usergift"><?php _e('Aantal credits na registratie', 'istie_sms'); ?>:&nbsp;</label></td>
			<td><input type="text" id="smscredits_usergift" name="smscredits_usergift" value="<?php echo $smscredits_usergift; ?>" size="10"><span class="description">
			<?php _e(' (Geef een aantal sms credits kado na registratie van een nieuwe gebruiker) '); ?></span></td>
		</tr>			
		<tr>
			<td colspan="2" align="right"><input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Bewaren'); ?>" /></td>
		</tr>
	  </table>
	</form>
<!--</div>-->
<?php
}
add_meta_box('icsms_meta_box_credits', $credits_title, 'icsms_pricelist_meta_box', 'sms-options', 'normal', 'sorted');


/*--- Gegevens SMS beheerder -----------------------------------------------------------------*/
$beheer_title = '<div class="icon16" id="icon-users"></div>';
$beheer_title .= __('Gegevens SMS beheerder', 'istie_sms');
$beheer_title .= '<span style="font-size:14px; margin-left: 15px;">';
$beheer_title .= '<small></small></span>';
function icsms_admin_meta_box() {
?>
	<?php _e('Onderstaande gegevens worden gebruikt tijdens een bestelling van nieuwe SMS-credits van een gebruiker. Na een bestelling ontvangt de gebruiker een email met betalingsinstructie. Indien de verplichte velden niet zijn ingevuld is het niet mogelijk om credits te bestellen.', 'istie_sms'); ?><br />
<?php	
	$icsms_admin_username = get_option( 'icsms_admin_username');
	$icsms_admin_email = get_option( 'icsms_admin_email');
	$icsms_admin_bankrek = get_option( 'icsms_admin_bankrek');
	$icsms_admin_banktnv = get_option( 'icsms_admin_banktnv');		
	$icsms_admin_bankname = get_option( 'icsms_admin_bankname');
	$icsms_admin_bankplace = get_option( 'icsms_admin_bankplace');

	// check is form is submitted
	if(isset($_POST['admin_form_submit']) && $_POST['admin_form_submit'] == 'ADMIN') {
		// read the code submitted by form
		$icsms_admin_username = $_POST[ 'icsms_admin_username'];
		$icsms_admin_email = $_POST[ 'icsms_admin_email'];
		$icsms_admin_bankrek = $_POST[ 'icsms_admin_bankrek'];
		$icsms_admin_banktnv = $_POST[ 'icsms_admin_banktnv'];
		$icsms_admin_bankname = $_POST[ 'icsms_admin_bankname'];
		$icsms_admin_bankplace = $_POST[ 'icsms_admin_bankplace'];
		// prepare error list
		unset($errors);	
		if ($icsms_admin_username == "") {
		  $errors[] = __('Naam van afzender is verplicht.', 'istie_sms');
		}		
		if ($icsms_admin_email == "") {
		  $errors[] = __('Email niet ingevuld.', 'istie_sms');
		}	
		if ($icsms_admin_bankrek == "") {
		  $errors[] = __('Geen bankrekening', 'istie_sms');
		}	
		if ($icsms_admin_banktnv == "") {
		  $errors[] = __('T.n.v. is niet ingevuld.', 'istie_sms');
		}			
		if (empty($errors)) {
			// save to db
			update_option( 'icsms_admin_username', $icsms_admin_username);
			update_option( 'icsms_admin_email', $icsms_admin_email);
			update_option( 'icsms_admin_bankrek', $icsms_admin_bankrek);
			update_option( 'icsms_admin_banktnv', $icsms_admin_banktnv);
			update_option( 'icsms_admin_bankname', $icsms_admin_bankname);
			update_option( 'icsms_admin_bankplace', $icsms_admin_bankplace);		
			// update screen message
			?>
			<div class="updated">
			<p><strong><?php _e('Gegevens beheerder bewaard', 'istie_sms'); ?></strong></p>
			</div>
			<?php
		} else {
			?>
			<div class="error">
			<p><strong><?php _e('Je bent onderstaande vergeten of hebt het verkeerd ingevuld:', 'istie_sms'); ?></strong></p>
			<span class="order_error"><ul>
			<?php 
			foreach ($errors as $f) {
				echo '<li>' . $f . '</li>';
			}
			?>
			</ul></span></div>
			<?php		
		}
	}
	//settings form
	?>
	<form name="icsms_form1" method="post" action="">
	<input type="hidden" name="admin_form_submit" value="ADMIN">
	  <table class="ic_table">
		<tr>
			<td><label for="icsms_admin_username"><?php _e('Naam:', 'istie_sms'); ?> *</label></td>
			<td><input type="text" id="icsms_admin_username" name="icsms_admin_username" value="<?php echo $icsms_admin_username; ?>" size="25"><br />
			<span class="description"><?php _e('Naam van de afzender als een gebruiker nieuwe credits bestelt.', 'istie_sms'); ?></span></td>
		</tr>
		<tr>
			<td><label for="icsms_admin_email"><?php _e('Email:', 'istie_sms'); ?> *</label></td>
			<td><input type="text" id="icsms_admin_email" name="icsms_admin_email" value="<?php echo $icsms_admin_email; ?>" size="25"><br />
			<span class="description"><?php _e('Email afzender bij bestelling nieuwe credits.', 'istie_sms'); ?></span></td>
		</tr>
		<tr>
			<td><label for="icsms_admin_bankrek"><?php _e('Bankrekening:', 'istie_sms'); ?> *</label></td>
			<td><input type="text" id="icsms_admin_bankrek" name="icsms_admin_bankrek" value="<?php echo $icsms_admin_bankrek; ?>" size="25"><br />
			<span class="description"><?php _e('Bankrekening waarnaar de gebruiker geld kan overmaken om credits te kopen.', 'istie_sms'); ?></span></td>
		</tr>
		<tr>
			<td><label for="icsms_admin_banktnv"><?php _e('T.n.v.:', 'istie_sms'); ?> *</label></td>
			<td><input type="text" id="icsms_admin_banktnv" name="icsms_admin_banktnv" value="<?php echo $icsms_admin_banktnv; ?>" size="25"><br />
			<span class="description"><?php _e('Naam waarop bovenstaand rekeningnummer staat geregistreerd.', 'istie_sms'); ?></span></td>
		</tr>
		<tr>
			<td><label for="icsms_admin_bankname"><?php _e('Banknaam:', 'istie_sms'); ?> </label></td>
			<td><input type="text" id="icsms_admin_bankname" name="icsms_admin_bankname" value="<?php echo $icsms_admin_bankname; ?>" size="25"><br />
			<span class="description"><?php _e('Naam van de Bank', 'istie_sms'); ?></span></td>
		</tr>
		<tr>
			<td><label for="icsms_admin_bankplace"><?php _e('Plaats:', 'istie_sms'); ?> </label></td>
			<td><input type="text" id="icsms_admin_bankplace" name="icsms_admin_bankplace" value="<?php echo $icsms_admin_bankplace; ?>" size="25"><br />
			<span class="description"><?php _e('Plaats van bankvestiging.', 'istie_sms'); ?></span></td>
		</tr>		
		<tr>
			<td colspan="2"><span class="description"><?php _e('Velden met een * zijn verplicht.', 'istie_sms'); ?></span></td>
		</tr>			
		<tr>
			<td colspan="2" align="right"><input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Bewaren', 'istie_sms'); ?>" /></td>
		</tr>
	  </table>
	</form>
<?php
}
add_meta_box('icsms_meta_box_admin', $beheer_title, 'icsms_admin_meta_box', 'sms-options', 'normal', 'sorted');


//--- widgets on the side ---------------------------------------------------------------*/


/*--- Algemeen --------------------------------------------------------------------------*/
$info_title = '<div class="icon16" id="icon-edit-comments"></div>';
$info_title .= __('Algemeen', 'istie_sms');
function icsms_info_meta_box() {
?>
	<?php _e('M.b.v. deze plugin is het mogelijk om sms-berichten via je website te versturen. Ook kun je sms-berichten agenderen om ze op een later tijdstip te kunnen versturen.<br/>
	Een account bij <a href="http://www.messagebird.com" target="_blank">Messagebird</a> is vereist. Vervolgens kun je bij Messagebird credits kopen (sms sturen is nog niet gratis...) en de API instellingen voor je account invoeren bij de instellingen van deze plugin.', 'istie_sms'); ?><br /><br />
	<?php _e('Maak een gebruikersnaam demo aan om bezoekers te laten inloggen en de mogelijkheden te bekijken. Een demo gebruker kan geen echte sms-berichten sturen of credits aanvragen.', 'istie_sms'); ?>
<?php
}
// add metabox
add_meta_box('icsms_info', $info_title, 'icsms_info_meta_box', 'sms-options', 'side', 'sorted');

/*--- Update --------------------------------------------------------------------------*/
$update_title = '<div class="icon16" id="icon-plugins"></div>';
$update_title .= __('Donatie', 'istie_sms');
function icsms_donate_meta_box() {
?>
	<?php _e('Het ontwikkelen van deze plugin kost energie en vrije tijd. Voor vragen, wensen of opmerkingen over deze plugin kun je een mail sturen naar de <a href="mailto:info@istiecool.nl?subject=WP-sms-plugin">ontwikkelaar</a>.') ?><br /><br />
	<?php _e('Ben je tevreden of wil je de (verdere) ontwikkeling van deze plugin steunen dan wordt een donatie zeer op prijs gesteld.', 'istie_sms'); ?><br />
	<?php _e('Geef een <a href="http://www.istiecool.nl/donate" target="_blank">donatie</a> in bitcoins ', 'istie_sms'); ?><a href="http://www.istiecool.nl/donate" target="_blank"><img src="<?php echo WP_PLUGIN_URL; ?>/istiesms/images/bitcoin-24.png" alt="bitcoin" width="16px" height="16px" /></a><br />
	<a href="http://www.istiecool.nl/donate" target="_blank"><img src="<?php echo WP_PLUGIN_URL; ?>/istiesms/images/donation1btc_qrcode_1istie4U.png" alt="donate" width="230px" height="230px" /></a><br />	
	1istie4UEGBjXjzwzuwRAChSChj6FwXzY<br />	
<?php
}
// add metabox
add_meta_box('icsms_donate', $update_title, 'icsms_donate_meta_box', 'sms-options', 'side', 'sorted');


//--- add all different metaboxes to this page ------------------------------------------*/
echo '<div id="icsms-widgets" class="metabox-holder columns-2">';
echo '<div id="meta-container-1" class="postbox-container">';
		do_meta_boxes('sms-options','normal',null);
echo '</div>'; //End container-1
echo '<div id="meta-container-2" class="postbox-container">';
		do_meta_boxes('sms-options','side',null);
echo '</div>'; //End container 2
echo '</div>'; //End widgets-wrap

//--- add link to reset pointers (infoscherm) -------------------------------------------*/
//echo '<span style="text-align:right; display: block; margin-right: 20px;"><small>';
//echo '<a href="?page=istiesms/admin/sms_options.php&reset-pointer='. get_current_user_id() .'"> Reset pointer</a>';
//echo '</small></span>';	
echo '</div>'; //End of wrap class
?>
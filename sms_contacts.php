<?php
/*
 * Script name: sms_contacts.php
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
		echo "add_postbox_toggles('istiesms-contacts');"; //For WP2.6 and below
	}
	else{
		echo "postboxes.add_postbox_toggles('istiesms-contacts');"; //For WP2.7 and above
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
		<div class="icon32" id="icon-edit-comments"><br /></div>
		<h3><strong><?php _e('Contacten', 'istie_sms'); ?></strong></h3>
		<div class="ic_border"></div>
		<?php _e('Op deze pagina kun je een eigen adresboek samenstellen. Mensen die je regelmatig een SMS-bericht stuurt, zet je in je adresboek. Zo hoef je de 06-nummers niet te onthouden en kun je nog makkelijker SMS-berichten sturen.', 'istie_sms'); ?><br/>
		<br/>
		<?php _e('Ook kun je je persoonlijke gegevens aanvullen en eventueel een SMS-rekening openen. Het voordeel van een eigen SMS-rekening is dat je geen credits hoeft te kopen maar dat je aan het einde van de maand je verstuurde SMS-berichten afrekent. Je krijgt dan een rekening thuis gestuurd met een overzicht van het aantal gestuurde SMS-berichten. Je betaalt dus achteraf voor de werkelijk verstuurde SMS-berichten. Heb je niks verstuurd? Dan betaal je ook niks!', 'istie_sms'); ?><br/>
		<br/>
		<?php _e('Meer uitleg over elk onderwerp lees je door te klikken op Persoonlijk Adresboek of SMS-rekening.', 'istie_sms'); ?>
		<br /><br />	
	</div>
</div>
<br />

<?php

/*--- Adressbook --------------------------------------------------------------------------*/
$adres_title = '<div class="icon16" id="icon-edit-comments"></div>';
$adres_title .= '<strong>'. __('Persoonlijk adresboek', 'istie_sms') .'</strong>';
function icsms_adress_meta_box() {

	echo __('Persoonlijk adresboek', 'istie_sms'); 
	echo '<br />';

}
// add metabox
add_meta_box('icsms_meta_box_adress', $adres_title, 'icsms_adress_meta_box', 'istiesms-contacts', 'advanced', 'default');

/*--- Rekening --------------------------------------------------------------------------*/
$account_title = '<div class="icon16" id="icon-plugins"></div>';
$account_title .= '<strong>'. __('SMS-rekening', 'istie_sms') .'</strong>';
function icsms_account_meta_box() {

	echo __('SMS-rekening', 'istie_sms'); 
	echo '<br />';

}
// add metabox
add_meta_box('icsms_meta_box_account', $account_title, 'icsms_account_meta_box', 'istiesms-contacts', 'advanced', 'default');

//--- add all different metaboxes to this page ------------------------------------------*/
do_meta_boxes('istiesms-contacts','advanced',null);

echo '</div>'; //End of wrap class
?>

<?php
/**
 * Plugin Name: PesaPay for Woocommerce
 * Plugin URI:  https://pesapay.org
 * Description: Le moyen le plus facile d'accepter les paiements via PesaPay.
 * Version:     1.0
 * Author:      Félix Maroy
 * Author URI:  https://felixmaroy.com/
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: pesapayforwoocommerce
 * Domain Path: /
 */

// Interdire l'accès direct.
if (!defined('ABSPATH')){
	exit;
}

define('PPFWC_VER', '1.0');
if (! defined('PPFWC_PLUGIN_FILE')) {
	define('PPFWC_PLUGIN_FILE', __FILE__);
}

add_action('wp', function (){
	if (! in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))){
		deactivate_plugins(plugin_basename(__FILE__));
	}
});

register_activation_hook(__FILE__, 'wc_pesapayforwoocommerce_activation_check');
function wc_pesapayforwoocommerce_activation_check() 
{
	if (! in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))){
		deactivate_plugins(plugin_basename(__FILE__));
		exit('Veuillez installer Woocommerce pour utiliser cette extension');
	}

    if (! is_plugin_active('woocommerce/woocommerce.php')){
        deactivate_plugins(plugin_basename(__FILE__));
    }
}

add_action('activated_plugin', 'wc_pesapayforwoocommerce_detect_plugin_activation', 10, 2);
function wc_pesapayforwoocommerce_detect_plugin_activation($plugin, $network_activation) {
    if($plugin == 'pesapay/pesapay.php'){
        exit(wp_redirect(admin_url('admin.php?page=wc-settings&tab=checkout&section=pesapay')));
    }
}

add_action('deactivated_plugin', 'wc_pesapayforwoocommerce_detect_woocommerce_deactivation', 10, 2);
function wc_pesapayforwoocommerce_detect_woocommerce_deactivation($plugin, $network_activation)
{
    if ($plugin == 'woocommerce/woocommerce.php'){
        deactivate_plugins(plugin_basename(__FILE__));
    }
}

add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'pesapayforwoocommerce_action_links');
function pesapayforwoocommerce_action_links($links)
{
	return array_merge(
		$links, 
		array(
			'<a href="'.admin_url('admin.php?page=wc-settings&tab=checkout&section=pesapayforwoocommerce').'">&nbsp;Configuration</a>',
			'<a href="https://pesapay.org/api-doc/">&nbsp;API DOC</a>'
		)
	);
} 

add_filter('plugin_row_meta', 'pesapayforwoocommerce_row_meta', 10, 2);
function pesapayforwoocommerce_row_meta($links, $file)
{
	$plugin = plugin_basename(__FILE__);

	if ($plugin == $file) {
		$row_meta = array(
			'business'    => '<a href="' . esc_url('https://business.pesapay.org/') . '" target="_blank" aria-label="' . esc_attr__('Créer un Compte Business', 'woocommerce') . '">' . esc_html__('Business Portal', 'woocommerce') . '</a>',
			'tutorial' => '<a href="' . esc_url('https://pesapay.org/api-doc/') . '" target="_blank" aria-label="' . esc_attr__('Lire la Documentation', 'woocommerce') . '">' . esc_html__('Documentation', 'woocommerce') . '</a>'
		);

		return array_merge($links, $row_meta);
	}

	return (array) $links;
}

/*
 * Enregistrer PesaPay avec woocommerce
 */
add_filter( 'woocommerce_payment_gateways', 'pesapayforwoocommerce_add_to_gateways' );
function pesapayforwoocommerce_add_to_gateways( $gateways )
{
	$gateways[] = 'PesaPay for Woocommerce';
	return $gateways;
}

add_action( 'plugins_loaded', 'pesapayforwoocommerce_init', 0 );
function pesapayforwoocommerce_init() {
	class PesaPay extends WC_Payment_Gateway {

		function __construct() {

			// ID global de PesaPay
			$this->id = "pesapayforwoocommerce";

			// Afficher le titre
			$this->method_title = __( "PesaPay", 'pesapayforwoocommerce' );

			// Afficher la Description
			$this->method_description = __( "Accepter les paiements via PesaPay", 'pesapayforwoocommerce' );

			// Titre de la colonne latérale
			$this->title = __( "PesaPay", 'pesapayforwoocommerce' );


			$this->icon = apply_filters('woocommerce_pesapay_icon', plugins_url('inc/logo.jpg', __FILE__));

			$this->has_fields = true;

			// Définir les paramètres
			$this->init_form_fields();

			// Charger les paramètres
			$this->init_settings();
			
			// Convertir ces paramètres en variables qu'on va bientôt utiliser
			foreach ( $this->settings as $setting_key => $value ) {
				$this->$setting_key = $value;
			}
			
			// Enregistrer les paramètres
			if ( is_admin() ) {
				add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			}		
		} // Ceci est la fin de notre  End __construct()

		// Les informations à afficher dans l'administration pour la configuration
		public function init_form_fields() {
			$this->form_fields = array(
				'enabled' 			=> array(
					'title'			=> __( 'Activer / Désactiver', 'pesapayforwoocommerce' ),
					'label'			=> __( 'Accepter les paiements via PesaPay', 'pesapayforwoocommerce' ),
					'type'			=> 'checkbox',
					'default'		=> 'no',
				),
				'title' 			=> array(
					'title'			=> __( 'Titre', 'pesapayforwoocommerce' ),
					'type'			=> 'text',
					'desc_tip'		=> __( 'Description', 'pesapayforwoocommerce' ),
					'default'		=> __( 'PesaPay', 'pesapayforwoocommerce' ),
				),
				'shortcode' 		=> array(
					'title'			=> __( 'Compte Business PesaPay', 'pesapayforwoocommerce' ),
					'type'			=> 'text',
					'desc_tip'		=> __( 'Votre code à 5 chiffres livré après l\'inscription pour identifier votre Business.', 'pesapayforwoocommerce' ),
					'default' 		=> ''
				),
				'apiKey' 			=> array(
					'title'			=> __( 'PesaPay API Key', 'pesapayforwoocommerce' ),
					'type'			=> 'text',
					'desc_tip'		=> __( 'Vous trouverez votre API Key dans la page Profil de votre compte Business PesaPay.', 'pesapayforwoocommerce' ),
					'default' 		=> ''
				),
				'description' 		=> array(
					'title'			=> __( 'Instructions de Paiement', 'pesapayforwoocommerce' ),
					'type'			=> 'textarea',
					'desc_tip'		=> __( 'Intitulé du Paiement et procédure de paiement.', 'pesapayforwoocommerce' ),
					'default'		=> __( 'N\'oubliez pas de mettre votre numéro PesaPay en commençant par le code de votre pays. Ex: +243 pour la RDC.', 'pesapayforwoocommerce' ),
					'css'			=> 'max-width:400px;'
				),
				'instructions' 		=> array( 
					'title'       	=> __( 'Instruction de Remerciement', 'woocommerce' ),
					'type'       	=> 'textarea',
					'description'	=> __( 'Les instructions à afficher sur la page de remerciement.', 'woocommerce' ),
					'default'     	=> __( 'Merci pour votre commande. PesaPay vous enverra un message de confirmation.', 'woocommerce' ),
					'desc_tip'    	=> true,
				)
			);		
		}
		
		// Le code pour envoyer les données nécessaires aux serveurs de PesaPay
		public function process_payment( $order_id ) {
			global $woocommerce;

			$customer_order = new WC_Order( $order_id );

			$endpoint = 'https://pesapay-alpha.herokuapp.com/pesapay/onlinepayments/processpayment';
			$payload = array(
			  "amount" 		=> round($customer_order->order_total),
			  "api_key" 	=> $this->apiKey,
			  "description" => "Commande ".str_replace( "#", "", $customer_order->get_order_number() ),
			  "otp" 		=> $_POST['otp'],
			  "receiver" 	=> $this->shortcode,
			  "sender" 		=> $customer_order->billing_phone
			);

			// Enfin, PesaPay recevra cette requête pour valider ou refuser le paiement
			$response = wp_remote_post( $endpoint, array(
				'method'    	=> 'POST',
				'headers'     	=> array('Content-Type' => 'application/json; charset=utf-8'),
    			'body'        	=> json_encode($payload),
    			'data_format' 	=> 'body',
			) );

			if ( is_wp_error( $response ) ){ 
				wc_add_notice(  'Désolé, mais nous ne pouvons pas nous connecter aux serveurs de PesaPay.', 'error' );
			}

			if ( empty( $response['body'] ) ){
				wc_add_notice(  'Aucune information disponible pour cette transaction.', 'error' );
			}

			$data = json_decode( wp_remote_retrieve_body( $response ), true);

			// Si Woocommerce reçoit 1 ou 4 c'est à dire que la transaction a réussie
			if ( !isset($data["error"]) ) {
				$code 		= $data["responseCode"];
				$message 	= $data["responseMessage"];
				$count 		= $data["totalCount"];
				$result 	= $data["responseData"];
				$customer 	= $result["createdByCustomer"];

				// Enregistrer le paiement dans la base des données
				$post_id = wp_insert_post( 
					array(
						'post_title' 	=> 'Commande '.time(),
						'post_status'	=> 'publish',
						'post_type'		=> 'pesapay_ipn',
						'post_author'	=> $author,
				 	) 
				);

				update_post_meta( $post_id, '_customer', $customer["fullName"]);
				update_post_meta( $post_id, '_phone', $customer_order->billing_phone);
				update_post_meta( $post_id, '_transaction', $result["transactionNumber"]);
				update_post_meta( $post_id, '_amount', round($result["amount"]) );
				update_post_meta( $post_id, '_created', $result["createdOn"]);

				if($code == '00'){
					// Rediriger l'utilisateur vers la page de remerciement après un paiement réussi
					$customer_order->add_order_note( __( $result["description"], 'pesapayforwoocommerce' ) );
					$customer_order->payment_complete();
					$woocommerce->cart->empty_cart();

					if ( 'processing' == $order->status){ $customer_order->update_status('completed'); }
					$customer_order->update_status('completed');

					return array(
						'result'   => 'success',
						'redirect' => $this->get_return_url( $customer_order ),
					);
				} else {
					$customer_order->add_order_note( __( $message, 'pesapayforwoocommerce' ) );
					wc_add_notice( 'ECHEC: '.$message, 'error' );
					return array(
						'result'   => 'fail',
						'redirect' => $this->get_return_url( $customer_order ),
					);
				}
			} else {
				$timestamp 	= $data["timestamp"];
				$status 	= $data["status"];
				$message 	= $data["message"];
				$path 		= $data["path"];

				//Transaction echouée
				wc_add_notice( $data['error'], 'error' );
				$customer_order->add_order_note( 'Erreur: '. $data['error'] );
			}

		}

		public function payment_fields() {

			// Code pour envoyer un OTP à l'utilisateur avant de valider le paiement
			if ( $this->description ) {
				echo wpautop( wp_kses_post( $this->description.'<p id="otp-cont">
				<button id="req-otp">CLIQUER ICI POUR RECEVOIR LE CODE DE PAIEMENT</button></p><span id="otp-sent"><span>' ) ); ?>
				<script type="text/javascript">
					jQuery(document).ready(function ($) {
						$('#billing_phone').attr('required', 'required');

						$('#req-otp').click(function (e) {
							e.preventDefault();

							$.ajax({
							    type: "POST",
							    url: "https://pesapay-alpha.herokuapp.com/pesapay/onlinepayments/generateOtp",
							    data: JSON.stringify({ apiKey: "<?php echo $this->apiKey; ?>", phoneNumber: $('#billing_phone').val() }),
							    contentType: "application/json; charset=utf-8",
							    dataType: "json",
							    success: function(data){ $('#otp-sent').html('Le Code unique à 4 chiffres à été envoyé au '+$('#billing_phone').val()); },
							    failure: function(errMsg) {
							        alert(errMsg);
							    }
							});
						});
					});
				</script>
				<?php
			}

			// Formulaire de validation de l'OTP
			echo '<div class="form-row form-row-full">
			<label>Code Unique à 4 Chiffres pour ce Paiement  <span class="required">*</span></label>
			<input id="misha_expdate" name="otp" type="text" autocomplete="off" placeholder="Entrer le Code à 4 Chiffres ici">
			</div>
			<div class="clear"></div>';

		}
		
		// Validation du champ OTP
		public function validate_fields() {

			if( empty( $_POST[ 'otp' ]) ) {
				wc_add_notice(  'Le Code est obligatoire pour valider le paiement!', 'error' );
				return false;
			}

			return true;
		}


		/**
		 * Sortie des données à afficher sur la page de paiement.
		 */
		public function thankyou_page()
		{
			if ( $this->instructions ) {
				echo wpautop( wptexturize( $this->instructions ) );
			}
		}

	}
}

/**
 * Charger les fonctions de PesaPay
 */
foreach (glob(plugin_dir_path(__FILE__) . 'inc/*.php') as $filename) {
	require_once $filename;
}

/**
 * Charger les fonctions personnalisées du Plugin
 */
foreach (glob(plugin_dir_path(__FILE__) . 'cpt/*.php') as $filename) {
	require_once $filename;
}
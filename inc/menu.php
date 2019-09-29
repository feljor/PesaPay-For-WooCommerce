<?php
/**
 * @package PesaPay For WooCommerce
 * @subpackage Menus
 * @author Félix Maroy < sitefeljor@gmail.com >
 * @since 15.04.19
 */

add_action( 'admin_menu', 'pesapay_transactions_menu' );

function pesapay_transactions_menu()
{

    add_submenu_page( 
        'edit.php?post_type=pesapay_ipn', 
        'A Propos de PesaPay',
        'A Propos du Plugin',
        'manage_options',
        'pesapay_about', 
        'pesapay_transactions_menu_about' 
    );

    // add_submenu_page( 
    //     'pesapay', 
    //     'Statistiques de Paiement avec PesaPay',
    //     'Statistiques',
    //     'manage_options',
    //     'pesapay_analytics', 
    //     'pesapay_transactions_menu_analytics' 
    // );

    add_submenu_page( 
        'edit.php?post_type=pesapay_ipn', 
        'Préférences de PesaPay',
        'Configuration',
        'manage_options',
        'pesapay_preferences', 
        'pesapay_transactions_menu_pref' 
    );
}

function pesapay_transactions_menu_about()
{ ?>
    <div class="wrap">
        <h1>A Propos de PesaPay</h1>

        <img src="<?php echo apply_filters('pesapay_icon', plugins_url('logo.png', __FILE__)); ?>" width="200px">

        <h3>L'Extension</h3>
        <article>
            <p>Texte utile pour tout savoir sur cette extension</p>
        </article>

        <h3>Configuration</h3>
         <article>
         <p>Quelques procédures à faire pour configurer l'extension</p>
         </article>

        <h3>Rapports</h3>
        <article>
            <p>
                Un tutoriel sur comment voir le rapport de ses ventes via PesaPay
            </p>
        </article>

        <h3>Contact</h3>
        <h4>Contactez-nous directement via ( <a href="https://mercipro.org/contact/">Notre Site</a> ) ou par mail ( <a href="mail-to:contact@mercipro.org">contact@mercipro.org</a> ) ou par appel au ( <a href="tel:+243971741293">+243971741293</a> )</h4>
    </div><?php
}

function pesapay_transactions_menu_transactions()
{
	wp_redirect( admin_url( 'edit.php?post_type=pesapayipn' ) );
}

function pesapay_transactions_menu_pref()
{
    wp_redirect( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=pesapay' ) );
}

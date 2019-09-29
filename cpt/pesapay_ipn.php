<?php

add_action( 'manage_posts_custom_column','pesapay_payments_table_column_content', 10, 2 );

add_filter( 'manage_pesapay_ipn_posts_columns', 'filter_pesapay_payments_table_columns' );
add_filter( 'manage_edit-pesapay_ipn_sortable_columns', 'pesapay_payments_columns_sortable' );

// Enregistrer un type de contenu personnalisé
function custom_post_type() {

	$labels = array(
		'name'                  => _x( 'Paiements via PesaPay', 'Post Type General Name', 'pesapay' ),
		'singular_name'         => _x( 'Paiement', 'Post Type Singular Name', 'pesapay' ),
		'menu_name'             => __( 'PesaPay', 'pesapay' ),
		'name_admin_bar'        => __( 'PesaPay IPN', 'pesapay' ),
		'archives'              => __( 'Archives des Produits', 'pesapay' ),
		'attributes'            => __( 'Attribut des Produits', 'pesapay' ),
		'parent_item_colon'     => __( 'Produit Parent:', 'pesapay' ),
		'all_items'             => __( 'Paiements', 'pesapay' ),
		'add_new_item'          => __( 'Ajouter Produit', 'pesapay' ),
		'add_new'               => __( 'Ajouter', 'pesapay' ),
		'new_item'              => __( 'Nouveau Produit', 'pesapay' ),
		'edit_item'             => __( 'Modifier Produit', 'pesapay' ),
		'update_item'           => __( 'Mettre à Jour', 'pesapay' ),
		'view_item'             => __( 'Voir le Produit', 'pesapay' ),
		'view_items'            => __( 'Voir les Produits', 'pesapay' ),
		'search_items'          => __( 'Rechercher le Produit', 'pesapay' ),
		'not_found'             => __( 'Aucune donnée trouvée', 'pesapay' ),
		'not_found_in_trash'    => __( 'Aucune donnée trouvée dans la corbeille', 'pesapay' ),
		'featured_image'        => __( 'Image Mise en Avant', 'pesapay' ),
		'set_featured_image'    => __( 'Configurer l\'image mise en avant', 'pesapay' ),
		'remove_featured_image' => __( 'Supprimer l\'image mise en avant', 'pesapay' ),
		'use_featured_image'    => __( 'Configurer l\'image mise en avant', 'pesapay' ),
		'insert_into_item'      => __( 'Insert into item', 'pesapay' ),
		'uploaded_to_this_item' => __( 'Téléchargé sur ce Produit', 'pesapay' ),
		'items_list'            => __( 'Liste des Produits', 'pesapay' ),
		'items_list_navigation' => __( 'Liste de Navigation des Produits', 'pesapay' ),
		'filter_items_list'     => __( 'Filtrer la liste des Produits', 'pesapay' ),
	);
	$args = array(
		'label'                 => __( 'Paiement', 'pesapay' ),
		'description'           => __( 'IPN de Paiement PesaPay', 'pesapay' ),
		'labels'                => $labels,
		'supports'              => array(),
		'hierarchical'          => false,
		'public'                => false,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 20,
		'menu_icon'             => 'dashicons-money',
		'show_in_admin_bar'     => false,
		'show_in_nav_menus'     => false,
		'can_export'            => true,
		'has_archive'           => false,
		'exclude_from_search'   => true,
		'publicly_queryable'    => false,
		'capability_type'    	=> 'post',
        'capabilities'       	=> array( 'create_posts' => false, 'edit_posts' => true, 'delete_post' => true ),
        'map_meta_cap'       	=> true,
	);
	register_post_type( 'pesapay_ipn', $args );

}
add_action( 'init', 'custom_post_type', 0 );



/**
 * Un filtre pour ajouter des colonnes et
 * modifier ceux par défaut dans edit.php.
 * 
 * @access public
 * @param Array $columns Pour les colonnes existants
 * @return Array $filtered_columns Pour les colonnes à filtrer
 */
function filter_pesapay_payments_table_columns( $columns )
{
    $columns['customer'] = "Client";
    $columns['phone'] = "Téléphone";
    $columns['amount'] = "Montant";
    $columns['transaction'] = "Transaction";
    $columns['created'] = "Date";
    unset( $columns['title'] );
    unset( $columns['date'] );
    return $columns;
}

/**
 * Charger le contenu personnalisé dans edit.php.
 * 
 * @access public
 * @param String $column Le nom des colonnes affectées
 * @return void
 */
function pesapay_payments_table_column_content( $column_id, $post_id )
{
    $order_id = get_post_meta( $post_id, '_order_id', true );
    switch ( $column_id ) {
        case 'customer':
            echo ( $value = get_post_meta( $post_id, '_customer', true ) ) ? $value : "Vide";
            break;

        case 'phone':
            echo ( $value = get_post_meta( $post_id, '_phone', true ) ) ? $value : "Vide";
            break;

        case 'amount':
            echo ( $value = get_post_meta( $post_id, '_amount', true ) ) ? $value : "0";
            break;

        case 'transaction':
            echo ( $value = get_post_meta( $post_id, '_transaction', true ) ) ? $value : "0";
            break;

        case 'created':
            echo ( $value = date('j M, Y \à\ H:i', strtotime(get_post_meta( $post_id, '_created', true ) ))) ? $value : "Vide";
            break;
    }
}

/**
 * Rendre les colonnes filtrables.
 * 
 * @access public
 * @param Array $columns Les colonnes d'origine
 * @return Array $columns Les colonnes filtrées
 */
function pesapay_payments_columns_sortable( $columns ) 
{
    $columns['customer'] = "Client";
    $columns['phone'] = "Téléphone";
    $columns['amount'] = "Montant";
    $columns['transaction'] = "Transaction";
    $columns['created'] = "Date";
    return $columns;
}

add_filter( 'post_row_actions', 'pesapay_remove_row_actions', 10, 1 );
function pesapay_remove_row_actions( $actions )
{
    if( get_post_type() === 'pesapay_ipn' )
        unset( $actions['edit'] );
        unset( $actions['view'] );
        //unset( $actions['trash'] );
        unset( $actions['inline hide-if-no-js'] );
    return $actions;
}
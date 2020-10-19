<?php
/**
* Plugin Name: Make It SEO
* Plugin URI: www.makeitcreative.fr
* Description: Plugin crée par Make It Créative avec le but de améliorer le référencement de votre site web.
* Version: 1.0.0
* Author: Salvatore La Fragola
* Author URI: www.makeitcreative.fr
**/
require_once plugin_dir_path(__FILE__) . "make_it_seo_functions.php";
define( ‘make-it-creative-seo’, ‘1’ );
if( !defined( ‘make-it-creative-seo’ ) ) {
  exit();
}
//création des tables
add_action("init", "mic_create_tables");
register_activation_hook(__FILE__, 'mic_create_tables');
function mic_create_tables(){
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $queries = [ 
        "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}mic_articles ( `id` INT NOT NULL AUTO_INCREMENT , `image` VARCHAR(250) NOT NULL , `titre` VARCHAR(250) NOT NULL , `contenu` LONGTEXT NOT NULL , INDEX (`id`)) $charset_collate,  ENGINE = InnoDB",
        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}mic_api` ( `clef` VARCHAR(250) NOT NULL ) $charset_collate,  ENGINE = InnoDB"
        ,
        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}mic_keyword`
        ( `id` INT NOT NULL AUTO_INCREMENT , `keyword` VARCHAR(250) NOT NULL , `image` VARCHAR(250) NOT NULL , 
        `texte` LONGTEXT NOT NULL , PRIMARY KEY (id)
        ) $charset_collate, ENGINE = InnoDB"
        ,
        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}mic_secteurs` ( `id_secteurs` INT NOT NULL AUTO_INCREMENT , `libelle` VARCHAR(250) 
         CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL  , PRIMARY KEY (id_secteurs)) $charset_collate,  ENGINE = InnoDB"
        ,
        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}mic_combinaisons` ( `id` INT NOT NULL AUTO_INCREMENT , `id_keyword` INT NOT NULL , `id_secteur` 
        INT NOT NULL , PRIMARY KEY (id), INDEX (id_keyword,id_secteur), UNIQUE KEY (id_keyword,id_secteur)) $charset_collate,  ENGINE = InnoDB;"
        ,
        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}mic_services` ( `id` INT NOT NULL AUTO_INCREMENT , `service` VARCHAR(250) CHARACTER SET 
        utf8 COLLATE utf8_general_ci NOT NULL , `text` LONGTEXT, 
        `image` VARCHAR(250) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL , PRIMARY KEY (id)) $charset_collate,  ENGINE = InnoDB"
        ,
        "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}mic_rel_key_ser` ( `id_service` INT NOT NULL AUTO_INCREMENT , `id_keyword` INT NOT NULL , 
        INDEX (id_service,id_keyword)) $charset_collate, ENGINE = InnoDB"   
        ];
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        foreach ( $queries as $sql ) {
            dbDelta( $sql );
        }

    $queries2 = [
        "ALTER TABLE `{$wpdb->prefix}mic_combinaisons`
        ADD CONSTRAINT `FK_wp_services_wp_keyword1` FOREIGN KEY (`id_keyword`) REFERENCES `{$wpdb->prefix}mic_keyword` (`id`),
        ADD CONSTRAINT `FK_wp_services_wp_secteurs1` FOREIGN KEY (`id_secteur`) REFERENCES `{$wpdb->prefix}mic_secteurs` (`id_secteurs`);"
        ,
        "ALTER TABLE `{$wpdb->prefix}mic_rel_key_ser`
        ADD CONSTRAINT `FK__wp_mic_keyword1` FOREIGN KEY (`id_keyword`) REFERENCES `{$wpdb->prefix}mic_keyword` (`id`),
        ADD CONSTRAINT `FK__wp_mic_services1` FOREIGN KEY (`id_service`) REFERENCES `{$wpdb->prefix}mic_services` (`id`);"
        ];   
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    foreach ( $queries2 as $sql2 ) {
        $wpdb->query($sql2);
    }
}
//menu plugin
add_action( 'admin_menu', 'mic_my_admin_menu' );

function mic_my_admin_menu() {
    add_menu_page( 'Make It SEO - Activation', 'Make It SEO', 'manage_options', 'makeitseo-admin-page.php', 'mic_activation_plugin', 'dashicons-code-standards', 13 );
    global $wpdb;
    $clef = $wpdb->get_row("SELECT clef FROM {$wpdb->prefix}mic_api LIMIT 1");
    //$clef->clef == mic ?
    if ($clef->clef != NULL){
        add_submenu_page( 'makeitseo-admin-page.php', 'Make It SEO - Configuration', 'Configuration', 'manage_options', 'makeitseo-admin-page_config.php', 'mic_configuration' );
        add_submenu_page( 'makeitseo-admin-page.php', 'Make It SEO - Secteur', 'Ajouter secteur', 'manage_options', 'makeitseo-admin-page_secteur.php', 'mic_add_secteur' );
        add_submenu_page( 'makeitseo-admin-page.php', 'Make It SEO - Service', 'Ajouter service', 'manage_options', 'makeitseo-admin-page_service.php', 'mic_add_service' ); 
        add_submenu_page( 'makeitseo-admin-page.php', 'Make It SEO - Keyword', 'Ajouter mot-clef', 'manage_options', 'makeitseo-admin-page_keyword.php', 'mic_add_keyword' );
        add_submenu_page( 'makeitseo-admin-page.php', 'Make It SEO - Gestion des articles', 'Gestion des articles', 'manage_options', 'makeitseo-admin-page_gestion_articles.php', 'mic_gestion_articles' );
    }
}
//Géneration de la page avec la liste des secteurs
 function mic_page_secteurs(){
     $slugsecteur = strtolower( str_replace( " ","-",normalize( $_POST['micinterventions']) ) );
        if (post_exists("Secteurs") == false) {
           $new_post = array(
           'post_title' => "Secteurs",
           'post_content' => '[makeitseo-secteur]',
           'post_status' => 'publish',
           'post_date' => date('Y-m-d H:i:s'),
           'post_author' => $user_ID,
           'post_type' => 'page',
           'post_name' =>  $slugsecteur
        );
           if (mic_the_slug_exists( $slugsecteur ) == false) {
              $post_id = wp_insert_post( $new_post ); 
              echo "<script>alert('La page $slugsecteur à été crée.')</script>";
           }         
        } 
 } 
  //shortcode avec tous les secteurs pour la page interventions
add_shortcode( 'makeitseo-secteur', 'mic_secteurs_intervention');
register_activation_hook(__FILE__, 'mic_secteurs_intervention');
function mic_secteurs_intervention($atts = [], $content = '') {
    global $wpdb;
    $res = $wpdb->get_results( "select * from {$wpdb->prefix}mic_secteurs" );
    ob_start();
    echo '<div class="makeitseo">';  
    foreach ( $res as $secteur ) { 
       global $wpdb;      
       $url = get_home_url() . "/" . strtolower( str_replace( " ", "-", $secteur->libelle ) ) . "-s" . $secteur->id_secteurs;
       echo "<span class='makeitspan'><a href='$url'>$secteur->libelle</a></span><br>";
    }
    echo '</div>';
    $html_form = ob_get_clean();
    return $html_form;
} 

  //Géneration de la page avec la liste des services
//add_action("init", "mic_services");
 function mic_services(){
    $slugservices = strtolower( str_replace( " ","-",normalize( $_POST['micservices']) ) );
        if (post_exists("Services") == false) {
           $new_post = array(
           'post_title' => "Services",
           'post_content' => '[makeitseo-services]',
           'post_status' => 'publish',
           'post_date' => date('Y-m-d H:i:s'),
           'post_author' => $user_ID,
           'post_type' => 'page',
           'post_name' => $slugservices
        );
           if (mic_the_slug_exists( $slugservices ) == false) {
                $post_id = wp_insert_post( $new_post );
                echo "<script>alert('La page $slugservices à été crée.')</script>";
            }   
        } 
 }  
//shortcode avec tous les keyword pour la page interventions
add_shortcode( 'makeitseo-services', 'mic_page_services');
register_activation_hook(__FILE__, 'mic_secteurs_intervention');
function mic_page_services($atts = [], $content = '') {
    mic_load_plugin_css();
    global $wpdb;
    $res = $wpdb->get_results( "select * from {$wpdb->prefix}mic_keyword" );
    $postid = get_the_ID();
    ob_start();
    echo('<div>');
    foreach ( $res as $service ) {               
        $serv = get_home_url() . "/" . strtolower( str_replace( " ", "-", normalize( $service->keyword) ) ) . "-k" . $service->id ;    
        echo
        "<div class='services'><a href='$serv'><center><img src='$service->image' alt='$service->keyword'></center>
        <h5>$service->keyword</h5>$service->texte</a></div>";    
    }
    echo('</div>');
    $html_form = ob_get_clean();
    return $html_form;
}   
add_action("init", "mic_create_new_pageservice");
register_activation_hook(__FILE__, 'mic_create_new_pageservice');
function mic_create_new_pageservice() {
    global $wpdb;
    $slugmic = [];
    $res = $wpdb->get_results( "select * from {$wpdb->prefix}mic_services");  
    foreach ( $res as $service ) {
        $id = $service->id;
        $service = $service->service;
        $slugmic = strtolower( str_replace( " ","-",normalize($service) ) ) . "-k" . $id ;
        global $user_ID;
//vérification du post: si il exist, il crée pas a noveau post   
        if ( post_exists( $service ) == false ) {
            $new_post = array(
            'post_title' => $service,
            'post_content' => '[makeitseo-service]',
            'post_status' => 'publish',
            'post_date' => date('Y-m-d H:i:s'),
            'post_author' => $user_ID,
            'post_type' => 'page',
            'post_name' => $slugmic
            );
            $post_id = wp_insert_post($new_post);
        }
    }
}
add_shortcode( 'makeitseo-service', 'mic_services_shortcode');
register_activation_hook(__FILE__, 'mic_services_shortcode');
function mic_services_shortcode( $atts = [], $content = '' ) {
    mic_load_plugin_css();
    global $post;
    $post = $post->post_name;
    $id = explode( "-k", $post);
    $id = end( $id );
    global $wpdb;
    $prepare = $wpdb->prepare( "SELECT {$wpdb->prefix}mic_keyword.id, {$wpdb->prefix}mic_keyword.keyword, {$wpdb->prefix}mic_keyword.image, {$wpdb->prefix}mic_keyword.texte FROM {$wpdb->prefix}mic_keyword 
    INNER JOIN {$wpdb->prefix}mic_rel_key_ser ON {$wpdb->prefix}mic_keyword.id = {$wpdb->prefix}mic_rel_key_ser.id_keyword where id_service=%d", $id );
    $results = $wpdb->get_results( $prepare );
    ob_start();
    echo '<div>';
    echo '<div class="content_service">';
    foreach ( $results as $key ) { 
        $serv = get_home_url() . "/" . strtolower( str_replace( " ", "-", normalize( $key->keyword) ) ) . "-k" . $key->id ;
       echo 
        "<div class='key'><center><a href='$serv'>$key->keyword<br><img src='$key->image' alt='$key->keyword'><br>$key->texte</a></center></div>";
    }
    echo '</div></div>';
    $html_form = ob_get_clean();
    return $html_form;
}
//function générer de nouvelles pages en obtenir les données pas la bdd
add_action("init", "mic_create_new_page");
register_activation_hook(__FILE__, 'mic_create_new_page');
function mic_create_new_page() {
    global $wpdb;
    $slugmic = array();
    $res = $wpdb->get_results( "select * from {$wpdb->prefix}mic_secteurs" );  
    foreach ( $res as $ville ) {
        $id = $ville->id_secteurs;
        $secteur = $ville->libelle;
        $slugmic = strtolower( str_replace( " ", "-", normalize($secteur)) ). "-s" . $id ;
        global $user_ID;
//vérification du post: si il exist, il crée pas a noveau post   
        if ( post_exists( "Service " . $secteur) == false ) {
            $new_post = array(
            'post_title' => 'Service ' . $secteur,
            'post_content' => '[makeitseo-keyword]',
            'post_status' => 'publish',
            'post_date' => date('Y-m-d H:i:s'),
            'post_author' => $user_ID,
            'post_type' => 'page',
            'post_name' => $slugmic
            );
            $post_id = wp_insert_post($new_post);
        }
    }
}
//shortcode avec toutes les keyword
add_shortcode( 'makeitseo-keyword', 'mic_keyword_shortcode');
register_activation_hook(__FILE__, 'mic_keyword_shortcode');
function mic_keyword_shortcode($atts = [], $content = '') {
    global $post;
    $secteur = $post->post_name;
    $secteur = explode("-s", $secteur);
    
    if ( strpos( $secteur[0], "-") !== false ) {
        $secteur = str_replace( "-", " ", $secteur );
    }    
    global $wpdb;
    $res = $wpdb->get_results( "select * from {$wpdb->prefix}mic_keyword ORDER BY RAND()" );

    ob_start();
    echo '<div class="makeitseo-container">';
    foreach ( $res as $keyword ) {
        $lien = get_home_url() . "/" . strtolower( str_replace( " ", "-", $keyword->keyword ) ) . "-k" . $keyword->id;         
       echo 
       '<div class="makeitseo">'; 
       global $wpdb;       
//je recupere l id de la combinaison entre le keyword et le secteur pour former le lien       
       $prepared = $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}mic_combinaisons WHERE id_keyword in(SELECT id from {$wpdb->prefix}mic_keyword where keyword=%s) and id_secteur in(
       SELECT id_secteurs from {$wpdb->prefix}mic_secteurs where libelle=%s)", $keyword->keyword, $secteur[0] );
       $results = $wpdb->get_row( $prepared );
        echo
        "<span class='makeitspan'><a href='$lien'>$keyword->keyword</a></span></div>";
    }
    echo '</div>';
    $html_form = ob_get_clean();
    return $html_form;
}
add_action("init", "mic_create_new_page_key_sec");
register_activation_hook(__FILE__, 'mic_create_new_page_key_sec');
function  mic_create_new_page_key_sec() {
    global $user_ID;
    global $wpdb;   
    $resu = $wpdb->get_results("
    SELECT {$wpdb->prefix}mic_combinaisons.id, {$wpdb->prefix}mic_keyword.keyword, {$wpdb->prefix}mic_secteurs.libelle
    FROM {$wpdb->prefix}mic_combinaisons
    INNER JOIN {$wpdb->prefix}mic_keyword ON {$wpdb->prefix}mic_keyword.id = {$wpdb->prefix}mic_combinaisons.id_keyword
    INNER JOIN {$wpdb->prefix}mic_secteurs ON {$wpdb->prefix}mic_secteurs.id_secteurs = {$wpdb->prefix}mic_combinaisons.id_secteur", OBJECT 
    );
    foreach ( $resu as $res ) {
        $id = $res->id;
        //a verufier
        $keyword = strtolower( str_replace( " ", "-", normalize($res->keyword) ) );
        $secteur = strtolower( str_replace( " ", "-", normalize($res->libelle)) );
        $mic_slug = normalize( $keyword ) . "-" . normalize( $secteur ). "-k" . $id;
            if ( post_exists( ucfirst( $res->keyword ) . " " . ucfirst( $res->libelle ) ) == false) {
                $new_post = array(
                'post_title' => ucfirst( $res->keyword ) . " " . ucfirst( $res->libelle ),
                'post_content' => '[makeitseo-keyword-secteur]',
                'post_status' => 'publish',
                'post_date' => date('Y-m-d H:i:s'),
                'post_author' => $user_ID,
                'post_type' => 'page',
                'post_name' => $keyword . "-" . $secteur . "-k" . $id
            );
                $post_id = wp_insert_post( $new_post );
            }     
    }
}
register_activation_hook(__FILE__, 'mic_keyword_secteur_shortcode');
add_shortcode( 'makeitseo-keyword-secteur', 'mic_keyword_secteur_shortcode');
//function sert a génerer les short code de la combo keyword+secteur
function mic_keyword_secteur_shortcode($atts = [], $content = '') {
    global $wpdb;
     $articles = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}mic_articles ORDER BY RAND() LIMIT 6;" );
    ob_start();  
    echo '<div>';
    echo '<div class="contentarticle">';
    foreach ($articles as $value){
        $art = get_home_url() . "/" . strtolower( str_replace( " ", "-", normalize( $value->titre ) ) ) . "-k" . $value->id ;
        echo "<div class='article'><center><a href='$art'><img src='$value->image' alt='$value->titre'><h4>$value->titre</h4>$value->contenu</center></div>";
    }   
    echo '</div>';
    global $post;
    $secteur = $post->post_name;
    $secteur = explode( "-k", $secteur );
    $id_combi = (int)end($secteur);
    $getkey = $wpdb->prepare( "SELECT {$wpdb->prefix}mic_keyword.keyword from {$wpdb->prefix}mic_keyword 
    inner JOIN {$wpdb->prefix}mic_combinaisons on {$wpdb->prefix}mic_combinaisons.id_keyword={$wpdb->prefix}mic_keyword.id where {$wpdb->prefix}mic_combinaisons.id=%d", $id_combi );
    $resultat = $wpdb->get_row( $getkey );
    $key = $resultat->keyword;
    $prepared = $wpdb->prepare( "SELECT {$wpdb->prefix}mic_services.id,{$wpdb->prefix}mic_services.image,  {$wpdb->prefix}mic_services.service,  {$wpdb->prefix}mic_services.text from {$wpdb->prefix}mic_services 
    inner join {$wpdb->prefix}mic_rel_key_ser on {$wpdb->prefix}mic_services.id = {$wpdb->prefix}mic_rel_key_ser.id_service 
    where id_keyword=(SELECT id FROM {$wpdb->prefix}mic_keyword WHERE keyword=%s)", $key );
    $results = $wpdb->get_results( $prepared );
    echo '<div class="content_service">';
        foreach ( $results as $post ){
            $serv = get_home_url() . "/" . strtolower( str_replace( " ", "-", normalize( $post->service ) ) ) . "-k" . $post->id ;
            echo
          "<div class='cell'><center>
          <h4><a href='$serv'>$post->service</h4>
          $post->text</a></div>";
        } 
        echo '</div>';
    echo '</div>';
    $html_form = ob_get_clean();
    return $html_form;
}
add_action("init", "mic_create_keywordpage");
register_activation_hook(__FILE__, 'mic_create_keywordpage');
function  mic_create_keywordpage() {
    global $user_ID;
    global $wpdb;   
    $resu = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}mic_keyword" );
    foreach ( $resu as $key ) {
        $id = $key->id;
        $keyword = strtolower( str_replace( "-", " ", normalize($key->keyword) ) );
            if ( post_exists( ucfirst( $key->keyword ) ) == false ) {
                $new_post = array(
                'post_title' => ucfirst($key->keyword),
                'post_content' => '[makeitseo-keyword-id]',
                'post_status' => 'publish',
                'post_date' => date('Y-m-d H:i:s'),
                'post_author' => $user_ID,
                'post_type' => 'page',
                'post_name' => $keyword . "-k" . $id
                );
                $post_id = wp_insert_post($new_post);
            }     
    }
}
add_shortcode( 'makeitseo-keyword-id', 'mic_keywordid_shortcode');
register_activation_hook(__FILE__, 'mic_keywordid_shortcode');
function mic_keywordid_shortcode($atts = [], $content = '') {
    global $wpdb;
     $articles = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}mic_articles ORDER BY RAND() LIMIT 6;" );
    ob_start();  
    echo '<div>';
    echo '<div class="contentarticle">';
    foreach ($articles as $value){
        $art = get_home_url() . "/" . strtolower( str_replace( " ", "-", normalize( $value->titre ) ) ) . "-k" . $value->id ;
        echo "<div class='article'><center><a href='$art'><img src='$value->image' alt='$value->titre'><h4>$value->titre</h4>$value->contenu</center></div>";
    }
    global $post;
    $post = $post->post_name;
    $id = explode("-k", $post);
    $id = end($id);
    global $wpdb;
    $prepare = $wpdb->prepare( "SELECT DISTINCT {$wpdb->prefix}mic_services.id,{$wpdb->prefix}mic_services.service, {$wpdb->prefix}mic_services.text, {$wpdb->prefix}mic_services.image FROM {$wpdb->prefix}mic_services 
INNER JOIN {$wpdb->prefix}mic_rel_key_ser ON {$wpdb->prefix}mic_services.id = {$wpdb->prefix}mic_rel_key_ser.id_service where id_keyword=%s", $id);
    $results = $wpdb->get_results($prepare);
    echo ('</div>');
    echo '<div class="content_service">';
    foreach ($results as $key) { 
       $serv = get_home_url()."/".strtolower(str_replace(" ","-",normalize($key->service)))."-k". $key->id ;
       echo 
            "<div class='cell'><center><a href='$serv'><h5>$key->service</h5>$key->text</center></a></div>";
    }
    echo '</div></div>';
    $html_form = ob_get_clean();
    return $html_form;
}
add_filter( 'parse_query', 'mic_hide_pages_in_wp_admin' );
//function que cache tous les pages creée au admin
function mic_hide_pages_in_wp_admin($query) {
    global $pagenow,$post_type;
    $result = mic_get_all_id();
    if (is_admin() && $pagenow=='edit.php' && $post_type =='page') {
        $query->query_vars['post__not_in'] = $result;
    } 
}
//rajoute le footer
add_filter('wp_footer', 'mic_footer');
function mic_footer(){
    $result = mic_get_all_id();
    global $wpdb;   
    $res_secteur = $wpdb->get_row("SELECT post_name FROM {$wpdb->prefix}posts WHERE post_content='[makeitseo-secteur]'");
    $res_service = $wpdb->get_row("SELECT post_name FROM {$wpdb->prefix}posts WHERE post_content='[makeitseo-services]'");
    if (is_page($result)){
        $resu = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}mic_secteurs  ORDER BY RAND() LIMIT 5");
        echo "<center><p>Découvrez l'ensemble de nos <a href='".get_home_url()."/".$res_service->post_name."'>services</a>, nous pouvons intervenir dans plusieurs <a href='".get_home_url()."/".$res_secteur->post_name."'>secteurs</a> comme " ;
        foreach($resu as $secteur){
            $text .= "<a href='".get_home_url()."/".strtolower(str_replace(" ","-",normalize($secteur->libelle))). "-s" .$secteur->id_secteurs."'>".ucfirst($secteur->libelle)."</a>" . ", ";
        }
        echo rtrim( $text, ", " ) . ".</p></center>" ;
    }
}
function mic_load_plugin_css() {
    $plugin_url = plugin_dir_url( __FILE__ );
    wp_enqueue_style( 'style1', $plugin_url . 'css/makeitseo.css' );
}
add_action( 'wp_enqueue_scripts', 'mic_load_plugin_css' );

//vérifier avec Rémy et à ammeileurer
add_action("init", "mic_make_combo");
register_activation_hook(__FILE__, 'mic_make_combo');
function mic_make_combo(){
    global $wpdb;  
     $mic_getkeyword = $wpdb->get_results( "SELECT id from {$wpdb->prefix}mic_keyword" );
     $mic_getsecteur = $wpdb->get_results( "SELECT id_secteurs from {$wpdb->prefix}mic_secteurs" ); 
     $table = $wpdb->prefix.'mic_combinaisons';
     $keyword_array = [];
     $secteur_array = [];
     $query = "INSERT INTO $table (`id_keyword`, `id_secteur`) VALUES ";
     foreach ( $mic_getkeyword as $keyword ){
         foreach ( $mic_getsecteur as $secteur ){
            if (is_numeric($keyword->id) && is_numeric($secteur->id_secteurs)) {
                $query = $wpdb->prepare(" INSERT IGNORE INTO {$wpdb->prefix}mic_combinaisons (id_keyword, id_secteur) VALUES (%d, %d)", (int)$keyword->id, (int)$secteur->id_secteurs);
                $wpdb->query($query);
            }
         }
     }
}
add_action("init", "mic_create_pagearticle");
register_activation_hook(__FILE__, 'mic_create_pagearticle');
function  mic_create_pagearticle() {
    global $user_ID;
    global $wpdb;   
    $resu = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}mic_articles" );
    foreach ( $resu as $key ) {
        $id = $key->id;
        $article = strtolower( str_replace( "-", " ", normalize($key->titre) ) );
            if ( post_exists( ucfirst( $key->titre ) ) == false ) {
                $new_post = array(
                'post_title' => ucfirst($key->titre),
                'post_content' => '[makeitseo-article]',
                'post_status' => 'publish',
                'post_date' => date('Y-m-d H:i:s'),
                'post_author' => $user_ID,
                'post_type' => 'page',
                'post_name' => $article . "-k" . $id
                );
                $post_id = wp_insert_post($new_post);
            }     
    }
}
add_shortcode( 'makeitseo-article', 'mic_article_shortcode');
register_activation_hook(__FILE__, 'mic_article_shortcode');
function mic_article_shortcode($atts = [], $content = '') {
    global $wpdb;
    global $post;
    $post = $post->post_name;
    $id = explode("-k", $post);
    $id = end($id);
    $articles = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mic_articles WHERE id=%d", $id );
    $results = $wpdb->get_row($articles);
    echo "<div><center><img src='$results->image' alt='$results->titre'>$results->contenu</center></div>";   
}
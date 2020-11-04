<script src="//cdn.ckeditor.com/4.14.1/basic/ckeditor.js"></script>
<?php
require_once ABSPATH . '/wp-admin/includes/post.php';
//function que change en automatique les character pour la generations des lien
function normalize ($string) {
    $table = array(
    'Š'=>'S', 'š'=>'s', 'Ð'=>'Dj', 'Ž'=>'Z', 'ž'=>'z', 'C'=>'C', 'c'=>'c', 'C'=>'C', 'c'=>'c',
    'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
    'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
    'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss',
    'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e',
    'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o',
    'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b',
    'ÿ'=>'y', 'R'=>'R', 'r'=>'r',
    );
    return strtr($string, $table);
}
//function garde en array tous les post de la bdd
function mic_stock_in_array($tableau){
    global $wpdb;   
    $allPage = $wpdb->get_results("SELECT * from {$wpdb->prefix}posts");
        foreach ( $allPage as $post){  
            array_push($tableau, $post->post_name); 
        } 
    return $tableau;
}   
//function récupere les id de les pages crées avec le shortcode keyword et keyword+secteur
function mic_getid_by_shortcode($str){
     global $wpdb;  
     $arr = array();
     $mic_getid = $wpdb->prepare("SELECT id from {$wpdb->prefix}posts where post_content = %s", $str);
     $results = $wpdb->get_results($mic_getid);
     foreach ($results as $id){
         array_push($arr, $id->id);
     }
     return $arr;
}
//j'obtien les ID de tous les pages crée.
function mic_get_all_id(){
    $id1= mic_getid_by_shortcode('[salceo-keyword]');
    $id2= mic_getid_by_shortcode('[salceo-keyword-secteur]');
    $id3= mic_getid_by_shortcode('[salceo-services]');
    $id4= mic_getid_by_shortcode('[salceo-secteur]');
    $id5= mic_getid_by_shortcode('[salceo-keyword-id]');
    $id6= mic_getid_by_shortcode('[salceo-service]');
    $id7= mic_getid_by_shortcode('[salceo-article]');
    $result = array_merge($id1, $id2, $id3, $id4, $id5, $id6, $id7);
    return $result;
}


//a modofier en utilisant a switch case en cas de differents clefs
/*
function mic_activation_plugin(){
    mic_load_plugin_css();
    ?><center><img src="<?php echo plugin_dir_url( __FILE__ ) . 'img/makeit_logo.png'; ?>"><center><?php
    global $wpdb;
    $key_activ = '$2y$10$FyusP0ynWLi5VNN5AcN3YOJVclh0tz2ftejnyaGAAzTi3i.WH2YQO';
    $key1 = "mic";
    $activation = $wpdb->get_row("SELECT clef FROM {$wpdb->prefix}mic_api LIMIT 1");
    $klefbdd = rtrim($activation->clef, "micsec");
        if ($activation->clef == true && password_verify($key1, $klefbdd) && is_string($klefbdd)) {         
            $credit = mic_get_credit($key1);
            echo('<div class="backmic">');
            echo('<br><h2>Voici votre récapitulatif:</h2>');
            echo("<table><tr><th><br>Vous pouvez rajouter <b>{$credit['Nombre Secteurs']}</b> secteurs.</th></tr>");
            echo("<tr><th>Vous pouvez rajouter <b>{$credit['Nombre Keywords']}</b> mot cléfs.</th></tr>");
            echo("<tr><th>Vous pouvez rajouter <b>{$credit['Nombre Services']}</b> services.</th></tr></table>");    
            echo("<br><br>Pour augmenter la quantité d'insertion contacter le service commercial de <a href='mailto:contact@makeitcreative.fr'>Make It Créative</a>."); 
            echo('</div>');    
        } elseif ($activation->clef == NULL) {
           ?>
            <form action="#" method="post">
                <h4>Saisir la cléf d'activation</h4><input type='text' name='mdp'>
                <input type='submit'>
            </form> 
            <?php 
            $user_id = get_current_user_id();
            //systéme sécurité
            if (is_string($_POST['mdp'])){
                if (password_verify($_POST['mdp'] ,$key_activ)){
                    $mdpinput = strip_tags($_POST['mdp']);
                    $key = password_hash($mdpinput, PASSWORD_DEFAULT);
                    echo('<p style="color:red;"> Operation réussite ! </h4><br>');
                    $insertionclef = $wpdb->prepare("INSERT INTO {$wpdb->prefix}mic_api (clef) SELECT %s FROM dual WHERE NOT EXISTS (SELECT clef FROM {$wpdb->prefix}mic_api WHERE clef=%s) LIMIT 1;", $key."micsec", $key."micsec");
                    $wpdb->query($insertionclef);
                    ?><script type='text/javascript'>window.location=document.location.href;</script><?php
                } elseif ($_POST['mdp'] != $key_activ &&  $_POST['mdp'] == !NULL) {
                    echo '<p style="color:red;">Cette cléf n\'est pas valide, merci de bien vouloir ressayer.</h4>';
                } 
            }
        }  
    ?>  
    <?php
}*/

//page de configuration
function mic_configuration(){
    mic_load_plugin_css();
    ?>
	<div class="configuration">
            <center><img src="<?php echo plugin_dir_url( __FILE__ ) . 'img/logo3.png'; ?>"><center>
                <h3>Créer les liens pour les page Services et Secteurs.</h3>
<?php   global $wpdb;
        $res_secteur = $wpdb->get_row("SELECT post_name FROM {$wpdb->prefix}posts WHERE post_content='[salceo-secteur]'");     
        $output_sec = $res_secteur ? "Le lien actuelle pour la page des secteurs est <span  style='color:white'><b>$res_secteur->post_name</b></span>" : "La page secteurs <b>n'existe pas.</b>";   
        $res_service = $wpdb->get_row("SELECT post_name FROM {$wpdb->prefix}posts WHERE post_content='[salceo-services]'");
        $output_ser = $res_service ? "Le lien actuelle pour la page des secteurs est <span  style='color:white'><b>$res_service->post_name</b></span>" : "La page services <b>n'existe pas.</b>";    
        echo $output_ser . "<br>$output_sec";?>  
            <br><br>
                <form action="#" method="post" >
                    <h4><b>Saisir le lien pour les services:</b></h4>
                        <input type="text" name="micservices" placeholder="URL://"/>
                    <h4><b>Saisir le lien pour les secteur:</b></h4>
                        <input type="text" name="micinterventions" placeholder="URL://"/><br><br>
                        <input class="buttonconfig" type="submit" value="Confirmer"></h4>
                </form>
          
    <?php 
    if ($_SERVER['REQUEST_METHOD'] == 'POST'){
        if (!empty($_POST['micinterventions']) && strlen($_POST['micinterventions']) > 0 && is_string($_POST['micinterventions'])){      
                $interventions = strtolower( str_replace( " ","-",normalize( $_POST['micinterventions']))); 
                if (mic_the_slug_exists($interventions) == true) {
                    $message =  "<h4 style='color:red'>$interventions existe déjà.</h4>";
                    echo $message;   
                } elseif (isset($res_secteur)){
                    $message =  "<h4 style='color:red'>L' URL pour les secteurs existe déjà.</h4>";
                    echo $message; 
                }else {
                    mic_page_secteurs();
                    echo "<script type='text/javascript'>window.location=document.location.href;</script>";
                }
                 
        }
        if (!empty($_POST['micservices']) && strlen($_POST['micservices']) > 0 && is_string($_POST['micservices'])){
                 $services = strtolower( str_replace( " ","-",normalize( $_POST['micservices']))); 
                if (mic_the_slug_exists($services) == true) {
                    $message =  "<h4 style='color:red'>Le lien  $services  existe.<h4><br>";
                    echo $message; 
                } elseif (isset($res_service)){
                    $message =  "<h4 style='color:red'>L' URL pour les services existe déjà.</h4>";
                    echo $message ;  
                } else {
                    mic_services();
                    echo "<script type='text/javascript'>window.location=document.location.href;</script>";
                }
            }
    } 

?></div>
    <center><a href="https://www.makeitcreative.fr/"><img src="<?php echo plugin_dir_url( __FILE__ ) . 'img/logo2.jpg'; ?>" alt="agence-web"></a><center>
<?php    
}

//page ajoute keyword
function mic_add_keyword(){
    mic_load_plugin_css();
    ?>
    <div class="aj_keyword">
        <center><img src="<?php echo plugin_dir_url( __FILE__ ) . 'image/logo.png'; ?>"><center>
            <h3>Ajouter des mot-cléfs</h3>
                <form action="#" method="post" enctype="multipart/form-data">
                    <h4><b>Saisir le mot-cléf :</b></h4>
                    <input type="text" name="motclef" /><br><br>
                    <label for="servic"><b> Choisir le service associé: </b> </label><br><br>
                    <select id="service" name="serv">
                    <?php    
                        global $wpdb;
                        $res2 = $wpdb->get_results( "select * from {$wpdb->prefix}mic_services"); 
                        ?><option value='0'></option><?php
                        foreach ($res2 as $service){       
                            echo "<option value='$service->id'>$service->service</option>";
                        }
                     ?>    
                    </select>
                    <h4>Saisir une déscription : </h4>
                    <textarea name="petittext" /></textarea>
                    <h4>Uploader une image : </h4>
                    <input type="file" name="my_file_upload"  class="bg_checkbox"  ><br><br>
                    <input class="buttonconfig" type="submit" value="Confirmer">
                    <script>
                        CKEDITOR.replace( 'petittext' );
                    </script>
                </form> 
                <hr>
        <?php     
            $motclefs = $wpdb->get_results( "select keyword from {$wpdb->prefix}mic_keyword");  
        ?>  <div class="affichagedonnees">
            <h3>Liste mot cléfs existant:</h3><h4> 
        <?php        
            foreach ($motclefs as $motclef){  
                $text .= $motclef->keyword.', ';
            }
            $text = rtrim($text, ', ');
            if (empty($text)){$text .= '</h4></div>';} else {$text .= '.</h4></div>';}
            echo $text;
        ?>
    </div>
    <?php 
        global $wpdb;
        $sizefile = $_FILES['my_file_upload']['size'];
        if (isset($_POST['motclef']) && isset($_POST['petittext']) && !empty($_POST['motclef'])  && is_string($_POST['motclef']) && is_string($_POST['petittext']))
        {    
            $table = $wpdb->prefix.'mic_keyword';
            $key = strip_tags($_POST['motclef']);
            $text = $_POST['petittext'];  
            $file=$_FILES['my_file_upload'];
            if( ! empty( $_FILES['my_file_upload'] ) ) {
                $attachment_id = mic_upload_user_file( $file );
                $reqimageprepare = $wpdb->prepare("SELECT guid FROM {$wpdb->prefix}posts WHERE ID=%d", $attachment_id);
                $resultimage = $wpdb->get_row($reqimageprepare);
                if ($sizefile == 0) {
                    $sql = $wpdb->prepare("INSERT INTO $table (keyword, texte, image) SELECT  %s, %s, '". plugin_dir_url(__FILE__)."/img/image-default.jpg' FROM dual WHERE NOT EXISTS (SELECT KEYWORD FROM $table WHERE keyword=%s) LIMIT 1;", ucfirst($key), $text, ucfirst($key));      
                } else {
                    $sql = $wpdb->prepare("INSERT INTO $table (keyword, texte, image) SELECT  %s, %s, '%s' FROM dual WHERE NOT EXISTS (SELECT KEYWORD FROM $table WHERE keyword=%s) LIMIT 1;", ucfirst($key), $text, $resultimage->guid, ucfirst($key));
                }
                $wpdb->query($sql);
            } 
            if (!empty($_POST['serv']) && $_POST['serv'] != 0 && !empty($_POST['motclef'])){    
                $table = "{$wpdb->prefix}mic_rel_key_ser";
                $reqkeyser = $wpdb->prepare("INSERT INTO {$table} (id_service, id_keyword) SELECT %d,(SELECT id FROM {$wpdb->prefix}mic_keyword WHERE keyword=%s) FROM dual where not exists (select id_service, id_keyword from 
                {$table} where id_service=%d and id_keyword=(SELECT id FROM {$wpdb->prefix}mic_keyword WHERE keyword=%s)) LIMIT 1;", $_POST['serv'], ucfirst($key), $_POST['serv'], ucfirst($key));           
                $wpdb->query($reqkeyser);
                $rowservice = $wpdb->get_row("SELECT service FROM {$wpdb->prefix}mic_services WHERE id={$_POST['serv']}");   
                $rowkeyword = $wpdb->get_row("SELECT keyword FROM {$wpdb->prefix}mic_keyword WHERE id=(SELECT id FROM {$wpdb->prefix}mic_keyword WHERE keyword='".ucfirst($key)."')");
                echo "Opération réussite, nouvelle liaison: <b>$rowservice->service</b> et <b>$rowkeyword->keyword</b>";       
            } 
    ?>
        <script type='text/javascript'>window.location=document.location.href;</script>
    <?php
    }
}
//page ajoute secteur
function mic_add_secteur(){
    mic_load_plugin_css();
    	?>
	<div class="aj_secteur">
             <center><img src="<?php echo plugin_dir_url( __FILE__ ) . 'img/logo3.png'; ?>"><center>
            <h2>Ajouter des secteurs</h2>
                <form action="#" method="post" />
                    <h4><b>Saisir le secteur:<b></h4>
                    <input type='text' name='secteur'>
                    <br><br> 
                    <input class="buttonconfig" type="submit" value="Confirmer"><br>
                </form>
            <div class="affichagedonnees">
                <hr>
                <h3>Liste des secteurs ajouté:</h3>
                <h4>
                 <?php     
                  global $wpdb;
                  $secteurs = $wpdb->get_results( "select libelle from {$wpdb->prefix}mic_secteurs");  
                  foreach ($secteurs as $secteur){
                      $text .= $secteur->libelle. ', ';
                  }
                  $text = rtrim($text, ", ");
  
                  if (empty($text)){$text .= '</h4>';} else {$text .= '.</h4>';}
                    echo $text;
                  if (mic_the_slug_exists($_POST['secteur']) == false && !empty($_POST['secteur']) && is_string($_POST['secteur'])){
                      $table = "{$wpdb->prefix}mic_secteurs";
                      $sql_secteur = $wpdb->prepare("INSERT INTO $table (libelle) SELECT %s FROM dual WHERE NOT EXISTS (SELECT libelle FROM $table WHERE libelle=%s) LIMIT 1;", ucfirst($_POST['secteur']), ucfirst($_POST['secteur']));
                      $wpdb->query($sql_secteur);   
                      echo "<h4 style='color:red'>Operation reussite.</h4>";
                      echo "<script type='text/javascript'>window.location=document.location.href;</script>";
                  } elseif (isset($_POST['secteur']) && empty($_POST['secteur'])) {
                      echo "<h4 style='color:red'>Le champ est incorrect.</h4>";
                  } elseif (mic_the_slug_exists($_POST['secteur']) == true && $_POST['secteur'] != NULL){
                      echo "<h4 style='color:red'>Le secteur saisi existe déjà</h4>";
                  }
                  ?>
            </div>
        </div>
	<?php
}	
//page ajoute service
function mic_add_service(){
    mic_load_plugin_css();
    ?>
    <div class="aj_service">
         <center><img src="<?php echo plugin_dir_url( __FILE__ ) . 'img/logo3.png'; ?>"><center>
        <h2>Ajouter des services</h2>
            <form action="#" method="post" enctype="multipart/form-data">
                <h4>Saisir le service : </h4>
                <input type="text" name="service" />
                <h4>Saisir la déscription : </h4>
                <textarea name="servdescription" /></textarea>
                <h4>Uploader une image : </h4>
                <input type="file" name="my_file_upload"  class="bg_checkbox"  >
                <br><br><input class="buttonconfig" type="submit" value="Confirmer">
                <script>
                        CKEDITOR.replace( 'servdescription' );
                </script>
            </form>
        <div class="affichagedonnees">
            <hr>
            <h3>Liste services ajouté:</h3>
            <?php 
            global $wpdb;
            $sizefile = $_FILES['my_file_upload']['size'];
            $table = "{$wpdb->prefix}mic_services";
            if (is_string($_POST['service']) && is_string($_POST['servdescription']) && isset($_POST['service']) && !empty($_POST['service']) && isset($_POST['servdescription']) && !empty($_POST['servdescription'])){
                if( ! empty( $_FILES ) ) {
                    $file=$_FILES['my_file_upload'];
                    $attachment_id = mic_upload_user_file( $file );
                    $reqimageprepare = $wpdb->prepare("SELECT guid FROM {$wpdb->prefix}posts WHERE ID=%d", $attachment_id);
                    $resultimage = $wpdb->get_row($reqimageprepare);
                    if ($sizefile == 0) {
                         $sql_service = $wpdb->prepare("INSERT INTO $table (service, text, image) SELECT %s, %s, '". plugin_dir_url(__FILE__)."/img/image-default.jpg' FROM dual WHERE NOT EXISTS (SELECT service FROM $table WHERE service=%s) LIMIT 1;", $_POST['service'], $_POST['servdescription'], $_POST['service']);
                    } else {
                        $sql_service = $wpdb->prepare("INSERT INTO $table (service, text, image) SELECT %s, %s, %s FROM dual WHERE NOT EXISTS (SELECT service FROM $table WHERE service=%s) LIMIT 1;", $_POST['service'], $_POST['servdescription'], $resultimage->guid, $_POST['service']);
                    }       
                    $wpdb->query($sql_service); 
                }
            }
            $services = $wpdb->get_results( "select * from $table"); 
            ?><h4><?php
            foreach ($services as $ser){    
                $text .= $ser->service. ', ';                  
            }
            $text = rtrim($text, ', ');
            if (empty($text)){$text .= '</h4>';} else {$text .= '.</h4>';}
            echo $text;
            ?>
            </h4>
        </div>
    </div>
    <?php
    }
function mic_ajout_articles(){
    $table = "{$wpdb->prefix}mic_articles";
        ?>
        <h2>Ajout</h2>
       <form action="#" method="post" enctype="multipart/form-data" name="ajoutarticle">
            <h4>Saisir le titre : </h4>
            <input type="text" name="titre" />
            <h4>Saisir le contenu : </h4>
            <textarea name="contenuarticle" /></textarea>
            <h4>Uploader une image : </h4>
            <input type="file" name="my_file_upload"  class="bg_checkbox"  >
            <br><br><input class="buttonconfig" type="submit" value="Confirmer">
            <script>
                    CKEDITOR.replace( 'contenuarticle' );
            </script>
        </form> 
            <hr>
            <?php
            global $wpdb;
            $table = "{$wpdb->prefix}mic_articles";
            $sizefile = $_FILES['my_file_upload']['size'];
            if ($_POST){
                if (is_string($_POST['titre']) && is_string($_POST['contenuarticle'])){
                    $file=$_FILES['my_file_upload'];
                    $attachment_id = mic_upload_user_file( $file );
                    $reqimageprepare = $wpdb->prepare("SELECT guid FROM {$wpdb->prefix}posts WHERE ID=%d", $attachment_id);
                    $resultimage = $wpdb->get_row($reqimageprepare);
                    if ($sizefile == 0) {
                        $sql_article = $wpdb->prepare("INSERT INTO $table (image, titre, contenu) SELECT '". plugin_dir_url(__FILE__)."/img/image-default.jpg', %s, %s  FROM dual WHERE NOT EXISTS (SELECT titre FROM $table WHERE titre=%s) LIMIT 1;", $_POST['titre'], $_POST['contenuarticle'], $_POST['titre']);
                    } else {
                        $sql_article = $wpdb->prepare("INSERT INTO $table (image, titre, contenu) SELECT %s, %s, %s FROM dual WHERE NOT EXISTS (SELECT titre FROM $table WHERE titre=%s) LIMIT 1;", $resultimage->guid, $_POST['titre'],  $_POST['contenuarticle'],  $_POST['titre']);
                    }  
                    $wpdb->query($sql_article); 
                }
            }
            
}
function mic_modif_articles(){
    global $wpdb;
    $table = "{$wpdb->prefix}mic_articles";
   ?> <h2>Modification</h2>
        <form action="#" method="post" enctype="multipart/form-data" name="modifpost">
           <h4>Choisir le post à modifier: </h4>
           <select id="service" name="articles">
           <?php    
               $articles = $wpdb->get_results( "select * from {$wpdb->prefix}mic_articles"); 
               ?><option value='0'></option><?php
               foreach ($articles as $article){       
                   echo "<option value='$article->id'>$article->titre</option>";
               }
            ?>   
           </select><br>
           <h4>Saisir le titre : </h4>
               <input type="text" name="titre2" />
           <h4>Saisir le contenu : </h4>
               <textarea name="contenuarticle2" /></textarea>
           <h4>Uploader une image : </h4>
               <input type="file" name="my_file_upload2"  class="bg_checkbox"  >
           <br><br>
               <input class="buttonconfig" type="submit" value="Confirmer">
           <script>
                   CKEDITOR.replace( 'contenuarticle2' );
           </script>
       </form> 
            <?php
            $sizefile2 = $_FILES['my_file_upload2']['size'];
            if ($_POST){
                if(is_string($_POST['titre2']) || is_string($_POST['contenuarticle2']) && $_POST['articles'] != 0){
                    $file2=$_FILES['my_file_upload2'];
                    $attachment_id2 = mic_upload_user_file( $file2 );
                    $reqimageprepare2 = $wpdb->prepare("SELECT guid FROM {$wpdb->prefix}posts WHERE ID=%d", $attachment_id2);
                    $resultimage2 = $wpdb->get_row($reqimageprepare2);
                    //cas de saisi form
                    if ($_POST['articles'] != 0){
                        if (!empty($_POST['titre2']) && !empty($_POST['contenuarticle2']) && $sizefile2 == false) {
                            $sql_uparticle = $wpdb->prepare("UPDATE $table SET titre=%s, contenu=%s WHERE id=%s", $_POST['titre2'], $_POST['contenuarticle2'], $_POST['articles']);
                        } elseif (!empty($_POST['titre2']) && empty($_POST['contenuarticle2']) && $sizefile2 == false){
                            $sql_uparticle = $wpdb->prepare("UPDATE $table SET titre=%s WHERE id=%s", $_POST['titre2'], $_POST['articles']);
                        } elseif (empty($_POST['titre2']) && !empty($_POST['contenuarticle2']) && $sizefile2 == false){
                            $sql_uparticle = $wpdb->prepare("UPDATE $table SET contenu=%s WHERE id=%s", $_POST['contenuarticle2'], $_POST['articles']);
                        } elseif (!empty($_POST['titre2']) && !empty($_POST['contenuarticle2']) && $sizefile2 == true){
                            $sql_uparticle = $wpdb->prepare("UPDATE $table SET image=%s, titre=%s, contenu=%s WHERE id=%s", $resultimage2->guid, $_POST['titre2'], $_POST['contenuarticle2'], $_POST['articles']);
                        } elseif (!empty($_POST['titre2']) && empty($_POST['contenuarticle2']) && $sizefile2 == true){
                            $sql_uparticle = $wpdb->prepare("UPDATE $table SET image=%s, titre=%s WHERE id=%s", $resultimage2->guid, $_POST['titre2'], $_POST['articles']);
                        } elseif (empty($_POST['titre2']) && !empty($_POST['contenuarticle2']) && $sizefile2 == true){
                            $sql_uparticle = $wpdb->prepare("UPDATE $table SET image=%s, contenu=%s WHERE id=%s", $resultimage2->guid, $_POST['contenuarticle2'], $_POST['articles']);
                        } elseif (empty($_POST['titre2']) && empty($_POST['contenuarticle2']) && $sizefile2 == true){
                            $sql_uparticle = $wpdb->prepare("UPDATE $table SET image=%s WHERE id=%s", $resultimage2->guid, $_POST['articles']);
                        }

                        $wpdb->query($sql_uparticle);
                        
                        echo "<script>alert('Post modifié.');</script>";
                        echo "<script type='text/javascript'>window.location=document.location.href;</script>";

                    } else {
                        echo '<h3 style="color:red;">Choisir un post à modifier.</h3>';
                    }
                }
            }
}
function mic_supprimer_articles(){
    global $wpdb;
    $table = "{$wpdb->prefix}mic_articles";
    ?>   
    <hr>
    <h2>Supprimer</h2>
    <form action="#" method="post" enctype="multipart/form-data" name="effacepost">
        <h4>Choisir le post à effacer: </h4>
            <select id="service" name="articles_effacer">
            <?php           
                $articles = $wpdb->get_results( "select * from {$wpdb->prefix}mic_articles"); 
            ?>
            <option value='0'></option>
            <?php
                foreach ($articles as $article){       
                    echo "<option value='$article->id'>$article->titre</option>";
                }
             ?>   
            </select><br>
            <br><br>
                <input class="buttonconfig" type="submit" value="Confirmer">
            <script>
                    CKEDITOR.replace( 'contenuarticle2' );
            </script>
    </form> 
    <?php   
    if ($_POST){
        if(is_numeric($_POST['articles_effacer'])){
            $sql_deletearticle = $wpdb->prepare("DELETE FROM $table WHERE id = %d;", $_POST['articles_effacer']);
            $sql_deletepost = $wpdb->prepare("DELETE FROM {$wpdb->prefix}posts WHERE post_title = (SELECT titre FROM $table WHERE id=%d);", $_POST['articles_effacer']);
            $wpdb->query($sql_deletepost);
            $wpdb->query($sql_deletearticle);
            echo "<script>alert('Post effacé.');</script>";
        }
    }
}    
function mic_gestion_articles(){
    global $wpdb;
    ?>
<?php 
    //mic_ajout_articles();
    //mic_modif_articles();     
    //0ic_supprimer_articles();   
    $articles = $wpdb->get_results( "select * from {$wpdb->prefix}mic_articles"); 

?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){
    $("#hideshow").click(function () {
        $("#ajout_articles").toggle();
    });
});
</script>
<div class="wrap">
<h1 class="wp-heading-inline">
Gestion des articles</h1>
<input type='button' id='hideshow' value='Ajouter' class="page-title-action">
<table class="widefat fixed" cellspacing="0">
    <thead>
    <tr>

            <th id="cb" class="manage-column column-cb check-column" scope="col">aa</th> // this column contains checkboxes
            <th id="columnname" class="manage-column column-columnname" scope="col">bb</th>
            <th id="columnname" class="manage-column column-columnname num" scope="col">cc</th> // "num" added because the column contains numbers

    </tr>
    </thead>

    <tfoot>
    <tr>

            <th class="manage-column column-cb check-column" scope="col">dd</th>
            <th class="manage-column column-columnname" scope="col">ee</th>
            <th class="manage-column column-columnname num" scope="col">ff</th>

    </tr>
    </tfoot>

    <tbody>
    <?php
    foreach ( $articles as $article){ 
        ?>
        <tr class="alternate">
            <th class="check-column" scope="row"><?php echo($article->id); ?></th>
            <td class="column-columnname"><?php echo($article->titre); ?></td>
            <td class="column-columnname"></td>
        </tr>
        
        <tr class="alternate" valign="top"> // this row contains actions
            <th class="check-column" scope="row"></th>
            <td class="column-columnname">
                <div class="row-actions">
                    <span><a href="#">Action</a> |</span>
                    <span><a href="#">Action</a></span>
                </div>
            </td>
            <td class="column-columnname"></td>
        </tr>
    <?php } ?>

        
    </tbody>
</table>
</div>
<div id="ajout_articles" style="display:none;"><?php mic_ajout_articles(); ?></div>
<?php

}
//pour checker les page qu exist
function mic_the_slug_exists($post_name) {
    global $wpdb;
    if($wpdb->get_row("SELECT post_name FROM wp_posts WHERE post_name LIKE '%" . $post_name . "%'", 'ARRAY_A')) {
        return true;
    } else {
        return false;
    }
}

//pour images
function mic_upload_user_file( $file = array() ) {
    require_once( ABSPATH . 'wp-admin/includes/admin.php' );
    $file_return = wp_handle_upload( $file, array('test_form' => false ) );
    if( isset( $file_return['error'] ) || isset( $file_return['upload_error_handler'] ) ) {
        return false;
    } else {
        $filename = $file_return['file'];
        $attachment = array(
            'post_mime_type' => $file_return['type'],
            'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
            'post_content' => '',
            'post_status' => 'inherit',
            'guid' => $file_return['url']
        );
        $attachment_id = wp_insert_attachment( $attachment, $file_return['url'] );
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attachment_data = wp_generate_attachment_metadata( $attachment_id, $filename );
        wp_update_attachment_metadata( $attachment_id, $attachment_data );
        if( 0 < intval( $attachment_id ) ) {
          return $attachment_id;
        }
    }
    return false;
}
//get les restriction de secteur, service et key
/*function mic_get_credit($mdp){
    $url = 'https://www.sciallano.fr/makeitseo/index.php';
    $response = wp_remote_post( $url, array(
    'method'      => 'POST',
    'timeout'     => 45,
    'redirection' => 5,
    'httpversion' => '1.0',
    'blocking'    => true,
    'headers'     => array(),
    'body'        => array(
    'clef_mic' => $mdp
   ),
    'cookies'     => array()
    )
);
 
    if ( is_wp_error( $response ) ) {
        $error_message = $response->get_error_message();
        $error = 'Un problem est survenue.';
        return $error;
    } else {
        $parsedjson = json_decode($response['body']); 
        $credits = array(
            'Nombre Secteurs' => $parsedjson->nb_secteurs,
            'Nombre Keywords' => $parsedjson->nb_keywords,
            'Nombre Services' => $parsedjson->nb_services
        );
        return $credits;
    }
}*/
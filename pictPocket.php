<?php
/*
	Plugin Name: pictPocket
	Plugin URI: http://www.semageek.com/2009/06/27/pictpocket-un-plugin-wp-qui-identifie-et-bloque-les-voleurs-de-contenu/
	Description: Identifier les voleurs de contenus et les bloquer - Identify and block HotLinks.
	Version: 1.3.0
	Author: Semageek
	Author URI: http://www.semageek.com
	
	Copyright 2009 Semageek

	This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/






function pictPocket_install () {
	$newoptions = get_option('pictpocket_options');
	$newoptions['pictpocket_version'] = '1.3.0';
	//$newoptions['pictpocket_autodelete'] = '';			$newoptions['pictpocket_custom_image'] = '';		add_option('pictpocket_options', $newoptions);
		global $wpdb;
	$table_name = $wpdb->prefix . "pictPocket";
	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
		pictPocket_CreateTable();
	}
	
	//la table existe est ce que le champ last_ref existe (passage version 1.0.0 à 1.1.0
	// ALTER TABLE `wp_pictPocket` ADD `last_ref` TEXT NULL AFTER `blocage` ;
	$sql_alter="ALTER TABLE `wp_pictPocket` ADD `last_ref` TEXT NULL AFTER `blocage`" ;
	$wpdb->query($sql_alter);	
	
	$table_name = $wpdb->prefix . "pictPocket_autorisation";
	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
		pictPocket_CreateTableAutorisation();
	}
}
//
function pictPocket() {
?>
<?php
	if ($_GET['action'] == 'view') {
		pictPocket_view( $_GET['do'],$_GET['id']);
	} elseif ($_GET['action'] == 'autorisation') {
		pictPocket_autorisation($_GET['do'],$_GET['id']);
	}elseif(1) {
		pictPocket_main();
	}
}
// Ajout des pages dans l'interface admin
function pictPocket_add_pages() {
	
	$page=$_GET['page'];
	
	global $wpdb;
	$table_name = $wpdb->prefix . "pictPocket";
	
	
	#TOTAL dde voleur
	$qry_total = $wpdb->get_row("
		SELECT count(DISTINCT url) AS voleurs		FROM $table_name WHERE blocage=''

		
	");
	
	
	
	$voleurs= $qry_total->voleurs.'<br/>';
	
	
	if (($voleurs == 0)||($page == "pictpocket/pictPocket.php" )){
		add_menu_page('PictPocket', 'PictPocket', 8, __FILE__, 'pictPocket');
	}else{ 
			add_menu_page('PictPocket', 'PictPocket'."<span id='awaiting-mod' ><span class='pending-count'> ".$voleurs."</span></span>", 8, __FILE__, 'pictPocket');
	}

	add_submenu_page(__FILE__, __('Overview','pictpocket'), __('Overview','PictPocket'), 8, __FILE__, 'pictPocket');
    
	add_submenu_page(__FILE__, __('Autorisations','PictPocket'), __('Autorisations','PictPocket'), 8, 'pictpocket_autorisation', 'pictPocket_autorisation');
	if ($voleurs == 0){
		add_submenu_page(__FILE__, __('HotLinks','PictPocket'), __('HotLinks','PictPocket'), 8, 'pictpocket_view', 'pictpocket_view');
	}else{
		add_submenu_page(__FILE__, __('HotLinks','PictPocket'), __('HotLinks','PictPocket')."<span id='awaiting-mod' ><span class='pending-count'> ".$voleurs."</span></span>", 8, 'pictpocket_view', 'pictpocket_view');
	}		add_submenu_page(__FILE__, __('Options','PictPocket'), __('Options','PictPocket'), 8, 'pictpocket_option', 'pictPocket_option');
	add_submenu_page(__FILE__, __('Donation','PictPocket'), __('Donation','PictPocket'),8, 'https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=admin%40semageek%2ecom&item_name=wp%2dPictPocket&no_shipping=0&no_note=1&tax=0&currency_code=EUR&lc=FR&bn=PP%2dDonationsBF&charset=UTF%2d8');
}


//Creationn de la table des voleurs
function pictPocket_CreateTable() {
	global $wpdb;
	global $wp_db_version;
	$table_name = $wpdb->prefix . "pictPocket";
	$sql_createtable = "CREATE TABLE " . $table_name . " (
	id mediumint(9) NOT NULL AUTO_INCREMENT,	
	url text,
	visited text,
	ip text,
	blocage text,
	last_ref text,
	last_pic text,
	timestamp text,
	UNIQUE KEY id (id)
	);";
	if($wp_db_version >= 5540)	$page = 'wp-admin/includes/upgrade.php';  
								else $page = 'wp-admin/upgrade'.'-functions.php';
	require_once(ABSPATH . $page);
	dbDelta($sql_createtable);
}	

//Creation de la table des autorisation
function pictPocket_CreateTableAutorisation() {
	global $wpdb;
	global $wp_db_version;		
	
	$table_name = $wpdb->prefix . "pictPocket_autorisation";
	$sql_createtable = "CREATE TABLE " . $table_name . " (
	id mediumint(9) NOT NULL AUTO_INCREMENT,	
	cond text,	
	UNIQUE KEY id (id)
	);";
	if($wp_db_version >= 5540)	$page = 'wp-admin/includes/upgrade.php';  
								else $page = 'wp-admin/upgrade'.'-functions.php';
	require_once(ABSPATH . $page);
	dbDelta($sql_createtable);	

	pictPocket_add_cond("!^$");	
	
	//ajout de la condition pour le blog en lui même
	$urlblogcond="!^".get_bloginfo('url' )."/.*$";	
	pictPocket_add_cond($urlblogcond);
	
	//Ajout de quelques conditions de base
	pictPocket_add_cond("!^http://www\.feedburner\.com/.*$");
	pictPocket_add_cond("!^http://(www\.)?google\.com/reader(/)?.*$");
	pictPocket_add_cond("!^http://(www\.)?netvibes\..*(/)?.*$");
	pictPocket_add_cond("!^http://(www\.)?wikio\..*(/)?.*$");
	pictPocket_add_cond("!^http://(www\.)?google\..*(/)?.*$");
	pictPocket_add_cond("!^http://images\.google\..*(/)?.*$");	
}



// function to display the options page
function pictPocket_main() {	
	
	if ( $_POST["pictPocket_htaccess"] ) {
		pictpocket_create_htaccess();		
	}		echo "<div class=\"wrap\">";		echo "<h2>PictPocket : ".__('Overview','pictpocket')."</h2>";	
		$cache_dir = get_home_path().'/wp-content/plugins/'.dirname( plugin_basename(__FILE__) ) . '/cache';		if (!is_writable($cache_dir))  	{		echo "<h1>".__('Warning','pictpocket')."</h1>";		echo __('The directory','pictpocket')." ".$cache_dir." ". __(' is not writeable.<br/>Use chmod command through your ftp or server software in 777. ','pictpocket');		echo "<br/>";	}
	
					
	echo "<h3>";
	_e('Management of the file','pictpocket');
	echo " .htacces</h3>";
	$home_path = get_home_path();
	$htaccess_file = $home_path.'.htaccess';
	//est ce que le plugin a déjà attaqué htacces
	if ( (!file_exists($home_path.'.htaccess') && is_writable($home_path)) || is_writable($home_path.'.htaccess') ){
		
			_e('File .htaccess exist.','pictpocket');
			echo "<br/>";
			$resultat=extract_from_markers( $htaccess_file, 'pictPocket');
			if ($resultat!=array())
			{
				_e('The configuration of .htaccess for PickPocket is Ok','pictpocket');
				echo "<br/>";
			}
			else
			{
				_e('The configuration of .htaccess for PickPocket is missing','pictpocket');
				echo "<br/><br/>";
				echo "<b>".__('Warning','pictpocket')."</b><br>";
				_e('Some webhost don\'t accept the Rewrite URL fonction necessary for the PictPocket plugin.','pictpocket');
				echo "<br/>";
				_e('In case of <b>Wordpress fail</b>, try to clear the .htaccess file in root forlder.','pictpocket');
				echo "<br/><br/>";				
				echo '<form method="post">';
				echo '<input type="hidden" name="pictPocket_htaccess" value="true"></input>';
				echo '<td><input type="submit" value="'.__('Auto Configuration...','pictpocket').'" class="button-primary"></input>';
				
				echo '</form>';
				
				
			}
	}
	else
	{
		//le fichier htacces n'existe pas ou est bloqué en ecriture
		_e('The .htaccess file don\'t exist or is not writeable.','pictpocket');
				
		echo "<br/>";
	}

	
	echo "<h3>".__('Overview','pictpocket')."</h3>";
	global $wpdb;
	$table_name = $wpdb->prefix . "pictPocket";
	
	
	#TOTAL dde voleur
	$qry_total = $wpdb->get_row("
		SELECT count(DISTINCT url) AS voleurs
		FROM $table_name
		
	");
	
	
	_e('Numbers of thieves : ','pictpocket');
	echo $qry_total->voleurs.'<br/>';
	$qry_total = $wpdb->get_results("SELECT visited FROM $table_name ");
	$visite_total=0;
	
	foreach ($qry_total as $qry){
		$visite_total=$visite_total+$qry->visited;
		
	}	
		
	_e('Numbers of hotlinking : ','pictpocket');
	echo $visite_total.'<br/>';
	
	
   
	
	echo "</div>";
}

// Ajoute une condition dans la table
function pictPocket_add_cond($cond)
{
	global $wpdb;
	$table_name = $wpdb->prefix . "pictPocket_autorisation";
	
	//on vérifie que la condition n'existe pas déja dans la table
	$qry = $wpdb->get_results("SELECT id FROM $table_name WHERE cond='$cond' ");

	if ($qry == null)
	{
		$insert = "INSERT INTO " . $table_name .
            " (cond) " .
            "VALUES ('$cond')";
		$results = $wpdb->query( $insert );
	}
}


function pictPocket_autorisation($do ='', $id='')
{
	global $wpdb;
	$table_name = $wpdb->prefix . "pictPocket_autorisation";
	
	$do=$_GET['do'];
	$id=$_GET['id'];
	
	
	if ( $_POST["pictPocket_autorisation"] ) {		
		$new_url = strip_tags(stripslashes($_POST["url"]));
		pictPocket_add_cond($new_url);
		pictpocket_create_htaccess();
	}
	
	if ( $do == 'delete' ) {
		$wpdb->query( "DELETE FROM " . $table_name . " WHERE id =".$id);
		pictpocket_create_htaccess();
	}
			
	
	
	echo '<form method="post">';
	echo "<div class=\"wrap\"><h2>PictPocket : ".__('Autorisations','pictpocket')."</h2>";
	echo "<br/>";	
	
	$qry_total = $wpdb->get_results("SELECT * FROM $table_name ");
	
	echo '<table class=\'widefat\' border="1">';
	
	echo '<tr valign="top"><th>'.__('RewriteCond','pictpocket').'</th><th>'.__('Clear','pictpocket').'</th><tr>';
	
	foreach ($qry_total as $qry){
			echo '<tr valign="top"><td>'.$qry->cond.'</td>';
			
	
	
	//fonction delete
	echo '<td><a href="admin.php?page=pictpocket_autorisation&action=autorisation&do=delete&id='.$qry->id.'">'.__('Clear','pictpocket').'</a>';
	
	
	echo'</td></tr>';
	
	}

	echo '<tr valign="top"><td><input type="text" name="url" value="" size="100"></input></td>';
	
	echo '<input type="hidden" name="pictPocket_autorisation" value="true"></input>';
	echo '<td><input type="submit" value="'.__('Add','pictpocket').' &raquo;" class="button-primary action"></input>';
	echo'</td></tr>';
	
	echo '</table></div>';
	echo "</div>";
	echo '</form>';
	
}


function pictpocket_create_htaccess()
{
	$home_path = get_home_path();
	$htaccess_file = $home_path.'.htaccess';
	$home_root = parse_url(get_option('home'));
	
	if ( isset( $home_root['path'] ) ) {
		$home_root = trailingslashit($home_root['path']);
	} else {
		$home_root = '/';
	}
	//conditions à écrire
	
	$rules .= "RewriteEngine On\n";
	$rules .= "RewriteBase $home_root\n";
	
	
	//gestion des conditions
	global $wpdb;
	$table_name = $wpdb->prefix . "pictPocket_autorisation";
	$qry_total = $wpdb->get_results("SELECT * FROM $table_name ");
	
	
	foreach ($qry_total as $qry){
			$rules .= "RewriteCond %{HTTP_REFERER} ".$qry->cond." [NC]\n";
	}
	
	$rules .= "RewriteRule (.*)\.(gif|jpe?g|png)$ wp-content/plugins/".plugin_basename(dirname(__FILE__))."/pictPocketMoteur.php?pic=$1.$2 [L]\n";	
	
	if ( (!file_exists($home_path.'.htaccess') && is_writable($home_path)) || is_writable($home_path.'.htaccess') ){
			$rules = explode( "\n", $rules );
			insert_with_markers( $htaccess_file, 'pictPocket', $rules );
	}
	
}

function pictpocket_remove_markers( $filename, $marker ) {	if (!file_exists( $filename ) || is_writeable( $filename ) ) {		if (!file_exists( $filename ) ) {			$markerdata = '';		} else {			$markerdata = explode( "\n", implode( '', file( $filename ) ) );		}		$f = fopen( $filename, 'w' );		$foundit = false;		if ( $markerdata ) {			$state = true;			foreach ( $markerdata as $n => $markerline ) {				if (strpos($markerline, '# BEGIN ' . $marker) !== false)					$state = false;				if ( $state ) {					if ( $n + 1 < count( $markerdata ) )						fwrite( $f, "{$markerline}\n" );					else						fwrite( $f, "{$markerline}" );				}				if (strpos($markerline, '# END ' . $marker) !== false) {										$state = true;					$foundit = true;				}			}		}				fclose( $f );		return true;	} else {		return false;	}}

// Cette focntion retire tous ce qui est entre les balises pictpocket du fichier Htaccess si il est présent
function pictpocket_remove_htaccess()
{
	$home_path = get_home_path();
	$htaccess_file = $home_path.'.htaccess';
	$home_root = parse_url(get_option('home'));
	
	if ( isset( $home_root['path'] ) ) {
		$home_root = trailingslashit($home_root['path']);
	} else {
		$home_root = '/';
	}
	
	$rules = "";	
	
	if ( (!file_exists($home_path.'.htaccess') && is_writable($home_path)) || is_writable($home_path.'.htaccess') ){
			$rules = explode( "\n", $rules );
			//insert_with_markers( $htaccess_file, 'pictPocket', $rules );			pictpocket_remove_markers( $htaccess_file, 'pictPocket');
	}
}
function pictPocket_option(){	if($_POST['saveit'] == 'yes') {		update_option('pictpocket_custom_image', $_POST['pictpocket_custom_image']);		/*update_option('pictpocket_autodelete', $_POST['pictpocket_autodelete']);*/			}				echo '<form method="post">';	echo "<div class=\"wrap\"><h2>PictPocket : ".__('Options','pictpocket')."</h2>";		echo "<h3>".__('Custom Image','pictpocket')."</h3>";		$image=get_option('pictpocket_custom_image');	if ($image=='')	{		echo '<tr><td><img src="'.get_bloginfo('url' ).'/wp-content/plugins/'.dirname( plugin_basename(__FILE__) ).'/images/pictPocket.jpg" width="125" height="125"></td></tr><br/><br/>';	}	else	{			echo '<tr><td><img src="'.$image.'" width="125" height="125"></td></tr><br/><br/>';	}				echo '<tr><td>'.__('If you want to use a custom image, just put the url below. Stay it empty for default image.','pictpocket').'</td></tr><br/>';	echo '<tr><td><input type="text" name="pictpocket_custom_image" value="'.get_option('pictpocket_custom_image').'" size="100"></input></td></tr>';						/*echo "<h3>".__('Auto delete','pictpocket')."</h3>";		<?php _e('Automatically delete hotlink older than','pictpocket'); ?>	<select name="pictpocket_autodelete">	<option value="" <?php if(get_option('pictpocket_autodelete') =='' ) print "selected"; ?>><?php _e('Never delete!','pictpocket'); ?></option>	<option value="1 days" <?php if(get_option('pictpocket_autodelete') == "1 days") print "selected"; ?>>1 <?php _e('day','pictpocket'); ?></option>	<option value="7 days" <?php if(get_option('pictpocket_autodelete') == "7 days") print "selected"; ?>>1 <?php _e('week','pictpocket'); ?></option>	<option value="14 days" <?php if(get_option('pictpocket_autodelete') == "14 days") print "selected"; ?>>2 <?php _e('weeks','pictpocket'); ?></option>	<option value="1 months" <?php if(get_option('pictpocket_autodelete') == "1 months") print "selected"; ?>>1 <?php _e('month','pictpocket'); ?></option>	<option value="6 months" <?php if(get_option('pictpocket_autodelete') == "6 months") print "selected"; ?>>6 <?php _e('month','pictpocket'); ?></option>		</select>	<br/>*/				?>			<input type=submit value="<?php _e('Save options','pictpocket'); ?>" class="button-primary action">			<input type=hidden name=saveit value=yes>	<input type=hidden name=page value=pictpocket><input type=hidden name=pictpocket_action value=options>		</div></form>	<?php		}

function pictPocket_view($do ='', $id='')
{
	$do=$_GET['do'];
	$id=$_GET['id'];
	
	global $wpdb;
	$table_name = $wpdb->prefix . "pictPocket";
	$table_auto_name = $wpdb->prefix . "pictPocket_autorisation";
	
	if ( $_POST["clearall"] ) {		
		$wpdb->query( "DELETE FROM " . $table_name . " WHERE 1");		
	}		if ( $_POST["clearold"] ) {						$t=gmdate("Ymd",strtotime('-1 month'));		$wpdb->query( "DELETE FROM " . $table_name . "  WHERE date < '" . $t . "'");					}
	
	if ( $do == 'delete' ) {
		$wpdb->query( "DELETE FROM " . $table_name . " WHERE id =".$id);
	}
	
	if ( $do == 'block' ) {
		$insert = "UPDATE " . $table_name .
            " SET blocage='bloc' WHERE id =".$id;
		$results = $wpdb->query( $insert );
	}
	
	if ( $do == 'unblock' ) {
		$insert = "UPDATE " . $table_name .
            " SET blocage='unblock' WHERE id =".$id;
		$results = $wpdb->query( $insert );
	}
	
	if ( $do == 'auto_url' ) {
		//rajouter l'url correspondant à l'id
		$the_url = $wpdb->get_results("SELECT url FROM $table_name WHERE id =".$id);		
		$the_auto = "!^http://".$the_url[0]->url.".*$";
		
		$new_url = strip_tags(stripslashes($the_auto));
		pictPocket_add_cond($new_url);
		pictpocket_create_htaccess();
		
		
		//suprimer l'url de la liste des voleurs
		$wpdb->query( "DELETE FROM " . $table_name . " WHERE id =".$id);
	}
		
	echo '<form method="post">';
	echo "<div class=\"wrap\"><h2>PictPocket : ".__('Thieves','pictpocket')."</h2>";
	echo '<table class=\'widefat\' border="1">';
	
	echo '<tr valign="top"><th>'.__('URL','pictpocket').'</th><th>'.__('Numbers','pictpocket').'</th><th>'.__('Date','pictpocket').'</th><th>'.__('Autorisation','pictpocket').'</th><th>'.__('Block','pictpocket').'</th><th>'.__('Last acces','pictpocket').'</th><th>'.__('Clear','pictpocket').'</th><tr>';
	
	
	//listage des entrées
	$qry_total = $wpdb->get_results("SELECT * FROM $table_name ");
	foreach ($qry_total as $qry){
	
		
	
		echo '<tr valign="top"><td><a href="'.$qry->last_ref.'">';		if ($qry->blocage == '')		{			echo '<b>'.$qry->url.'</b>';		}		else		{			echo $qry->url;		}						echo '</a></td><td>'.$qry->visited.'</td><td>'.gmdate('d M Y', $qry->timestamp).'-'.gmdate("H:i:s",$qry->timestamp).'</td>';
		
		
		echo '<td><a href="admin.php?page=pictpocket_view&action=view&do=auto_url&id='.$qry->id.'">'.__('Allow','pictpocket').'</a>	</td>';
	
		if ($qry->blocage != 'bloc') {
			echo '<td><a href="admin.php?page=pictpocket_view&action=view&do=block&id='.$qry->id.'">'.__('Block','pictpocket').'</a>';		
		}
		else {
			echo '<td><a href="admin.php?page=pictpocket_view&action=view&do=unblock&id='.$qry->id.'">'.__('Unblock','pictpocket').'</a>';		
		}
		
		echo '</td><td><a href="'.get_bloginfo('url' ).'/'.$qry->last_pic.'">'.$qry->last_pic.'</a></td><td>';
		
		
		//fonction delete
		echo '<a href="admin.php?page=pictpocket_view&action=view&do=delete&id='.$qry->id.'">'.__('Clear','pictpocket').'</a>';
		
		
		echo'</td></tr>';
	
	}
	
	echo '</table>';
	
	echo "<br/>";
	
	echo '<input type="hidden" name="pictPocket_view" value="true"></input>';
	echo '<td><input type="submit" value="'.__('Clear All','pictpocket').' &raquo;" name="clearall" id="clearall" class="button-primary action"></input>';
		//echo '<td><input type="submit" value="'.__('Clear old','pictpocket').' &raquo;" name="clearold" id="clearold" class="button-primary action"></input>';				echo "</div>";
	echo '</form>';
	
	$insert = "UPDATE " . $table_name .
            " SET blocage='unblock' WHERE blocage=''";
	$results = $wpdb->query( $insert );

}
function init_language(){

	load_plugin_textdomain('pictpocket', false, dirname( plugin_basename(__FILE__) ) . '/lang');
}
//uninstall all options
function pictPocket_uninstall () {
	delete_option('pictPocket_options');
	
	//delete htaccess entry
	pictpocket_remove_htaccess();
}

// add the actions
add_action('admin_menu', 'pictPocket_add_pages');
add_action ('init', 'init_language');

register_activation_hook( __FILE__, 'pictPocket_install' );
register_deactivation_hook( __FILE__, 'pictPocket_uninstall' );
?>
<?php /*
 Plugin Name: WDS CityTech
 Plugin URI: http://citytech.webdevstudios.com
 Description: Custom Functionality for CityTech BuddyPress Site.
 Version: 1.0
 Author: WebDevStudios
 Author URI: http://webdevstudios.com
 */
//
//  IMPORTANT !!!! BUDDYPRESS CORE HACKS  !!!!!
// bp-broups/bp-groups-classes.php - contains function added to the class called wds_get_by_meta()
//    (copy of code stored as bp-groups-classes-wds.php in same directory - to be used after a BP upgrade)
// buddypress/bp-core.php - changed function bp_core_time_since to call mktime instead of gmmktime to fix last activity calculation
//    (copy of code stored as bp-core-wds.php in same directory - to be used after a BP upgrade)
//
include "wds-register.php";
include "wds-docs.php";
global $wpdb;
date_default_timezone_set('America/New_York');

//update_option('timezone_string', 'America/New_York');
//$time_zone = $wpdb->query("SET `time_zone` = '".date('P')."'");
add_filter( 'bp_core_mysteryman_src', 'wds_add_default_member_avatar' );
function wds_add_default_member_avatar ($url) {
 return site_url() . "/wp-content/uploads/2011/08/avatar.jpg";
}
add_filter( 'bp_get_signup_avatar', 'wds_default_signup_avatar' );
function wds_default_signup_avatar ($img) {
// return "<img src='http://openlab.citytech.cuny.edu/wp-content/uploads/2011/08/avatar.jpg' width='200' height='200'>";
 return "<img src='" . site_url() . "/wp-content/uploads/2011/08/avatar.jpg' width='200' height='200'>";
}

//
//   This function creates an excerpt of the string passed to the length specified and
//   breaks on a word boundary
//
function wds_content_excerpt($text,$text_length) {
	$full_text = $text;
	$text_length_1 = $text_length + 1;
	if (strlen($full_text) > $text_length) {
	    $text_plus_1 = substr($full_text,0,$text_length_1);
	    $last_space = strrpos($text_plus_1," ");
	    if ($last_space === false) {
		$text = $text_plus_1;
	    } else {
		$text = substr($text_plus_1,0,$last_space);
	    }
	} else {
	    $text = $full_text;
	}
	return $text;
}
//
//   Following filter is to correct Forum display of time since a post was written
//   It calls a core wordpress function called "current_time", and specifies GMT (2nd parameter) as false so it
//   returns the local time instead of GMT. Note that local "server" time is set above to eastern time
//

add_filter('bp_core_current_time','wds_core_current_time');
function wds_core_current_time($current_time) {

//    $current_time = current_time( 'mysql', '0' );
//      $unix_time = time() - 14400;
      $unix_time = time();
      $current_time = date('Y-m-d H:i:s',$unix_time);
    return $current_time;
}

add_action('bp_before_group_forum_topic_posts', 'wds_forum_topic_next_prev');
function wds_forum_topic_next_prev () {
    global $groups_template,
           $wpdb;
 $forum_id = groups_get_groupmeta( $groups_template->group->id, 'forum_id' );
 $topic_id = bp_get_the_topic_id();
 $group_slug = bp_get_group_slug();
 $next_topic = $wpdb->get_results("SELECT * FROM wp_bb_topics
				                 WHERE forum_id='$forum_id' AND topic_id > '$topic_id' AND topic_status='0'
						 ORDER BY topic_id ASC LIMIT 1","ARRAY_A");
 $next_topic_slug = $next_topic[0]['topic_slug'];
 //echo "<br />Next Topic ID: " . $next_topic[0]['topic_id'];
 $previous_topic = $wpdb->get_results("SELECT * FROM wp_bb_topics
				                 WHERE forum_id='$forum_id' AND topic_id < '$topic_id' AND topic_status='0'
						 ORDER BY topic_id DESC LIMIT 1","ARRAY_A");
 $previous_topic_slug = $previous_topic[0]['topic_slug'];
 if ($previous_topic_slug != "") {
  echo "<a href='" . site_url() . "/groups/$group_slug/forum/topic/$previous_topic_slug'><<< Previous Topic &nbsp;&nbsp;&nbsp&nbsp;</a>";
 }
 if ($next_topic_slug != "") {
  echo "<a href='" . site_url() . "/groups/$group_slug/forum/topic/$next_topic_slug'> Next Topic >>></a>";
 }
/*
 echo "<br />Previous Topic ID: " . $previous_topic[0]['topic_id'];
 echo "<br />Next Topic / Previous Topic ";
 echo "<br />Forum ID: " . $forum_id;
 echo "<br />Topic ID: " . bp_get_the_topic_id();
*/
}

/**
 * On activation, copies the BP first/last name profile field data into the WP 'first_name' and
 * 'last_name' fields.
 *
 * @todo This should probably be moved to a different hook. This $last_user lookup is hackish and
 *       may fail in some edge cases. I believe the hook bp_activated_user is correct.
 */
add_action( 'bp_after_activation_page', 'wds_bp_complete_signup' );
function wds_bp_complete_signup(){
        global $bp,$wpdb,$user_ID;

       $last_user = $wpdb->get_results("SELECT * FROM wp_users ORDER BY ID DESC LIMIT 1","ARRAY_A");
//       echo "<br />Last User ID: " . $last_user[0]['ID'] . " Last Login name: " . $last_user[0]['user_login'];
	$user_id = $last_user[0]['ID'];
	$first_name= xprofile_get_field_data( 'First Name', $user_id);
	$last_name=  xprofile_get_field_data( 'Last Name', $user_id);
//	echo "<br />User ID: $user_id First : $first_name Last: $last_name";
	$update_user_first = update_user_meta($user_id,'first_name',$first_name);
	$update_user_last = update_user_meta($user_id,'last_name',$last_name);
}


/**
 * Add members to wpms website if attached to bp group and they are a group member
 *
 * @todo With an updated of BP Groupblog, this should not be necssary. As it is, it adds a lot of
 *       overhead, and should be rewritten to avoid PHP warnings.
 */
add_action('init','wds_add_group_members_2_blog');
function wds_add_group_members_2_blog(){
	global $wpdb, $user_ID, $bp;
	if ($bp->groups->current_group->id) {
	     $group_id = $bp->groups->current_group->id;
	     $blog_id = groups_get_groupmeta($group_id, 'wds_bp_group_site_id' );
	}
	if($user_ID!=0 && $group_id != "" && $blog_id != ""){
		switch_to_blog($blog_id);
		if(!is_blog_user($blog_id)){
		      $sql="SELECT user_title FROM {$bp->groups->table_name}_members WHERE group_id = $group_id and user_id=$user_ID AND is_confirmed='1'";
		      $rs = $wpdb->get_results( $sql );
		      if ( count( $rs ) > 0 ) {
			      foreach( $rs as $r ) {
				      $user_title = $r->user_title;
			      }
			      if($user_title=="Group Admin"){
				      $role="administrator";
			      }elseif($user_title=="Group Mod"){
				      $role="editor";
			      }else{
				      $role="author";
			      }
			      add_user_to_blog( $blog_id, $user_ID, $role );
		      }
		}
		restore_current_blog();
	}
}

//child theme privacy - if corresponding group is private or hidden restrict access to site
/*add_action('init','wds_check_blog_privacy');
function wds_check_blog_privacy(){
	global $bp, $wpdb, $blog_id, $user_ID;
	if($blog_id!=1){
		$wds_bp_group_id=get_option('wds_bp_group_id');
		if($wds_bp_group_id){
			$group = new BP_Groups_Group( $wds_bp_group_id );
			$status = $group->status;
			if($status!="public"){
				//check memeber
				if(!is_user_member_of_blog($user_ID, $blog_id)){
					echo "<center><img src='http://openlab.citytech.cuny.edu/wp-content/mu-plugins/css/images/cuny-sw-logo.png'><h1>";
					echo "This is a private website, ";
					if($user_ID==0){
						echo "please login to gain access.";
					}else{
						echo "you do not have access.";
					}
					echo "</h1></center>";
					exit();
				}
			}
		}
	}
}*/




//child theme menu filter to link to website
add_filter('wp_page_menu','my_page_menu_filter');
function my_page_menu_filter( $menu ) {
	global $bp, $wpdb;
	if (!(strpos($menu,"Home") === false)) {
	    $menu = str_replace("Site Home","Home",$menu);
	    $menu = str_replace("Home","Site Home",$menu);
	} else {
		$menu = str_replace('<div class="menu"><ul>','<div class="menu"><ul><li><a title="Site Home" href="' . site_url() . '">Site Home</a></li>',$menu);
	}
	$menu = str_replace("Site Site Home","Site Home",$menu);
	$wds_bp_group_id=get_option('wds_bp_group_id');
	if($wds_bp_group_id){
		$group_type=ucfirst(groups_get_groupmeta($wds_bp_group_id, 'wds_group_type' ));
		$group = new BP_Groups_Group( $wds_bp_group_id, true );
		$menu = str_replace('<div class="menu"><ul>','<div class="menu"><ul><li><a title="Site" href="http://openlab.citytech.cuny.edu/groups/'.$group->slug.'/">'.$group_type.' Home</a></li>',$menu);
	}
	return $menu;
}

//child theme menu filter to link to website
add_filter( 'wp_nav_menu_items','cuny_add_group_menu_items' );
function cuny_add_group_menu_items($items) {
	if (!(strpos($items,"Home") === false)) {
	    $items = str_replace("Site Home","Home",$items);
	    //$items = str_replace("Home","Site Home",$items);
	} else {
		$items = '<li><a title="Site Home" href="' . site_url() . '">Site Home</a></li>' . $items;
	}
	$items = cuny_group_menu_items() . $items;
	return $items;
}
function cuny_group_menu_items() {
	global $bp, $wpdb;

	$wds_bp_group_id = get_option('wds_bp_group_id');

	if($wds_bp_group_id){
		$group_type=ucfirst(groups_get_groupmeta($wds_bp_group_id, 'wds_group_type' ));
		$group = new BP_Groups_Group( $wds_bp_group_id, true );

		$tab = '<li><a title="Site" href="http://openlab.citytech.cuny.edu/groups/'.$group->slug.'/">'.$group_type.' Home</a></li>';
		$tabs = $tab;
	}

	return $tabs;
}

//Change "Group" to something else
class buddypress_Translation_Mangler {
 /*
 * Filter the translation string before it is displayed.
  */
 function filter_gettext($translation, $text, $domain) {
   $group_id = bp_get_group_id();
   $grouptype = groups_get_groupmeta( $group_id, 'wds_group_type' );
   $uc_grouptype = ucfirst($grouptype);
   $translations = &get_translations_for_domain( 'buddypress' );
   switch($text){
	case "Forum":
     return $translations->translate( "Discussion" );
     break;
	case "Group Forum":
     return $translations->translate( "$uc_grouptype Discussion" );
     break;
	case "Group Forum Directory":
     return $translations->translate( "" );
     break;
	case "Group Forums Directory":
     return $translations->translate( "Group Discussions Directory" );
     break;
	case "Join Group":
     return $translations->translate( "Join Now!" );
     break;
	case "You successfully joined the group.":
     return $translations->translate( "You successfully joined!" );
     break;
	case "Recent Discussion":
     return $translations->translate( "Recent Forum Discussion" );
     break;
    case "This is a hidden group and only invited members can join.":
     return $translations->translate( "This is a hidden " . $grouptype . " and only invited members can join." );
     break;
    case "This is a private group and you must request group membership in order to join.":
     return $translations->translate( "This is a private " . $grouptype . " and you must request " . $grouptype . " membership in order to join." );
     break;
    case "This is a private group. To join you must be a registered site member and request group membership.":
     return $translations->translate( "This is a private " . $grouptype . ". To join you must be a registered site member and request " . $grouptype . " membership." );
     break;
    case "This is a private group. Your membership request is awaiting approval from the group administrator.":
     return $translations->translate( "This is a private " . $grouptype . ". Your membership request is awaiting approval from the " . $grouptype . " administrator." );
     break;
    case "said ":
     return $translations->translate( "" );
     break;
  }
  return $translation;
 }
}
add_filter('gettext', array('buddypress_Translation_Mangler', 'filter_gettext'), 10, 4);


//add breadcrumbs for buddypress pages
add_action('wp_footer','wds_footer_breadcrumbs');
function wds_footer_breadcrumbs(){
	global $bp,$bp_current;
	if($bp->current_component=="groups"){
		$group_id=$bp->groups->current_group->id;
		$b2=$bp->groups->current_group->name;
		$group_type=groups_get_groupmeta($bp->groups->current_group->id, 'wds_group_type' );
		if($group_type=="course"){
			$b1='<a href="'.site_url().'/courses/">Courses</a>';
		}elseif($group_type=="project"){
			$b1='<a href="'.site_url().'/projects/">Projects</a>';
		}elseif($group_type=="club"){
			$b1='<a href="'.site_url().'/clubs/">Clubs</a>';
		}else{
			$b1='<a href="'.site_url().'/groups/">Groups</a>';
		}

	}
	if($bp->displayed_user->id){
		$account_type = xprofile_get_field_data( 'Account Type', $bp->displayed_user->id);
		if($account_type=="Staff"){
			$b1='<a href="'.site_url().'/people/">People</a> / <a href="'.site_url().'/people/staff/">Staff</a>';
		}elseif($account_type=="Faculty"){
			$b1='<a href="'.site_url().'/people/">People</a> / <a href="'.site_url().'/people/faculty/">Faculty</a>';
		}elseif($account_type=="Student"){
			$b1='<a href="'.site_url().'/people/">People</a> / <a href="'.site_url().'/people/students/">Students</a>';
		}else{
			$b1='<a href="'.site_url().'/people/">People</a>';
		}
		$last_name= xprofile_get_field_data( 'Last Name', $bp->displayed_user->id);
		$b2=ucfirst($bp->displayed_user->fullname).' '.ucfirst($last_name);
	}
	if($bp->current_component=="groups" || $bp->displayed_user->id){
		$breadcrumb='<div class="breadcrumb">You are here:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a title="View Home" href="http://openlab.citytech.cuny.edu/">Home</a> / '.$b1.' / '.$b2.'</div>';
		$breadcrumb=str_replace("'","\'",$breadcrumb);?>
    	<script>document.getElementById('breadcrumb-container').innerHTML='<?php echo $breadcrumb; ?>';</script>
    <?php
	}
}




//Filter bp members full name
//add_filter('bp_get_member_name', 'wds_bp_the_site_member_realname');
//add_filter('bp_member_name', 'wds_bp_the_site_member_realname');
//add_filter('bp_get_displayed_user_fullname', 'wds_bp_the_site_member_realname');
//add_filter('bp_displayed_user_fullname', 'wds_bp_the_site_member_realname');
//add_filter('bp_get_loggedin_user_fullname', 'wds_bp_the_site_member_realname' );
//add_filter('bp_loggedin_user_fullname', 'wds_bp_the_site_member_realname' );
function wds_bp_the_site_member_realname(){
	global $bp;
	global $members_template;
	$members_template->member->fullname = $members_template->member->display_name;
	$user_id=$members_template->member->id;
	$first_name= xprofile_get_field_data( 'Name', $user_id);
	$last_name= xprofile_get_field_data( 'Last Name', $user_id);
	return ucfirst($first_name)." ".ucfirst($last_name);
}


//filter names in activity
/*add_filter('bp_get_activity_action', 'wds_bp_the_site_member_realname_activity' );
add_filter('bp_get_activity_user_link', 'wds_bp_the_site_member_realname_activity' );
function wds_bp_the_site_member_realname_activity(){
	global $bp;
	global $activities_template;
	print_r($activities_template);
	$action = $activities_template->activity->action;
	echo "<hr><xmp>".$action."</xmp>";
	return $action;
	$user_id=$activities_template->activity->user_id;
	$first_name= xprofile_get_field_data( 'Name', $user_id);
	$last_name= xprofile_get_field_data( 'Last Name', $user_id);
	$activities_template->activity->user_nicename="rr";
	$link = bp_core_get_user_domain( $activities_template->activity->user_id, $activities_template->activity->user_nicename, $activities_template->activity->user_login );
	return "werwe";
}*/

//Default BP Avatar Full
if ( !defined( 'BP_AVATAR_FULL_WIDTH' ) )
define( 'BP_AVATAR_FULL_WIDTH', 225 );
if ( !defined( 'BP_AVATAR_FULL_HEIGHT' ) )
define( 'BP_AVATAR_FULL_HEIGHT', 225 );


/**
 * Don't let child blogs use bp-default or a child thereof
 *
 * @todo Why isn't this done by network disabling BP Default and its child themes?
 * @todo Why isn't BP_DISABLE_ADMIN_BAR defined somewhere like bp-custom.php?
 */
function wds_default_theme(){
	global $wpdb,$blog_id;
	if($blog_id>1){
		define('BP_DISABLE_ADMIN_BAR', true);
		$theme=get_option('template');
		if($theme=="bp-default"){
			switch_theme( "twentyten", "twentyten" );
			wp_redirect( home_url() );
			exit();
		}
	}
}
add_action( 'init', 'wds_default_theme' );

//register.php -hook for new div to show account type fields
add_action( 'bp_after_signup_profile_fields', 'wds__bp_after_signup_profile_fields' );
function wds__bp_after_signup_profile_fields(){?>
<div class="editfield"><div id="wds-account-type"></div></div>
<?php
}


add_action('wp_head', 'wds_registration_ajax' );
function wds_registration_ajax(){
	wp_print_scripts( array( 'sack' ));
	$sack='var isack = new sack("'.get_bloginfo( 'wpurl' ).'/wp-admin/admin-ajax.php");';
	$loading='<img src="'.get_bloginfo('template_directory').'/_inc/images/ajax-loader.gif">';?>
	<script type="text/javascript">
		//<![CDATA[

		//load register account type
		function wds_load_account_type(id,default_type){
			<?php echo $sack;?>
			//document.getElementById('save-pad').innerHTML='<?php echo $loading; ?>';
			if (default_type != "") {
			 selected_value = default_type;
			} else {
			   var select_box=document.getElementById(id);
			   var selected_index=select_box.selectedIndex;
			   var selected_value = select_box.options[selected_index].value;
			}

			if(selected_value!=""){
				document.getElementById('signup_submit').style.display='';
			}else{
				document.getElementById('signup_submit').style.display='none';
			}

			isack.execute = 1;
			isack.method = 'POST';
			isack.setVar( "action", "wds_load_account_type" );
			isack.setVar( "account_type", selected_value );
			isack.runAJAX();
			return true;
		}


		//]]>
	</script>
	<?php
}
add_action('bp_after_registration_submit_buttons' , 'wds_load_default_account_type');
function wds_load_default_account_type() {
 		    $return = '<script type="text/javascript">';
		    if ($_POST['field_7'] == "Student" || $_POST['field_7'] == "") {
			$type = "Student";
			$selected_index = 1;
		    }
		    if ($_POST['field_7'] == "Faculty") {
			$type = "Faculty";
			$selected_index = 2;
		    }
		    if ($_POST['field_7'] == "Staff") {
			$type = "Staff";
			$selected_index = 3;
		    }
			$return .=  'var select_box=document.getElementById(\'field_7\');';
			$return .=  'select_box.selectedIndex = ' . $selected_index . ';';
		    $return .= "wds_load_account_type('field_7','$type');";
		    $return .= '</script>';
		    echo $return;

}

add_action('wp_ajax_wds_load_account_type', 'wds_load_account_type');
add_action('wp_ajax_nopriv_wds_load_account_type', 'wds_load_account_type');
function wds_load_account_type(){
	global $wpdb, $bp;
	$return='';
	$account_type = $_POST['account_type'];
	if($account_type){
		//get matching profile group_id
		$sql = "SELECT id FROM wp_bp_xprofile_groups where name='".$account_type."'";
		$posts = $wpdb->get_results($sql, OBJECT);
		if ($posts){
			foreach ($posts as $post):
				$group_id=$post->id;
			endforeach;
			$return.=wds_get_register_fields($group_id);
		}
	}else{
		$return="Please select an Account Type.";
	}
	$return=str_replace("'","\'",$return);
	die("document.getElementById('wds-account-type').innerHTML='$return'");
}

function wds_bp_profile_group_tabs() {
	global $bp, $group_name;
	if ( !$groups = wp_cache_get( 'xprofile_groups_inc_empty', 'bp' ) ) {
		$groups = BP_XProfile_Group::get( array( 'fetch_fields' => true ) );
		wp_cache_set( 'xprofile_groups_inc_empty', $groups, 'bp' );
	}
	if ( empty( $group_name ) )
		$group_name = bp_profile_group_name(false);

	for ( $i = 0; $i < count($groups); $i++ ) {
		if ( $group_name == $groups[$i]->name ) {
			$selected = ' class="current"';
		} else {
			$selected = '';
		}
		$account_type=bp_get_profile_field_data( 'field=Account Type' );
		if ( $groups[$i]->fields ){
			echo '<li' . $selected . '><a href="' . $bp->displayed_user->domain . $bp->profile->slug . '/edit/group/' . $groups[$i]->id . '">' . esc_attr( $groups[$i]->name ) . '</a></li>';
		}
	}
	do_action( 'xprofile_profile_group_tabs' );
}
//Group Stuff
add_action('wp_head', 'wds_groups_ajax');
function wds_groups_ajax(){
	global $bp;
	wp_print_scripts( array( 'sack' ));
	$sack='var isack = new sack("'.get_bloginfo( 'wpurl' ).'/wp-admin/admin-ajax.php");';
	$loading='<img src="'.get_bloginfo('template_directory').'/_inc/images/ajax-loader.gif">';?>
	<script type="text/javascript">
		//<![CDATA[
		function wds_load_group_type(id){
			<?php echo $sack;?>
			var select_box=document.getElementById(id);
			var selected_index=select_box.selectedIndex;
			var selected_value = select_box.options[selected_index].value;
			isack.execute = 1;
			isack.method = 'POST';
			isack.setVar( "action", "wds_load_group_type" );
			isack.setVar( "group_type", selected_value );
			isack.runAJAX();
			return true;
		}

		function wds_load_group_departments(id){
			<?php $group=$bp->groups->current_group->id;
			echo $sack;?>
			var schools="0";
			if(document.getElementById('school_tech').checked){
				schools=schools+","+document.getElementById('school_tech').value;
			}
			if(document.getElementById('school_studies').checked){
				schools=schools+","+document.getElementById('school_studies').value;
			}
			if(document.getElementById('school_arts').checked){
				schools=schools+","+document.getElementById('school_arts').value;
			}
			isack.execute = 1;
			isack.method = 'POST';
			isack.setVar( "action", "wds_load_group_departments" );
			isack.setVar( "schools", schools );
			isack.setVar( "group", "<?php echo $group;?>" );
			isack.runAJAX();
			return true;
		}
		//]]>
	</script>
	<?php
}

add_action('wp_ajax_wds_load_group_departments', 'wds_load_group_departments');
add_action('wp_ajax_nopriv_wds_load_group_departments', 'wds_load_group_departments');
function wds_load_group_departments(){
	global $wpdb, $bp;
	$group = $_POST['group'];
	$schools = $_POST['schools'];
	$schools=str_replace("0,","",$schools);
	$schools=explode(",",$schools);

	$departments_tech=array('Advertising Design and Graphic Arts','Architectural Technology','Computer Engineering Technology','Computer Systems Technology','Construction Management and Civil Engineering Technology','Electrical and Telecommunications Engineering Technology','Entertainment Technology','Environmental Control Technology','Mechanical Engineering Technology');
	$departments_studies=array('Business','Career and Technology Teacher Education','Dental Hygiene','Health Services Administration','Hospitality Management','Human Services','Law and Paralegal Studies','Nursing','Radiologic Technology and Medical Imaging','Restorative Dentistry','Vision Care Technology');
	$departments_arts=array('African-American Studies','Biological Sciences','Chemistry','English','Humanities','Library','Mathematics','Physics','Social Science');
	$departments=array();
	if(in_array("tech",$schools)){
		$departments=array_merge_recursive($departments, $departments_tech);
	}
	if(in_array("studies",$schools)){
		$departments=array_merge_recursive($departments, $departments_studies);
	}
	if(in_array("arts",$schools)){
		$departments=array_merge_recursive($departments, $departments_arts);
	}
	sort($departments);
	$wds_departments=groups_get_groupmeta($group, 'wds_departments' );
	$wds_departments=explode(",",$wds_departments);
	$return="<div style='height:100px;overflow:scroll;'>";
	foreach ($departments as $i => $value) {
		$checked="";
		if(in_array($value,$wds_departments)){
			$checked="checked";
		}
		$return.="<input type='checkbox' name='wds_departments[]' value='".$value."' ".$checked."> ".$value."<br>";
	}
	$return.="</div>";
	$return=str_replace("'","\'",$return);
	die("document.getElementById('departments_html').innerHTML='$return'");
}

add_action('init', 'wds_new_group_type');
function wds_new_group_type(){
  if($_GET['new']=="true" && $_GET['type']){
	  global $bp;
	  unset( $bp->groups->current_create_step );
	  unset( $bp->groups->completed_create_steps );

	  setcookie( 'bp_new_group_id', false, time() - 1000, COOKIEPATH );
	  setcookie( 'bp_completed_create_steps', false, time() - 1000, COOKIEPATH );
	  setcookie( 'wds_bp_group_type', $_GET['type'], time() + 20000, COOKIEPATH );
	  bp_core_redirect( $bp->root_domain . '/' . $bp->groups->slug . '/create/step/group-details/?type='.$_GET['type'] );
  }
}

add_action('wp_ajax_wds_load_group_type', 'wds_load_group_type');
add_action('wp_ajax_nopriv_wds_load_group_type', 'wds_load_group_type');
function wds_load_group_type($group_type){
	global $wpdb, $bp, $user_ID;
	$return='';
	if($group_type){
		$echo=true;
		$return='<input type="hidden" name="group_type" value="'.ucfirst($group_type).'">';
	}else{
		$group_type = $_POST['group_type'];
	}

	if(is_super_admin( $user_ID )){
		$wds_group_featured=groups_get_groupmeta($bp->groups->current_group->id, 'wds_group_featured' );
		if($wds_group_featured){
			$checked="checked";
		}
		$return.='<input type="checkbox" id="wds_group_featured" name="wds_group_featured" value="yes" '.$checked.'> Featured '.$group_type;
	}
	$return.='<table>';
	$wds_group_school=groups_get_groupmeta($bp->groups->current_group->id, 'wds_group_school' );
	$wds_group_school=explode(",",$wds_group_school);
		$return.='<tr>';
            $return.='<td>School(s):';
            $return.='<td>';
			$checked="";
			if($bp->groups->current_group->id && in_array("tech",$wds_group_school)){
				$checked="checked";
			}
			if($group_type=="course"){
				$onclick='onclick="wds_load_group_departments();"';
			}
			$return.='<input type="checkbox" id="school_tech" name="wds_group_school[]" value="tech" '.$onclick.' '.$checked.'> Technology & Design ';
			$checked="";
			if($bp->groups->current_group->id &&in_array("studies",$wds_group_school)){
				$checked="checked";
			}
			$return.='<input type="checkbox" id="school_studies" name="wds_group_school[]" value="studies" '.$onclick.' '.$checked.'> Professional Studies ';
			$checked="";
			if($bp->groups->current_group->id &&in_array("arts",$wds_group_school)){
				$checked="checked";
			}
			$return.='<input type="checkbox" id="school_arts" name="wds_group_school[]" value="arts" '.$onclick.' '.$checked.'> Arts & Sciences ';
			$return.='</td>';

		$return.='</tr>';
	if($group_type=="course"){
		if($bp->groups->current_group->id){
		  $wds_faculty=groups_get_groupmeta($bp->groups->current_group->id, 'wds_faculty' );
		  $wds_course_code=groups_get_groupmeta($bp->groups->current_group->id, 'wds_course_code' );
		  $wds_section_code=groups_get_groupmeta($bp->groups->current_group->id, 'wds_section_code' );
		  $wds_semester=groups_get_groupmeta($bp->groups->current_group->id, 'wds_semester' );
		  $wds_year=groups_get_groupmeta($bp->groups->current_group->id, 'wds_year' );
		  $wds_course_html=groups_get_groupmeta($bp->groups->current_group->id, 'wds_course_html' );
		}
        //$return.='<tr>';
           //$return.=' <td>Faculty:';
            //$return.='<td><input type="text" name="wds_faculty" value="'.$bp->loggedin_user->fullname.'"></td>';
        //$return.='</tr>';
		$last_name= xprofile_get_field_data( 'Last Name', $bp->loggedin_user->id);
		$return.='<input type="hidden" name="wds_faculty" value="'.$bp->loggedin_user->fullname.' '.$last_name.'">';

		$return.='<tr>';
            $return.='<td>Department(s):';
            $return.='<td id="departments_html"></td>';
        $return.='</tr>';
		$return.='<tr>';
           $return.=' <td>Course Code:';
            $return.='<td><input type="text" name="wds_course_code" value="'.$wds_course_code.'"></td>';
        $return.='</tr>';
		$return.='<tr>';
            $return.='<td>Section Code:';
            $return.='<td><input type="text" name="wds_section_code" value="'.$wds_section_code.'"></td>';
        $return.='</tr>';
		$return.='<tr>';
            $return.='<td>Semester:';
            $return.='<td><select name="wds_semester">';
                $return.='<option value="">--select one--';
				$checked="";
				if($wds_semester=="Spring"){
					$Spring="selected";
				}elseif($wds_semester=="Summer"){
					$Summer="selected";
				}elseif($wds_semester=="Fall"){
					$Fall="selected";
				}elseif($wds_semester=="Winter"){
					$Winter="selected";
				}
				$return.='<option value="Spring" '.$Spring.'>Spring';
                $return.='<option value="Summer" '.$Summer.'>Summer';
                $return.='<option value="Fall" '.$Fall.'>Fall';
                $return.='<option value="Winter" '.$Winter.'>Winter';
            $return.='</select></td>';
        $return.='</tr>';
		$return.='<tr>';
            $return.='<td>Year:';
            $return.='<td><input type="text" name="wds_year" value="'.$wds_year.'"></td>';
        $return.='</tr>';
		$return.='<tr>';
            $return.='<td>Additional Description/HTML:';
            $return.='<td><textarea name="wds_course_html">'.$wds_course_html.'</textarea></td>';
        $return.='</tr>';

	}elseif($group_type=="project"){

	}elseif($group_type=="club"){

	}else{
		$return="Please select a Group Type.";
	}
	$return.='</table>';
	if($group_type=="course"){
		$return.='<script>wds_load_group_departments();</script>';
	}
	if($echo){
		return $return;
	}else{
		$return=str_replace("'","\'",$return);
		die("document.getElementById('wds-group-type').innerHTML='$return'");
	}
}

add_action( 'bp_after_group_details_creation_step', 'wds_bp_group_meta');
add_action( 'bp_after_group_details_admin', 'wds_bp_group_meta');
function wds_bp_group_meta(){
	global $wpdb, $bp, $current_site, $base;
	$group_type=groups_get_groupmeta($bp->groups->current_group->id, 'wds_group_type' );
	$group_school=groups_get_groupmeta($bp->groups->current_group->id, 'wds_group_school' );
	$group_project_type=groups_get_groupmeta($bp->groups->current_group->id, 'wds_group_project_type' );
	?>
    <div class="ct-group-meta">
      <?php
	  $type=$_GET['type'];
	  if(!$type){
		  $type=groups_get_groupmeta(bp_get_new_group_id(), 'wds_group_type' );
	  }

	  if(!$type){
		  $type=$_COOKIE["wds_bp_group_type"];
	  }

	  if(!$type || !in_array($type,array("club","project","course","school"))){
		  $type="group";
	  }
	  if($group_type!="group" && $group_type){
		  echo wds_load_group_type($group_type);?>
           <input type="hidden" name="group_type" value="<?php echo $group_type;?>" />
          <?php
		}elseif($type!="group"){
		  $group_type=$type;
		  echo wds_load_group_type($type);?>
           <input type="hidden" name="group_type" value="<?php echo $group_type;?>" />
          <?php
	  }else{?>
        <table>
        <tr>
        <td>Type:</td>
        <td><select id="group_type" name="group_type" onchange="wds_load_group_type('group_type');">
            <option value="" <?php if($group_type==""){echo "selected";}?>>--select one--
            <option value="club" <?php if($group_type=="club"){echo "selected";}?>>Club
            <option value="project" <?php if($group_type=="project"){echo "selected";}?>>Project
            <?php if(is_super_admin(get_current_user_id())){?><option value="course" <?php if($group_type=="course"){echo "selected";}?>>Course
            <option value="school" <?php if($group_type=="school"){echo "selected";}?>>School<?php } ?>
        </select></td>
        </tr>
        </table>
      <?php } ?>
      <div id="wds-group-type"></div>
      <?php //Copy Site
	  $wds_bp_group_site_id=groups_get_groupmeta($bp->groups->current_group->id, 'wds_bp_group_site_id' );
	  if(!$wds_bp_group_site_id){
		$template="template-".strtolower($group_type);
		$blog_details = get_blog_details($template);
		?>
		<script>
		function showHide(id)
		{
		  var style = document.getElementById(id).style
		   if (style.display == "none")
			style.display = "";
		   else
			style.display = "none";
		}
		</script>
        <input type="hidden" name="action" value="copy_blog" />
		<input type="hidden" name="source_blog" value="<?php echo $blog_details->blog_id; ?>" />
		<table class="form-table">
			<?php
			/*if($bp->current_action=="create"){?>
            <tr class="form-field form-required">
				<td>
            		<input type="checkbox" name="wds_bp_docs_wiki" value="yes" checked="checked" /> Setup a <?php echo $group_type;?> Wiki?
				</td>
            </tr>
			<?php }*/
			if($group_type!="course"){
				$show_website="none"?>
            <tr class="form-field form-required">
				<th style="text-align:center;" scope='row'>
            		<input type="checkbox" name="wds_website_check" value="yes" onclick="showHide('wds-website');" /> Setup a Site?
				</th>
            </tr>
            <?php }else{
			 	$show_website="";
			}?>
            <tr id="wds-website" class="form-field form-required" style="display:<?php echo $show_website;?>">
				<td valign="top" scope='row'><?php _e('Site Address') ?><br /></td>
				<td>
				<?php
				if( constant( "VHOST" ) == 'yes' ) : ?>
					<input name="blog[domain]" type="text" title="<?php _e('Domain') ?>"/>.<?php echo $current_site->domain;?>
				<?php else:
					echo $current_site->domain . $current_site->path ?><input name="blog[domain]" type="text" title="<?php _e('Domain') ?>"/>
				<?php endif; ?>

                <select name="wds_group_privacy">
                    	<option value="">Public
                        <option value="private">Private
                    </select>
                </td>
			</tr>
		</table>
   	<?php } ?>
    </div>
    <?php
}



//Save Group Meta
add_action( 'groups_group_after_save', 'wds_bp_group_meta_save' );
function wds_bp_group_meta_save($group) {
	global $wpdb, $user_ID;
	if ( isset($_POST['group_type']) ) {
		groups_update_groupmeta( $group->id, 'wds_group_type', $_POST['group_type']);
	}

	if ( isset($_POST['wds_faculty']) ) {
		groups_update_groupmeta( $group->id, 'wds_faculty', $_POST['wds_faculty']);
	}
	if ( isset($_POST['wds_group_school']) ) {
		$wds_group_school=implode(",",$_POST['wds_group_school']);
		groups_update_groupmeta( $group->id, 'wds_group_school', $wds_group_school);
	}
	if ( isset($_POST['wds_departments']) ) {
		$wds_departments=implode(",",$_POST['wds_departments']);
		groups_update_groupmeta( $group->id, 'wds_departments', $wds_departments);
	}
	if ( isset($_POST['wds_course_code']) ) {
		groups_update_groupmeta( $group->id, 'wds_course_code', $_POST['wds_course_code']);
	}
	if ( isset($_POST['wds_section_code']) ) {
		groups_update_groupmeta( $group->id, 'wds_section_code', $_POST['wds_section_code']);
	}
	if ( isset($_POST['wds_semester']) ) {
		groups_update_groupmeta( $group->id, 'wds_semester', $_POST['wds_semester']);
	}
	if ( isset($_POST['wds_year']) ) {
		groups_update_groupmeta( $group->id, 'wds_year', $_POST['wds_year']);
	}
	if ( isset($_POST['wds_course_html']) ) {
		groups_update_groupmeta( $group->id, 'wds_course_html', $_POST['wds_course_html']);
	}
	if ( isset($_POST['group_project_type']) ) {
		groups_update_groupmeta( $group->id, 'wds_group_project_type', $_POST['group_project_type']);
	}
	if(is_super_admin( $user_ID )){
	  if ( isset($_POST['wds_group_featured']) ) {
		  groups_update_groupmeta( $group->id, 'wds_group_featured', $_POST['wds_group_featured']);
	  }else{
		  groups_update_groupmeta( $group->id, 'wds_group_featured', '');
	  }
	}
	/*//WIKI
	if ( isset($_POST['wds_bp_docs_wiki']) && $_POST['wds_bp_docs_wiki']=="yes" ) {
		groups_update_groupmeta( $group->id, 'bpdocs', 'a:2:{s:12:"group-enable";s:1:"1";s:10:"can-create";s:6:"member";}');
	}*/

	//copy blog function
	ra_copy_blog_page($group->id);
}

//show blog and pages on menu
class WDS_Group_Extension extends BP_Group_Extension {

	var $enable_nav_item = true;
	var $enable_create_step = false;
	function wds_group_extension() {
		global $bp;
		$group_id=$bp->groups->current_group->id;
		$wds_bp_group_site_id=groups_get_groupmeta($group_id, 'wds_bp_group_site_id' );
		if($wds_bp_group_site_id!=""){
		  $this->name = 'Activity';
		  $this->slug = 'activity';
  		  $this->nav_item_position = 10;
		}
	}

	function create_screen() {
		if ( !bp_is_group_creation_step( $this->slug ) )
			return false;
		wp_nonce_field( 'groups_create_save_' . $this->slug );
	}

	function create_screen_save() {
		global $bp;

		check_admin_referer( 'groups_create_save_' . $this->slug );

		groups_update_groupmeta( $bp->groups->new_group_id, 'my_meta_name', 'value' );
	}

	function edit_screen() {
		if ( !bp_is_group_admin_screen( $this->slug ) )
			return false; ?>

		<h2><?php echo esc_attr( $this->name ) ?></h2>
        <?php
		wp_nonce_field( 'groups_edit_save_' . $this->slug );
	}

	function edit_screen_save() {
		global $bp;

		if ( !isset( $_POST['save'] ) )
			return false;

		check_admin_referer( 'groups_edit_save_' . $this->slug );

		/* Insert your edit screen save code here */

		/* To post an error/success message to the screen, use the following */
		if ( !$success )
			bp_core_add_message( __( 'There was an error saving, please try again', 'buddypress' ), 'error' );
		else
			bp_core_add_message( __( 'Settings saved successfully', 'buddypress' ) );

		bp_core_redirect( bp_get_group_permalink( $bp->groups->current_group ) . '/admin/' . $this->slug );
	}

	function display() {
		global $bp;
		gconnect_locate_template( array( 'groups/single/group-header.php' ), true );
		gconnect_locate_template( array( 'groups/single/activity.php' ), true );

		/*$group_id=$bp->groups->current_group->id;
		$wds_bp_group_site_id=groups_get_groupmeta($group_id, 'wds_bp_group_site_id' );
		if($wds_bp_group_site_id!=""){
		  switch_to_blog($wds_bp_group_site_id);
		  $pages = get_pages();
		  ?>
		  <div role="navigation" id="subnav" class="item-list-tabs no-ajax">
			  <ul>
				 <?php foreach ($pages as $pagg) {?>
					<li class="current"><a href="?page=<?php echo $pagg->ID;?>"><?php echo $pagg->post_title;?></a></li>
				  <?php }?>
			  </ul>
		  </div>
		  <?php
		  if($_GET['page']){
			  $id=$_GET['page'];
			  $post = get_post($id);
			  echo $post->post_content;
		  }
		  restore_current_blog();
		}*/
	}

	function widget_display() { ?>
		<div class=&quot;info-group&quot;>
			<h4><?php echo esc_attr( $this->name ) ?></h4>
		</div>
		<?php
	}

}
//bp_register_group_extension( 'WDS_Group_Extension' );


add_action("bp_group_options_nav","wds_bp_group_site_pages");
function wds_bp_group_site_pages(){
	global $bp;
	//print_r($bp);
	$site=site_url();
	$group_id=$bp->groups->current_group->id;
	$wds_bp_group_site_id=groups_get_groupmeta($group_id, 'wds_bp_group_site_id' );
	if($wds_bp_group_site_id!=""){
	  switch_to_blog($wds_bp_group_site_id);
	  $pages = get_pages(array('sort_order' => 'ASC','sort_column' => 'menu_order'));
	  echo "<ul class='website-links'>";
	  echo "<li><a href='".site_url()."'>Site</a><ul>";
	  foreach ($pages as $pagg) {
		echo "<li><a href='".get_page_link($pagg->ID)."'>".$pagg->post_title."</li>";
	  }
	  echo "</ul></ul>";
	  restore_current_blog();
	}
}

//Copy the group blog template
function ra_copy_blog_page($group_id) {
	global $bp, $wpdb, $current_site, $user_email, $base, $user_ID;
	$blog = $_POST['blog'];
	if($blog[ 'domain' ] && $group_id){
	  $wpdb->dmtable = $wpdb->base_prefix . 'domain_mapping';
	  if(!defined('SUNRISE') || $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->dmtable}'") != $wpdb->dmtable) {
		  $join = $where = '';
	  } else {
		  $join = "LEFT JOIN {$wpdb->dmtable} d ON d.blog_id = b.blog_id ";
		  $where = "AND d.domain IS NULL ";
	  }

	  $src_id = intval( $_POST['source_blog'] );
	  $blog_privacy=$_POST['wds_group_privacy'];

	  $domain = sanitize_user( str_replace( '/', '', $blog[ 'domain' ] ) );
	  $domain=str_replace(".","",$domain);
	  $email = sanitize_email( $user_email );
	  $title = $_POST['group-name'];

	  if ( !$src_id) {
		  $msg = __('Select a source blog.');
	  } elseif ( empty($domain) || empty($email)) {
		  $msg = __('Missing blog address or email address.');
	  } elseif( !is_email( $email ) ) {
		  $msg = __('Invalid email address');
	  } else {
		  if( constant('VHOST') == 'yes' ) {
			  $newdomain = $domain.".".$current_site->domain;
			  $path = $base;
		  } else {
			  $newdomain = $current_site->domain;
			  $path = $base.$domain.'/';
		  }

		  $password = 'N/A';
		  $user_id = email_exists($email);
		  if( !$user_id ) {
			  $password = generate_random_password();
			  $user_id = wpmu_create_user( $domain, $password, $email );
			  if(false == $user_id) {
				  $msg = __('There was an error creating the user');
			  } else {
				  wp_new_user_notification($user_id, $password);
			  }
		  }
		  $wpdb->hide_errors();
		  $new_id = wpmu_create_blog($newdomain, $path, $title, $user_id , array( "public" => 1 ), $current_site->id);
		  $id=$new_id;
		  $wpdb->show_errors();
		  if( !is_wp_error($id) ) { //if it dont already exists then move over everything
			  groups_update_groupmeta( $group_id, 'wds_bp_group_site_id', $id);
			  /*if( get_user_option( $user_id, 'primary_blog' ) == 1 )
				  update_user_option( $user_id, 'primary_blog', $id, true );*/
			  $content_mail = sprintf( __( "New site created by %1s\n\nAddress: http://%2s\nName: %3s"), $current_user->user_login , $newdomain.$path, stripslashes( $title ) );
			  wp_mail( get_site_option('admin_email'),  sprintf(__('[%s] New Blog Created'), $current_site->site_name), $content_mail, 'From: "Site Admin" <' . get_site_option( 'admin_email' ) . '>' );
			  wpmu_welcome_notification( $id, $user_id, $password, $title, array( "public" => 1 ) );
			  $msg = __('Site Created');
			  // now copy
			  $blogtables = $wpdb->base_prefix . $src_id . "_";
			  $newtables = $wpdb->base_prefix . $new_id . "_";
			  $query = "SHOW TABLES LIKE '{$blogtables}%'";
  //				var_dump($query);
			  $tables = $wpdb->get_results($query, ARRAY_A);
			  if($tables) {
				  reset($tables);
				  $create = array();
				  $data = array();
				  $len = strlen($blogtables);
				  $create_col = 'Create Table';
				  // add std wp tables to this array
				  $wptables = array($blogtables . 'links', $blogtables . 'postmeta', $blogtables . 'posts',
					  $blogtables . 'terms', $blogtables . 'term_taxonomy', $blogtables . 'term_relationships');
				  for($i = 0;$i < count($tables);$i++) {
					  $table = current($tables[$i]);
					  if(substr($table,0,$len) == $blogtables) {
						  if(!($table == $blogtables . 'options' || $table == $blogtables . 'comments')) {
							  $create[$table] = $wpdb->get_row("SHOW CREATE TABLE {$table}");
							  $data[$table] = $wpdb->get_results("SELECT * FROM {$table}", ARRAY_A);
						  }
					  }
				  }
  //					var_dump($create);
				  if($data) {
					  switch_to_blog($src_id);
					  $src_url = get_option('siteurl');
					  $option_query = "SELECT option_name, option_value FROM {$wpdb->options}";
					  restore_current_blog();
					  $new_url = get_blog_option($new_id, 'siteurl');
					  foreach($data as $k => $v) {
						  $table = str_replace($blogtables, $newtables, $k);
						  if(in_array($k, $wptables)) { // drop new blog table
							  $query = "DROP TABLE IF EXISTS {$table}";
							  $wpdb->query($query);
						  }
						  $key = (array) $create[$k];
						  $query = str_replace($blogtables, $newtables, $key[$create_col]);
						  $wpdb->query($query);
						  $is_post = ($k == $blogtables . 'posts');
						  if($v) {
							  foreach($v as $row) {
								  if($is_post) {
									  $row['guid'] = str_replace($src_url,$new_url,$row['guid']);
									  $row['post_content'] = str_replace($src_url,$new_url,$row['post_content']);
									  $row['post_author'] = $user_id;
								  }
								  $wpdb->insert($table, $row);
							  }
						  }
					  }
					  // copy media
					  $cp_base = ABSPATH . '/' . UPLOADBLOGSDIR . '/';
					  $cp_cmd = 'cp -r ' . $cp_base . $src_id . ' ' . $cp_base . $new_id;
					  exec($cp_cmd);
					  // update options
					  $skip_options = array('admin_email','blogname','blogdescription','cron','db_version','doing_cron',
						  'fileupload_url','home','nonce_salt','random_seed','rewrite_rules','secret','siteurl','upload_path',
						  'upload_url_path', "{$wpdb->base_prefix}{$src_id}_user_roles");
					  $options = $wpdb->get_results($option_query);
					  //new blog stuff
					  if($options) {
						  switch_to_blog($new_id);
						  update_option( "wds_bp_group_id", $group_id );
						  foreach($options as $o) {
  //								var_dump($o);
							  if(!in_array($o->option_name,$skip_options) && substr($o->option_name,0,6) != '_trans') {
								  update_option($o->option_name, maybe_unserialize($o->option_value));
							  }
						  }
						  if(version_compare( $GLOBALS['wpmu_version'], '2.8', '>')) {
							  set_transient('rewrite_rules', '');
						  } else {
							  update_option('rewrite_rules', '');
						  }
						  //update privacy
						  if($blog_privacy=="private"){
							  update_option('blog_public', '-2');
						  }
						  //creaTE UPLOAD DOCS PAGE
						  $args = array (
							  'post_title'	=>	'Upload Documents',
							  'post_content'	=>	'[lab-docs]',
							  'post_status'	=>	'publish',
							  'post_author'	=>	$user_ID,
							  'post_type'		=>	'page'
						  );
						  wp_insert_post( $args );

						  restore_current_blog();
						  $msg = __('Blog Copied');
					  }
				  }
			  }
		  } else {
			  $msg = $id->get_error_message();
		  }
	  }
	}
}

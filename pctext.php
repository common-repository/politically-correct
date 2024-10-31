<?php
	/*
	 Plugin Name: Politically Correct
	 Plugin URI:  http://www.bbqiguana.com/tag/wordpress-plugins/
	 Version: 0.2
	 Description: Swaps the occasional "politically incorrect" term with a more socially acceptable replacement.
	 Author: Randall Hunt
	 Author URI: http://www.bbqiguana.com/
	 */

function pctext_run_as_action ($post_id) {

	$when = get_option('pctext_when');
	if ('All'!=$when) {
		$what = get_option('pctext_what');
		$tags = get_post_custom_values($what);
		if('Exclude'==$when)
			if(count($tags)>0) return $content;
		else
			if(count($tags)==0) return $content;
	}
	if ($c=get_option('pctext_where')) {
		$catfound = false;
		$catlist = get_the_category();
		foreach ($catlist as $category) {
			if (in_array($category->cat_ID, explode(',', $c)))
				$catfound = true;
		}
		if (!$catfound) return $content;
	}

	$content = pctext_filter($content);
}

function pctext_run_as_filter ($content) {

	$when = get_option('pctext_when');
	if ('All'!=$when) {
		$what = get_option('pctext_what');
		$tags = get_post_custom_values($what);
		if('Exclude'==$when)
			if(count($tags)>0) return $content;
		else
			if(count($tags)==0) return $content;
	}
	if ($c=get_option('pctext_where')) {
		$catfound = false;
		$catlist = get_the_category();
		foreach ($catlist as $category) {
			if (in_array($category->cat_ID, explode(',', $c)))
				$catfound = true;
		}
		if (!$catfound) return $content;
	}

	return pctext_filter($content);
}

function pctext_filter ($content) {

	//races
	$content = preg_replace('/\b(chinese|japanese|korean|thai) (man|men|woman|women|kid|child|boy|boys|girl|girls|person|people|culture|pride|music|neighborhood)\b/i', 'Asian $2', $content);
	$content = preg_replace('/\b(white) (man|men|woman|women|kid|child|boy|boys|girl|girls|person|people|culture|pride|music|neighborhood)\b/i', 'caucasian $2', $content);
	$content = preg_replace('/\b(black|negro|nigger|colored) (man|men|woman|women|kid|child|boy|boys|girl|girls|person|people|culture|pride|music|neighborhood)\b/i', 'African-American $2', $content);//	$content = preg_replace('/\bold\b/i', 'elderly', $content);
	$content = preg_replace('/\b(honke?y|honkie|cracker|white?y|whitie)(s?)\b/i', 'caucasian$2', $content);
	$content = preg_replace('/\b(negroe?|nigger)(s?)\b/i', 'African-American$2', $content);

	//swears
	$content = preg_replace('/\b(fuck|screw)\b/i', 'have sexual relations', $content);
	$content = preg_replace('/\b(shit)\b/i', 'defacate', $content);
	$content = preg_replace('/\b(ass)\b/i', 'rear end', $content);
	$content = preg_replace('/\b(asshole)\b/i', 'meanie', $content);
	$content = preg_replace('/\bgod ?damn\b/i', 'gosh darn', $content);
	$content = preg_replace('/\b(blow ?job|rim ?job|hand ?job)(s?)\b/i', 'sexual favor$2', $content);

	//other euphemisms
	$content = preg_replace('/\bgay\b/i', 'homosexual', $content);
	$content = preg_replace('/\bretarded\b/i', 'mentally challenged', $content);
	$content = preg_replace('/\b(handicapped)\b/i', 'disabled', $content);
	$content = preg_replace('/\b(blind)\b/i', 'vision-imparied', $content);
	$content = preg_replace('/\b(deaf)\b/i', 'hearing-impaired', $content);
	$content = preg_replace('/\bold\b/i', 'elderly', $content);
	$content = preg_replace('/\bshell[ -]shock\b/i', 'post-traumatic stress disorder (PTSD)', $content);

//	$content = preg_replace('/\b\b/i', '', $content);
//	$content = preg_replace('/\b\b/i', '', $content);
	return $content;
}

//function pctext_gettagname () {
//	global $foia_default_tagname;
//	$tag = get_option('foia_tagname');
//	return ($tag ? $tag : $foia_default_tagname);
//}

function pctext_getauthors() {
	global $wpdb;
	$query = "SELECT $wpdb->users.* FROM $wpdb->users ORDER BY display_name;";
	$authors = $wpdb->get_results($query);
	return $authors;
}

function pctext_menu () {
	if ( function_exists('add_options_page') ) {
		add_options_page('Political Correction', 'Politically Correct', 8, 'pctext', 'pctext_options');
	}
}
	
function pctext_init () {
	register_setting('pctext', 'pctext_which');
	register_setting('pctext', 'pctext_when');
	register_setting('pctext', 'pctext_where');
	register_setting('pctext', 'pctext_who');
	register_setting('pctext', 'pctext_what');
	register_setting('pctext', 'pctext_cats');
	register_setting('pctext', 'pctext_auths');
}
	
function pctext_install () {
	//add default options
	$which = get_option('pctext_which');
	$when  = get_option('pctext_when');
	$where = get_option('pctext_where');
	$who   = get_option('pctext_who');
	$what  = get_option('pctext_what');
	
	if(!$which) update_option('pctext_which', 'filter');
	if(!$when)  update_option('pctext_when',  'All');
	if(!$where) update_option('pctext_where', '');
	if(!$who)   update_option('pctext_who',   '');
	if(!$what)  update_option('pctext_what', 'pctext');
}
	
function pctext_options () {
	$_cats  = '';
	$_auths = '';
	echo '<div class="wrap">';
	echo '<h2>Freedom of Information!</h2>';
	if ( ($_POST['action']=='update') ) {
		//check_admin_referer('pctext_update-action');
		update_option('pctext_which', $_POST['pctext_which']);
		update_option('pctext_when',  $_POST['pctext_when']);
		update_option('pctext_what',  $_POST['pctext_what']);
		update_option('pctext_where',  ($_POST['pctext_where'] ) ? implode(',', $_POST['pctext_cats'] ) : '');
		update_option('pctext_who',    ($_POST['pctext_who']   ) ? implode(',', $_POST['pctext_auths']) : '');
		echo '<div id="message" class="updated fade" style="background-color:rgb(255,251,204);"><p>Settings updated.</p></div>';
	}
	$which = get_option('pctext_which');
	$when  = get_option('pctext_when');
	$where = get_option('pctext_where');
	$who   = get_option('pctext_who');
	$what  = get_option('pctext_what');

	echo '<big>Options</big>';
	echo '<form name="pctext-options" method="post" action="">';
	settings_fields('pctext');
	echo '<table class="form-table"><tbody>';
	echo '<tr valign="top"><th scope="row"><strong>How should the content be filtered?</strong></th>';
	echo '<td><label for="myradio1"><input id="myradio1" type="radio" name="pctext_which" value="action" '.($which!='filter'?'checked="checked"':'').'/> Replace text in post body when saving</label><br/>';
	echo '<label for="myradio2"><input id="myradio2" type="radio" name="pctext_which" value="filter" '.($which=='filter'?'checked="checked"':'').' /> Filter outgoing text before display</label><br/>';
	echo '<p>By default, posts are not edited.  Authors can write what they want and it will be saved as written, but the filter will run before the content is displayed. If this setting is changed to "Replace text", content will be edited upon save, preventing the filtered terms from ever being saved at all.</p>';
	echo '</td></tr>';

//	echo '<tr valign="top"><th scope="row"><strong>When should the plugin run:</strong></th><td>';
//	echo '<label for="myradio1"><input type="radio" id="myradio1" name="" value="" '.().' /> During
//	echo '</td></tr>';

//	echo '<tr valign="top"><th scope="row"><strong></strong></th><td>';
//	echo '</td></tr>';

//	echo '<tr valign="top"><th scope="row"><strong></strong></th><td>';
//	echo '</td></tr>';

//	echo '<tr valign="top"><th scope="row"><strong></strong></th><td>';
//	echo '</td></tr>';


	echo '<tr valign="top"><th scope="row"><strong>Which content should be filtered?:</th>';
	echo '<td><label for="myradio3"><input id="myradio3" type="radio" name="pctext_when" value="All" ' .($when!='Exclude'&&$when!='Include'?'checked="checked"':'') . ' /> All content should be filtered</label><br/>';
	echo '<label for="myradio4"><input id="myradio4" type="radio" name="pctext_when" value="Exclude" '.($when=='Exclude'?'checked="checked"':'').' /> Exclude posts with the custom tag:</label><br/> ';
	echo '<label for="myradio5"><input id="myradio5" type="radio" name="pctext_when" value="Include" '.($when=='Include'?'checked="checked"':'').' /> Only filter posts marked with the custom tag:</label><br/> ';
	echo 'Custom tag name: <input type="text" size="20" name="pctext_what" value="'.$what.'" /><br/>';
	echo '<p>By default, this plugin will filter all posts.  If that is too heavy-handed for you, however, you can choose to apply it based on a custom tag.</p></td></tr>';
	//	echo '<tr valign="top"><th scope="row">This site name:</th><td>' . get_option('siteurl') . '</td></tr>';

	echo '<tr align="top"><th scope="row"><strong>Apply to these categories:</strong></th>';
	echo '<td><label for="myradio6"><input type="radio" id="myradio6" name="pctext_where" value="" '.($where==''?'checked="checked"':'').' /> All categories</label><br/>';
	echo '<label for="myradio7"><input type="radio" id="myradio7" name="pctext_where" value="Y" '.($where!=''?'checked="checked"':'').' /> Selected categories</label><br/>';

	$_cats = explode(',', get_option('pctext_where'));
	$chcount = 0;
	$cats = get_categories();
	foreach ($cats as $cat) {
		$chcount++;
		echo '<label for="mycheck'.$chcount.'"><input type="checkbox" id="mycheck'.$chcount.'" name="pctext_cats[]" value="' . $cat->cat_ID . '" '.(in_array($cat->cat_ID, $_cats)?'checked="checked"':'').' /> ' . $cat->cat_name . '</label><br/>';
	}
	echo '</td></tr>';
	echo '<tr align="top"><th scope="row"><strong>Apply to these authors:</strong></th>';
	echo '<td><label for="myradio8"><input type="radio" id="myradio8" name="pctext_who" value="" '.($who==''?'checked="checked"':'').' /> All authors</label><br/>';
	echo '<label for="myradio9"><input type="radio" id="myradio9" name="pctext_who" value="Y" '.($who!=''?'checked="checked"':'').' /> selected authors</label><br/>';

	$_auths = explode(',', get_option('pctext_who'));
	$auths = pctext_getauthors();
	foreach ($auths as $auth) {
		$chcount++;
		echo '<label for="mycheck'.$chcount.'"><input type="checkbox" id="mycheck'.$chcount.'" name="pctext_auths[]" value="' . $auth->ID . '" '.(in_array($auth->ID, $_auths)?'checked="checked"':'').'/> ' . $auth->display_name . '</label><br/>';
	}
	echo '</td></tr>';

	echo '</tbody></table>';
	echo '<div class="submit">';
	//echo '<input type="hidden" name="pctext_update" value="action" />';
	echo '<input type="submit" name="submit" class="button-primary" value="' . __('Save Changes') . '" />';
	echo '</div>';
	echo '</form>';
	echo '<div class="wrap">';
	echo '<big>Donate</big>';
	echo '<p>If you like this plugin consider donating a small amount to the author using PayPal to support further plugin development.</p>';
	echo '<div align="center"><form name="_xclick" action="https://www.paypal.com/us/cgi-bin/webscr" method="post"><input type="hidden" name="cmd" value="_xclick"><input type="hidden" name="business" value="bbqiguana@gmail.com"><input type="hidden" name="item_name" value="Donations for WP-Externimage Plugin"><input type="hidden" name="currency_code" value="USD"><input type="image" src="http://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="Make payments with PayPal - it\'s fast, free and secure!"></form></div>';
	echo '<p>If you think donating money is somehow impersonal you could also choose items from my <a href="http://www.amazon.com/registry/wishlist/18LMHOMRM49P8/ref=cm_wl_act_vv">Amazon.com wishlist</a>.</p>';
	echo '</div>';
	echo '';
	echo '</div>';
}
	
if ( is_admin() ) { // admin actions
	add_action('admin_menu', 'pctext_menu');
	add_action('admin_init', 'pctext_init');
}

register_activation_hook(__FILE__, 'pctext_install');
add_filter ('the_content', 'pctext_run_as_filter');

?>
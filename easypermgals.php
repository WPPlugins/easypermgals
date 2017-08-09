<?php
	/*
	Plugin Name: EasyPermGals
	Plugin URI: http://www.mores.cc/easypermgals/
	Description: A plugin that automatically generates an image gallery based on a post's or Page's attachments<br>Based on Walter Vos' <a href="http://www.waltervos.com/downloads/wordpress-plugins/easypermgals">EasyPermGals</a>
	Author: Daniel Mores 
	Version: 1.3
	Author URI: http://www.mores.cc/
	*/
	
	function easypermgals() {
		global $wpdb;
		global $post;
		
		$easypermgals_style_rules = get_option('easypermgals_style_rules');
		$easypermgals_embedding_before = get_option('easypermgals_embedding_before');
		$easypermgals_embedding_after = get_option('easypermgals_embedding_after');
		$easypermgals_seperator = get_option('easypermgals_seperator');
		
		$thisgallery = $wpdb->get_results( "SELECT ID FROM " . $wpdb->posts . "
			WHERE post_parent = '" . $post->ID . "'
			AND `post_type` = 'attachment'
			AND `post_mime_type` LIKE 'imag%'" );
		$this_easygal = "$easypermgals_embedding_before <div class='easypermgals' style='$easypermgals_style_rules'>";
		if (get_option('easypermgals_lightbox') == 'yes') {
				$lightbox = " rel=\"lightbox[$post->ID]\"";
			}
		foreach ( $thisgallery as $picture ) {
		
			/* thanks elfin (http://wordpress.org/support/profile/3085) for
				this bit ... it fixes problems with the thumbnails that occured
				when 2.5 was released and thumbnails were named differently */
		
			$img_url= wp_get_attachment_thumb_url($picture->ID);
			$chkimg=wp_get_attachment_url($picture->ID);
			if($img_url==$chkimg){
				$img_url = preg_replace('!(\.[^.]+)?$!', __('.thumbnail') . '$1', $img_url, 1);
			}else{
				$img_url = wp_get_attachment_thumb_url($picture->ID);
			}
		
			$this_easygal .= "<a href=\"" . wp_get_attachment_url($picture->ID) . "\"$lightbox>";
			$this_easygal .= "<img src=\"$img_url\" />";
			$this_easygal .= "</a>$easypermgals_seperator\n";

		}
		$this_easygal .= '</div>'. $easypermgals_embedding_after;
		if (sizeof($thisgallery)>0) {
			return $this_easygal;
		} else {
			return null;
		}
	}
	
	add_filter( 'the_content', 'easypermgals_replace_marker' );
	
	function easypermgals_replace_marker($content) {
		$easypermgals_permanent = get_option('easypermgals_permanent');

		if ($easypermgals_permanent) {
			$easygal = easypermgals();
			
			if(!preg_match('[easypermgals]', $content)) {
				$content .=$easygal;
				return $content;
			}
			else {
				return str_replace('[easypermgals]', $easygal, $content);
			}	
		} else {
			if(!preg_match('[easypermgals]', $content)) {
				return $content;
			}
			else {
				$easygal = easypermgals();
				return str_replace('[easypermgals]', $easygal, $content);
			}	
		}
	}
	
	add_action('admin_menu', 'add_easypermgals_options');
	function add_easypermgals_options() {
		add_options_page(__('EasyPermGals options'), __('EasyPermGals'), 5, basename(__FILE__), 'easypermgals_options');
	}
	
	function easypermgals_options() {
		if (isset($_POST['easypermgals_updated'])) {
			update_option('easypermgals_permanent', $_POST['easypermgals_permanent']);
			update_option('easypermgals_lightbox', $_POST['easypermgals_lightbox']);
			update_option('easypermgals_style_rules', $_POST['easypermgals_style_rules']);
			update_option('easypermgals_embedding_before', $_POST['easypermgals_embedding_before']);
			update_option('easypermgals_embedding_after', $_POST['easypermgals_embedding_after']);
			update_option('easypermgals_seperator', $_POST['easypermgals_seperator']);
			$updated = true;
		}
		
		if(get_option('easypermgals_permanent')) {
			$easypermgals_permanent = get_option('easypermgals_permanent');
		} else {
			add_option('easypermgals_permanent', "no", "Should EasyPermGals automatically show all thumbnails?", "yes");
		}

		if(get_option('easypermgals_lightbox')) {
			$easypermgals_lightbox = get_option('easypermgals_lightbox');
		} else {
			add_option('easypermgals_lightbox', "no", "Should EasyPermGals insert lightbox code, yes or no?", "yes");
		}
		
		if(get_option('easypermgals_style_rules')) {
			$easypermgals_style_rules = get_option('easypermgals_style_rules');
		} else {
			add_option('easypermgals_style_rules', "text-align:center;", "Style rules for EasyPermGals' galleries", "yes");
		}
		
		if(get_option('easypermgals_embedding_before')) {
			$easypermgals_embedding_before = get_option('easypermgals_embedding_before');
		} else {
			add_option('easypermgals_embedding_before', "<p>", "Code inserted before the EasyPermGals DIV' galleries", "yes");
		}

		if(get_option('easypermgals_embedding_after')) {
			$easypermgals_embedding_after = get_option('easypermgals_embedding_after');
		} else {
			add_option('easypermgals_embedding_after', "</p><p> </p>", "Code inserted after the EasyPermGals DIV' galleries", "yes");
		}
		
		if(get_option('easypermgals_seperator')) {
			$easypermgals_seperator = get_option('easypermgals_seperator');
		} else {
			add_option('easypermgals_seperator', " ", "Separate thumbnails with' galleries", "yes");
		}

		
		if ($updated) {
			echo '<div class="updated"><p><strong>Options saved.</strong></p></div>';
		}
		?>
		<div class="wrap" id="easypermgals_options">
			<h2>EasyPermGals options</h2>
			<form name="easypermgals_form" method="post" action="<?php echo $_SERVER['../easypermgals/REQUEST_URI']; ?>">

			<input type="hidden" id="easypermgals_updated" name="easypermgals_updated" value="yes" />
			
			<table class="form-table">
			<tbody>
			<tr valign="top">
				<th scope="row">Automatic/Manual</th>
				<td>
					<input type="checkbox" name="easypermgals_permanent" value="yes" <?php if (get_option('easypermgals_permanent') == 'yes') echo "checked";?>/> Full-Auto mode<br>
					In Full-Auto mode, EasyPermGals will add thumbnails to all posts and Pages automatically.<br>
					If you deactivate this function, you will need to manually tell EasyPermGals to show thumbnails by inserting [easypermgals] in your post or Page
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Lightbox</th>
				<td>
					<input type="checkbox" name="easypermgals_lightbox" value="yes" <?php if (get_option('easypermgals_lightbox') == 'yes') echo "checked";?>/> Use Lightbox feature if installed on this System
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Style Definitions</th>
				<td>
					<input type="text" name="easypermgals_style_rules" id="easypermgals_style_rules" value="<? echo get_option('easypermgals_style_rules'); ?>" style="width:95%;"><br>
					In this you can write style rules for the EasyPermGals div. Just the style rules are enough, you don't need to write things like style=&quot;text-align:center;&quot;, just text-align:center will be enough
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Embedding-code *before* EasyPermGals DIV</th>
				<td>
					<textarea rows="1" cols="50" id="easypermgals_embedding_before" name="easypermgals_embedding_before"><? echo get_option('easypermgals_embedding_before'); ?></textarea><br>
					You may add a headline, you can add a &lt;p&gt; or you can leave it blank if it works out-of-the-box
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Embedding-code *after* EasyPermGals DIV</th>
				<td>
					<textarea rows="1" cols="50" id="easypermgals_embedding_after" name="easypermgals_embedding_after"><? echo get_option('easypermgals_embedding_after'); ?></textarea><br>
					You may add a subline, you can add a &lt;/p&gt; or just leave it blank.
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Separate Images with</th>
				<td>
					<input type="text" name="easypermgals_seperator" id="easypermgals_seperator" value="<? echo get_option('easypermgals_seperator'); ?>" style="width:50px"><br>
					For more than one space you will need to use &amp;nbsp;
				</td>
			</tr>
			</tbody>
			</table>
			
				<p class="submit">
					<input type="submit" name="easypermgals_update" value="Update options &raquo;" />
				</p>
			</form>
		</div>
        <?php
	}
?>
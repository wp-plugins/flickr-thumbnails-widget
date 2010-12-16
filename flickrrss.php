<?php
/*
Plugin Name: Flickr Thumbnails Widget
Description: Displays thumbnails from Flickr based on the user/group/tag you provide.
Author: James Carppe
Version: 1.3
Author URI: http://www.karrderized.com/old-stuff/flickr-thumbnail-widget/
*/

// Changelog:
// v1.3: added column support (thanks debashish!)
// v1.2: .. too long ago to remember ;)

function widget_flickrthumbs_init() {

	if ( !function_exists('register_sidebar_widget') )
		return;

	function widget_flickrthumbs($args) {
		
		extract($args);

		$options = get_option('widget_flickrthumbs');
		$title = $options['title'];
		$searchtext = $options['searchtext'];
		$type = $options['type'];
		$numpics = $options['numpics'];
		$numcols = $options['numcols'];
		
		echo $before_widget . $before_title . $title . $after_title;
		if ($type == "group") {
			$feedurl = "http://www.flickr.com/groups/" . $searchtext . "/pool/feed/?format=rss_200";
		} else if ($type == "tag") {
			$feedurl = "http://www.flickr.com/services/feeds/photos_public.gne?tags=" . $searchtext;
		} else {
			$feedurl = "http://www.flickr.com/services/feeds/photos_public.gne?id=" . $searchtext . "&format=rss_200";
		}
		require_once (ABSPATH . WPINC . '/rss-functions.php');
		$rss = fetch_rss($feedurl);
		define('MAGPIE_CACHE_AGE', 300);

		if ( $rss ) {
			$count = 0;			
			echo "<table border=0 width=100%><tr>";
			foreach ($rss->items as $item ) {
				if ($count < $numpics) {
				    $r = $count % $numcols;				    
					if($r == 0) {
						echo "<tr>";
					}					
					preg_match('<img src="([^"]*)" [^/]*/>', $item['description'],$imgUrlMatches);
					$imgurl = $imgUrlMatches[1];
					$imgurl = str_replace("m.jpg", "s.jpg", $imgurl);
					$thetitle = $item[title];
					$thelink = $item[link];
					echo "<td><a href=\"$thelink\" title=\"$thetitle\"><img width=\"75px\" height=\"75px\" src=\"$imgurl\" alt=\"$thetitle\" /></a></td>";
					if($r == ($numcols - 1)) {
						echo "</tr>";
					}
					$count++;										
				}
			}
			echo "</table>";
		}
		else {
		    echo "An error occured!  " .      
		        "<br>Error Message: "   . magpie_error();
		}
		
		echo $after_widget;
	}

	function widget_flickrthumbs_control() {

		$options = get_option('widget_flickrthumbs');
		if ( !is_array($options) )
			$options = array('title'=>'Flickr', 'searchtext'=>'', 'type'=>'user', 'numpics'=>'6', 'numcols'=>'3');
		if ( $_POST['flickrthumbs-submit'] ) {

			$options['title'] = strip_tags(stripslashes($_POST['flickrthumbs-title']));
			$options['searchtext'] = strip_tags(stripslashes($_POST['flickrthumbs-searchtext']));			
			$options['type'] = strip_tags(stripslashes($_POST['flickrthumbs-type']));
			$options['numpics'] = strip_tags(stripslashes($_POST['flickrthumbs-numpics']));			
			$options['numcols'] = strip_tags(stripslashes($_POST['flickrthumbs-numcols']));	
			update_option('widget_flickrthumbs', $options);
		}

		$title = htmlspecialchars($options['title'], ENT_QUOTES);
		$searchtext = htmlspecialchars($options['searchtext'], ENT_QUOTES);
		$type = htmlspecialchars($options['type'], ENT_QUOTES);
		$numpics = htmlspecialchars($options['numpics'], ENT_QUOTES);
		$numcols = htmlspecialchars($options['numcols'], ENT_QUOTES);
		
		echo '<p style="text-align:right;"><label for="flickrthumbs-title">Title: <input style="width: 200px;" id="flickrthumbs-title" name="flickrthumbs-title" type="text" value="'.$title.'" /></label></p>';
		echo '<p style="text-align:right;"><label for="flickrthumbs-searchtext">Search Text: <input style="width: 200px;" id="flickrthumbs-searchtext" name="flickrthumbs-searchtext" type="text" value="'.$searchtext.'" /></label></p>';
		echo '<p style="text-align:right;"><label for="flickrthumbs">Type: <input type="radio" name="flickrthumbs-type" value="group"';
		if($type == 'group') {echo 'checked';}
		echo '>group</input> ';
		echo '<input type="radio" name="flickrthumbs-type" value="tag"';
		if($type == 'tag') {echo 'checked';}
		echo '>tag</input> ';
		echo '<input type="radio" name="flickrthumbs-type" value="user"';
		if($type == 'user') {echo 'checked';}
		echo '>user</input></label></p>';
		echo '<p style="text-align:right;"><label for="flickrthumbs-numpics">Number of images to show: <input style="width: 50px;" id="flickrthumbs-numpics" name="flickrthumbs-numpics" type="text" value="'.$numpics.'" /></label></p>';
		echo '<p style="text-align:right;"><label for="flickrthumbs-numpics">Number of columns: <input style="width: 50px;" id="flickrthumbs-numcols" name="flickrthumbs-numcols" type="text" value="'.$numcols.'" /></label></p>';
		echo '<input type="hidden" id="flickrthumbs-submit" name="flickrthumbs-submit" value="1" />';
	}
	
	register_sidebar_widget('Flickr Thumbnails', 'widget_flickrthumbs');

	register_widget_control('Flickr Thumbnails', 'widget_flickrthumbs_control', 300, 150);
}

add_action('plugins_loaded', 'widget_flickrthumbs_init');

?>
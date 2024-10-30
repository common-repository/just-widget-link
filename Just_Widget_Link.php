<?php
/*
Plugin Name: Just_Widget_Link
Plugin URI: http://justfly.idv.tw/2009/06/06/Just_652.html
Description: Manager Link by Widget in your sidebar
Version: 1.1.2
Author: Justfly. Chang
Author URI: http://justfly.idv.tw/
*/

function widget_justlink($args, $widget_args = 1) {
	extract( $args, EXTR_SKIP );
	if ( is_numeric($widget_args) )
		$widget_args = array( 'number' => $widget_args );
	$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
	extract( $widget_args, EXTR_SKIP );

	$options = get_option('widget_justlink');
	if ( !isset($options[$number]) )
		return;

	$title = $options[$number]['title'];
	$category = $options[$number]['category'];
	$linknum = $options[$number]['linknum'];
	$order = $options[$number]['order'];
	
	echo $before_widget . $before_title . $title . $after_title;?>
	<ul>
	<?php get_links($category, '<li>', '</li>',0,0, $order, 0, 0, $linknum, 0);?>
	</ul>
   	<?php echo $after_widget;
}

function widget_justlink_control($widget_args) {
	global $wp_registered_widgets;
	static $updated = false;

	if ( is_numeric($widget_args) )
		$widget_args = array( 'number' => $widget_args );
	$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
	extract( $widget_args, EXTR_SKIP );

	$options = get_option('widget_justlink');
	if ( !is_array($options) )
		$options = array();

	if ( !$updated && !empty($_POST['sidebar']) ) {
		$sidebar = (string) $_POST['sidebar'];

		$sidebars_widgets = wp_get_sidebars_widgets();
		if ( isset($sidebars_widgets[$sidebar]) )
			$this_sidebar =& $sidebars_widgets[$sidebar];
		else
			$this_sidebar = array();

		foreach ( $this_sidebar as $_widget_id ) {
			if ( 'widget_justlink' == $wp_registered_widgets[$_widget_id]['callback'] && isset($wp_registered_widgets[$_widget_id]['params'][0]['number']) ) {
				$widget_number = $wp_registered_widgets[$_widget_id]['params'][0]['number'];
				if ( !in_array( "justlink-$widget_number", $_POST['widget-id'] ) ) unset($options[$widget_number]);
			}
		}

		foreach ( (array) $_POST['widget-justlink'] as $widget_number => $widget_text ) {
			$title = strip_tags(stripslashes($widget_text['title']));
			$category = strip_tags(stripslashes(implode(",", $widget_text['category'])));
			$linknum = strip_tags(stripslashes($widget_text['linknum']));
			$order = strip_tags(stripslashes($widget_text['order']));
			$options[$widget_number] = compact( 'title', 'category', 'linknum' ,'order');
		}

		update_option('widget_justlink', $options);
		$updated = true;
	}

	if ( -1 == $number ) {
		$title = '';
		$category = '';
		$linknum = '';
		$order = 'id';
		$number = '%i%';
	} else {
		$title = attribute_escape($options[$number]['title']);
		$category = attribute_escape($options[$number]['category']);
		$linknum = attribute_escape($options[$number]['linknum']);
		$order = attribute_escape($options[$number]['order']);
	}
?>
		<dl>
        	<dt>TITLE:</dt>
			<dd><input type="text" id="justlink-title-<?php echo $number; ?>" name="widget-justlink[<?php echo $number; ?>][title]" value="<?php echo $title; ?>" /></dd>
           	<?php $categories = get_terms( 'link_category', array( 'orderby' => 'count', 'order' => 'DESC', 'number' => 10, 'hierarchical' => false ) );
				   //$oldcategory= explode(',', $category ); 
				   
			 foreach ( (array) $categories as $categorylink ) { ?>		
                <label class="just_link_selectit">
                <?php ?> 
                <input type="checkbox" <?php if (stristr($category,$categorylink->term_id)) echo 'checked="checked"'; ?> value="<?php echo (int) $categorylink->term_id; ?>" name="widget-justlink[<?php echo $number; ?>][category][]" />
                    <?php echo wp_specialchars( apply_filters( 'the_category', $categorylink->name ) ); ?>
                </label>
            <?php } ?>            
			<dt>Link MAX(-1 for all):</dt>
			<dd><input type="text" class="justinput" id="justlink-linknum-<?php echo $number; ?>" name="widget-justlink[<?php echo $number; ?>][linknum]" value="<?php echo $linknum; ?>"/></dd>
             <?php $orders = array (1 => 'id','url','name','target','category','description','owner','rating','updated','rel','notes','rss','length','rand'); ?>
             <dt>Order By:</dt>
             <dd><select name="widget-justlink[<?php echo $number; ?>][order]">;
             <?php foreach ($orders as $key => $value) { ?>
  				<option <?php if ($order==$value) echo 'selected="selected"';?> value=<?php echo $value ?>><?php echo $value ?></option>;
 			<?php } ?>
			</select></dd>
			<dd><input type="hidden" id="justlink-submit-<?php echo $number; ?>" name="justlink-submit-<?php echo $number; ?>" value="1" /></dd>
		</dl>
<?php
}

function widget_justlink_register() {

	// Check for the required API functions
	if ( !function_exists('wp_register_sidebar_widget') || !function_exists('wp_register_widget_control') )
		return;

	if ( !$options = get_option('widget_justlink') )
		$options = array();
	$widget_ops = array('classname' => 'widget_justlink', 'description' => __('Manager Links by better way'));
	$control_ops = array('width' => 300, 'height' => 350, 'id_base' => 'justlink');
	$name = __('Just Link');

	$id = false;
	foreach ( array_keys($options) as $o ) {
		// Old widgets can have null values for some reason
		if ( !isset($options[$o]['title']) || !isset($options[$o]['category']) )
			continue;
		$id = "justlink-$o"; // Never never never translate an id
		wp_register_sidebar_widget($id, $name, 'widget_justlink', $widget_ops, array( 'number' => $o ));
		wp_register_widget_control($id, $name, 'widget_justlink_control', $control_ops, array( 'number' => $o ));
	}
	
	// If there are none, we register the widget's existance with a generic template
	if ( !$id ) {
		wp_register_sidebar_widget( 'justlink-1', $name, 'widget_justlink', $widget_ops, array( 'number' => -1 ) );
		wp_register_widget_control( 'justlink-1', $name, 'widget_justlink_control', $control_ops, array( 'number' => -1 ) );
	}
	
}

add_action( 'widgets_init', 'widget_justlink_register' );

?>
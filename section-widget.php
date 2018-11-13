<?php

/*
Plugin Name: Section Widget
Plugin URI: http://blogs.ubc.ca/support/plugins/section-widget/
Description: Display arbitrary text or HTML on certain sections of your site.
Author: Godfrey Chan (UBC CTLT), Enej,
Version: 3.2.7
Author URI: http://www.chancancode.com/
*/

include_once('olt-checklist/loader.php');
enqueue_olt_checklist_loader(plugins_url('section-widget/olt-checklist'));

include_once('section-widget-tabbed.php');

add_action('init', 'section_widget_text_init');
/**
 * section_widget_text_init function.
 *
 * @access public
 * @return void
 */
function section_widget_text_init() {

	load_plugin_textdomain( 'section-widget', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

/**
 * Section widget class
 */
/**
 * OLT_Section_Widget class.
 *
 * @extends WP_Widget
 */
class OLT_Section_Widget extends WP_Widget {
	/**
	 * OLT_Section_Widget function.
	 *
	 * @access public
	 * @return void
	 */
	function __construct() {
		$widget_ops = array('classname' => 'section-widget', 'description' => __('Display section-specific content.','section-widget'));
		$control_ops = array('width' => 400);
		parent::__construct( 'section', __( 'Section','section-widget' ), $widget_ops, $control_ops );
	}
	/**
	 * widget function.
	 *
	 * @access public
	 * @param mixed $args
	 * @param mixed $instance
	 * @return void
	 */
	function widget( $args, $instance ) {
		extract($args);

		// $instance['conditions'] had to be renamed to $instance['section_conditions'] to resolve conflict with the Jetpack plugin
		// For backwards compatibility:
		if(!is_array($instance['section_conditions']) && is_array($instance['conditions'])) {
			$instance['section_conditions'] = $instance['conditions'];
		}
		// For backwards compatibility:
		if(!is_array($instance['section_conditions'])) {
			$instance['section_conditions'] = array(
				'special-pages' => $instance['special-pages'],
				'pages'         => $instance['pages'],
				'categories'    => $instance['categories'],
				'tags'          => $instance['tags']
			);
		}

		// olt_checklist_conditions_check is the replacement for $should_display
		if(olt_checklist_conditions_check($instance['section_conditions'])) {
			echo $before_widget;

			if($instance['display-title']){
				echo $before_title;
				echo apply_filters('widget_title', $instance['title']);
				echo $after_title;
			}

			echo apply_filters('widget_text', do_shortcode($instance['body']));
			echo $after_widget;
		}
	}
	/**
	 * update function.
	 *
	 * @access public
	 * @param mixed $new_instance
	 * @param mixed $old_instance
	 * @return void
	 */
	function update( $new_instance, $old_instance ) {
		// Mostly borrowed from text widget
		$instance = $old_instance;

		// For backwards compatibility:
		if(!is_array($instance['section_conditions'])) {
			$instance['section_conditions'] = array(
				'special-pages' => $instance['special-pages'],
				'pages'         => $instance['pages'],
				'categories'    => $instance['categories'],
				'tags'          => $instance['tags']
			);

			unset($instance['special-pages']);
			unset($instance['pages']);
			unset($instance['categories']);
			unset($instance['tags']);
		}

		$instance['title'] = strip_tags($new_instance['title']);
		$instance['display-title'] = (bool) $new_instance['display-title'];

		$instance['section_conditions']['special-pages'] =
			is_array($new_instance['section_conditions']['special-pages'])?
				$new_instance['section_conditions']['special-pages'] : array();

		$instance['section_conditions']['special-pages'] =
			is_array($new_instance['section_conditions']['special-pages'])?
				$new_instance['section_conditions']['special-pages'] : array();

		$instance['section_conditions']['pages'] =
			is_array($new_instance['section_conditions']['pages'])?
				$new_instance['section_conditions']['pages'] : array();

		$instance['section_conditions']['categories'] =
			is_array($new_instance['section_conditions']['categories'])?
				$new_instance['section_conditions']['categories'] : array();

		$instance['section_conditions']['tags'] =
			is_array($new_instance['section_conditions']['tags'])?
				$new_instance['section_conditions']['tags'] : array();

		$instance['body'] = current_user_can('unfiltered_html')?
			$new_instance['body'] : wp_filter_post_kses( $new_instance['body'] );

		return $instance;
	}
	/**
	 * form function.
	 *
	 * @access public
	 * @param mixed $instance
	 * @return void
	 */
	function form( $instance ) {

		// For backwards compatibility:
		if( isset( $instance['section_conditions'] ) && ! is_array( $instance['section_conditions'] ) && isset( $instance['conditions'] ) && is_array( $instance['conditions'] ) ) {
			$instance['section_conditions'] = $instance['conditions'];
		}
		if(is_array($instance) && isset($instance['section_conditions']) && !is_array($instance['section_conditions'])) {
			$instance['section_conditions'] = array(
				'special-pages' => $instance['special-pages'],
				'pages'         => $instance['pages'],
				'categories'    => $instance['categories'],
				'tags'          => $instance['tags']
			);
		}

		// Provide the defaults here
		$instance = wp_parse_args((array) $instance, array(
			'title' => '',
			'display-title' => true,
			'body' => '',
			'section_conditions' => array(
				'special-pages' => array(),
				'pages' => array(),
				'categories' => array(),
				'tags' => array()
			)
		));

		// Make sure second level options are actually arrays
		foreach($instance['section_conditions'] as $i => $v)
			if(!is_array($v))
				$instance['section_conditions'][$i] = array();

		$title = strip_tags($instance['title']);
		$display_title = (bool) $instance['display-title'];
		$special_pages = $instance['section_conditions']['special-pages'];
		$pages = $instance['section_conditions']['pages'];
		$categories = $instance['section_conditions']['categories'];
		$tags = $instance['section_conditions']['tags'];
		$body = format_to_edit($instance['body']);
?>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php _e('Title:','section-widget'); ?></label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title') ); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
			<input id="<?php echo $this->get_field_id('display-title'); ?>" name="<?php echo $this->get_field_name('display-title'); ?>" type="checkbox" <?php checked($display_title);  ?> />
			<label for="<?php echo esc_attr($this->get_field_id('display-title')); ?>"><?php _e('Display title','section-widget'); ?></label>
		</p>
<?php
		olt_checklist_pane(array(
			'id' => $this->get_field_id('section_conditions'),
			'name' => $this->get_field_name('section_conditions'),
			'special-pages' => array('selected' => $special_pages),
			'pages' => array('selected' => $pages),
			'categories' => array('selected' => $categories),
			'tags' => array('selected' => $tags)
		));
?>
		<div class="olt-sw-body">
			<p class="olt-sw-body-help">
				<?php _e('<strong>Formatting Help:</strong> You may use HTML in this widget, and it is probably a good idea to wrap the content in your own <code>&lt;div&gt;</code> to aid styling. Shortcodes are also allowed, but please beware not all of them will function properly on archive pages.','section-widget'); ?>
			</p>
			<textarea rows="16" cols="20" id="<?php echo esc_attr($this->get_field_id('body')); ?>" name="<?php echo esc_attr($this->get_field_name('body')); ?>"><?php echo $body; ?></textarea>
		</div>
		<script type="text/javascript">
			if(typeof OLTChecklistPaneInit == 'function')
				OLTChecklistPaneInit(jQuery('#<?php echo $this->get_field_id('section_conditions-wrapper'); ?>'));
		</script>
<?php
	}
}
/**
 * section_widget_init function.
 *
 * @access public
 * @return void
 */
function section_widget_init() {
	$olt_section_widget = new OLT_Section_Widget();
	register_widget( $olt_section_widget );
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
	if(is_admin()){
		global $pagenow;
		if( $pagenow == 'widgets.php' ):
			wp_enqueue_style('section-widget-admin', plugins_url('section-widget/css/section-widget-admin'.$suffix.'.css'));
		endif;
	}
}

### Function: Init Section Widget
add_action('widgets_init', 'section_widget_init');

?>

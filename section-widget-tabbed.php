<?php

include_once('olt-checklist/loader.php');
enqueue_olt_checklist_loader(plugins_url('section-widget/olt-checklist'));

include_once('section-widget-options-page.php');

/**
 * Tabbed section widget class
 */
/**
 * OLT_Tabbed_Section_Widget class.
 *
 * @extends WP_Widget
 */
class OLT_Tabbed_Section_Widget extends WP_Widget {


	public static $widget_ids = array();

    /**
     * OLT_Tabbed_Section_Widget function.
     *
     * @access public
     * @return void
     */
    function __construct() {
        $widget_ops = array('classname' => 'section-widget-tabbed', 'description' => __('Display section-specific content in tabs.'));
        $control_ops = array('width' => 400);
        parent::__construct('section-tabbed', __('Section (Tabbed)'), $widget_ops, $control_ops);


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

        extract(wp_parse_args((array) get_option('section-widget-settings'), array(
            'heightfix' => false // This is all I care about
        )));

        if(isset($_GET['swt-scope-test'])) {
            echo $before_widget . '<div class="swt-wrapper">Section Widget Scope Test</div>' . $after_widget;
            return;
        }

        // olt_checklist_conditions_check is the replacement for $should_display
        if(olt_checklist_conditions_check($instance['section_conditions'])) {
            if(count($instance['tabs']) == 0)
                return;

            $list = '';
            $content = '';

            if( is_array(  get_theme_support('tabs') ) ) {
            	$current_tabs_theme_support = reset( get_theme_support('tabs') );
            } else {
            	$current_tabs_theme_support = false;
            }


            if ( $current_tabs_theme_support == 'twitter-bootstrap' ) {
            foreach($instance['tabs'] as $id => $tab) {

            if ( $id == 0 ):
            	$list .= "<li class=\"active\">";
            else:
            	$list .= "<li>";
            endif;
           		$list .= "<a href=\"#{$widget_id}-tab-{$id}\">{$tab['title']}</a></li>";


           	if ( $id == 0 ):
           		$content .= "<div class=\"tab-pane active\" ";
           	else:
           		$content .= "<div class=\"tab-pane\" ";
           	endif;

				$content .= "id=\"{$widget_id}-tab-{$id}\">".do_shortcode($tab['body']).'</div>';
            }
            } else {
            foreach($instance['tabs'] as $id => $tab) {
                $list .= "<li><a href=\"#{$widget_id}-tab-{$id}\">{$tab['title']}</a></li>";
                $content .= "<div id=\"{$widget_id}-tab-{$id}\">".do_shortcode($tab['body']).'</div>';
            }
            }
            $heightFixClass = ($heightfix)? ' class="swt-height-fix"' : '';
            if ( $current_tabs_theme_support == 'twitter-bootstrap' ) {
            $html = '<ul class="nav nav-tabs" id="'.$widget_id.'">';
            #$html .=
            $html .= $list;
            $html .= '</ul>';
            $html .= "<div class='tab-content'>";
            $html .= $content;
            $html .= "</div>";
            } else {

	        $html = "<ul{$heightFixClass}>".$list.'</ul>'.$content;

            }
			$before_widget = str_replace( 'class="', 'class="section-widget-tabbed ', $before_widget );
            echo $before_widget;

            if($instance['display-title']){
                echo $before_title;
                echo apply_filters('widget_title', $instance['title']);
                echo $after_title;
            }

            if ( $current_tabs_theme_support == 'twitter-bootstrap' ) {



            echo apply_filters('widget_text', $html);

            if ( is_null(self::$widget_ids) ) {
            	self::$widget_ids = array ( $widget_id );
            } else {
            	array_push(self::$widget_ids, $widget_id);
            }

            ?>

            <?php
            } else {
            echo '<div class="swt-outter"><div class="swt-wrapper">';
            echo apply_filters('widget_text', $html);
            echo '</div></div>';
            }
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
        if(!is_array($instance['section_conditions']) && is_array($instance['conditions'])) {
            $instance['section_conditions'] = $instance['conditions'];
        }
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['display-title'] = (bool) $new_instance['display-title'];

        $instance['section_conditions'] = is_array($new_instance['section_conditions'])?
            $new_instance['section_conditions'] : array();

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

        $instance['tabs'] = array();

        if(is_array($new_instance['tabs'])) {
            $tabs = array();

            if(isset($new_instance['order']) && $new_instance['order'] != '') {
                // order=1&order=0&order=2...
                $order = explode('&', str_replace('order=', '', $new_instance['order']));

                foreach($order as $i) {
                    if(isset($new_instance['tabs'][intval($i)])) {
                        $tabs[] = $new_instance['tabs'][intval($i)];
                        unset($new_instance['tabs'][intval($i)]);
                    }
                }
            }

            $tabs = array_merge($tabs, $new_instance['tabs']);

            foreach($tabs as $tab){
                $title = strip_tags($tab['title']);
                if ( current_user_can('unfiltered_html') )
                    $body =  $tab['body'];
                else
                    $body = wp_filter_post_kses( $tab['body'] );

                $instance['tabs'][] = array(
                    'title' => $title,
                    'body' => $body
                );
            }
        }

        // Processing tabs below
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
        // Provide the defaults here
        $instance = wp_parse_args((array) $instance, array(
            'title' => '',
            'display-title' => true,
            'tabs' => array(),
            'section_conditions' => array(
                'special-pages' => array(),
                'pages' => array(),
                'categories' => array(),
                'tags' => array()
            )
        ));

        // Make sure second level options are actually arrays
        foreach($instance['tabs'] as $i => $v)
            if(!is_array($v))
                $instance['tabs'][$i] = array();

        foreach($instance['section_conditions'] as $i => $v)
            if(!is_array($v))
                $instance['section_conditions'][$i] = array();


        $title = strip_tags($instance['title']);
        $display_title = (bool) $instance['display-title'];
        $special_pages = $instance['section_conditions']['special-pages'];
        $pages = $instance['section_conditions']['pages'];
        $categories = $instance['section_conditions']['categories'];
        $tags = $instance['section_conditions']['tags'];

        $tabs = is_array($instance['tabs'])? $instance['tabs'] : array();

        foreach($tabs as $i => $tab) {
            $tabs[$i]['title'] = strip_tags($tab['title']);
            $tabs[$i]['body'] = format_to_edit($tab['body']);
        }
?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:','section-widget'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
            <input id="<?php echo $this->get_field_id('display-title'); ?>" name="<?php echo $this->get_field_name('display-title'); ?>" type="checkbox" <?php checked($display_title); ?> />
            <label for="<?php echo $this->get_field_id('display-title'); ?>"><?php _e('Display title','section-widget'); ?></label>
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
        <div class="olt-swt-designer">
            <input type="hidden" name="idprefix" value="<?php echo $this->get_field_id('tab') ?>" />
            <input type="hidden" name="nameprefix" value="<?php echo $this->get_field_name('tabs') ?>" />
            <input type="hidden" name="<?php echo $this->get_field_name('order') ?>" class="olt-swt-order" />
            <div class="olt-swt-designer-wrapper" id="<?php echo $this->get_field_id('designer-wrapper') ?>">
                <div  id="<?php echo $this->get_field_id('designer-main') ?>" class="olt-swt-designer-main">
                    <ul>
                        <?php foreach($tabs as $id => $tab): ?>
                        <li class="olt-swt-designer-tab" id="<?php echo $this->get_field_id('tab-'.$id); ?>-list">
                            <a href="#<?php echo $this->get_field_id('tab-'.$id); ?>" id="<?php echo $this->get_field_id('tab-'.$id.'-title-link'); ?>">
                                <?php echo esc_html($tab['title']); ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                        <li class="olt-swt-designer-tabs-controls olt-swt-designer-add-tab">
                            <a><span class="ui-icon ui-icon-plusthick" style="float:left;margin-right:.3em;margin-top: -1px;"></span><?php _e('Add a new tab','section-widget'); ?></a>
                        </li>
                    </ul>
                    <?php foreach($tabs as $id => $tab): ?>
                    <div id="<?php echo $this->get_field_id('tab-'.$id) ?>" class="olt-swt-designer-panel">
                        <div class="olt-swt-designer-top">
                            <label for="<?php echo $this->get_field_id('tab-'.$id.'-title'); ?>"><?php _e('Title:','section-widget'); ?></label>
                            <input id="<?php echo $this->get_field_id('tab-'.$id.'-title'); ?>" class="olt-swt-designer-tab-title" name="<?php echo $this->get_field_name('tabs')."[$id][title]"; ?>" type="text" value="<?php echo esc_attr($tab['title']); ?>" />
                            <p class="olt-swt-designer-tabs-controls olt-swt-designer-delete-tab">
                                <a href="#" id="<?php echo $this->get_field_id('tab-'.$id) ?>-delete"><span class="ui-icon ui-icon-trash" style="float:left;margin-right:.3em;margin-top: -2px;"></span><?php _e('Delete this tab','section-widget');?></a>
                            </p>
                        </div>
                        <div class="olt-sw-body">
                            <p class="olt-sw-body-help">
                                <?php _e('<strong>Formatting Help:</strong> You may use HTML in this widget, and it is probably a good idea to wrap the content in your own <code>&lt;div&gt;</code> to aid styling. Shortcodes are also allowed, but please beware not all of them will function properly on archive pages.','section-widget');?></p>
                            <textarea rows="16" cols="20" name="<?php echo $this->get_field_name('tabs')."[$id][body]"; ?>"><?php echo esc_html($tab['body']); ?></textarea>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <script type="text/javascript">
        	//console.log('<?php echo $this->get_field_id('designer-main'); ?>', '<?php echo $this->get_field_id('conditions-wrapper'); ?>');
            if(typeof OLTChecklistPaneInit == 'function')
                OLTChecklistPaneInit(jQuery('#<?php echo $this->get_field_id('conditions-wrapper'); ?>'));
            if(typeof OLTSWTInit == 'function')
                OLTSWTInit(jQuery('#<?php echo $this->get_field_id('designer-wrapper') ?>'));
        </script>
<?php
    }
}
/**
 * tabbed_section_widget_init function.
 *
 * @access public
 * @return void
 */
function tabbed_section_widget_init() {
    register_widget('OLT_Tabbed_Section_Widget');
}
/**
 * tabbed_section_widget_load_scripts function.
 *
 * @access public
 * @return void
 */
function tabbed_section_widget_load_scripts() {

    extract(wp_parse_args((array) get_option('section-widget-settings'), array(
        'theme' => 'redmond',
        'scope' => '.swt-outter',
        'heightfix' => false
    )));

    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    if(is_admin() ):
    	global $pagenow;

    	if( $pagenow == 'widgets.php' ):

        	if($theme == 'none') $theme = 'base';

        	wp_enqueue_style( 'section-widget-admin', plugins_url('section-widget/js/section-widget-admin'. $suffix.'.css') );
        	wp_enqueue_style( "section-widget-theme-{$theme}", plugins_url("section-widget/themes/theme-loader.php?theme={$theme}&scope=.olt-swt-designer"));
        	wp_enqueue_script('section-widget-admin', plugins_url('section-widget/js/section-widget-tabs'. $suffix.'.js'), array('jquery','jquery-ui-tabs','jquery-ui-sortable'), '3.3.1');

        elseif( $pagenow == 'themes.php' && isset( $_GET['page'] ) && $_GET['page'] == 'section-widget' ):
        	wp_enqueue_script('section-widget-admin', plugins_url('section-widget/js/section-widget-admin'. $suffix.'.js'), array('jquery','jquery-ui-tabs','jquery-ui-sortable'), '3.3.1');

        endif;
   else:
	        // Only load script and css if there is at least one active tabbed widget
	        if( is_active_widget( false, false, 'section-tabbed' ) ):
	            if($theme != 'none'):
	                wp_enqueue_style("section-widget-theme-{$theme}",
	                plugins_url("section-widget/themes/theme-loader.php?theme={$theme}&scope=").urlencode($scope));
	      		endif;

	      	$current_tabs_theme_support = false;
	      	if( is_array( get_theme_support('tabs') ) )
	        	$current_tabs_theme_support = reset( get_theme_support('tabs') );

	         if ( $current_tabs_theme_support != 'twitter-bootstrap' ):
	         	wp_enqueue_script('section-widget',
	           		plugins_url('section-widget/js/section-widget'. $suffix.'.js'), array('jquery','jquery-ui-tabs'), '3.3.1' );

	         endif;   // uses_twitter_bootstrap ?

    	endif;
    endif; // is_admin
}

function enqueue_assets_for_widget_page() {
    $current_screen = get_current_screen();

    if( $current_screen->id !== "widgets" ) {
        return;
    }

    if ( version_compare( $GLOBALS['wp_version'], '5.8', '<' ) ) {
        return;
    }

    if( function_exists('is_plugin_active_for_network') && is_plugin_active_for_network('classic-widgets/classic-widgets.php') ) {
        return;
    }

    if( function_exists('is_plugin_active') && is_plugin_active('classic-widgets/classic-widgets.php') ) {
        return;
    }

    wp_enqueue_script('section-widget-admin-gutenberg', plugins_url('section-widget/js/section-widget-tabs-gutenberg.js'), array('jquery','jquery-ui-tabs','jquery-ui-sortable'), '3.3.1');
}

### Function: Init Section Widget
add_action('widgets_init', 'tabbed_section_widget_init');
add_action('init', 'tabbed_section_widget_load_scripts');
add_action('wp_footer', 'print_script');
add_action( 'current_screen', 'enqueue_assets_for_widget_page' );


function print_script() {
	echo '<script type="text/javascript">';
	#foreach ( OLT_Tabbed_Section_Widget::$widget_ids as $widget_id ) { ?>
	jQuery(function () { jQuery('.section-widget-tabbed .nav-tabs a, widget-inside .nav-tabs a').click(function (e) { e.preventDefault();
	jQuery(this).tab('show'); }) });

<?php
	echo '</script>';
}

<?php
/*
Plugin Name: Job Listings from remoteok.io
Plugin URI: https://www.angusokereafor.com/fun-projects/remote-ok-plugin/
Description: Displays remote job openings provided by remoteok.io
Version: 1.0.0
Author: Angus Okereafor
Author URI: https://www.angusokereafor.com/
License: MIT
*/

if( ! defined( 'ABSPATH' ) ) { exit; }
define( 'MTC_ROK_PLUGIN_FILE', __FILE__ );

// The widget class
class mtc_remoteok extends WP_Widget {
	public function __construct() {
        parent::__construct('mtc_remoteok_widget', __( 'remoteok.io Jobs', 'mtc_rok' ), array( 'customize_selective_refresh' => true));
        $this->getdata();
        $this->loadcss();
    }

	// The widget form (for the backend )
	public function form( $instance ) {
		// Set widget defaults
		$defaults = array(
			'title'    => 'remoteok.io Jobs',
			'page_size' => 10
		);
		
		// Parse current settings with defaults
        extract( wp_parse_args( ( array ) $instance, $defaults ) ); ?>
        
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Widget Title', 'mtc_text' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'page_size' ) ); ?>"><?php _e( 'Page Size:', 'mtc_text' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'page_size' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'page_size' ) ); ?>" type="number" min="1" value="<?php echo esc_attr( $page_size ); ?>" />
		</p>
	<?php }

	// Update widget settings
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title']  = isset( $new_instance['title'] ) ? wp_strip_all_tags( $new_instance['title'] ) : '';
		$instance['page_size'] = isset( $new_instance['page_size'] ) ? wp_strip_all_tags( $new_instance['page_size'] ) : '';
		return $instance;
	}

	// Display the widget
	public function widget( $args, $instance ) {
		extract( $args );
		// Check the widget options
		$title    = isset( $instance['title'] ) ? apply_filters( 'widget_title', $instance['title'] ) : '';
		$page_size = isset( $instance['page_size'] ) ? $instance['page_size'] : '';
        
        // WordPress core before_widget hook
		echo $before_widget;
        
        // Display the widget title
        echo '<div class="mtc_rok">';
        empty($title) ?: print '<div class="mtc_rok__header">'.$args['before_title'] . apply_filters('widget_title', $title) . $args['after_title'].'</div>';
        
        //Footer
        $rok_footer = '
            <div class="mtc_rok__footer">
                <div class="mtc_rok__footer__pagination mtc_rok__footer__pagination--left"><a data-control="previous" href="#"><i class="dashicons dashicons-arrow-left-alt2"></i></a></div>
                <div class="mtc_rok__footer__logo"><a href="https://remoteok.io" target="_blank"><img src="'.plugins_url('assets/images/logo.svg', MTC_ROK_PLUGIN_FILE).'"></a></div>
                <div class="mtc_rok__footer__pagination mtc_rok__footer__pagination--right"><a data-control="next" href="#"><i class="dashicons dashicons-arrow-right-alt2"></i></a></div>
                <input name="rok_page" id="rok_page" value="1" type="hidden">
                <input name="rok_page_length" id="rok_page_length" value="'.$page_size.'" type="hidden">
            </div>
        ';

        // Display the widget body
        echo $rok_footer.'<div class="mtc_rok__body"></div><div class="mtc_rok__prefooter"></div>'.$rok_footer;

        echo '</div>';

        // WordPress core after_widget hook
		echo $after_widget;
	}

    public function getdata(){
        $url = "https://remoteok.io/api";
        try{
            $response = wp_remote_retrieve_body(wp_remote_get($url));
            $data = json_decode($response, true);
            unset($data[0]);
            
            $data = json_encode(array("status" => "successful", "message" => "Data successfully retrieved", "data" => array_values($data)));
        }catch(Exception $ex){
            $data = json_encode(array("status" => "error", "message" => $ex->getMessage()));
        }

        wp_enqueue_script('rok_js', plugins_url('assets/js/rok.js', MTC_ROK_PLUGIN_FILE), array('jquery'), '1.0.0', true );
        $reshuffled_data = array('l10n_print_after' => 'rok_data = ' . $data . ';');
        wp_localize_script('rok_js', 'rok_filler_data', $reshuffled_data);
    }

    public function loadcss(){
        wp_enqueue_style('dashicons');
        wp_enqueue_style('rok_css', plugins_url('assets/css/rok.css', MTC_ROK_PLUGIN_FILE), array(), '1.0.0', 'all');
    }
}

// Register the widget
function mtc_remoteok_widget() {
	register_widget('mtc_remoteok');
}
add_action( 'widgets_init', 'mtc_remoteok_widget' );
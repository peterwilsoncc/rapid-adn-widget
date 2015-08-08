<?php
/*
Plugin Name: Rapid App.net Widget
Plugin URI: 
Description: Display the latest updates from a App.net user inside a widget. 
Version: 1.2
Author: Peter Wilson
Author URI: 
License: GPLv2
*/

define('RAPID_ADN_WIDGET_VERSION', '1.2');

class Rapid_ADN_Widget extends WP_Widget {

	function __construct() {
		$widget_ops = array(
			'classname'   => 'widget_adn widget_adn--hidden',
			'description' => __( 'Display your posts from App.net')
		);
		parent::__construct( 'rapid-adn', __( 'Rapid App.net' ), $widget_ops );
		
		if ( is_active_widget(false, false, $this->id_base) ) {
			add_action( 'wp_head', array(&$this, 'rapid_adn_widget_style') );
		}
		
		add_action( 'wp_enqueue_scripts', array( &$this, 'rapid_adn_widget_script' ) );
	}
	
	function rapid_adn_widget_style() {
		if ( ! current_theme_supports( 'widgets' ) 
			|| ! apply_filters( 'show_rapid_adn_widget_style', true, $this->id_base ) ) {
			return;
		}
		echo "<style>.widget_adn--hidden{display:none!important;}</style>";
	}

	function rapid_adn_widget_script() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '-min';
		wp_register_script(
			'rapid-adn-widget',
			plugins_url( 'rapid-adn-widget/rapid-adn-widget' . $suffix . '.js' ),
			'',
			RAPID_ADN_WIDGET_VERSION,
			true
		);

	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['account'] = trim( strip_tags( stripslashes( $new_instance['account'] ) ) );
		$instance['account'] = str_replace( 'http://alpha.app.net/', '', $instance['account'] );
		$instance['account'] = str_replace( 'https://alpha.app.net/', '', $instance['account'] );
		$instance['account'] = str_replace( '/', '', $instance['account'] );
		$instance['account'] = str_replace( '@', '', $instance['account'] );
		$instance['account'] = str_replace( '#!', '', $instance['account'] ); // account for the Ajax URI
		$instance['title'] = strip_tags( stripslashes( $new_instance['title'] ) );
		$instance['show'] = absint( $new_instance['show'] );
		$instance['hidereplies'] = isset( $new_instance['hidereplies'] );
		$instance['includereposts'] = isset( $new_instance['includereposts'] );

		return $instance;
	}

	function form( $instance ) {
		//Defaults
		$instance = wp_parse_args( (array) $instance,
			array(
				'account'     => '',
				'title'       => '',
				'show'        => 5,
				'hidereplies'	=> false
			) );

		$account = esc_attr( $instance['account'] );
		$title = esc_attr( $instance['title'] );
		$show = absint( $instance['show'] );
		if ( $show < 1 || 20 < $show )
			$show = 5;
		$hidereplies = (bool) $instance['hidereplies'];
		$include_reposts = (bool) $instance['includereposts'];

		//Title
		echo '<p>';
		echo '<label for="' . $this->get_field_id('title') . '">' . esc_html__('Title:') . '</label>';
		echo '<input class="widefat" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . $title . '" />';
		echo '</p>';

		//Username
		echo '<p>';
		echo '<label for="' . $this->get_field_id('account') . '">' . esc_html__('App.net username:') . '</label>';
		echo '<input class="widefat" id="' . $this->get_field_id('account') . '" name="' . $this->get_field_name('account') . '" type="text" value="' . $account . '" />';
		echo '</p>';

		//Max Posts
		echo '<p>';
		echo '<label for="' . $this->get_field_id('show') . '">' . esc_html__('Maximum number of posts to show:') . '</label>';
		echo '<select id="' . $this->get_field_id('show') . '" name="' . $this->get_field_name('show') . '">';

		for ( $i = 1; $i <= 20; ++$i )
			echo "<option value='$i' " . ( $show == $i ? "selected='selected'" : '' ) . ">$i</option>";

		echo '</select>';
		echo '</p>';

		//Hide Reploes
		echo '<p>';
		echo '<label for="' . $this->get_field_id('hidereplies') . '">';
		echo '<input id="' . $this->get_field_id('hidereplies') . '" class="checkbox" type="checkbox" name="' . $this->get_field_name('hidereplies') . '"';
		if ( $hidereplies )
			echo ' checked="checked"';
		echo ' /> ' . esc_html__('Hide replies');
		echo '</label>';
		echo '</p>';

		//Include Reposts
		echo '<p>';
		echo '<label for="' . $this->get_field_id('includereposts') . '"><input id="' . $this->get_field_id('includereposts') . '" class="checkbox" type="checkbox" name="' . $this->get_field_name('includereposts') . '"';
		if ( $include_reposts )
			echo ' checked="checked"';
		echo ' /> ' . esc_html__('Include reposts');
		echo '</label>';
		echo '</p>';
	}

	function widget( $args, $instance ) {
		extract( $args );
		
		$account = trim( urlencode( $instance['account'] ) );
		if ( empty( $account ) ) return;
		$title = apply_filters( 'widget_title', $instance['title'] );
		if ( empty( $title ) ) $title = __( 'App.net Updates' );
		$show = absint( $instance['show'] );  // # of Updates to show
		if ( $show > 200 ) {
			// App.net paginates at 200 max posts. update() should not have accepted greater than 20
			$show = 200;
		}
		$hidereplies = (bool) $instance['hidereplies'] ? 't' : 'f';
		$include_reposts = (bool) $instance['includereposts'] ? 't' : 'f';

		echo $before_widget;

		echo $before_title;
		echo "<a href='" . esc_url( "https://alpha.app.net/{$account}" ) . "'>" . esc_html($title) . "</a>";
		echo $after_title;
		
		$numbers = array( '1', '2', '3', '4', '5', '6', '7', '8', '9', '0' );
		$letters = array( 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z' );
		
		$url_ref = '';
		$url_ref .= $show . '__';
		$url_ref .= $hidereplies . '__';
		$url_ref .= $include_reposts . '__';
		$url_ref .= $account . '';
		
		$url_ref = hash( 'md5', $url_ref );
		$url_ref = base_convert( $url_ref, 16, 26 );
		$url_ref = str_replace( $numbers, $letters, $url_ref );
		
		
		$widget_ref = '';
		$widget_ref .= $args['widget_id'];
		$widget_ref .= '__';
		$widget_ref .= $instance['title'];
		$widget_ref .= '__';
		$widget_ref .= $url_ref;
		
		$script_id = hash( 'md5', $widget_ref );
		$script_id = base_convert( $script_id, 16, 36 );

		echo '<script id="' . $script_id . '">';
		echo 'if(typeof(RapidADN)==\'undefined\'){';
		echo 'RapidADN={};RapidADN.apis={};';
		echo '}';
		echo 'if(typeof(RapidADN.apis.' . $url_ref . ')==\'undefined\'){';
		echo 'RapidADN.apis.' . $url_ref . '={';
		echo 'screen_name:\'' . esc_js( $account ) . '\'';
		echo ',count:\'' . esc_js( $show ) . '\'';
		echo ',exclude_replies:\'' . esc_js( $hidereplies ) . '\'';
		echo ',include_rts:\'' . esc_js( $include_reposts ) . '\'';
		echo ',widgets: []';
		echo '};';
		echo '}';
		echo 'RapidADN.apis.' . $url_ref . '.widgets.push(\'' . $script_id . '\');';
		echo '</script>';
		wp_enqueue_script( 'rapid-adn-widget' );
		echo $after_widget;
		
	}

}

add_action( 'widgets_init', 'rapid_adn_widget_init' );

function rapid_adn_widget_init() {
	register_widget( 'Rapid_ADN_Widget' );
}
<?php
/*
Plugin Name: Rapid Twitter Widget
Plugin URI: 
Description: Display the <a href="http://twitter.com/">Twitter</a> latest updates from a Twitter user inside a widget. 
Version: 1.0
Author: Peter Wilson, Floate Design Partners
Author URI: 
License: GPLv2
*/

define('RAPID_TWITTER_WIDGET_VERSION', '1.0');

class Rapid_Twitter_Widget extends WP_Widget {

	function Rapid_Twitter_Widget() {
		$widget_ops = array(
			'classname'   => 'widget_twitter widget_twitter--hidden',
			'description' => __( 'Display your tweets from Twitter')
		);
		parent::WP_Widget( 'rapid-twitter', __( 'Rapid Twitter' ), $widget_ops );
		
		if ( is_active_widget(false, false, $this->id_base) ) {
			add_action( 'wp_head', array(&$this, 'rapid_twitter_widget_style') );
		}
		
		add_action( 'wp_enqueue_scripts', array( &$this, 'rapid_twitter_widget_script' ) );
	}
	
	function rapid_twitter_widget_style() {
		if ( ! current_theme_supports( 'widgets' ) 
			|| ! apply_filters( 'show_rapid_twitter_widget_style', true, $this->id_base ) ) {
			return;
		}
		echo "<style>.widget_twitter--hidden{display:none!important;}</style>";
	}

	function rapid_twitter_widget_script() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '-min';
		wp_register_script(
			'rapid-twitter-widget',
			WP_PLUGIN_URL . "/rapid-twitter-widget/rapid-twitter-widget$suffix.js",
			'',
			RAPID_TWITTER_WIDGET_VERSION,
			true
		);

	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['account'] = trim( strip_tags( stripslashes( $new_instance['account'] ) ) );
		$instance['account'] = str_replace( 'http://twitter.com/', '', $instance['account'] );
		$instance['account'] = str_replace( 'https://twitter.com/', '', $instance['account'] );
		$instance['account'] = str_replace( '/', '', $instance['account'] );
		$instance['account'] = str_replace( '@', '', $instance['account'] );
		$instance['account'] = str_replace( '#!', '', $instance['account'] ); // account for the Ajax URI
		$instance['title'] = strip_tags( stripslashes( $new_instance['title'] ) );
		$instance['show'] = absint( $new_instance['show'] );
		$instance['hidereplies'] = isset( $new_instance['hidereplies'] );
		$instance['includeretweets'] = isset( $new_instance['includeretweets'] );

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
		$include_retweets = (bool) $instance['includeretweets'];

		//Title
		echo '<p>';
		echo '<label for="' . $this->get_field_id('title') . '">' . esc_html__('Title:') . '</label>';
		echo '<input class="widefat" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . $title . '" />';
		echo '</p>';

		//Username
		echo '<p>';
		echo '<label for="' . $this->get_field_id('account') . '">' . esc_html__('Twitter username:') . '</label>';
		echo '<input class="widefat" id="' . $this->get_field_id('account') . '" name="' . $this->get_field_name('account') . '" type="text" value="' . $account . '" />';
		echo '</p>';

		//Max Tweets
		echo '<p>';
		echo '<label for="' . $this->get_field_id('show') . '">' . esc_html__('Maximum number of tweets to show:') . '</label>';
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

		//Include Retweets
		echo '<p>';
		echo '<label for="' . $this->get_field_id('includeretweets') . '"><input id="' . $this->get_field_id('includeretweets') . '" class="checkbox" type="checkbox" name="' . $this->get_field_name('includeretweets') . '"';
		if ( $include_retweets )
			echo ' checked="checked"';
		echo ' /> ' . esc_html__('Include retweets');
		echo '</label>';
		echo '</p>';
	}

	function widget( $args, $instance ) {
		extract( $args );
		
		$account = trim( urlencode( $instance['account'] ) );
		if ( empty( $account ) ) return;
		$title = apply_filters( 'widget_title', $instance['title'] );
		if ( empty( $title ) ) $title = __( 'Twitter Updates' );
		$show = absint( $instance['show'] );  // # of Updates to show
		if ( $show > 200 ) {
			// Twitter paginates at 200 max tweets. update() should not have accepted greater than 20
			$show = 200;
		}
		$hidereplies = (bool) $instance['hidereplies'] ? 't' : 'f';
		$include_retweets = (bool) $instance['includeretweets'] ? 't' : 'f';

		echo $before_widget;

		echo $before_title;
		echo "<a href='" . esc_url( "https://twitter.com/{$account}" ) . "'>" . esc_html($title) . "</a>";
		echo $after_title;
		
		$numbers = array( '1', '2', '3', '4', '5', '6', '7', '8', '9', '0' );
		$letters = array( 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z' );
		
		$url_ref = '';
		$url_ref .= $show . '__';
		$url_ref .= $hidereplies . '__';
		$url_ref .= $include_retweets . '__';
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
		echo 'if(typeof(RapidTwitter)==\'undefined\'){';
		echo 'RapidTwitter={};RapidTwitter.apis={};';
		echo '}';
		echo 'if(typeof(RapidTwitter.apis.' . $url_ref . ')==\'undefined\'){';
		echo 'RapidTwitter.apis.' . $url_ref . '={';
		echo 'screen_name:\'' . esc_js( $account ) . '\'';
		echo ',count:\'' . esc_js( $show ) . '\'';
		echo ',exclude_replies:\'' . esc_js( $hidereplies ) . '\'';
		echo ',include_rts:\'' . esc_js( $include_retweets ) . '\'';
		echo ',widgets: []';
		echo '};';
		echo '}';
		echo 'RapidTwitter.apis.' . $url_ref . '.widgets.push(\'' . $script_id . '\');';
		echo '</script>';
		wp_enqueue_script( 'rapid-twitter-widget' );
		echo $after_widget;
		
	}

}

add_action( 'widgets_init', 'rapid_twitter_widget_init' );

function rapid_twitter_widget_init() {
	register_widget( 'Rapid_Twitter_Widget' );
}
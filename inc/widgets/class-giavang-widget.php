<?php

/**
 * Giavang page embed widget class
 *
 * @package giavang
 */
if (!defined('ABSPATH')) {
    die();
}

/**
 * Giavang page embed widget class
 */
class Giavang_Widget extends WP_Widget {

    /**
     * Embed settings
     *
     * @var array
     */
    private $settings;

    /**
     * Instantiate the widget
     */
    public function __construct() {
        $this->settings = Giavang::get_instance()->get_settings();
        parent::__construct('giavang_widget', __('Giavang', 'giavang'), array('description' => __('Generates a Giavang Page feed in your widget area', 'giavang')));
    }

    /**
     * Render widget on the front end
     *
     * @param array $args Widget args.
     * @param array $instance The settings for the particular instance of the widget.
     */
    public function widget($args, $instance) {
        if (isset($instance['title']) && !empty($instance['title'])) {
            $title = apply_filters('widget_title', $instance['title']);
        } else {
            $title = null;
        }
        if (isset($instance['gold']) && !empty($instance['gold'])) {
            $gold = 'true';
        } else {
            $gold = 'false';
        }
        if (isset($instance['dollar']) && !empty($instance['dollar'])) {
            $dollar = 'true';
        } else {
            $dollar = 'false';
        }

        echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput
        if (!empty($title)) {
            // Apparently people like to put HTML in their widget titles, not a good idea but okay ¯\_(ツ)_/¯.
            echo $args['before_title'] . $title . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput
        }
        $shortcode = '[giavang ';
        if (isset($gold) && !empty($gold)) {
            $shortcode .= ' gold="' . esc_attr($gold) . '"';
        }
        if (isset($dollar) && !empty($dollar)) {
            $shortcode .= ' dollar="' . esc_attr($dollar) . '"';
        }

        $shortcode .= ' _implementation="widget"';
        $shortcode .= ']';
        echo do_shortcode($shortcode);

        echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput
    }

    /**
     * Register the widget edit form
     *
     * @param array $instance The settings for the particular instance of the widget.
     */
    public function form($instance) {
        if (isset($instance['title'])) {
            $title = $instance['title'];
        } else {
            $title = __('New title', 'giavang');
        }

        if (isset($instance['gold'])) {
            $gold = $instance['gold'];
        } else {
            $gold = 'false';
        }
        if (isset($instance['dollar'])) {
            $dollar = $instance['dollar'];
        } else {
            $dollar = 'true';
        }


        Giavang::get_instance()->donate_notice(true);

        printf(
                '<p><label for="%1$s">%2$s</label><input class="widefat" id="%1$s" name="%3$s" type="text" value="%4$s" /></p>',
                esc_attr($this->get_field_id('title')),
                esc_html__('Title:', 'giavang'),
                esc_attr($this->get_field_name('title')),
                esc_attr($title)
        );

        printf(
                '<p><label for="%1$s">%2$s</label> <input class="widefat" id="%1$s" name="%3$s" type="checkbox" value="true" %4$s /></p>',
                esc_attr($this->get_field_id('gold')),
                esc_html__('Gia vang', 'giavang'),
                esc_attr($this->get_field_name('gold')),
                checked(esc_attr($gold), 'true', false)
        );

        printf(
                '<p><label for="%1$s">%2$s</label> <input class="widefat" id="%1$s" name="%3$s" type="checkbox" value="true" %4$s /></p>',
                esc_attr($this->get_field_id('dollar')),
                esc_html__('Gia Dollar', 'giavang'),
                esc_attr($this->get_field_name('dollar')),
                checked(esc_attr($dollar), 'true', false)
        );
    }

    /**
     * Updating widget replacing old instances with new
     *
     * @param array $new_instance Updated widget instance.
     * @param array $old_instance Previous widget instance.
     * @return array
     */
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title']) ) ? wp_strip_all_tags($new_instance['title']) : '';
        $instance['gold'] = (!empty($new_instance['gold']) ) ? wp_strip_all_tags($new_instance['gold']) : '';
        $instance['dollar'] = (!empty($new_instance['dollar']) ) ? wp_strip_all_tags($new_instance['dollar']) : '';

        return $instance;
    }

}

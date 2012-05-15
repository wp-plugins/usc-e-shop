<?php

/**
 * Welcart_calendar Class
 */
class Welcart_calendar extends WP_Widget {
    /** constructor */
    function Welcart_calendar() {
        parent::WP_Widget(false, $name = 'Welcart '.__('Calendar', 'usces'));	
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {		
        extract( $args );
        $title = $instance['title'] == '' ? 'Welcart '.__('Business Calendar', 'usces') : $instance['title'];
        $icon = $instance['icon'] == '' ? 1 : (int)$instance['icon'];
		//if($icon == 1) $before_title = '<div class="widget_title"><img src="' . USCES_PLUGIN_URL . '/images/calendar.png" alt="' . $title . '" width="24" height="24" />';
		$img_path = file_exists(get_stylesheet_directory().'/images/calendar.png') ? get_stylesheet_directory_uri().'/images/calendar.png' : USCES_FRONT_PLUGIN_URL . '/images/calendar.png';
		if($icon == 1) $before_title .= '<img src="' . $img_path . '" alt="' . $title . '" />';
        ?>
              <?php echo $before_widget; ?>
                  <?php echo $before_title
                      . wp_specialchars($title)
                      . $after_title; ?>
					  
		<ul class="ucart_calendar_body ucart_widget_body"><li>
		<?php usces_the_calendar(); ?>
		</li></ul>
				  
              <?php echo $after_widget; ?>
        <?php
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {				
        return $new_instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {				
        $title = $instance['title'] == '' ? 'Welcart '.__('Business Calendar', 'usces') : esc_attr($instance['title']);
		$icon = $instance['icon'] == '' ? 1 : (int)$instance['icon'];
        ?>
            <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
			<p><label for="<?php echo $this->get_field_id('icon'); ?>"><?php _e('display of icon', 'usces'); ?>: <select class="widefat" id="<?php echo $this->get_field_id('icon'); ?>" name="<?php echo $this->get_field_name('icon'); ?>"><option value="1"<?php if($icon == 1){echo ' selected="selected"';} ?>><?php _e('Indication', 'usces'); ?></option><option value="2"<?php if($icon == 2){echo ' selected="selected"';} ?>><?php _e('Non-indication', 'usces'); ?></option></select></label></p>
			<p><?php _e("The setting of the business day, In a 'business day setting' page of the admin screen.", 'usces'); ?></p>
        <?php 
    }

}
?>
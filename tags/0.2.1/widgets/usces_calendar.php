<?php

/**
 * Welcart_calendar Class
 */
class Welcart_calendar extends WP_Widget {
    /** constructor */
    function Welcart_calendar() {
        parent::WP_Widget(false, $name = 'Welcartカレンダー');	
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {		
        extract( $args );
        $title = $instance['title'] == '' ? 'Welcart営業日カレンダー' : $instance['title'];
        $icon = $instance['icon'] == '' ? 1 : (int)$instance['icon'];
		//if($icon == 1) $before_title = '<div class="widget_title"><img src="' . USCES_PLUGIN_URL . '/images/calendar.png" alt="' . $title . '" width="24" height="24" />';
		if($icon == 1) $before_title .= '<img src="' . USCES_PLUGIN_URL . '/images/calendar.png" alt="' . $title . '" width="24" height="24" />';
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
        $title = $instance['title'] == '' ? 'Welcart営業日カレンダー' : esc_attr($instance['title']);
		$icon = $instance['icon'] == '' ? 1 : (int)$instance['icon'];
        ?>
            <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
			<p><label for="<?php echo $this->get_field_id('icon'); ?>">アイコンの示数: <select class="widefat" id="<?php echo $this->get_field_id('icon'); ?>" name="<?php echo $this->get_field_name('icon'); ?>"><option value="1"<?php if($icon == 1){echo ' selected="selected"';} ?>>表示する</option><option value="2"<?php if($icon == 2){echo ' selected="selected"';} ?>>表示しない</option></select></label></p>
			<p>営業日の設定はWelcartショップの「営業日設定」で行います</p>
        <?php 
    }

}
?>
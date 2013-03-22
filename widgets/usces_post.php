<?php

/**
 * Welcart_post Class
 */
class Welcart_post extends WP_Widget {
    /** constructor */
    function Welcart_post() {
        parent::WP_Widget(false, $name = 'Welcart '.__('Post', 'usces'));	
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {		
        extract( $args );
		$wid = str_replace('-', '_', $this->id);
        $title = ( !isset($instance['title']) || WCUtils::is_blank($instance['title'])) ? 'Welcart '.__('Post', 'usces') : $instance['title'];
        $rows_num = ( !isset($instance['rows_num']) || WCUtils::is_blank($instance['rows_num'])) ? 3 : $instance['rows_num'];
        $icon = ( !isset($instance['icon']) || WCUtils::is_blank($instance['icon'])) ? 1 : (int)$instance['icon'];
		//if($icon == 1) $before_title = '<div class="widget_title"><img src="' . USCES_PLUGIN_URL . '/images/infomation.png" alt="' . $title . '" width="24" height="24" />';
		$img_path = file_exists(get_stylesheet_directory().'/images/post.png') ? get_stylesheet_directory_uri().'/images/post.png' : USCES_FRONT_PLUGIN_URL . '/images/post.png';
		if($icon == 1) $before_title .= '<img src="' . $img_path . '" alt="' . $title . '" />';
		?>
              <?php echo $before_widget; ?>
                  <?php echo $before_title
                      . apply_filters( 'usces_filter_post_widget_title', esc_html($title), $instance)
                      . $after_title; ?>
					  
		<ul class="ucart_widget_body <?php echo $instance['category']; ?>">
		<?php usces_list_post( $instance['category'], $rows_num, $wid ) ; ?>
		</ul>
				  
              <?php echo $after_widget; ?>
        <?php
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {				
        return $new_instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {				
		$wid = ('welcart_post-__i__' != $this->id) ? str_replace('-', '_', $this->id) : '';
        $title = ( !isset($instance['title']) || WCUtils::is_blank($instance['title'])) ? 'Welcart '.__('Post', 'usces') : esc_attr($instance['title']);
        $rows_num = ( !isset($instance['rows_num']) || WCUtils::is_blank($instance['rows_num'])) ? 3 : esc_attr($instance['rows_num']);
		$icon = ( !isset($instance['icon']) || WCUtils::is_blank($instance['icon'])) ? 1 : (int)$instance['icon'];
        ?>
            <p>ID : <?php echo $wid; ?></p>
            <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
            <p><label for="<?php echo $this->get_field_id('icon'); ?>"><?php _e('display of icon', 'usces'); ?>: <select class="widefat" id="<?php echo $this->get_field_id('icon'); ?>" name="<?php echo $this->get_field_name('icon'); ?>"><option value="1"<?php if($icon == 1){echo ' selected="selected"';} ?>><?php _e('Indication', 'usces'); ?></option><option value="2"<?php if($icon == 2){echo ' selected="selected"';} ?>><?php _e('Non-indication', 'usces'); ?></option></select></label></p>
            <p><label for="<?php echo $this->get_field_id('category'); ?>"><?php _e('category slug', 'usces'); ?> <input class="widefat" id="<?php echo $this->get_field_id('rows_num'); ?>" name="<?php echo $this->get_field_name('category'); ?>" type="text" value="<?php echo $instance['category']; ?>" /></label></p>
            <p><label for="<?php echo $this->get_field_id('rows_num'); ?>"><?php _e('number of indication', 'usces'); ?> <input class="widefat" id="<?php echo $this->get_field_id('rows_num'); ?>" name="<?php echo $this->get_field_name('rows_num'); ?>" type="text" value="<?php echo $rows_num; ?>" /></label></p>
        <?php 
    }

}
?>
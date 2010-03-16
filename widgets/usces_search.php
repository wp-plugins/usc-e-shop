<?php

/**
 * Welcart_search Class
 */
class Welcart_search extends WP_Widget {
    /** constructor */
    function Welcart_search() {
        parent::WP_Widget(false, $name = 'Welcart '.__('keyword search', 'usces'));	
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {		
        extract( $args );
        $title = $instance['title'] == '' ? 'Welcart '.__('keyword search', 'usces') : $instance['title'];
        $icon = $instance['icon'] == '' ? 1 : (int)$instance['icon'];
		//if($icon == 1) $before_title = '<div class="widget_title"><img src="' . USCES_PLUGIN_URL . '/images/find.png" alt="' . $title . '" width="24" height="24" />';
		$img_path = file_exists(get_template_directory().'/images/search.png') ? get_template_directory_uri().'/images/search.png' : USCES_FRONT_PLUGIN_URL . '/images/search.png';
		if($icon == 1) $before_title .= '<img src="' . $img_path . '" alt="' . $title . '" />';
        ?>
              <?php echo $before_widget; ?>
                  <?php echo $before_title
                      . wp_specialchars($title)
                      . $after_title; ?>
					  
		<ul class="ucart_search_body ucart_widget_body"><li>
		<form method="get" id="searchform" action="<?php bloginfo('home'); ?>" >
		<input type="text" value="" name="s" id="s" class="searchtext" /><input type="submit" id="searchsubmit" value="<?php _e('Search', 'usces'); ?>" />
		<div><a href="<?php echo USCES_CART_URL; ?>&page=search_item"><?php _e("'AND' search by categories", 'usces'); ?>&gt;</a></div>
		</form>
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
        $title = $instance['title'] == '' ? 'Welcart '.__('keyword search', 'usces') : esc_attr($instance['title']);
		$icon = $instance['icon'] == '' ? 1 : (int)$instance['icon'];
        ?>
            <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
			<p><label for="<?php echo $this->get_field_id('icon'); ?>"><?php _e('display of icon', 'usces'); ?>: <select class="widefat" id="<?php echo $this->get_field_id('icon'); ?>" name="<?php echo $this->get_field_name('icon'); ?>"><option value="1"<?php if($icon == 1){echo ' selected="selected"';} ?>><?php _e('Indication', 'usces'); ?></option><option value="2"<?php if($icon == 2){echo ' selected="selected"';} ?>><?php _e('Non-indication', 'usces'); ?></option></select></label></p>
        <?php 
    }

}
?>
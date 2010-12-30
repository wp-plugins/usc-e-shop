<?php

/**
 * Welcart_bestseller Class
 */
class Welcart_bestseller extends WP_Widget {
    /** constructor */
    function Welcart_bestseller() {
        parent::WP_Widget(false, $name = 'Welcart '.__('best seller', 'usces'));	
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {
		global $usces;
        extract( $args );
        $title = $instance['title'] == '' ? 'Welcart '.__('best seller', 'usces') : $instance['title'];
        $rows_num = $instance['rows_num'] == '' ? 10 : $instance['rows_num'];
        $icon = $instance['icon'] == '' ? 1 : (int)$instance['icon'];
		//if($icon == 1) $before_title = '<div class="widget_title"><img src="' . USCES_PLUGIN_URL . '/images/best-seller.png" alt="' . $title . '" width="24" height="24" />';
		$img_path = file_exists(get_template_directory().'/images/bestseller.png') ? get_template_directory_uri().'/images/bestseller.png' : USCES_FRONT_PLUGIN_URL . '/images/bestseller.png';
		if($icon == 1) $before_title .= '<img src="' . $img_path . '" alt="' . $title . '" />';
		$list = $instance['list'] == '' ? 1 : (int)$instance['list'];
        ?>
              <?php echo $before_widget; ?>
                  <?php echo $before_title
                      . wp_specialchars($title)
                      . $after_title; ?>
			
		<ul class="ucart_widget_body">
			<?php if($list === 1): ?>
			<?php usces_list_bestseller($rows_num); ?>
			<?php else: ?>
			<?php  
					for($i=0; $i<$rows_num; $i++) { 
						$cname = 'code' . ($i+1);
						$code = wp_specialchars(trim($instance[$cname]));
						if('' == $code) continue;
						$id = $usces->get_postIDbyCode($code);
						if('' == $id) continue;
						$post = get_post($id);
						$list = '<li><a href="' . get_permalink($id) . '">' . wp_specialchars($post->post_title) . '</a></li>' . "\n";
						$htm .= apply_filters('usces_filter_bestseller', $list, $post->ID, $i);
					}
					echo $htm;
			?>
			<?php endif; ?>
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
        $title = $instance['title'] == '' ? 'Welcart '.__('best seller', 'usces') : esc_attr($instance['title']);
        $rows_num = $instance['rows_num'] == '' ? 10 : esc_attr($instance['rows_num']);
		$icon = $instance['icon'] == '' ? 1 : (int)$instance['icon'];
		$list = $instance['list'] == '' ? 1 : (int)$instance['list'];
		$code1 = esc_attr($instance['code1']);
		$code2 = esc_attr($instance['code2']);
		$code3 = esc_attr($instance['code3']);
		$code4 = esc_attr($instance['code4']);
		$code5 = esc_attr($instance['code5']);
		$code6 = esc_attr($instance['code6']);
		$code7 = esc_attr($instance['code7']);
		$code8 = esc_attr($instance['code8']);
		$code9 = esc_attr($instance['code9']);
		$code10 = esc_attr($instance['code10']);
        ?>
            <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
			<p><label for="<?php echo $this->get_field_id('icon'); ?>"><?php _e('display of icon', 'usces'); ?>: <select class="widefat" id="<?php echo $this->get_field_id('icon'); ?>" name="<?php echo $this->get_field_name('icon'); ?>"><option value="1"<?php if($icon == 1){echo ' selected="selected"';} ?>><?php _e('Indication', 'usces'); ?></option><option value="2"<?php if($icon == 2){echo ' selected="selected"';} ?>><?php _e('Non-indication', 'usces'); ?></option></select></label></p>
			<p><label for="<?php echo $this->get_field_id('rows_num'); ?>"><?php _e('number of indication', 'usces'); ?>: <input class="widefat" id="<?php echo $this->get_field_id('rows_num'); ?>" name="<?php echo $this->get_field_name('rows_num'); ?>" type="text" value="<?php echo $rows_num; ?>" /></label></p>
			<p><label for="<?php echo $this->get_field_id('list'); ?>"><?php _e('automatic count', 'usces'); ?>: <select class="widefat" id="<?php echo $this->get_field_id('list'); ?>" name="<?php echo $this->get_field_name('list'); ?>"><option value="1"<?php if($list == 1){echo ' selected="selected"';} ?>><?php _e('automatic list', 'usces'); ?></option><option value="2"<?php if($list == 2){echo ' selected="selected"';} ?>><?php _e('handwriting list', 'usces'); ?></option></select></label></p>
			<fieldset><legend><?php _e('handwriting list', 'usces'); ?></legend>
			<p><?php _e('Please input an article cord.', 'usces'); ?></p>
			<p><label for="<?php echo $this->get_field_id('code1'); ?>"><?php _e('item code', 'usces'); ?>1 : <input class="widefat" id="<?php echo $this->get_field_id('code1'); ?>" name="<?php echo $this->get_field_name('code1'); ?>" type="text" value="<?php echo $code1; ?>" /></label></p>
			<p><label for="<?php echo $this->get_field_id('code2'); ?>"><?php _e('item code', 'usces'); ?>2 : <input class="widefat" id="<?php echo $this->get_field_id('code2'); ?>" name="<?php echo $this->get_field_name('code2'); ?>" type="text" value="<?php echo $code2; ?>" /></label></p>
			<p><label for="<?php echo $this->get_field_id('code3'); ?>"><?php _e('item code', 'usces'); ?>3 : <input class="widefat" id="<?php echo $this->get_field_id('code3'); ?>" name="<?php echo $this->get_field_name('code3'); ?>" type="text" value="<?php echo $code3; ?>" /></label></p>
			<p><label for="<?php echo $this->get_field_id('code4'); ?>"><?php _e('item code', 'usces'); ?>4 : <input class="widefat" id="<?php echo $this->get_field_id('code4'); ?>" name="<?php echo $this->get_field_name('code4'); ?>" type="text" value="<?php echo $code4; ?>" /></label></p>
			<p><label for="<?php echo $this->get_field_id('code5'); ?>"><?php _e('item code', 'usces'); ?>5 : <input class="widefat" id="<?php echo $this->get_field_id('code5'); ?>" name="<?php echo $this->get_field_name('code5'); ?>" type="text" value="<?php echo $code5; ?>" /></label></p>
			<p><label for="<?php echo $this->get_field_id('code6'); ?>"><?php _e('item code', 'usces'); ?>6 : <input class="widefat" id="<?php echo $this->get_field_id('code6'); ?>" name="<?php echo $this->get_field_name('code6'); ?>" type="text" value="<?php echo $code6; ?>" /></label></p>
			<p><label for="<?php echo $this->get_field_id('code7'); ?>"><?php _e('item code', 'usces'); ?>7 : <input class="widefat" id="<?php echo $this->get_field_id('code7'); ?>" name="<?php echo $this->get_field_name('code7'); ?>" type="text" value="<?php echo $code7; ?>" /></label></p>
			<p><label for="<?php echo $this->get_field_id('code8'); ?>"><?php _e('item code', 'usces'); ?>8 : <input class="widefat" id="<?php echo $this->get_field_id('code8'); ?>" name="<?php echo $this->get_field_name('code8'); ?>" type="text" value="<?php echo $code8; ?>" /></label></p>
			<p><label for="<?php echo $this->get_field_id('code9'); ?>"><?php _e('item code', 'usces'); ?>9 : <input class="widefat" id="<?php echo $this->get_field_id('code9'); ?>" name="<?php echo $this->get_field_name('code9'); ?>" type="text" value="<?php echo $code9; ?>" /></label></p>
			<p><label for="<?php echo $this->get_field_id('code10'); ?>"><?php _e('item code', 'usces'); ?>10 : <input class="widefat" id="<?php echo $this->get_field_id('code10'); ?>" name="<?php echo $this->get_field_name('code10'); ?>" type="text" value="<?php echo $code10; ?>" /></label></p>
			</fieldset>
        <?php 
    }

}
?>
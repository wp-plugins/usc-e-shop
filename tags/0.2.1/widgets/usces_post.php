<?php

/**
 * Welcart_post Class
 */
class Welcart_post extends WP_Widget {
    /** constructor */
    function Welcart_post() {
        parent::WP_Widget(false, $name = 'Welcartポスト');	
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {		
        extract( $args );
        $title = $instance['title'] == '' ? 'Welcartポスト' : $instance['title'];
        $rows_num = $instance['rows_num'] == '' ? 3 : $instance['rows_num'];
        $icon = $instance['icon'] == '' ? 1 : (int)$instance['icon'];
		//if($icon == 1) $before_title = '<div class="widget_title"><img src="' . USCES_PLUGIN_URL . '/images/infomation.png" alt="' . $title . '" width="24" height="24" />';
		if($icon == 1) $before_title .= '<img src="' . USCES_PLUGIN_URL . '/images/diary.png" alt="' . $title . '" width="24" height="24" />';
		?>
              <?php echo $before_widget; ?>
                  <?php echo $before_title
                      . wp_specialchars($title)
                      . $after_title; ?>
					  
		<ul class="ucart_widget_body">
		<?php usces_list_post( $instance['category'], $rows_num ) ; ?>
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
        $title = $instance['title'] == '' ? 'Welcartポスト' : esc_attr($instance['title']);
        $rows_num = $instance['rows_num'] == '' ? 3 : esc_attr($instance['rows_num']);
		$icon = $instance['icon'] == '' ? 1 : (int)$instance['icon'];
        ?>
            <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
            <p><label for="<?php echo $this->get_field_id('icon'); ?>">アイコンの示数: <select class="widefat" id="<?php echo $this->get_field_id('icon'); ?>" name="<?php echo $this->get_field_name('icon'); ?>"><option value="1"<?php if($icon == 1){echo ' selected="selected"';} ?>>表示する</option><option value="2"<?php if($icon == 2){echo ' selected="selected"';} ?>>表示しない</option></select></label></p>
            <p><label for="<?php echo $this->get_field_id('category'); ?>">カテゴリー・スラッグ <input class="widefat" id="<?php echo $this->get_field_id('rows_num'); ?>" name="<?php echo $this->get_field_name('category'); ?>" type="text" value="<?php echo $instance['category']; ?>" /></label></p>
            <p><label for="<?php echo $this->get_field_id('rows_num'); ?>">表示数 <input class="widefat" id="<?php echo $this->get_field_id('rows_num'); ?>" name="<?php echo $this->get_field_name('rows_num'); ?>" type="text" value="<?php echo $rows_num; ?>" /></label></p>
        <?php 
    }

}
?>
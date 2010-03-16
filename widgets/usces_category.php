<?php

/**
 * Welcart_category Class
 */
class Welcart_category extends WP_Widget {
    /** constructor */
    function Welcart_category() {
        parent::WP_Widget(false, $name = 'Welcart '.__('Categories', 'usces'));	
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {		
        extract( $args );
        $title = $instance['title'] == '' ? 'Welcart '.__('Categories', 'usces') : $instance['title'];
        $cat_slug = $instance['cat_slug'] == '' ? 'itemgenre' : $instance['cat_slug'];
        $icon = $instance['icon'] == '' ? 1 : (int)$instance['icon'];
		//if($icon == 1) $before_title = '<div class="widget_title"><img src="' . USCES_PLUGIN_URL . '/images/category2.png" alt="' . $title . '" width="24" height="24" />';
		$img_path = file_exists(get_template_directory().'/images/category.png') ? get_template_directory_uri().'/images/category.png' : USCES_FRONT_PLUGIN_URL . '/images/category.png';
		if($icon == 1) $before_title .= '<img src="' . $img_path . '" alt="' . $title . '" />';
        ?>
              <?php echo $before_widget; ?>
                  <?php echo $before_title
                      . wp_specialchars($title)
                      . $after_title; ?>
					  
		<ul class="ucart_widget_body">
		<?php $cats = get_category_by_slug($cat_slug); ?>
		<?php wp_list_categories('orderby=id&use_desc_for_title=0&child_of='.$cats->term_id.'&title_li='); ?>
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
        $title = $instance['title'] == '' ? 'Welcart '.__('Categories', 'usces') : esc_attr($instance['title']);
        $cat_slug = $instance['cat_slug'] == '' ? 'itemgenre' : esc_attr($instance['cat_slug']);
		$icon = $instance['icon'] == '' ? 1 : (int)$instance['icon'];
        ?>
            <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
			<p><label for="<?php echo $this->get_field_id('icon'); ?>"><?php _e('display of icon', 'usces'); ?>: <select class="widefat" id="<?php echo $this->get_field_id('icon'); ?>" name="<?php echo $this->get_field_name('icon'); ?>"><option value="1"<?php if($icon == 1){echo ' selected="selected"';} ?>><?php _e('Indication', 'usces'); ?></option><option value="2"<?php if($icon == 2){echo ' selected="selected"';} ?>><?php _e('Non-indication', 'usces'); ?></option></select></label></p>
            <p><label for="<?php echo $this->get_field_id('cat_slug'); ?>"><?php _e('parent category(slug)', 'usces'); ?> <input class="widefat" id="<?php echo $this->get_field_id('cat_slug'); ?>" name="<?php echo $this->get_field_name('cat_slug'); ?>" type="text" value="<?php echo $cat_slug; ?>" /></label></p>
        <?php 
    }

}
?>
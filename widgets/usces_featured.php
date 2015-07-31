<?php

/**
 * Welcart_featured Class
 */
class Welcart_featured extends WP_Widget {
    /** constructor */
    function __construct() {
        parent::__construct(false, $name = 'Welcart '.__('Items recommended', 'usces'));	
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {	
		global $usces;
        extract( $args );
        $title = ( !isset($instance['title']) || WCUtils::is_blank($instance['title'])) ? 'Welcart '.__('Items recommended', 'usces') : $instance['title'];
        $icon = ( !isset($instance['icon']) || WCUtils::is_blank($instance['icon'])) ? 1 : (int)$instance['icon'];
        $num = ( !isset($instance['num']) || WCUtils::is_blank($instance['num'])) ? 1 : (int)$instance['num'];
		//if($icon == 1) $before_title = '<div class="widget_title"><img src="' . USCES_PLUGIN_URL . '/images/osusume.png" alt="' . $title . '" width="24" height="24" />';
		$img_path = file_exists(get_stylesheet_directory().'/images/osusume.png') ? get_stylesheet_directory_uri().'/images/osusume.png' : USCES_FRONT_PLUGIN_URL . '/images/osusume.png';
		if($icon == 1) $before_title .= '<img src="' . $img_path . '" alt="' . $title . '" />';
        ?>
              <?php echo $before_widget; ?>
                  <?php echo $before_title
                      . esc_html($title)
                      . $after_title; ?>
					  
		<ul class="ucart_featured_body ucart_widget_body">
			<?php
//			$offset = usces_posts_random_offset(get_posts('category='.usces_get_cat_id( 'itemreco' )));
//			$myposts = get_posts('numberposts=' . $num . '&offset='.$offset.'&category='.usces_get_cat_id( 'itemreco' ));
			$myposts = get_posts('numberposts=' . $num . '&category='.usces_get_cat_id( 'itemreco' ) . '&orderby=rand');
			$list_index = 0;
			foreach($myposts as $post) :
				$post_id = $post->ID;
			?>
				<li class="featured_list<?php echo ((1 === (int)$num) ? ' featured_single' : ''); ?><?php echo apply_filters('usces_filter_featured_list_class', NULL, $list_index, $num); ?>">
			<?php
				$list = '<div class="thumimg"><a href="' . get_permalink($post_id) . '">' . usces_the_itemImage(0, 150, 150, $post, 'return' ) . "</a></div>\n";
				$list .= '<div class="thumtitle"><a href="' . get_permalink($post_id) . '" rel="bookmark">' . $usces->getItemName($post_id) . '&nbsp;(' . $usces->getItemCode($post_id) . ")</a></div>\n";
				echo apply_filters( 'usces_filter_featured_widget', $list, $post, $list_index, $instance );
			?>
				</li>
			<?php $list_index++; endforeach; ?>
		</ul>
				  
              <?php echo $after_widget; ?>
        <?php
		wp_reset_postdata();
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {				
        return $new_instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {				
        $title = ( !isset($instance['title']) || WCUtils::is_blank($instance['title'])) ? 'Welcart '.__('Items recommended', 'usces') : esc_attr($instance['title']);
		$icon = ( !isset($instance['icon']) || WCUtils::is_blank($instance['icon'])) ? 1 : (int)$instance['icon'];
        $num = ( !isset($instance['num']) || WCUtils::is_blank($instance['num'])) ? 1 : (int)$instance['num'];
        ?>
            <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
			<p><label for="<?php echo $this->get_field_id('icon'); ?>"><?php _e('display of icon', 'usces'); ?>: <select class="widefat" id="<?php echo $this->get_field_id('icon'); ?>" name="<?php echo $this->get_field_name('icon'); ?>"><option value="1"<?php if($icon == 1){echo ' selected="selected"';} ?>><?php _e('Indication', 'usces'); ?></option><option value="2"<?php if($icon == 2){echo ' selected="selected"';} ?>><?php _e('Non-indication', 'usces'); ?></option></select></label></p>
            <p><label for="<?php echo $this->get_field_id('num'); ?>"><?php _e('number of indication', 'usces'); ?> <input class="widefat" id="<?php echo $this->get_field_id('num'); ?>" name="<?php echo $this->get_field_name('num'); ?>" type="text" value="<?php echo $num; ?>" /></label></p>
        <?php 
    }

}
?>
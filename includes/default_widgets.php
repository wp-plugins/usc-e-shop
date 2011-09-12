<?php
/***********************************************************
* Product Widgets
***********************************************************/
/**
 * Archives widget class
 *
 * @since 2.8.0
 */
$usces_product_flag = false;
class Welcart_Widget_Archives extends WP_Widget {
	function __construct() {
		$widget_ops = array('classname' => 'widget_archive', 'description' => __( 'A monthly archive of your site&#8217;s products') );
		parent::__construct('product-archives', __('Product Archives'), $widget_ops);
	}

	function widget( $args, $instance ) {
		global $usces_product_flag;

		$usces_product_flag = true;
		extract($args);
		$c = $instance['count'] ? '1' : '0';
		$d = $instance['dropdown'] ? '1' : '0';
		$title = apply_filters('widget_title', empty($instance['title']) ? __('Product Archives') : $instance['title'], $instance, $this->id_base);
		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;

		if ( $d ) {
?>
		<select name="archive-dropdown" onchange='document.location.href=this.options[this.selectedIndex].value;'> <option value=""><?php echo esc_attr(__('Select Month')); ?></option> <?php wp_get_archives(apply_filters('widget_archives_dropdown_args', array('post_type' => WELCART_PRODUCT, 'type' => 'monthly', 'format' => 'option', 'show_post_count' => $c))); ?> </select>
<?php
		} else {
?>
		<ul>
		<?php wp_get_archives(apply_filters('widget_archives_args', array('post_type' => WELCART_PRODUCT, 'type' => 'monthly', 'show_post_count' => $c))); ?>
		</ul>
<?php
		}

		echo $after_widget;
		$usces_product_flag = false;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$new_instance = wp_parse_args( (array) $new_instance, array( 'title' => '', 'count' => 0, 'dropdown' => '') );
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['count'] = $new_instance['count'] ? 1 : 0;
		$instance['dropdown'] = $new_instance['dropdown'] ? 1 : 0;

		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'count' => 0, 'dropdown' => '') );
		$title = strip_tags($instance['title']);
		$count = $instance['count'] ? 'checked="checked"' : '';
		$dropdown = $instance['dropdown'] ? 'checked="checked"' : '';
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>
		<p>
			<input class="checkbox" type="checkbox" <?php echo $dropdown; ?> id="<?php echo $this->get_field_id('dropdown'); ?>" name="<?php echo $this->get_field_name('dropdown'); ?>" /> <label for="<?php echo $this->get_field_id('dropdown'); ?>"><?php _e('Display as dropdown'); ?></label>
			<br/>
			<input class="checkbox" type="checkbox" <?php echo $count; ?> id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>" /> <label for="<?php echo $this->get_field_id('count'); ?>"><?php _e('Show post counts'); ?></label>
		</p>
<?php
	}
}

/**
 * Search widget class
 *
 * @since 2.8.0
 */
class Welcart_Widget_Search extends WP_Widget {

	function __construct() {
		$widget_ops = array('classname' => 'widget_search', 'description' => __( "A search form for product") );
		parent::__construct('product-search', __('Product Search'), $widget_ops);
	}

	function widget( $args, $instance ) {
		extract($args);
		$title = apply_filters('widget_title', $instance['title'], $instance, $this->id_base);

		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;

		$form = '<form role="search" method="get" id="searchform" action="' . home_url( '/' ) . '" >
			<div><label class="screen-reader-text" for="s">' . __('Search for:') . '</label>
			<input type="text" value="' . get_search_query() . '" name="s" id="s" />
			<input type="hidden" value="' . WELCART_PRODUCT .'" name="post_type" />
			<input type="submit" id="searchsubmit" value="'. esc_attr__('Search') .'" />
			</div>
			</form>';

		echo $form;
		echo $after_widget;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '') );
		$title = $instance['title'];
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$new_instance = wp_parse_args((array) $new_instance, array( 'title' => ''));
		$instance['title'] = strip_tags($new_instance['title']);
		return $instance;
	}

}

/**
 * Categories widget class
 *
 * @since 2.8.0
 */
class Welcart_Widget_Categories extends WP_Widget {

	function __construct() {
		$widget_ops = array( 'classname' => 'widget_categories', 'description' => __( "A list or dropdown of taxonomies" ) );
		parent::__construct('product-categories', __('Product Categories'), $widget_ops);
	}

	function widget( $args, $instance ) {
		extract( $args );

		$title = apply_filters('widget_title', empty( $instance['title'] ) ? __( 'Product Categories' ) : $instance['title'], $instance, $this->id_base);
		$c = $instance['count'] ? '1' : '0';
		$h = $instance['hierarchical'] ? '1' : '0';
		$d = $instance['dropdown'] ? '1' : '0';

		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;

		$cat_args = array('orderby' => 'name', 'show_count' => $c, 'hierarchical' => $h);

		if ( $d ) {
			$cat_args['show_option_none'] = __('Select Category');
			wp_dropdown_categories(apply_filters('usces_filter_widget_categories_dropdown_args', $cat_args));
?>

<script type='text/javascript'>
/* <![CDATA[ */
	var dropdown = document.getElementById("cat");
	function onCatChange() {
		if ( dropdown.options[dropdown.selectedIndex].value > 0 ) {
			location.href = "<?php echo home_url(); ?>/?cat="+dropdown.options[dropdown.selectedIndex].value;
		}
	}
	dropdown.onchange = onCatChange;
/* ]]> */
</script>

<?php
		} else {
?>
		<ul>
<?php
		$cat_args['title_li'] = '';
		wp_list_categories(apply_filters('usces_filter_widget_categories_args', $cat_args));
?>
		</ul>
<?php
		}

		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['count'] = !empty($new_instance['count']) ? 1 : 0;
		$instance['hierarchical'] = !empty($new_instance['hierarchical']) ? 1 : 0;
		$instance['dropdown'] = !empty($new_instance['dropdown']) ? 1 : 0;

		return $instance;
	}

	function form( $instance ) {
		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 'title' => '') );
		$title = esc_attr( $instance['title'] );
		$count = isset($instance['count']) ? (bool) $instance['count'] :false;
		$hierarchical = isset( $instance['hierarchical'] ) ? (bool) $instance['hierarchical'] : false;
		$dropdown = isset( $instance['dropdown'] ) ? (bool) $instance['dropdown'] : false;
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>

		<p><input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('dropdown'); ?>" name="<?php echo $this->get_field_name('dropdown'); ?>"<?php checked( $dropdown ); ?> />
		<label for="<?php echo $this->get_field_id('dropdown'); ?>"><?php _e( 'Display as dropdown' ); ?></label><br />

		<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>"<?php checked( $count ); ?> />
		<label for="<?php echo $this->get_field_id('count'); ?>"><?php _e( 'Show post counts' ); ?></label><br />

		<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('hierarchical'); ?>" name="<?php echo $this->get_field_name('hierarchical'); ?>"<?php checked( $hierarchical ); ?> />
		<label for="<?php echo $this->get_field_id('hierarchical'); ?>"><?php _e( 'Show hierarchy' ); ?></label></p>
<?php
	}

}

/**
 * Recent_Posts widget class
 *
 * @since 2.8.0
 */
class Welcart_Widget_Recent_Posts extends WP_Widget {

	function __construct() {
		$widget_ops = array('classname' => 'widget_recent_entries', 'description' => __( "The most recent products") );
		parent::__construct('product-recent-posts', __('Recent Products'), $widget_ops);
		$this->alt_option_name = 'widget_recent_entries';

		add_action( 'save_post', array(&$this, 'flush_widget_cache') );
		add_action( 'deleted_post', array(&$this, 'flush_widget_cache') );
		add_action( 'switch_theme', array(&$this, 'flush_widget_cache') );
	}

	function widget($args, $instance) {
		$cache = wp_cache_get('widget_recent_posts', 'widget');

		if ( !is_array($cache) )
			$cache = array();

		if ( isset($cache[$args['widget_id']]) ) {
			echo $cache[$args['widget_id']];
			return;
		}

		ob_start();
		extract($args);

		$title = apply_filters('widget_title', empty($instance['title']) ? __('Recent Products') : $instance['title'], $instance, $this->id_base);
		if ( ! $number = absint( $instance['number'] ) )
 			$number = 10;

		$r = new WP_Query(array('post_type' => WELCART_PRODUCT, 'posts_per_page' => $number, 'no_found_rows' => true, 'post_status' => 'publish', 'ignore_sticky_posts' => true));
		if ($r->have_posts()) :
?>
		<?php echo $before_widget; ?>
		<?php if ( $title ) echo $before_title . $title . $after_title; ?>
		<ul>
		<?php  while ($r->have_posts()) : $r->the_post(); ?>
		<li><a href="<?php the_permalink() ?>" title="<?php echo esc_attr(get_the_title() ? get_the_title() : get_the_ID()); ?>"><?php if ( get_the_title() ) the_title(); else the_ID(); ?></a></li>
		<?php endwhile; ?>
		</ul>
		<?php echo $after_widget; ?>
<?php
		// Reset the global $the_post as this query will have stomped on it
		wp_reset_postdata();

		endif;

		$cache[$args['widget_id']] = ob_get_flush();
		wp_cache_set('widget_recent_posts', $cache, 'widget');
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['number'] = (int) $new_instance['number'];
		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset($alloptions['widget_recent_entries']) )
			delete_option('widget_recent_entries');

		return $instance;
	}

	function flush_widget_cache() {
		wp_cache_delete('widget_recent_posts', 'widget');
	}

	function form( $instance ) {
		$title = isset($instance['title']) ? esc_attr($instance['title']) : '';
		$number = isset($instance['number']) ? absint($instance['number']) : 5;
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>

		<p><label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of posts to show:'); ?></label>
		<input id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" size="3" /></p>
<?php
	}
}

/**
 * Recent_Comments widget class
 *
 * @since 2.8.0
 */
class Welcart_Widget_Recent_Comments extends WP_Widget {

	function __construct() {
		$widget_ops = array('classname' => 'widget_recent_comments', 'description' => __( 'The most recent comments for product' ) );
		parent::__construct('product-recent-comments', __('Product Recent Comments'), $widget_ops);
		$this->alt_option_name = 'widget_recent_comments';

		if ( is_active_widget(false, false, $this->id_base) )
			add_action( 'wp_head', array(&$this, 'recent_comments_style') );

		add_action( 'comment_post', array(&$this, 'flush_widget_cache') );
		add_action( 'transition_comment_status', array(&$this, 'flush_widget_cache') );
	}

	function recent_comments_style() {
		if ( ! current_theme_supports( 'widgets' ) // Temp hack #14876
			|| ! apply_filters( 'show_recent_comments_widget_style', true, $this->id_base ) )
			return;
		?>
	<style type="text/css">.recentcomments a{display:inline !important;padding:0 !important;margin:0 !important;}</style>
<?php
	}

	function flush_widget_cache() {
		wp_cache_delete('widget_recent_comments', 'widget');
	}

	function widget( $args, $instance ) {
		global $comments, $comment;

		$cache = wp_cache_get('widget_recent_comments', 'widget');

		if ( ! is_array( $cache ) )
			$cache = array();

		if ( isset( $cache[$args['widget_id']] ) ) {
			echo $cache[$args['widget_id']];
			return;
		}

 		extract($args, EXTR_SKIP);
 		$output = '';
 		$title = apply_filters('widget_title', empty($instance['title']) ? __('Product Recent Comments') : $instance['title']);

		if ( ! $number = absint( $instance['number'] ) )
 			$number = 5;

		$comments = get_comments( array( 'post_type' => WELCART_PRODUCT, 'number' => $number, 'status' => 'approve', 'post_status' => 'publish' ) );
		$output .= $before_widget;
		if ( $title )
			$output .= $before_title . $title . $after_title;

		$output .= '<ul id="recentcomments">';
		if ( $comments ) {
			foreach ( (array) $comments as $comment) {
				$output .=  '<li class="recentcomments">' . /* translators: comments widget: 1: comment author, 2: post link */ sprintf(_x('%1$s on %2$s', 'widgets'), get_comment_author_link(), '<a href="' . esc_url( get_comment_link($comment->comment_ID) ) . '">' . get_the_title($comment->comment_post_ID) . '</a>') . '</li>';
			}
 		}
		$output .= '</ul>';
		$output .= $after_widget;

		echo $output;
		$cache[$args['widget_id']] = $output;
		wp_cache_set('widget_recent_comments', $cache, 'widget');
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['number'] = absint( $new_instance['number'] );
		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset($alloptions['widget_recent_comments']) )
			delete_option('widget_recent_comments');

		return $instance;
	}

	function form( $instance ) {
		$title = isset($instance['title']) ? esc_attr($instance['title']) : '';
		$number = isset($instance['number']) ? absint($instance['number']) : 5;
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>

		<p><label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of comments to show:'); ?></label>
		<input id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" size="3" /></p>
<?php
	}
}

/**
 * Tag cloud widget class
 *
 * @since 2.8.0
 */
class Welcart_Widget_Tag_Cloud extends WP_Widget {

	function __construct() {
		$widget_ops = array( 'description' => __( "Your most used tags for product in cloud format") );
		parent::__construct('product-tag_cloud', __('Product Tag Cloud'), $widget_ops);
	}

	function widget( $args, $instance ) {
		extract($args);
		$current_taxonomy = $this->_get_current_taxonomy($instance);
		if ( !empty($instance['title']) ) {
			$title = $instance['title'];
		} else {
			if ( 'post_tag' == $current_taxonomy ) {
				$title = __('Product Tags');
			} else {
				$tax = get_taxonomy($current_taxonomy);
				$title = $tax->labels->name;
			}
		}
		$title = apply_filters('widget_title', $title, $instance, $this->id_base);

		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;
		echo '<div class="tagcloud">';
		wp_tag_cloud( apply_filters('widget_tag_cloud_args', array('taxonomy' => $current_taxonomy) ) );
		echo "</div>\n";
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance['title'] = strip_tags(stripslashes($new_instance['title']));
		$instance['taxonomy'] = stripslashes($new_instance['taxonomy']);
		return $instance;
	}

	function form( $instance ) {
		$current_taxonomy = $this->_get_current_taxonomy($instance);
?>
	<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:') ?></label>
	<input type="text" class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php if (isset ( $instance['title'])) {echo esc_attr( $instance['title'] );} ?>" /></p>
	<p><label for="<?php echo $this->get_field_id('taxonomy'); ?>"><?php _e('Taxonomy:') ?></label>
	<select class="widefat" id="<?php echo $this->get_field_id('taxonomy'); ?>" name="<?php echo $this->get_field_name('taxonomy'); ?>">
	<?php foreach ( get_object_taxonomies(WELCART_PRODUCT) as $taxonomy ) :
				$tax = get_taxonomy($taxonomy);
				if ( !$tax->show_tagcloud || empty($tax->labels->name) )
					continue;
	?>
		<option value="<?php echo esc_attr($taxonomy) ?>" <?php selected($taxonomy, $current_taxonomy) ?>><?php echo $tax->labels->name; ?></option>
	<?php endforeach; ?>
	</select></p><?php
	}

	function _get_current_taxonomy($instance) {
		if ( !empty($instance['taxonomy']) && taxonomy_exists($instance['taxonomy']) )
			return $instance['taxonomy'];

		return 'post_tag';
	}
}

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
						$disp_text = apply_filters('usces_widget_bestseller_manual_text', esc_html($post->post_title), $id);
						$list = '<li><a href="' . get_permalink($id) . '">' . $disp_text . '</a></li>' . "\n";
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
		$img_path = file_exists(get_template_directory().'/images/calendar.png') ? get_template_directory_uri().'/images/calendar.png' : USCES_FRONT_PLUGIN_URL . '/images/calendar.png';
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

/**
 * Welcart_featured Class
 */
class Welcart_featured extends WP_Widget {
    /** constructor */
    function Welcart_featured() {
        parent::WP_Widget(false, $name = 'Welcart '.__('Items recommended', 'usces'));	
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {	
		global $usces;
        extract( $args );
        $title = $instance['title'] == '' ? 'Welcart '.__('Items recommended', 'usces') : $instance['title'];
        $icon = $instance['icon'] == '' ? 1 : (int)$instance['icon'];
        $num = $instance['num'] == '' ? 1 : (int)$instance['num'];
		//if($icon == 1) $before_title = '<div class="widget_title"><img src="' . USCES_PLUGIN_URL . '/images/osusume.png" alt="' . $title . '" width="24" height="24" />';
		$img_path = file_exists(get_template_directory().'/images/osusume.png') ? get_template_directory_uri().'/images/osusume.png' : USCES_FRONT_PLUGIN_URL . '/images/osusume.png';
		if($icon == 1) $before_title .= '<img src="' . $img_path . '" alt="' . $title . '" />';
        ?>
              <?php echo $before_widget; ?>
                  <?php echo $before_title
                      . wp_specialchars($title)
                      . $after_title; ?>
					  
		<ul class="ucart_featured_body ucart_widget_body clearfix">
			<?php
//			$offset = usces_posts_random_offset(get_posts('category='.usces_get_cat_id( 'itemreco' )));
//			$myposts = get_posts('numberposts=' . $num . '&offset='.$offset.'&category='.usces_get_cat_id( 'itemreco' ));
//			$myposts = get_posts('numberposts=' . $num . '&category='.usces_get_cat_id( 'itemreco' ) . '&orderby=rand');
			$reco_ob = new wp_query(array(WELCART_GENRE=>'reco', 'posts_per_page'=>$num, 'post_status'=>'publish', 'orderby'=>'rand'));
			if ($reco_ob->have_posts()) : while ($reco_ob->have_posts()) : $reco_ob->the_post(); usces_the_item();
			?>
			<li class="thumbnail_box">
				<div class="thumimg"><a href="<?php the_permalink() ?>"><?php the_post_thumbnail(array(108,108));//usces_the_itemImage($number = 0, $width = 108, $height = 108 ); ?></a></div>
				<div class="thumtitle"><a href="<?php the_permalink() ?>"><?php usces_the_itemName(); ?>&nbsp;(<?php usces_the_itemCode(); ?>)</a></div>
			<?php if (usces_have_zaiko_anyone()) : ?>
				<div class="price"><?php usces_crform( usces_the_firstPrice('return'), true, false ); ?></div>
			<?php else: ?>
				<div class="status"><?php _e('Sold Out', 'usces'); ?></div>
			<?php endif; ?>
			</li>
			<?php endwhile; else: ?>
			<li id="nothing"><?php _e('Sorry, no posts matched your criteria.'); ?></li>
			<?php endif; wp_reset_query(); ?>
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
        $title = $instance['title'] == '' ? 'Welcart '.__('Items recommended', 'usces') : esc_attr($instance['title']);
		$icon = $instance['icon'] == '' ? 1 : (int)$instance['icon'];
        $num = $instance['num'] == '' ? 1 : (int)$instance['num'];
        ?>
            <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
			<p><label for="<?php echo $this->get_field_id('icon'); ?>"><?php _e('display of icon', 'usces'); ?>: <select class="widefat" id="<?php echo $this->get_field_id('icon'); ?>" name="<?php echo $this->get_field_name('icon'); ?>"><option value="1"<?php if($icon == 1){echo ' selected="selected"';} ?>><?php _e('Indication', 'usces'); ?></option><option value="2"<?php if($icon == 2){echo ' selected="selected"';} ?>><?php _e('Non-indication', 'usces'); ?></option></select></label></p>
            <p><label for="<?php echo $this->get_field_id('num'); ?>"><?php _e('number of indication', 'usces'); ?> <input class="widefat" id="<?php echo $this->get_field_id('num'); ?>" name="<?php echo $this->get_field_name('num'); ?>" type="text" value="<?php echo $num; ?>" /></label></p>
        <?php 
    }

}

/**
 * Welcart_login Class
 */
class Welcart_login extends WP_Widget {
    /** constructor */
    function Welcart_login() {
        parent::WP_Widget(false, $name = 'Welcart '.__('Log-in', 'usces'));	
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {		
        extract( $args );
        $title = $instance['title'] == '' ? 'Welcart '.__('Log-in', 'usces') : $instance['title'];
        $icon = $instance['icon'] == '' ? 1 : (int)$instance['icon'];
		//if($icon == 1) $before_title = '<div class="widget_title"><img src="' . USCES_PLUGIN_URL . '/images/find.png" alt="' . $title . '" width="24" height="24" />';
		$img_path = file_exists(get_template_directory().'/images/login.png') ? get_template_directory_uri().'/images/login.png' : USCES_FRONT_PLUGIN_URL . '/images/login.png';
		if($icon == 1) $before_title .= '<img src="' . $img_path . '" alt="' . $title . '" />';
        ?>
              <?php echo $before_widget; ?>
                  <?php echo $before_title
                      . wp_specialchars($title)
                      . $after_title; ?>
					  
		<ul class="ucart_login_body ucart_widget_body"><li>
		
		<div class="loginbox">
		
		<?php if ( ! usces_is_login() ) { ?>
		<form name="loginwidget" id="loginformw" action="<?php echo USCES_MEMBER_URL; ?>" method="post">
		<p>
		<label><?php _e('e-mail adress', 'usces'); ?><br />
		<input type="text" name="loginmail" id="loginmailw" class="loginmail" value="<?php usces_remembername(); ?>" size="20" /></label><br />
		<label><?php _e('password', 'usces'); ?><br />
		<input type="password" name="loginpass" id="loginpassw" class="loginpass" size="20" /></label><br />
		<label><input name="rememberme" type="checkbox" id="remembermew" value="forever" /> <?php _e('Remember Me', 'usces'); ?></label></p>
		<p class="submit">
		<input type="submit" name="member_login" id="member_loginw" value="<?php _e('Log-in', 'usces'); ?>" />
		</p>
		<?php echo apply_filters('usces_filter_login_inform', NULL); ?>
		</form>
		<a href="<?php echo USCES_LOSTMEMBERPASSWORD_URL; ?>" title="<?php _e('Pssword Lost and Found', 'usces'); ?>"><?php _e('Lost your password?', 'usces'); ?></a><br />
		<a href="<?php echo USCES_NEWMEMBER_URL; ?>" title="<?php _e('New enrollment for membership.', 'usces'); ?>"><?php _e('New enrollment for membership.', 'usces'); ?></a>
		<?php }else{ ?>
		<div><?php echo sprintf(__('Mr/Mrs %s', 'usces'), usces_the_member_name('return')); ?></div>
		<?php echo usces_loginout(); ?><br />
		<a href="<?php echo USCES_MEMBER_URL; ?>" class="login_widget_mem_info_a"><?php _e('Membership information','usces') ?></a>
		<?php } ?>
		</div>		
		

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
        $title = $instance['title'] == '' ? 'Welcart '.__('Log-in', 'usces') : esc_attr($instance['title']);
		$icon = $instance['icon'] == '' ? 1 : (int)$instance['icon'];
        ?>
            <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
			<p><label for="<?php echo $this->get_field_id('icon'); ?>"><?php _e('display of icon', 'usces'); ?>: <select class="widefat" id="<?php echo $this->get_field_id('icon'); ?>" name="<?php echo $this->get_field_name('icon'); ?>"><option value="1"<?php if($icon == 1){echo ' selected="selected"';} ?>><?php _e('Indication', 'usces'); ?></option><option value="2"<?php if($icon == 2){echo ' selected="selected"';} ?>><?php _e('Non-indication', 'usces'); ?></option></select></label></p>
        <?php 
    }

}

/**
 * Welcart_page Class
 */
class Welcart_page extends WP_Widget {
    /** constructor */
    function Welcart_page() {
        parent::WP_Widget(false, $name = 'Welcart '.__('Page', 'usces'));	
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {		
        extract( $args );
        $title = $instance['title'] == '' ? 'Welcart '.__('Page', 'usces') : $instance['title'];
        $rows_num = $instance['rows_num'] == '' ? 3 : $instance['rows_num'];
        $icon = $instance['icon'] == '' ? 1 : (int)$instance['icon'];
		//if($icon == 1) $before_title = '<div class="widget_title"><img src="' . USCES_PLUGIN_URL . '/images/diary.png" alt="' . $title . '" width="24" height="24" />';
		$img_path = file_exists(get_template_directory().'/images/page.png') ? get_template_directory_uri().'/images/page.png' : USCES_FRONT_PLUGIN_URL . '/images/page.png';
		if($icon == 1) $before_title .= '<img src="' . $img_path . '" alt="' . $title . '" />';
        ?>
              <?php echo $before_widget; ?>
                  <?php echo $before_title
                      . wp_specialchars($title)
                      . $after_title; ?>
					  
		<ul class="ucart_widget_body">
		<?php wp_list_pages(apply_filters('usces_filter_wc_widget_page_arg', ('title_li=&include=' . $instance['page']), $instance['page'])) ; ?>
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
        $title = $instance['title'] == '' ? 'Welcart '.__('Page', 'usces') : esc_attr($instance['title']);
        $rows_num = $instance['rows_num'] == '' ? 3 : esc_attr($instance['rows_num']);
		$icon = $instance['icon'] == '' ? 1 : (int)$instance['icon'];
        ?>
            <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
			<p><label for="<?php echo $this->get_field_id('icon'); ?>"><?php _e('display of icon', 'usces'); ?>: <select class="widefat" id="<?php echo $this->get_field_id('icon'); ?>" name="<?php echo $this->get_field_name('icon'); ?>"><option value="1"<?php if($icon == 1){echo ' selected="selected"';} ?>><?php _e('Indication', 'usces'); ?></option><option value="2"<?php if($icon == 2){echo ' selected="selected"';} ?>><?php _e('Non-indication', 'usces'); ?></option></select></label></p>
            <p><label for="<?php echo $this->get_field_id('page'); ?>"><?php _e('Page ID(comma separate)', 'usces'); ?>: <input class="widefat" id="<?php echo $this->get_field_id('page'); ?>" name="<?php echo $this->get_field_name('page'); ?>" type="text" value="<?php echo $instance['page']; ?>" /></label></p>
            <p><label for="<?php echo $this->get_field_id('rows_num'); ?>"><?php _e('number of indication', 'usces'); ?> <input class="widefat" id="<?php echo $this->get_field_id('rows_num'); ?>" name="<?php echo $this->get_field_name('rows_num'); ?>" type="text" value="<?php echo $rows_num; ?>" /></label></p>
        <?php 
    }

}

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
        $title = $instance['title'] == '' ? 'Welcart '.__('Post', 'usces') : $instance['title'];
        $rows_num = $instance['rows_num'] == '' ? 3 : $instance['rows_num'];
        $icon = $instance['icon'] == '' ? 1 : (int)$instance['icon'];
		//if($icon == 1) $before_title = '<div class="widget_title"><img src="' . USCES_PLUGIN_URL . '/images/infomation.png" alt="' . $title . '" width="24" height="24" />';
		$img_path = file_exists(get_template_directory().'/images/post.png') ? get_template_directory_uri().'/images/post.png' : USCES_FRONT_PLUGIN_URL . '/images/post.png';
		if($icon == 1) $before_title .= '<img src="' . $img_path . '" alt="' . $title . '" />';
		?>
              <?php echo $before_widget; ?>
                  <?php echo $before_title
                      . apply_filters( 'usces_filter_post_widget_title', esc_html($title), $instance)
                      . $after_title; ?>
					  
		<ul class="ucart_widget_body">
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
        $title = $instance['title'] == '' ? 'Welcart '.__('Post', 'usces') : esc_attr($instance['title']);
        $rows_num = $instance['rows_num'] == '' ? 3 : esc_attr($instance['rows_num']);
		$icon = $instance['icon'] == '' ? 1 : (int)$instance['icon'];
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
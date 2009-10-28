<?php

/**
 * Welcart_login Class
 */
class Welcart_login extends WP_Widget {
    /** constructor */
    function Welcart_login() {
        parent::WP_Widget(false, $name = 'Welcartログイン');	
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {		
        extract( $args );
        $title = $instance['title'] == '' ? 'Welcartログイン' : $instance['title'];
        $icon = $instance['icon'] == '' ? 1 : (int)$instance['icon'];
		//if($icon == 1) $before_title = '<div class="widget_title"><img src="' . USCES_PLUGIN_URL . '/images/find.png" alt="' . $title . '" width="24" height="24" />';
		if($icon == 1) $before_title .= '<img src="' . USCES_PLUGIN_URL . '/images/find.png" alt="' . $title . '" width="24" height="24" />';
        ?>
              <?php echo $before_widget; ?>
                  <?php echo $before_title
                      . wp_specialchars($title)
                      . $after_title; ?>
					  
		<ul class="ucart_login_body ucart_widget_body"><li>
		
		<div class="loginbox">
		
		<?php if ( ! usces_is_login() ) { ?>
		<form name="loginwidget" id="loginform" action="<?php echo USCES_MEMBER_URL; ?>" method="post">
		<p>
		<label>メールアドレス<br />
		<input type="text" name="loginmail" id="loginmail" class="loginmail" value="<?php echo usces_remembername('return'); ?>" size="20" tabindex="10" /></label><br />
		<label>パスワード<br />
		<input type="password" name="loginpass" id="loginpass" class="loginpass" value="<?php echo usces_rememberpass('return'); ?>" size="20" tabindex="20" /></label><br />
		<label><input name="rememberme" type="checkbox" id="rememberme" value="forever" tabindex="90"<?php echo usces_remembercheck('return'); ?> /> ログイン情報を記憶</label></p>
		<p class="submit">
		<input type="submit" name="member_login" id="member_login" value="ログイン" tabindex="100" />
		</p>
		</form>
		<a href="<?php echo USCES_MEMBER_URL; ?>&page=lostmemberpassword" title="パスワード紛失取り扱い">パスワードをお忘れですか？</a><br />
		<a href="<?php echo USCES_MEMBER_URL; ?>&page=newmember" title="新規ご入会はこちら">新規ご入会はこちら</a>
		<?php }else{ ?>
		<?php echo usces_the_member_name().' 様'; ?><br />
		<?php echo usces_loginout(); ?><br />
		<a href="<?php echo USCES_MEMBER_URL; ?>"><?php _e('mombership information','usces') ?></a>
		<?php } ?>
		</div>		
		
		
		
		
		
		
<!--		<form method="get" id="searchform" action="<?php bloginfo('home'); ?>" >
		<input type="text" value="" name="s" id="s" class="searchtext" /><input type="submit" id="searchsubmit" value="検索" />
		<div><a href="<?php echo USCES_CART_URL; ?>&page=search_item">商品カテゴリー複合検索&gt;</a></div>
		</form>
-->		</li></ul>
				  
              <?php echo $after_widget; ?>
        <?php
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {				
        return $new_instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {				
        $title = $instance['title'] == '' ? 'Welcartログイン' : esc_attr($instance['title']);
		$icon = $instance['icon'] == '' ? 1 : (int)$instance['icon'];
        ?>
            <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
			<p><label for="<?php echo $this->get_field_id('icon'); ?>">アイコンの示数: <select class="widefat" id="<?php echo $this->get_field_id('icon'); ?>" name="<?php echo $this->get_field_name('icon'); ?>"><option value="1"<?php if($icon == 1){echo ' selected="selected"';} ?>>表示する</option><option value="2"<?php if($icon == 2){echo ' selected="selected"';} ?>>表示しない</option></select></label></p>
        <?php 
    }

}
?>
<?php
$status = $this->action_status;
$message = $this->action_message;
$this->action_status = 'none';
$this->action_message = '';
$mail_datas = $this->options['mail_data'];
$smtp_hostname = $this->options['smtp_hostname'];
?>
<script type="text/javascript">
jQuery(function($){
<?php if($status == 'success'){ ?>
			$("#anibox").animate({ backgroundColor: "#ECFFFF" }, 2000);
<?php }else if($status == 'caution'){ ?>
			$("#anibox").animate({ backgroundColor: "#FFF5CE" }, 2000);
<?php }else if($status == 'error'){ ?>
			$("#anibox").animate({ backgroundColor: "#FFE6E6" }, 2000);
<?php } ?>

	$("#aAdditionalURLs").click(function () {
		$("#AdditionalURLs").toggle();
	});
});

function toggleVisibility(id) {
   var e = document.getElementById(id);
   if(e.style.display == 'block')
	  e.style.display = 'none';
   else
	  e.style.display = 'block';
}
</script>
<div class="wrap">
<div class="usces_admin">
<h2>Welcart Shop メール設定<?php //echo __('USC e-Shop Options','usces'); ?></h2>
<div id="aniboxStatus" class="<?php echo $status; ?>">
	<div id="anibox" class="clearfix">
		<img src="<?php echo USCES_PLUGIN_URL; ?>/images/list_message_<?php echo $status; ?>.gif" />
		<div class="mes" id="info_massage"><?php echo $message; ?></div>
	</div>
</div>
<form action="" method="post" name="option_form" id="option_form">
<input name="usces_option_update" type="submit" class="button" value="設定を更新" />
<div id="poststuff" class="metabox-holder">

<div class="postbox">
<h3 class="hndle"><span>SMTPサーバーホスト</span><a style="cursor:pointer;" onclick="toggleVisibility('ex_smtp_host');">（説明）</a></h3>
<div class="inside">
<table class="form_table">
	<tr>
	    <th width="150">ホスト名</th>
	    <td><input name="smtp_hostname" id="smtp_hostname" type="text" class="mail_title" value="<?php echo $smtp_hostname; ?>" /></td>
		<td>&nbsp;</td>
	</tr>
</table>
<hr size="1" color="#CCCCCC" />
<div id="ex_smtp_host" class="explanation">メール送信用サーバーのホスト名を設定します。localhost で送信ができない場合はSMTPサーバーが必要になります。</div>
</div>
</div><!--postbox-->

<div class="postbox">
<h3 class="hndle"><span>サンキューメール（自動送信）</span><a style="cursor:pointer;" onclick="toggleVisibility('ex_thakyou_mail');">（説明）</a></h3>
<div class="inside">
<table class="form_table">
	<tr>
	    <th width="150">タイトル</th>
	    <td><input name="title[thankyou]" id="title[thankyou]" type="text" class="mail_title" value="<?php echo $mail_datas['title']['thankyou']; ?>" /></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
	    <th>ヘッダ</th>
	    <td><textarea name="header[thankyou]" id="header[thankyou]" class="mail_header"><?php echo $mail_datas['header']['thankyou']; ?></textarea></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
	    <th>フッタ</th>
	    <td><textarea name="footer[thankyou]" id="footer[thankyou]" class="mail_footer"/><?php echo $mail_datas['footer']['thankyou']; ?></textarea></td>
		<td>&nbsp;</td>
	</tr>
</table>
<hr size="1" color="#CCCCCC" />
<div id="ex_thakyou_mail" class="explanation">受注時にお客様に対して自動送信するメールです。</div>
</div>
</div><!--postbox-->

<div class="postbox">
<h3 class="hndle"><span>受注メール（自動送信）</span><a style="cursor:pointer;" onclick="toggleVisibility('ex_order_mail');">（説明）</a></h3>
<div class="inside">
<table class="form_table">
	<tr>
	    <th width="150">タイトル</th>
	    <td><input name="title[order]" id="title[order]" type="text" class="mail_title" value="<?php echo $mail_datas['title']['order']; ?>" /></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
	    <th>ヘッダ</th>
	    <td><textarea name="header[order]" id="header[order]" class="mail_header"><?php echo $mail_datas['header']['order']; ?></textarea></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
	    <th>フッタ</th>
	    <td><textarea name="footer[order]" id="footer[order]" class="mail_footer"/><?php echo $mail_datas['footer']['order']; ?></textarea></td>
		<td>&nbsp;</td>
	</tr>
</table>
<hr size="1" color="#CCCCCC" />
<div id="ex_order_mail" class="explanation">受注時に受注用メールアドレス（<?php echo $this->options['order_mail']; ?>）に対して送信するメールです。</div>
</div>
</div><!--postbox-->

<div class="postbox">
<h3 class="hndle"><span>問い合わせ受付メール（自動送信）</span><a style="cursor:pointer;" onclick="toggleVisibility('ex_inquiry_mail');">（説明）</a></h3>
<div class="inside">
<table class="form_table">
	<tr>
	    <th width="150">タイトル</th>
	    <td><input name="title[inquiry]" id="title[inquiry]" type="text" class="mail_title" value="<?php echo $mail_datas['title']['inquiry']; ?>" /></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
	    <th>ヘッダ</th>
	    <td><textarea name="header[inquiry]" id="header[inquiry]" class="mail_header"><?php echo $mail_datas['header']['inquiry']; ?></textarea></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
	    <th>フッタ</th>
	    <td><textarea name="footer[inquiry]" id="footer[inquiry]" class="mail_footer"/><?php echo $mail_datas['footer']['inquiry']; ?></textarea></td>
		<td>&nbsp;</td>
	</tr>
</table>
<hr size="1" color="#CCCCCC" />
<div id="ex_inquiry_mail" class="explanation">問い合わせ時に、お客様宛てに自動送信するメールです。</div>
</div>
</div><!--postbox-->

<div class="postbox">
<h3 class="hndle"><span>入会完了のご連絡メール（自動送信）</span><a style="cursor:pointer;" onclick="toggleVisibility('ex_membercomp_mail');">（説明）</a></h3>
<div class="inside">
<table class="form_table">
	<tr>
	    <th width="150">タイトル</th>
	    <td><input name="title[membercomp]" id="title[membercomp]" type="text" class="mail_title" value="<?php echo $mail_datas['title']['membercomp']; ?>" /></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
	    <th>ヘッダ</th>
	    <td><textarea name="header[membercomp]" id="header[membercomp]" class="mail_header"><?php echo $mail_datas['header']['membercomp']; ?></textarea></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
	    <th>フッタ</th>
	    <td><textarea name="footer[membercomp]" id="footer[membercomp]" class="mail_footer"/><?php echo $mail_datas['footer']['membercomp']; ?></textarea></td>
		<td>&nbsp;</td>
	</tr>
</table>
<hr size="1" color="#CCCCCC" />
<div id="ex_membercomp_mail" class="explanation">会員登録が完了した際に自動送信されるメールです。</div>
</div>
</div><!--postbox-->


<div class="postbox">
<h3 class="hndle"><span>発送完了メール（管理画面より送信）</span><a style="cursor:pointer;" onclick="toggleVisibility('ex_completionmail_mail');">（説明）</a></h3>
<div class="inside">
<table class="form_table">
	<tr>
	    <th width="150">タイトル</th>
	    <td><input name="title[completionmail]" id="title[completionmail]" type="text" class="mail_title" value="<?php echo $mail_datas['title']['completionmail']; ?>" /></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
	    <th>ヘッダ</th>
	    <td><textarea name="header[completionmail]" id="header[completionmail]" class="mail_header"><?php echo $mail_datas['header']['completionmail']; ?></textarea></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
	    <th>フッタ</th>
	    <td><textarea name="footer[completionmail]" id="footer[completionmail]" class="mail_footer"/><?php echo $mail_datas['footer']['completionmail']; ?></textarea></td>
		<td>&nbsp;</td>
	</tr>
</table>
<hr size="1" color="#CCCCCC" />
<div id="ex_completionmail_mail" class="explanation">管理画面より発送完了登録した際に手動送信するメール。</div>
</div>
</div><!--postbox-->

<div class="postbox">
<h3 class="hndle"><span>ご注文確認メール（管理画面より送信）</span><a style="cursor:pointer;" onclick="toggleVisibility('ex_ordermail_mail');">（説明）</a></h3>
<div class="inside">
<table class="form_table">
	<tr>
	    <th width="150">タイトル</th>
	    <td><input name="title[ordermail]" id="title[ordermail]" type="text" class="mail_title" value="<?php echo $mail_datas['title']['ordermail']; ?>" /></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
	    <th>ヘッダ</th>
	    <td><textarea name="header[ordermail]" id="header[ordermail]" class="mail_header"><?php echo $mail_datas['header']['ordermail']; ?></textarea></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
	    <th>フッタ</th>
	    <td><textarea name="footer[ordermail]" id="footer[ordermail]" class="mail_footer"/><?php echo $mail_datas['footer']['ordermail']; ?></textarea></td>
		<td>&nbsp;</td>
	</tr>
</table>
<hr size="1" color="#CCCCCC" />
<div id="ex_ordermail_mail" class="explanation">管理画面より新規受注を登録した際に手動送信するメール。</div>
</div>
</div><!--postbox-->

<div class="postbox">
<h3 class="hndle"><span>ご注文内容変更の確認メール（管理画面より送信）</span><a style="cursor:pointer;" onclick="toggleVisibility('ex_changemail_mail');">（説明）</a></h3>
<div class="inside">
<table class="form_table">
	<tr>
	    <th width="150">タイトル</th>
	    <td><input name="title[changemail]" id="title[changemail]" type="text" class="mail_title" value="<?php echo $mail_datas['title']['changemail']; ?>" /></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
	    <th>ヘッダ</th>
	    <td><textarea name="header[changemail]" id="header[changemail]" class="mail_header"><?php echo $mail_datas['header']['changemail']; ?></textarea></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
	    <th>フッタ</th>
	    <td><textarea name="footer[changemail]" id="footer[changemail]" class="mail_footer"/><?php echo $mail_datas['footer']['changemail']; ?></textarea></td>
		<td>&nbsp;</td>
	</tr>
</table>
<hr size="1" color="#CCCCCC" />
<div id="ex_changemail_mail" class="explanation">管理画面より受注内容を変更した際に手動送信するメール。</div>
</div>
</div><!--postbox-->

<div class="postbox">
<h3 class="hndle"><span>ご入金確認のご連絡メール（管理画面より送信）</span><a style="cursor:pointer;" onclick="toggleVisibility('ex_receiptmail_mail');">（説明）</a></h3>
<div class="inside">
<table class="form_table">
	<tr>
	    <th width="150">タイトル</th>
	    <td><input name="title[receiptmail]" id="title[receiptmail]" type="text" class="mail_title" value="<?php echo $mail_datas['title']['receiptmail']; ?>" /></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
	    <th>ヘッダ</th>
	    <td><textarea name="header[receiptmail]" id="header[receiptmail]" class="mail_header"><?php echo $mail_datas['header']['receiptmail']; ?></textarea></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
	    <th>フッタ</th>
	    <td><textarea name="footer[receiptmail]" id="footer[receiptmail]" class="mail_footer"/><?php echo $mail_datas['footer']['receiptmail']; ?></textarea></td>
		<td>&nbsp;</td>
	</tr>
</table>
<hr size="1" color="#CCCCCC" />
<div id="ex_receiptmail_mail" class="explanation">振込み入金を確認した際に手動送信するメール。</div>
</div>
</div><!--postbox-->

<div class="postbox">
<h3 class="hndle"><span>お見積りメール（管理画面より送信）</span><a style="cursor:pointer;" onclick="toggleVisibility('ex_mitumorimail_mail');">（説明）</a></h3>
<div class="inside">
<table class="form_table">
	<tr>
	    <th width="150">タイトル</th>
	    <td><input name="title[mitumorimail]" id="title[mitumorimail]" type="text" class="mail_title" value="<?php echo $mail_datas['title']['mitumorimail']; ?>" /></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
	    <th>ヘッダ</th>
	    <td><textarea name="header[mitumorimail]" id="header[mitumorimail]" class="mail_header"><?php echo $mail_datas['header']['mitumorimail']; ?></textarea></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
	    <th>フッタ</th>
	    <td><textarea name="footer[mitumorimail]" id="footer[mitumorimail]" class="mail_footer"/><?php echo $mail_datas['footer']['mitumorimail']; ?></textarea></td>
		<td>&nbsp;</td>
	</tr>
</table>
<hr size="1" color="#CCCCCC" />
<div id="ex_mitumorimail_mail" class="explanation">管理画面より見積り登録した際に手動送信するメール。</div>
</div>
</div><!--postbox-->

<div class="postbox">
<h3 class="hndle"><span>ご注文キャンセルの確認メール（管理画面より送信）</span><a style="cursor:pointer;" onclick="toggleVisibility('ex_cancelmail_mail');">（説明）</a></h3>
<div class="inside">
<table class="form_table">
	<tr>
	    <th width="150">タイトル</th>
	    <td><input name="title[cancelmail]" id="title[cancelmail]" type="text" class="mail_title" value="<?php echo $mail_datas['title']['cancelmail']; ?>" /></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
	    <th>ヘッダ</th>
	    <td><textarea name="header[cancelmail]" id="header[cancelmail]" class="mail_header"><?php echo $mail_datas['header']['cancelmail']; ?></textarea></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
	    <th>フッタ</th>
	    <td><textarea name="footer[cancelmail]" id="footer[cancelmail]" class="mail_footer"/><?php echo $mail_datas['footer']['cancelmail']; ?></textarea></td>
		<td>&nbsp;</td>
	</tr>
</table>
<hr size="1" color="#CCCCCC" />
<div id="ex_cancelmail_mail" class="explanation">受注をキャンセルした際に手動送信するメール。</div>
</div>
</div><!--postbox-->

<div class="postbox">
<h3 class="hndle"><span>その他のメール（管理画面より送信）</span><a style="cursor:pointer;" onclick="toggleVisibility('ex_othermail_mail');">（説明）</a></h3>
<div class="inside">
<table class="form_table">
	<tr>
	    <th width="150">タイトル</th>
	    <td><input name="title[othermail]" id="title[othermail]" type="text" class="mail_title" value="<?php echo $mail_datas['title']['othermail']; ?>" /></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
	    <th>ヘッダ</th>
	    <td><textarea name="header[othermail]" id="header[othermail]" class="mail_header"><?php echo $mail_datas['header']['othermail']; ?></textarea></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
	    <th>フッタ</th>
	    <td><textarea name="footer[othermail]" id="footer[othermail]" class="mail_footer"/><?php echo $mail_datas['footer']['othermail']; ?></textarea></td>
		<td>&nbsp;</td>
	</tr>
</table>
<hr size="1" color="#CCCCCC" />
<div id="ex_othermail_mail" class="explanation">臨時で送信するメール。</div>
</div>
</div><!--postbox-->



</div><!--poststuff-->
<input name="usces_option_update" type="submit" class="button" value="設定を更新" />
</form>
</div><!--usces_admin-->
</div><!--wrap-->
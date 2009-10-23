<?php

$zaiko_status = array('有り','在庫僅少','売切れ','入荷待ち','廃盤');
$management_status = array(
					'estimate' => '見積り',
					'adminorder' => '管理受注',
					'noreceipt' => '未入金',
					'receipted' => '入金済み',
					'duringorder' => '取り寄せ中',
					'cancel' => 'キャンセル',
					'completion' => '発送済み',
					);

$customer_status = array(
					'0' => '通常会員',
					'1' => '優良会員',
					'2' => 'VIP会員',
					'99' => '不良会員'
					);

$payment_structure = array(
					'acting' => '業者代行決済',
					'transferAdvance' => '振込み（前払い）',
					'transferDeferred' => '振込み（後払い）',
					'COD' => '代金引換',
					'installment' => '割賦払い'
					);

$display_mode = array(
					'Usualsale' => '通常営業中',
					'Promotionsale' => 'キャンペーン中',
					'Maintenancemode' => 'メンテナンス中'
					);

$shipping_rule = array(
					'0' => '--選択--',
					'1' => '即日',
					'2' => '1～2日後',
					'3' => '2～3日後',
					'4' => '3～5日後',
					'5' => '4～6日後',
					'6' => '約1週間後',
					'7' => '約2週間後',
					'8' => '約3週間後',
					'9' => '商品入荷後'
					);

//$shipping_charge_structure = array(
//					'1' => '通常料金',
//					'2' => '特別料金1',
//					'3' => '特別料金2'
//					);


$usces_pref = array(
				"-選択-",
				"北海道",
				"青森県",
				"岩手県",
				"宮城県",
				"秋田県",
				"山形県",
				"福島県",
				"茨城県",
				"栃木県",
				"群馬県",
				"埼玉県",
				"千葉県",
				"東京都",
				"神奈川県",
				"新潟県",
				"富山県",
				"石川県",
				"福井県",
				"山梨県",
				"長野県",
				"岐阜県",
				"静岡県",
				"愛知県",
				"三重県",
				"滋賀県",
				"京都府",
				"大阪府",
				"兵庫県",
				"奈良県",
				"和歌山県",
				"鳥取県",
				"島根県",
				"岡山県",
				"広島県",
				"山口県",
				"徳島県",
				"香川県",
				"愛媛県",
				"高知県",
				"福岡県",
				"佐賀県",
				"長崎県",
				"熊本県",
				"大分県",
				"宮崎県",
				"鹿児島県",
				"沖縄県"
				);


update_option('usces_management_status',$management_status);
update_option('usces_zaiko_status',$zaiko_status);
update_option('usces_customer_status',$customer_status);
update_option('usces_payment_structure',$payment_structure);
update_option('usces_display_mode',$display_mode);
update_option('usces_pref',$usces_pref);
update_option('usces_shipping_rule',$shipping_rule);
//update_option('shipping_charge_structure',$shipping_charge_structure);



$usces_op = get_option('usces');

$usces_op['mail_default']['title']['thankyou'] = "【ご注文内容の確認】";
$usces_op['mail_default']['title']['order'] = "【受注報告】";
$usces_op['mail_default']['title']['inquiry'] = "【お問合せを承りました】";
$usces_op['mail_default']['title']['returninq'] = "【お問合せの件】";
$usces_op['mail_default']['title']['membercomp'] = "【ご入会完了のご連絡】";
$usces_op['mail_default']['title']['completionmail'] = "【商品発送のご連絡】";
$usces_op['mail_default']['title']['ordermail'] = "【ご注文内容の確認】";
$usces_op['mail_default']['title']['changemail'] = "【ご注文内容変更の確認】";
$usces_op['mail_default']['title']['receiptmail'] = "【ご入金確認のご連絡】";
$usces_op['mail_default']['title']['mitumorimail'] = "【お見積り】";
$usces_op['mail_default']['title']['cancelmail'] = "【ご注文キャンセルの確認】";
$usces_op['mail_default']['title']['othermail'] = "【】";

$usces_op['mail_default']['header']['thankyou'] = "この度は" . get_option('blogname') . "をご利用下さいまして誠に有難うございます。\r\n下記の通りご注文をお受けいたしましたのでご確認をお願いいたします。\r\n\r\n商品の準備ができ次第、メールにて発送のご案内をさせていただきます。\r\nよろしくお願いいたします。\r\n\r\n";
$usces_op['mail_default']['header']['order'] = get_option('blogname') . "の注文が入りました。\r\n\r\n";
$usces_op['mail_default']['header']['inquiry'] = "この度は" . get_option('blogname') . "をご利用下さいまして誠に有難うございます。\r\n下記の通りお問合せをお受けいたしました。\r\n\r\n準備ができ次第、メールにてご返答させていただきます。\r\nしばらくお待ちください。\r\n\r\n";
$usces_op['mail_default']['header']['returninq'] = "";
$usces_op['mail_default']['header']['membercomp'] = "この度は" . get_option('blogname') . "の会員にご登録下さいまして誠に有難うございます。\r\n\r\n「会員情報」にて商品ご購入の履歴が確認できます。\r\n\r\n";
$usces_op['mail_default']['header']['completionmail'] = "本日、ご注文の商品を発送いたしました。\r\n配送業者は○○運輸となっております。\r\n一両日中には到着する予定ですが、万が一届かない場合はご連絡ください。\r\nよろしくお願いいたします。\r\n\r\nまたのご利用を待ちいたしております。\r\nありがとうございました\r\n";
$usces_op['mail_default']['header']['ordermail'] = "この度は" . get_option('blogname') . "をご利用下さいまして誠に有難うございます。\r\n下記の通りご注文をお受けいたしましたのでご確認をお願いいたします。\r\n\r\n商品の準備ができ次第、メールにて発送のご案内をさせていただきます。\r\nよろしくお願いいたします。\r\n\r\n";
$usces_op['mail_default']['header']['changemail'] = "この度は" . get_option('blogname') . "をご利用下さいまして誠に有難うございます。\r\n下記の通りご注文内容を変更いたしましたのでご確認をお願いいたします。\r\n\r\n商品の準備ができ次第、メールにて発送のご案内をさせていただきます。\r\nよろしくお願いいたします。\r\n\r\n";
$usces_op['mail_default']['header']['receiptmail'] = "この度は" . get_option('blogname') . "をご利用下さいまして誠に有難うございます。\r\nご入金の確認ができましたのでご連絡いたします。\r\n\r\n商品の準備ができ次第、メールにて発送のご案内をさせていただきます。\r\nよろしくお願いいたします。\r\n\r\n";
$usces_op['mail_default']['header']['mitumorimail'] = "この度は" . get_option('blogname') . "をご利用下さいまして誠に有難うございます。\r\n下記の通りお見積りいたしましたのでご確認をお願いいたします。\r\n\r\nお見積りの有効期限は一週間となっております。\r\nよろしくお願いいたします。\r\n\r\n";
$usces_op['mail_default']['header']['cancelmail'] = "この度は" . get_option('blogname') . "をご利用下さいまして誠に有難うございます。\r\nご注文のキャンセルを承りました。\r\n\r\n今後ともよろしくお願いいたします。\r\n\r\n";
$usces_op['mail_default']['header']['othermail'] = "この度は" . get_option('blogname') . "をご利用下さいまして誠に有難うございます。\r\n\r\n\r\n";

$usces_op['mail_default']['footer']['thankyou'] = "=============================================\r\n" . get_option('blogname') . "\r\n" . $usces_op['company_name'] . "\r\n" . "〒 " . $usces_op['zip_code'] . "\r\n" . $usces_op['address1'] . "\r\n" . $usces_op['address2'] . "\r\n" . "TEL " . $usces_op['tel_number'] . "\r\n" . "FAX " . $usces_op['fax_number'] . "\r\n" . "お問合せ " . $usces_op['inquiry_mail'] . "\r\n" . get_option('home') . "\r\n" . "=============================================\r\n";
$usces_op['mail_default']['footer']['order'] = "=============================================\r\n" . get_option('blogname') . "\r\n" . $usces_op['company_name'] . "\r\n" . "〒 " . $usces_op['zip_code'] . "\r\n" . $usces_op['address1'] . "\r\n" . $usces_op['address2'] . "\r\n" . "TEL " . $usces_op['tel_number'] . "\r\n" . "FAX " . $usces_op['fax_number'] . "\r\n" . "お問合せ " . $usces_op['inquiry_mail'] . "\r\n" . get_option('home') . "\r\n" . "=============================================\r\n";
$usces_op['mail_default']['footer']['inquiry'] = "=============================================\r\n" . get_option('blogname') . "\r\n" . $usces_op['company_name'] . "\r\n" . "〒 " . $usces_op['zip_code'] . "\r\n" . $usces_op['address1'] . "\r\n" . $usces_op['address2'] . "\r\n" . "TEL " . $usces_op['tel_number'] . "\r\n" . "FAX " . $usces_op['fax_number'] . "\r\n" . "お問合せ " . $usces_op['inquiry_mail'] . "\r\n" . get_option('home') . "\r\n" . "=============================================\r\n";
$usces_op['mail_default']['footer']['returninq'] = "=============================================\r\n" . get_option('blogname') . "\r\n" . $usces_op['company_name'] . "\r\n" . "〒 " . $usces_op['zip_code'] . "\r\n" . $usces_op['address1'] . "\r\n" . $usces_op['address2'] . "\r\n" . "TEL " . $usces_op['tel_number'] . "\r\n" . "FAX " . $usces_op['fax_number'] . "\r\n" . "お問合せ " . $usces_op['inquiry_mail'] . "\r\n" . get_option('home') . "\r\n" . "=============================================\r\n";
$usces_op['mail_default']['footer']['membercomp'] = "=============================================\r\n" . get_option('blogname') . "\r\n" . $usces_op['company_name'] . "\r\n" . "〒 " . $usces_op['zip_code'] . "\r\n" . $usces_op['address1'] . "\r\n" . $usces_op['address2'] . "\r\n" . "TEL " . $usces_op['tel_number'] . "\r\n" . "FAX " . $usces_op['fax_number'] . "\r\n" . "お問合せ " . $usces_op['inquiry_mail'] . "\r\n" . get_option('home') . "\r\n" . "=============================================\r\n";
$usces_op['mail_default']['footer']['completionmail'] = "=============================================\r\n" . get_option('blogname') . "\r\n" . $usces_op['company_name'] . "\r\n" . "〒 " . $usces_op['zip_code'] . "\r\n" . $usces_op['address1'] . "\r\n" . $usces_op['address2'] . "\r\n" . "TEL " . $usces_op['tel_number'] . "\r\n" . "FAX " . $usces_op['fax_number'] . "\r\n" . "お問合せ " . $usces_op['inquiry_mail'] . "\r\n" . get_option('home') . "\r\n" . "=============================================\r\n";
$usces_op['mail_default']['footer']['ordermail'] = "=============================================\r\n" . get_option('blogname') . "\r\n" . $usces_op['company_name'] . "\r\n" . "〒 " . $usces_op['zip_code'] . "\r\n" . $usces_op['address1'] . "\r\n" . $usces_op['address2'] . "\r\n" . "TEL " . $usces_op['tel_number'] . "\r\n" . "FAX " . $usces_op['fax_number'] . "\r\n" . "お問合せ " . $usces_op['inquiry_mail'] . "\r\n" . get_option('home') . "\r\n" . "=============================================\r\n";
$usces_op['mail_default']['footer']['changemail'] = "=============================================\r\n" . get_option('blogname') . "\r\n" . $usces_op['company_name'] . "\r\n" . "〒 " . $usces_op['zip_code'] . "\r\n" . $usces_op['address1'] . "\r\n" . $usces_op['address2'] . "\r\n" . "TEL " . $usces_op['tel_number'] . "\r\n" . "FAX " . $usces_op['fax_number'] . "\r\n" . "お問合せ " . $usces_op['inquiry_mail'] . "\r\n" . get_option('home') . "\r\n" . "=============================================\r\n";
$usces_op['mail_default']['footer']['receiptmail'] = "=============================================\r\n" . get_option('blogname') . "\r\n" . $usces_op['company_name'] . "\r\n" . "〒 " . $usces_op['zip_code'] . "\r\n" . $usces_op['address1'] . "\r\n" . $usces_op['address2'] . "\r\n" . "TEL " . $usces_op['tel_number'] . "\r\n" . "FAX " . $usces_op['fax_number'] . "\r\n" . "お問合せ " . $usces_op['inquiry_mail'] . "\r\n" . get_option('home') . "\r\n" . "=============================================\r\n";
$usces_op['mail_default']['footer']['mitumorimail'] = "=============================================\r\n" . get_option('blogname') . "\r\n" . $usces_op['company_name'] . "\r\n" . "〒 " . $usces_op['zip_code'] . "\r\n" . $usces_op['address1'] . "\r\n" . $usces_op['address2'] . "\r\n" . "TEL " . $usces_op['tel_number'] . "\r\n" . "FAX " . $usces_op['fax_number'] . "\r\n" . "お問合せ " . $usces_op['inquiry_mail'] . "\r\n" . get_option('home') . "\r\n" . "=============================================\r\n";
$usces_op['mail_default']['footer']['cancelmail'] = "=============================================\r\n" . get_option('blogname') . "\r\n" . $usces_op['company_name'] . "\r\n" . "〒 " . $usces_op['zip_code'] . "\r\n" . $usces_op['address1'] . "\r\n" . $usces_op['address2'] . "\r\n" . "TEL " . $usces_op['tel_number'] . "\r\n" . "FAX " . $usces_op['fax_number'] . "\r\n" . "お問合せ " . $usces_op['inquiry_mail'] . "\r\n" . get_option('home') . "\r\n" . "=============================================\r\n";
$usces_op['mail_default']['footer']['othermail'] = "=============================================\r\n" . get_option('blogname') . "\r\n" . $usces_op['company_name'] . "\r\n" . "〒 " . $usces_op['zip_code'] . "\r\n" . $usces_op['address1'] . "\r\n" . $usces_op['address2'] . "\r\n" . "TEL " . $usces_op['tel_number'] . "\r\n" . "FAX " . $usces_op['fax_number'] . "\r\n" . "お問合せ " . $usces_op['inquiry_mail'] . "\r\n" . get_option('home') . "\r\n" . "=============================================\r\n";


update_option('usces', $usces_op);



?>

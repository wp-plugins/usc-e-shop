(function() {
	
	function JeMemberShortCode(){
		edInsertContent( edCanvas, "[member][/member]\n[nonmember][/nonmember]" );
	};
	 
	function JeCartShortCode(){
		edInsertContent( edCanvas, '[cart_button code="" sku=""]' );
	};
	 
	function JeRegisterQuickTag(){
		jQuery( "#ed_toolbar" ).each( function(){
			var member       = document.createElement( "input" );
			member.type      = "button";
			member.value     = "メンバー/ノンメンバー";
			member.onclick   = JeMemberShortCode;
			member.className = "JeMember";
			member.title     = "メンバー/ノンメンバー・ショートコード";
			member.id        = "ed_JeMemberShortCode";
	 
			jQuery( this ).append( member );
			
			var cart       = document.createElement( "input" );
			cart.type      = "button";
			cart.value     = "カート投入ボタン";
			cart.onclick   = JeCartShortCode;
			cart.className = "JeCart";
			cart.title     = "カート投入ボタン・ショートコード";
			cart.id        = "ed_JeCartShortCode";
	 
			jQuery( this ).append( cart );
		} );
	};
	 
	JeRegisterQuickTag();
	
})()
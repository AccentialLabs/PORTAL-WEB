
	function refresh(){
		$('body').css('overflow','hidden');
		$('.bg_refresh').height($('body').height());
		$('.bg_refresh').css('top',$(window).scrollTop());
		$('.bg_refresh').fadeIn();
		var altura = ($('.bg_refresh').height() / 3) - $('.area-refresh').height() / 2;
		var largura = ($('.bg_refresh').width() / 2) - $('.area-refresh').width() /2;
		$('.area-refresh').css('top', altura);
		$('.area-refresh').css('left', largura);	
		
	}
	
	function fecharefresh(){		
		$('.bg_refresh').fadeOut();
		$('body').css('overflow','auto');	
		
	}

	function refreshzinho(){
		$('.refreshzinho').fadeIn();
	}
	function fecharefreshzinho(){
		$('.refreshzinho').fadeOut();
	}
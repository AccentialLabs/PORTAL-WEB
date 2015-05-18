$(function(){   
	$('.area-faq h4').click(function(){		
		if($(this).next('p').css('display') == 'block'){
			$(this).next('p').slideToggle();
			$(this).removeClass('ativo');
			$(this).find('img').addClass('seta-prabaixo').removeClass('seta-pracima');
		}else{
			$(this).addClass('ativo');
			$(this).next('p').slideToggle();
			$(this).find('img').addClass('seta-pracima').removeClass('seta-prabaixo');
		}
	});


});
$(function(){
	
	$('.area-esquerda ul li').each(function(i,val){
		//console.log($(val).offset().top);
		$(val).find('.contador').css('top',$(val).offset().top + 190);
	});
	$('.abriroquehadenovo span').html($('.conteudooquehadenovo .itens').length);
	$('.itens').mouseover(function(){
		$(this).animate({
			paddingLeft: '20',
			width: '230'
		},100)
	}).mouseout(function(){
		$(this).animate({
			paddingLeft: '10',
			width: '240'
		},100);		
	});

	$('eu-tenho').live('click', function(e) {	
		$(this).parent().parent().next('.linha_detalhe_desejos').show();
	});
	
	$('.nao-tenho').live('click', function(e) {
		id_linha_registro_excluir = $(this).attr('id_linha_registro');
		refresh();
		$.ajax({			
			type: "POST",			
			data:{				
			excluir_wishlist: true, id_linha: id_linha_registro_excluir},			
			url: location.href,
			success: function(result){								
				$('#div-content').html(result)
				fecharefresh();
		},
		error: function(XMLHttpRequest, textStatus, errorThrown){
			alert(errorThrown);
		}
	 });
		
	});
	
	//mais ofertas e mais compras
	$(window).scroll(function(){		
		var tamanho = $('body').height()-$(window).height();		
		if($(window).scrollTop() == tamanho){					
			var update = $('#id').attr('update');
			var limit = $('#id').attr('data-token');
			
			if(update==true){				
				refreshzinho();
				$.ajax({			
					type: "POST",			
					data:{				
					limit: limit},			
					url: location.href,
					success: function(result){								
						$('#div-content').html(result)								
						fecharefreshzinho();
				},
				error: function(XMLHttpRequest, textStatus, errorThrown){
					alert(errorThrown);
				}
			 });
		  }
	   }
	})
	
	
});
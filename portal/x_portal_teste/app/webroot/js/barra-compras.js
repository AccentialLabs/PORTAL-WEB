	
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
						$('.tabela-padrao .status').each(function(i, val){		
							$(this).attr('style','background-image:url(../users/img/icones/tabela-status-'+$(val).attr('status')+'.png)');
						});		
					fecharefreshzinho();
				},
				error: function(XMLHttpRequest, textStatus, errorThrown){
					alert(errorThrown);
				}
			 });
		  }
	   }
	})

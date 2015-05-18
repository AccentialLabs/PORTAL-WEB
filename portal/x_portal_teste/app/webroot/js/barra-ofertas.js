//alert('oi');
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
					console.log();
					alert(errorThrown);
				}
			 });
		  }
	   }
	})

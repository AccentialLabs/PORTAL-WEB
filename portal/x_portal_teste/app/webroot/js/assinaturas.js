
$('.ativo-inativo').live('click', function(e) {			
		var id = $(this).attr('id');
		var status = null;
		var up = $(this).attr('update');
		if($(this).attr('alt') == 'ativado'){
			var assinatura = false;
			$(this).removeClass('ativado');
			$(this).attr('alt','inativado');
			$(this).attr('title','Assinar Empresa');
			status = 'INACTIVE';
		}else{
			var assinatura = true;
			$(this).addClass('ativado');
			$(this).attr('alt','ativado');
			$(this).attr('title','Cancelar Assinatura');				
			status = 'ACTIVE';
		}			
		refresh();
		$.ajax({			
			type: "POST",			
			data:{				
			status: status, id: id, update: 'true', up: up, assinatura: assinatura},			
			url: location.href,
			success: function(result){
				fecharefresh();
				//alert(result);				
		},
		error: function(XMLHttpRequest, textStatus, errorThrown){
			alert(errorThrown);
		}
		});
	});
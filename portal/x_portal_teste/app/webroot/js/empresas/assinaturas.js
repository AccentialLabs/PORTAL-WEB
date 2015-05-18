$(function(){  		
	$('#MaisAssinaturas').live('click', function(e) {
		var limit = $('#paginacao').attr('limit');		
		$.ajax({			
			type: "POST",			
			data:{				
			limit: limit},			
			url: location.href,
			success: function(result){								
				$('.area-assinantes-ajax').html(result)				
		},
		error: function(XMLHttpRequest, textStatus, errorThrown){
			alert(errorThrown);
		}
	  });
	});
});


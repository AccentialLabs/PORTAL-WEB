$(function(){ 
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
	
	$('.on-off').each(function(i,val){
		if($(val).parent().parent().find('input').attr('value') == '1'){
			$(val).parent().find('a').css('left','30px');
		}else{
			$(val).parent().find('a').css('left','0px');
		}
	});
	$('.on-off').live('click', function(e) {	
		if($(this).find('a').css('left') == '30px'){
			$(this).find('a').animate({
				left: '0px'
			},'fast');
			$(this).parent().parent().find('input').attr('value','0');			
			var id_comentario = $(this).attr('valor');
			var status = 'INACTIVE';
				$.ajax({			
					type: "POST",			
					data:{				
					id_comentario: id_comentario, status: status},			
					url: location.href,
					success: function(result){
						//alert(result);					
				},
				error: function(XMLHttpRequest, textStatus, errorThrown){
					alert(errorThrown);
				}
			  });	
		}else{
			$(this).find('a').animate({
				left: '30px'
			},'fast');
			$(this).parent().parent().find('input').attr('value','1');
			var id_comentario = $(this).attr('valor');
			var status = 'ACTIVE';
			$.ajax({			
				type: "POST",			
				data:{				
				id_comentario: id_comentario, status: status},		
				url: location.href,
				success: function(result){
					//alert(result);					
			},
			error: function(XMLHttpRequest, textStatus, errorThrown){
				alert(errorThrown);
			}
		  });
		}
	});
	
	$('.comentario a').live('click', function(e) {			
		$('.linha_detalhe').hide();
		$(this).parent().parent().next('.linha_detalhe').show();
		
	});	
	$('.opcoes-de-lista-de-compras .opcoes').live('click', function(e) {				
		$('.opcoes-de-lista-de-compras .opcoes').removeClass('ativo');
		$(this).addClass('ativo');
		var status = $(this).attr('title');
		var valor = 0;
		console.log(status);
		$('.tabela-padrao tbody tr').hide();
		$('.tabela-padrao tbody tr').each(function(i, val){								
			if($(this).attr('status') == status){
				$(this).show();
				valor += eval($(this).attr('valor'));				
			}		
			if(status == 'tudinho'){				
				$('.tabela-padrao tbody tr').show();				
				$('.linha_detalhe').hide();
				valor += eval($(this).attr('valor'));
			};
			
			$('.valor_total').html('R$ '+valor);
		});

	});
	$('.link-de-opcoes').live('click', function(e) {				
		if($('.area-dos-filtros').css('display') == 'block'){
			$('.area-dos-filtros').hide();
		}else{
			$('.area-dos-filtros').show();
		}
	});
	$('.area-dos-filtros .envio').click(function(){
		$('.area-dos-filtros').hide();
	});
	
	$('.area-dos-filtros .cancelar').live('click', function(e) {
		$('.area-dos-filtros').hide();
	});
	
	$('.area-dos-filtros input').live('blur', function(e) {	
		if($(this).val() == '' || $(this).val() == ' '){
			$(this).val($(this).attr('alt'))
		}
	});
	$('.area-dos-filtros input').focus(function(){
		if($(this).val() == $(this).attr('alt')){
			$(this).val('');
		};
	});	

    $('.pesquisaproduto').autocomplete({source: itensProduto});	
    $('.pesquisacomprador').autocomplete({source: itensComprador});	




	$("#calendario-inicio").datepicker({
	    dateFormat: 'dd/mm/yy',
	    dayNames: ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado','Domingo'
	        ],
	    dayNamesMin: [
	    'D','S','T','Q','Q','S','S','D'
	    ],
	    dayNamesShort: [
	    'Dom','Seg','Ter','Qua','Qui','Sex','Sáb','Dom'
	    ],
	    monthNames: [  'Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro',
	    'Outubro','Novembro','Dezembro'
	    ],
	    monthNamesShort: [
	    'Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set',
	    'Out','Nov','Dez'
	    ],
	    nextText: 'Próximo',
	    prevText: 'Anterior'
	    });
	$("#calendario-fim").datepicker({
	    dateFormat: 'dd/mm/yy',
	    dayNames: ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado','Domingo'
	        ],
	    dayNamesMin: [
	    'D','S','T','Q','Q','S','S','D'
	    ],
	    dayNamesShort: [
	    'Dom','Seg','Ter','Qua','Qui','Sex','Sáb','Dom'
	    ],
	    monthNames: [  'Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro',
	    'Outubro','Novembro','Dezembro'
	    ],
	    monthNamesShort: [
	    'Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set',
	    'Out','Nov','Dez'
	    ],
	    nextText: 'Próximo',
	    prevText: 'Anterior'
	    });


});


var itensProduto = new Array();
var itensComprador = new Array();

itensProduto = [
      "Produto 1",
      "Produto 2",
      "Produto 3",
      "Produto 4",
      "Produto 5"                        
];
itensComprador = [
      "Jose",
      "Maria",
      "Antonio",
      "Juliana",
      "Wilson" 
];

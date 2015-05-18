
$(function(){  		
	$('.selecao-genero h5').click(function(){		
		if($(this).next('div').css('display') == 'block'){
			$(this).find('img').removeClass().addClass('seta-prabaixo');
			$('.graficos-gerais').slideUp();
		}else{
			$(this).find('img').removeClass().addClass('seta-pracima');
			$('.graficos-gerais').slideUp();
			$(this).next('div').slideToggle();
		}
	});
	
	// Inicio de Graficos generos
	var basetotal = $('.total-base strong').html();
	valormasculino = ($('.genero-masculino').find('span').html()*100 ) / basetotal;
	//$('.genero-masculino').height(valormasculino);
	valorfeminino = ($('.genero-feminino').find('span').html()*100) / basetotal;
	//$('.genero-feminino').height(valorfeminino);	
	 
 	$('.genero-masculino').animate({width: valormasculino});
 	$('.genero-feminino').animate({width: valorfeminino});	
	
	$('#genero-masculino strong').html($('.genero-masculino h3 span').html());
	$('#genero-feminino strong').html($('.genero-feminino h3 span').html());	
	
	$('.genero-masculino').click(function(){alert($('.genero-masculino').find('span').html());});      
	$('.genero-feminino').click(function(){alert($('.genero-feminino').find('span').html());});      	


	$('.area-item').each(function(i,val){
		tamanho = $(val).find('strong').html();
		$(this).find('span').animate({width: tamanho + '%'});
	});
	
	$('.envio').click(function(){		
		var clone = $('#photo_principal').clone();
		clone.attr('id', 'photo1');
		$('#field2_area').html(clone);
		console.log();
		
	});
	
	$('.on-off').each(function(i,val){
		if($(val).parent().parent().find('input').attr('value') == '1'){
			$(val).parent().find('a').css('left','30px');
		}else{
			$(val).parent().find('a').css('left','0px');
		}
	});
	$('.on-off').click(function(){
		if($(this).find('a').css('left') == '30px'){
			$(this).find('a').animate({
				left: '0px'
			},'fast');
			$(this).parent().parent().find('input').attr('value','0');
		}else{
			$(this).find('a').animate({
				left: '30px'
			},'fast');
			$(this).parent().parent().find('input').attr('value','1');
		}
	});
	
	$('.verifica_parcelamento').click(function(){	
		if($(this).attr('valor')==1){
			$('.juros-parcelamento').show();
		}else{
			$('.juros-parcelamento').hide();
		}
	});
	
	//removendo imagens cadastradas na oferta        
    $('.removeOfferImage').click(function(){        
        var index = $(this).attr('id');
        $.ajax({
            url:location.href,
            type:'post',
            data:{'indexOfferImage':index},
            success:location.href = ''
        });          
    });	
	
	//verifica quando entra na pagina offersFilters
	var url = window.location;
	var urlString = url.toString();
	var urlArray = urlString.split("/")	
	var ajaxOffer = urlArray.pop();	
	if(ajaxOffer=='offerFilters'){
		/* Ajax para salvar os filtros numa Session - EDICAO DE OFERTAS*/
		$.ajax({
			url:location.href,
			type:'post',
			data:{'filters':'0'},
			success:function(result) {				
				var data = $.parseJSON(result);														
				//carregando filtros direto da sessao				
				var nameFiltros = new Array('gender', 'location', 'age_group', 'political', 'relationship_status', 'religion');
				var filtros = new Array();
				filtros[0] = data.gender;
				filtros[1] = data.location;
				filtros[2] = data.age_group;
				filtros[3] = data.political;
				filtros[4] = data.relationship_status;
				filtros[5] = data.religion;										
				for(u=0;u<filtros.length;u++){
					var filter = nameFiltros[u];					
					for(i=0;i<filtros[u].length;i++){						
						var value = filtros[u][i];																					
						$($('.area-item[atributo="'+value+'"]')).attr('usando', 'sim');
						$($('.area-item[atributo="'+value+'"]')).css('border','1px solid #053b76');
						$($('.area-item[atributo="'+value+'"]')).css('box-shadow','0px 2px 5px #333');
						
						if($('#'+filter).attr('contador')==1){				
							$('#'+filter).html('<strong>você escolheu:</strong>');
						}
						teste = parseInt($('#'+filter).attr('contador')) + 1;
						$('#'+filter).attr('contador', teste);
						
						var link = "<a href='javascript:void(0);' id='remove' tipo='"+filter+"' atributo='"+value+"' class='"+value+"'>"+value+"<span></span></a>";
						if(!filters[filter].valueExists(value)) {
							//adcionando link
							$('#'+filter).append(link);							
							filters[filter].addObjectWithValue(value);				
						}							
					}								
				}							
			},
			error:function(xhr,status) {
				if(status) {
					alert('O filtro n�o foi gravado corretamente. Tente novamente.');
				}
			}
		});
	}
	
	
	
	var valor = parseInt('0');
	var valorGeral = parseInt('0');	
	/* Sele��o dos filtros de oferta */
	filters = {'gender':[],'location':[],'age_group':[],'political':[],'relationship_status':[],'religion':[]};	
	
	$('.area-item').live('click', function(e) {				
		var filter 	    = $(this).attr('tipo');
		var value	 	= $(this).attr('atributo');
		var quantidade  = $(this).attr('quantidade');
		
		if($(this).attr('usando') == 'sim'){
			valorGeral = parseInt(valorGeral) - quantidade;
			$('.totalgeral').html(valorGeral);
			teste = parseInt($('#'+filter).attr('contador')) - 1;
			$('#'+filter).attr('contador', teste);
			if($('#'+filter).attr('contador')==1){				
				$('#'+filter).html('<strong>Você ainda não escolheu nenhum filtro</strong> ');
			}
					
			$(this).attr('usando', '');
			$(this).css('border','1px solid #dfdfdf');
			$(this).css('box-shadow','0px 0px 0px #333');
				
			if(filters[filter].valueExists(value)) {
				//removendo link
				$('.'+value).remove();				
				filters[filter].removeObjectWithValue(value);							
			}					
		}else{							
			if($('#'+filter).attr('contador')==1){				
				$('#'+filter).html('<strong>você escolheu:</strong>');
			}
			teste = parseInt($('#'+filter).attr('contador')) + 1;
			$('#'+filter).attr('contador', teste);
			
			
			var link = "<a href='javascript:void(0);' id='remove' tipo='"+filter+"' atributo='"+value+"' class='"+value+"'>"+value+"<span>["+quantidade+"]</span></a>";
			
			$(this).attr('usando', 'sim');
			$(this).css('border','1px solid #053b76');
			$(this).css('box-shadow','0px 2px 5px #333');					
			if(!filters[filter].valueExists(value)) {
				//adcionando link
				$('#'+filter).append(link);
				valorGeral = parseInt(quantidade) + valorGeral;
				$('.totalgeral').html(valorGeral);
				filters[filter].addObjectWithValue(value);				
			}	
			
		}			
		
		/* Ajax para salvar os filtros numa Session */
		$.ajax({
			url:location.href,
			type:'post',
			data:{'filters':$.stringify(filters)},
			success:function(data) {
				//alert(data);
				var status = $.parseJSON(data);					
				if(status.status == 'FILTERS_NOT_OK') {
					alert('O filtro n�o foi gravado corretamente. Tente novamente.');
				}								
				// debug
				console.log(data);
			},
			error:function(xhr,status) {
				if(status) {
					alert('O filtro n�o foi gravado corretamente. Tente novamente.');
				}
			}
	   });
	});

	$('.linkpopup').click(function(){
		$('.popup').html($("#popescondido").html());
		$('body').css('overflow','hidden');
		$('.bg_popup').height($('body').height());
		$('.bg_popup').css('top',$(window).scrollTop());
		$('.bg_popup').show();
		var altura = ($('.bg_popup').height() / 6) - $('.popup').height() /2;
		var largura = ($('.bg_popup').width() / 2) - $('.popup').width() /2;
		$('.popup').css('top', altura);
		$('.popup').css('left', largura);			
	});
	$('.popup .fechar').click(function(){
		$('.bg_popup').fadeOut();
		$('body').css('overflow','auto');	
	});
	
	$('#envio').live('click', function(e) {
		$.ajax({			
			type: "POST",			
			data:{				
			finalizar_metricas:true},			
			url: location.href,
			success: function(result){	
			//alert(result);
			$('.bg_popup').fadeOut();
			$('body').css('overflow','auto');
			$('#metricas-selecionadas').html(result);
						
		},
		error: function(XMLHttpRequest, textStatus, errorThrown){
			alert(errorThrown);
		}
	  });
	});
	
	var multi = new Array();	
	$('.multi-select a').live('click', function(e) {			
		if($(this).attr('class') == 'ativo'){			
			multi.removeObjectWithValue($(this).attr('valor'));			
			$(this).removeClass('ativo');
			valor 	 = $(this).attr('valor');
			atributo = $(this).attr('nome');
			$.ajax({			
				type: "POST",			
				data:{				
				excluir_metricas:true, atributo:atributo, valor:valor},			
				url: location.href,
				success: function(result){				
			},
			error: function(XMLHttpRequest, textStatus, errorThrown){
				alert(errorThrown);
			}
		  });
		}else{				
			multi.addObjectWithValue($(this).attr('valor'));			
			$(this).addClass('ativo');	
			valor 	 = $(this).attr('valor');
			atributo = $(this).attr('nome');
			$.ajax({			
				type: "POST",			
				data:{				
					salvar_metricas:true, atributo:atributo, valor:valor},				
				url: location.href,
				success: function(result){												
			},
			error: function(XMLHttpRequest, textStatus, errorThrown){
				alert(errorThrown);
			}
		  });
		};			
		//$(this).parent().parent().find('input').val($.stringify(check));		
	});
	
	
	$('.select').live('click', function(e) {						
		$(this).find('ul li').click(function(){
			$(this).parent().parent().find('strong').html($(this).html());
			$(this).parent().parent().find('input').val($(this).attr('valor'));
			var id = $(this).attr('valor');
			$.ajax({			
				type: "POST",			
				data:{				
				metricas:true, id:id},			
				url: location.href,
				success: function(result){				
				$('#dados-metricas').html(result);				
			},
			error: function(XMLHttpRequest, textStatus, errorThrown){
				alert(errorThrown);
			}
		  });
		});
	});
	
	/* Valida��o Form Detalhes da Oferta */
	$('form#OfferAddOfferForm').validate({
		highlight: function(element) {
		     $(element).addClass('offer-detail-form-input-validation');
		},
		unhighlight: function(element, errorClass) {
		     $(element).removeClass('offer-detail-form-input-validation');
		},
		rules:{
			"data[Offer][title]":{required:true},
			"data[Offer][value]":{required:true},
			"data[Offer][resume]":{required:true},
			"data[Offer][photo]":{required:true},
			"data[Offer][begins_at]":{required:true},
			"data[Offer][ends_at]":{required:true},
			"data[Offer][weight]":{required:true}			
		},
		messages:{
			"data[Offer][title]":{required:""},
			"data[Offer][value]":{required:""},
			"data[Offer][resume]":{required:""},
			"data[Offer][photo]":{required:""},
			"data[Offer][begins_at]":{required:""},
			"data[Offer][ends_at]":{required:""},
			"data[Offer][weight]":{required:""}			
		}
	});
	
	
	$('.ckeditor').parent().css('width','95%');
	
	//$('#peso').mask('9.99',{reverse:true});
	$('#valor').mask('999.999,99',{reverse:true});

	$('.opcoes-de-lista-de-compras a').click(function(){
		$('.opcoes-de-lista-de-compras a').removeClass('ativo');
		$(this).addClass('ativo');
		var status = $(this).attr('title');
		console.log(status);
		$('.tabela-padrao tbody tr').hide();
		$('.tabela-padrao tbody tr').each(function(i, val){

			if($(this).attr('status') == status){
				$(this).show();
			}
			if(status == 'tudinho'){
				
				$('.tabela-padrao tbody tr').show();
				$('.linha_detalhe').hide();
			};
		});

	});

$('.localizacao li').click(function(){
	if($(this).find('input').attr('checked')){
		$(this).find('input').removeAttr('checked');
	}else{
		$(this).find('input').attr('checked','')
	}

});

$('#desconto').blur(function(){
	var desconto = parseFloat($(this).attr('value'));
	var valor = parseFloat($('#valor').attr('value'));
	var valor_final = valor - ((valor * desconto) / 100);
	
	$('#valor-final').attr('value','R$ '+parseFloat(valor_final));
});


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


function fechar_popUP(){		
		$('.bg_popup').fadeOut();
		$('body').css('overflow','auto');	
}
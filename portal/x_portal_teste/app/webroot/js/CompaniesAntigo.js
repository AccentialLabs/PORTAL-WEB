$(function(){ 	
	
	$('.destaque-ultimas-compras li').click(function(){
		$('.destaque-ultimas-compras li').animate({
			height: '30px'
		}, 'fast').css('background-image','url(imagens/icones/seta-baixo.png)');
		if($(this).css('height') == '450px'){
			$(this).animate({
				 height: '30px'
			});
		}else{
			$(this).animate({
				height: '450px'
			}).css('background-image','url(imagens/icones/seta-cima.png)');			
		}
	});
	$('.link-alterar-status').click(function(){
		$('.alterar-status').toggle();
	});
	$('.destaque-ultimas-compras li:odd').css('background-color','#efefef');
	
	$('.area-compra').click(function(){
		$(this).animate({scrollTop:192, border: '1px solid #ccc'}, 'fast').delay(3500).animate({scrollTop:0, border: '1px solid #efefef'}, 'fast');
	});
	
	
	$('.comentario a').live('click', function(e){
		//$('.comentario a').click(function(){
			$('.linha_detalhe').hide();
			$(this).parent().parent().next('tr').show();
		});	
		$('h1').append('<img src="imagens/icones/icone-titulo.png">');

		$('.linha_detalhe button').live('click', function(e){
		//$('.linha_detalhe button').click(function(){
			$(this).parent().find('button').removeClass('ativo');
			$(this).addClass('ativo');		
			var id = $(this).val();
			
			$.ajax({			
				type: "POST",			
				data:{				
				comentarioId: id},			
				url: location.href,
				success: function(result){													
					//alert('Atualizacao realizada');
			},
			error: function(XMLHttpRequest, textStatus, errorThrown){
				alert(errorThrown);
			}
			});		
			
		})	

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
	})
	/*$('.area-item').click(function(){
		alert($(this).find('strong').html());
	});*/

	// Fim de Graficos generos
	// Inicio de Grafico de Localização
	
	$('input').focus(function(){
		if($(this).val() == $(this).attr('alt')){$(this).val('')};
	});
	$('input').blur(function(){
		if($(this).val() == ''){$(this).val($(this).attr('alt'))};	
	});

	$('.passos').mouseover(function(){
   // 	$('body').append('<div class="fundobranco"></div>');
   // 	$('.fundobranco').fadeIn(100);
   // 	$('.fundobranco').width($('body').width());
   // 	$('.fundobranco').height($('window').height());
   // 	$(this).css('z-index','999999999');
		$(this).css('border','1px solid #999');
   //	$(this).animate({
   //		border : '1px solid #999',
   //		duration:10
   //	});
		
	});
	$('.passos').mouseout(function(){
	//	$('.fundobranco').remove();
	//	$(this).css('z-index','999');	
   //$(this).animate({
   //	border : '1px solid #efefef',
   //	duration:10
   //});	
		$(this).css('border','1px solid #efefef');
		
	});
	
	
	//ajax pedidos	
	$('#linkmaispedidos').live('click', function(e){
	//$("#linkmaispedidos").click(function() {		
		var limit = $('#id').attr('limit');
		$.ajax({			
			type: "POST",			
			data:{				
			limit: limit},			
			url: location.href,
			success: function(result){								
				$('#div-content').html(result)				
		},
		error: function(XMLHttpRequest, textStatus, errorThrown){
			alert(errorThrown);
		}
		});
	});
	
	
	//ajax pedidos	
	$('#linkmais').live('click', function(e){
	//$("#linkmaispedidos").click(function() {		
		var limit = $('#id').attr('data-token');
		$.ajax({			
			type: "POST",			
			data:{				
			limit: limit},			
			url: location.href,
			success: function(result){								
				$('#content-offers').html(result)				
		},
		error: function(XMLHttpRequest, textStatus, errorThrown){
			alert(errorThrown);
		}
		});
	});
	

	$("#calendario-vinculacao").datepicker({
	    dateFormat: 'dd/mm/yy',
	    altField: '#OfferBeginsAt',
	    altFormat: 'yy-mm-dd',
	    dayNames: ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado','Domingo'],
	    dayNamesMin: ['D','S','T','Q','Q','S','S','D'],
	    dayNamesShort: ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb','Dom'],
	    monthNames: [  'Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'],
	    monthNamesShort: ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'],
	    nextText: 'Próximo',
	    prevText: 'Anterior'
	});

	$("#calendario-final").datepicker({
	    dateFormat: 'dd/mm/yy',
	    altField: '#OfferEndsAt',
	    altFormat: 'yy-mm-dd',
	    dayNames: ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado','Domingo'],
	    dayNamesMin: ['D','S','T','Q','Q','S','S','D'],
	    dayNamesShort: ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb','Dom'],
	    monthNames: [  'Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'],
	    monthNamesShort: ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'],
	    nextText: 'Próximo',
	    prevText: 'Anterior'
	});

	$('.localizacao li:odd').css('background-color','#dfdfdf');
	
	//	Codigo de compras
		
	//	Codigo de compras

		
	$('.abas a').click(function(){
		$('.abas a').parent().removeClass('ativo');
		$(this).parent().addClass('ativo');

		$('.area-abas div').hide();
		var div = '.'+$(this).attr('alt');

		$(div).show();
		$('body').scrollTop($(div).offset().top);

	});

	/* ======================== PÁGINA CADASTRO DE OFERTAS =========================== */

	/* Máscaras Form Detalhes da Oferta */
	$('#OfferOldValue').mask('999.999.999,99',{reverse:true});
	$('#OfferValue').mask('999.999.999,99',{reverse:true});

	/* Validação Form Detalhes da Oferta */
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
			"data[Offer][description]":{required:true},
			"data[begins_at]":{required:true},
			"data[ends_at]":{required:true}
		},
		messages:{
			"data[Offer][title]":{required:""},
			"data[Offer][value]":{required:""},
			"data[Offer][description]":{required:""},
			"data[begins_at]":{required:""},
			"data[ends_at]":{required:""}
		}
	});

	/* Movimentação da barra de filtros no topo */
	$(window).scroll(function(){
		if($(this).scrollTop() > '440'){
			$('.resumo').addClass('movimentacao');
		}else{
			$('.resumo').removeClass('movimentacao');
		};
	});
	$('.resumo .resumo-lista').css('top',(183 / 2) - ( 43 * 1.8));
	$('.resumo-lista li').mouseover(function(){
		$(this).find('p').show();
	});
	$('.resumo-lista li').mouseout(function(){
		$(this).find('p').hide();
	});

	/* Filtros de Ofertas */
	$('div.area > div.area-item').css({'cursor':'pointer'});

	/* Seleção dos filtros de oferta */
	filters = {'gender':[],'location':[],'age_group':[],'political':[],'relationship_status':[],'religion':[]};
	
	
	//verifica quando entra na pagina offersFilters
	var url = window.location;
	var urlString = url.toString();
	var urlArray = urlString.split("/")	
	var ajaxOffer = urlArray.pop();
	
	if(ajaxOffer=='offerFilters'){	
		
		/* Ajax para salvar os filtros numa Session */
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
						var link = "<a href='#' action='remove' control='filter-data' filter='"+ filter +"' value='"+ value +"'>{text}</a>";
						var text = filtros[u][i];
						
						if(!filters[filter].valueExists(value)) {
							filters[filter].addObjectWithValue(value);
								
							// adiciona filtro na barra
							if(!$('li#'+filter).hasClass('blink')) {
								$('li#'+filter).addClass('blink');
							}
							$('li#'+filter+' > p').append(link.replace('{text}',text));
						}
					}								
				}							
			},
			error:function(xhr,status) {
				if(status) {
					alert('O filtro não foi gravado corretamente. Tente novamente.');
				}
			}
		});
	}								

	$('*[control=filter-data]').live('click', function(e){
		e.preventDefault();

		var action = $(this).attr('action');
		var filter = $(this).attr('filter');
		var value = $(this).attr('value');
		
		var link = "<a href='#' action='remove' control='filter-data' filter='"+ filter +"' value='"+ value +"'>{text}</a>";

		if(action == 'add') {			
			var text = $(this).attr('text');
			var count = $(this).attr('count');
			
			if(!filters[filter].valueExists(value)) {
				filters[filter].addObjectWithValue(value);
					
				// adiciona filtro na barra
				if(!$('li#'+filter).hasClass('blink')) {
					$('li#'+filter).addClass('blink');
				}
				$('li#'+filter+' > p').append(link.replace('{text}',text));
			}

		} else if(action == 'remove') {
			
			if(filters[filter].valueExists(value)) {
				filters[filter].removeObjectWithValue(value);
				
				// remove filtro da barra
				$(this).remove();
				
				if($('li#'+filter+' > p').find('a').length <= 0) {
					$('li#'+filter).removeClass('blink');
				}
			}
		}

		/* Ajax para salvar os filtros numa Session */
		$.ajax({
			url:location.href,
			type:'post',
			data:{'filters':$.stringify(filters)},
			success:function(data) {
				var status = $.parseJSON(data);				
				if(status.status == 'FILTERS_NOT_OK') {
					alert('O filtro não foi gravado corretamente. Tente novamente.');
				}
				// debug
				console.log(data);
			},
			error:function(xhr,status) {
				if(status) {
					alert('O filtro não foi gravado corretamente. Tente novamente.');
				}
			}
		});
	});

	/* ======================== FIM PÁGINA DETALHES DA OFERTA - ETAPA 1 =========================== */

});
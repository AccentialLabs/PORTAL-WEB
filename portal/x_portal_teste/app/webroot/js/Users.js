$(function(){	
	//categorias e subcategorias wishlist
	$('#categoria').click(function(){	
		categoria = $("#teste").val();		
		$.ajax({			
			type: "POST",			
			data:{				
			categoria: categoria},			
			url: location.href,
			success: function(result){						
				$('#categorias').html(result);
				alert(result);
		},
		error: function(XMLHttpRequest, textStatus, errorThrown){
			alert(errorThrown);
		}
	  });
	});
	$('#sub-categoria-ajax').live('click', function(e) {
		$('#sub-categoria-input').val($('#sub-categoria-ajax-input').val());		
	});
	
	$('.excluir_desejo').live('click', function(e) {
		var id = $(this).attr('id_desejo');
		refresh();
		$.ajax({			
			type: "POST",			
			data:{				
			id: id, excluir_desejo: 'true'},			
			url: location.href,
			success: function(result){
				$('#div-content').html(result)
				fecharefresh();
				//alert(result);				
		},
		error: function(XMLHttpRequest, textStatus, errorThrown){
			alert(errorThrown);
		}
	  });
	});
	
	$("a[rel=external]").live('click', function(e) {
        if (!this.oldHref) {
           this.oldHref = this.href;
           this.href = "#";
        }
        window.open(this.oldHref);
     });
	
	$('#buscar_cep_dados_cadastrais').live('click', function(e){									
		var valor = $('#CEP').val();
		valor = valor.replace('-', '');	
		refresh();
		$.ajax({			
			type: "POST",			
			data:{				
			cep_buscar: valor},			
			url: location.href,
			success: function(result){					
				var data = jQuery.parseJSON(result);				
				$('#Endereco').focus();
				$('#Endereco').val(data.logradouro);
				$('#Bairro').val(data.bairro);
				$('#Estado').val(data.uf);
				$('#Cidade').val(data.localidade);
				fecharefresh();
		},
		error: function(XMLHttpRequest, textStatus, errorThrown){
			alert(errorThrown);
		}
	  });		
	});
	
	$('#submitFormOfertas').live('click', function(e) {
		$('#formPostOfertas').submit();
	});
	$('#submitFormOfertasPerfil').live('click', function(e) {		
		$('#formPostOfertasPerfil').submit();
	});
	
	$('.excluir').live('click', function(e) {
		var id = $(this).attr('linha_registro');		
		refresh();
		$.ajax({			
			type: "POST",			
			data:{				
			id: id, excluir_convite: 'true'},			
			url: location.href,
			success: function(result){
				$('#div-content').html(result)
				fecharefresh();
				//alert(result);				
		},
		error: function(XMLHttpRequest, textStatus, errorThrown){
			alert(errorThrown);
		}
	  });
	})
	$('.area-esquerda ul li').each(function(i,val){
		//console.log($(val).offset().top);
		// $(val).click(function(){console.log($(val).offset().top)});
		$(val).find('.contador').css('top',$(val).offset().top -146);
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
	
	$('.ativo-inativo').live('click', function(e) {	
		var id 			= $(this).attr('linha_registro');	
		var id_empresa  = $(this).attr('id_empresa');
		refresh();
		$.ajax({			
			type: "POST",			
			data:{				
			id: id, id_empresa: id_empresa, assinar_empresa: 'true'},			
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

	$('.tabela-assinaturas tbody td').live('click', function(e) {		
		if($(this).find('a').attr('class') == 'ativo-inativo' || $(this).find('a').attr('class') == 'ativo-inativo ativado'){
			
		}else{						
			$(this).parent().next('.linha_detalhe').toggle();
			//$('.linha_detalhe').each(function(i,val){
			//	if($(val).css('display') == 'table-row'){
			//		$(val).hide();
			//	}
			//});	
			
			// if($(this).parent().next('.linha_detalhe').css('display') == 'table-row'){
			// 	$(this).parent().next('.linha_detalhe').hide();		
			// }else{
			// 	$(this).parent().next('.linha_detalhe').show();
			// }
		}
	});	
	$('.abriropcoesusuario span').prepend('<img src="http://secure.trueone.com.br/portal/app/webroot/img/icones/arrow-usuario-logado.png">');
	$('.abriropcoesusuario').click(function(){
		if($('.conteudoopcoesusuario').css('display') == 'block'){
			$('.conteudoopcoesusuario').slideUp('fast');
		}else{
			$('.conteudoopcoesusuario').slideDown('fast');
		}
	});
	
	$('.usuario').click(function(){
		$('#usuario_login').val('');
		$('#nome').val('');
	});
	$('.email').click(function(){		
		$('#email').val('');
	});
	$('.senha').click(function(){
		$('#password').val('');
		$('#usuario_senha').val('');
	});
	
	
	
	$("#selecione-o-meio a").css("display", "none");
	$("form#UserCadPerfilForm").submit(function() {
		if(jQuery.trim($("#nome").val()) == ""){					
			$('#nome').removeClass("textbox").addClass("textbox erro");
			return false;
			//alert('2');
		}
		else if(jQuery.trim($("#email").val()) == ""){					
			$('#email').removeClass("textbox").addClass("textbox erro");
			return false;
			//alert('2');
		}
		else if(jQuery.trim($("#password").val()) == ""){					
			$('#password').removeClass("textbox").addClass("textbox erro");
			return false;
			//alert('2');
		}
		else{
			return true;
		}
	});
		
	$('.area-esquerda').tooltip({		
		show: null,
		open: function( event, ui ) {
	       	//ui.tooltip.animate({ left: ui.tooltip.position().left + 10 }, "fast" );
	     },
		position: {
	        my: "left+110",
	        at: "left bottom-15"
	    }
	});	
	
	// Inicio de codigo da animacao da area-esquerda
	$('.area-esquerda li a').each(function(e, a){
		//$(a).attr('alt', $(this).attr('title'));		
	});
	
	
		
	$('.area-esquerda li a').click(function(){

		if($('h1 .img img').attr('src') != $(this).find('img').attr('src')){
			$('.area-esquerda li').animate({opacity:'1'},{duration:100});
			$(this).parent().animate({opacity:'0.3'},{duration:100});	
			$('h1 .img img').fadeOut(50).attr('src',$(this).find('img').attr('src')).fadeIn(50);
			$('h1 span').hide().fadeIn().html($(this).attr('alt'));		
// $('h1 .img img').fadeOut(50).animate({left: posX},{duration: 100, specialEasing: {'left': 'easeOutBounce'}}).fadeIn(50);			
//$('h1 span').animate({top:'-400px'},{duration:100, specialEasing:{'top': 'easeInQuad'}}).html($(this).html()).animate({top: '18px'},{duration:200, specialEasing:{'top': 'easeInQuad'}});
//$(this).parent().slideUp();
		}

	});
	// Inicio de codigo da lista de ofertas
	//$('.lista-de-ofertas .oferta-destaque-principal .descricoes .botao-comprar').prepend('');
	//$('.lista-de-ofertas .oferta-destaque .descricoes .botao-comprar').prepend('');
	//$('.lista-de-ofertas .oferta-destaque-impar .descricoes .botao-comprar').prepend('<div class="seta-de-ofertas"><a href=""><img src="../users/img/icones/seta-de-ofertas.png"></a><span>mais informações</span></div>');
	
	//$('.lista-de-ofertas .oferta-destaque-principal .descricoes').prepend('<div class="seta-de-ofertas"><a href=""><img src="imagens/icones/seta-de-ofertas.png"></a><span>mais informações</span></div>');
	
	$('.seta-de-ofertas a').live('mouseover', function(e) {				
		$(this).parent().find('span').show();
	}).live('mouseout', function(e) {		
		$(this).parent().find('span').hide();
	});
// Fim de codigo da lista de ofertas
	$('.seta-de-ofertas').live('mouseover', function(e) {			
		$(this).animate({
			transform: 'rotate(190deg)'
		});

	});
	//Inicio de codigo POPUP	
	$('.linkpopup').live('click', function(e) {	
	
		$('.popup').html($("#popescondido").html());
		$('body').css('overflow','hidden');
		// $('.bg_popup').height($('body').height());
		$('.bg_popup').height($(window).height());
		//$('.bg_popup').css('top',$(window).scrollTop());
		$('.bg_popup').show();
		var altura = ($('.bg_popup').height() / 2) - $('.popup').height() / 2;
		var largura = ($('.bg_popup').width() / 2) - $('.popup').width() / 2;
		$('.popup').css('margin-top', altura);
		$('.popup').css('left', largura);			
	});
	$('.popup').mouseover(function(){
		var altura = ($('.bg_popup').height() / 2) - $('.popup').height() / 2;
		$('.popup').animate({
			marginTop: altura
		},'fast');

	});	
	$('.fechar').live('click', function(e) {		
		$('.bg_popup').fadeOut();
		$('body').css('overflow','auto');	
	});
	// Fim de codigo POPUP
	//trabalhando select de popup de minhas compras
	$('.select').live('click', function(e) {		
		if($(this).find('ul').css('display') == 'block'){
			$(this).find('ul').slideToggle('fast');
		}else{
			$(this).find('ul').slideToggle('fast');
		}
		$(this).find('ul li').click(function(){
			$(this).parent().parent().find('strong').html($(this).html());
			$(this).parent().parent().find('input').val($(this).attr('valor'));								
		});
	});
		
	//selecionando endereco
	$('.seleciona_endereco').live('click', function(e) {		
		id_endereco = $(this).attr('id_endereco');		
		refresh();
		$.ajax({			
			type: "POST",			
			data:{				
			seleciona_endereco: true, id_endereco: id_endereco},			
			url: location.href,
			success: function(result){
			//alert(result);
			$('#div-content').html(result);			
			liberaBoleto(false);		
			fecharefresh();	
		},
		error: function(XMLHttpRequest, textStatus, errorThrown){
			alert(errorThrown);
		}
	  });
	});
	//selecionando endereco
	$('.excluir_endereco').live('click', function(e) {		
		id_endereco = $(this).attr('id_endereco');		
		//refreshzinho();
		$.ajax({			
			type: "POST",			
			data:{				
			excluir_endereco: true, id_endereco: id_endereco},			
			url: location.href,
			success: function(result){			
			$('#div-content').html(result);
			liberaBoleto(false);		
			//fecharefreshzinho();	
		},
		error: function(XMLHttpRequest, textStatus, errorThrown){
			alert(errorThrown);
		}
	  });
	});
	
	//envio de opiniao - comentario de minhas compras
	$('.add-endereco').live('click', function(e) {	
		var validacao = liberaBoleto(true);
		if(validacao!=false){	
			refresh();
			$.ajax({			
				type: "POST",			
				data:{				
				add_endereco: true},			
				url: location.href,
				success: function(result){		
				$('#div-content').html(result);	
				liberaBoleto(false);
				fecharefresh();	
			},
			error: function(XMLHttpRequest, textStatus, errorThrown){
				alert(errorThrown);
			}
		  });
		}		
	});
	
	//envio de opiniao - comentario de minhas compras
	$('.envio').live('click', function(e) {
		var opiniao 		= $('#opiniao').val();
		var id_comentario 	= $('#id_comentario').val();		
		var id_oferta 		= $('#id_oferta').val();
		var textarea 		= $('.textarea').val();
		var limit           = $('#id').attr('data-token');
		refresh();
		$.ajax({			
			type: "POST",			
			data:{				
			opiniao: opiniao, id_comentario: id_comentario, id_oferta: id_oferta, textarea: textarea, limit: limit},			
			url: location.href,
			success: function(result){					
				$('#div-content').html(result)	
				$('.tabela-padrao .status').each(function(i, val){		
					$(this).attr('style','background-image:url(../users/img/icones/tabela-status-'+$(val).attr('status')+'.png)');
				});						
			fecharefresh();
		},
		error: function(XMLHttpRequest, textStatus, errorThrown){
			alert(errorThrown);
		}
	  });		
	});
	
	
	
	
	//jogando valores para hidden do popup
	$('#compraspopup').live('click', function(e) {		
		$('#id_oferta').val($(this).attr('id_oferta'));	
		$('#id_comentario').val($(this).attr('id_comentario'));		
	});

	$('.tabela-padrao tbody tr').live('click', function(e) {		
		if($(this).next('.linha_detalhe_').css('display') == 'none'){
			$('.linha_detalhe_').hide();
			$(this).next('.linha_detalhe_').show();			
		}else{
			$('.linha_detalhe_').hide();
		}
		
		if($(this).next('.linha_detalhe_desejos').css('display') == 'none'){
			$('.linha_detalhe_desejos').hide();
			$(this).next('.linha_detalhe_desejos').show();			
		}//else{
			//$('.linha_detalhe_desejos').hide();
		//}
		
		
	});
	
	//$('.tabela-padrao .status').live('each', function(e, i, val) {
	$('.tabela-padrao .status').each(function(i, val){		
		$(this).attr('style','background-image:url(../users/img/icones/tabela-status-'+$(val).attr('status')+'.png)');
	});
	
	$('.fotos-do-item').cycle({
		fx:      'scrollRight',
		timeout: 	0, 	
		pager:  '#navegacaodiv'
	});
	
	$('.selecione-metrica input').disableSelection();
	$('.selecione-metrica li div').click(function(){
		
		if($(this).parent().find('ul').css('display') == 'block'){
			$(this).parent().find('ul').hide();
		}else{
			$('.selecione-metrica ul').hide();
			$(this).parent().find('ul').show();
		};
	})	
	
	$('.selecione-metrica li span').height($(this).parent().height());		
	$('.selecione-metrica li ul a').click(function(){
		$(this).parent().parent().parent().find('input').val($(this).html());	
		$("#parcelas").val($(this).attr('valor_parcela'));		
		var chave = $(this).attr('tipo');
		var valor = $(this).html();				
		$.ajax({			
			type: "POST",			
			data:{				
			chave: chave, valor: valor},			
			url: location.href,
			success: function(result){												
		},
		error: function(XMLHttpRequest, textStatus, errorThrown){
			alert(errorThrown);
		}
		});
		console.log($(this).parent().parent().parent().find('ul').hide());
	
	});
	
	// Codido de Abas
	$('.abas a').click(function(){
		$('.abas a').parent().removeClass('ativo');
		$(this).parent().addClass('ativo');

		$('.area-abas div').slideUp();
		var div = '.'+$(this).attr('alt');
		$(div).slideDown();
		var altura = $(div).offset().top;
		$('body').animate({scrollTop: altura});


	});	

	//Codigo de detalhes da Oferta
	$('.thumb-destaque').click(function(){
		$('.thumb-destaque').removeClass('ativa');
		$(this).addClass('ativa');
		$('.imagem-destaque').attr('src',$(this).attr('src'));
	});


	$('.detalhe-do-item .faltam-dias').css('height',$('.detalhe-do-item').css('height'));
	$('.detalhe-do-item .compre-agora').css('height',$('.detalhe-do-item').css('height'));

	$("#linkmaiscompras").live('click', function(e) {		
		var limit = $('#id').attr('data-token');
		$.ajax({			
			type: "POST",			
			data:{				
			limit: limit},			
			url: location.href,
			success: function(result){								
				$('#compras-lista').html(result)				
		},
		error: function(XMLHttpRequest, textStatus, errorThrown){
			alert(errorThrown);
		}
		});
	});
	
	 $('.comentario a').live('click', function(e) {
			$('.linha_detalhe').hide();
			$(this).parent().parent().next('.linha_detalhe').show();
			
		});
	
	$('.mais-conteudo').live('click', function(e){		
		//$('.comentario a').click(function(){
			$('.linha_detalhe').hide();
			$(this).parent().next('tr').show();
		});	
	
	//recebe id unico da linha(no banco de dados) para mudar nome da div - id do comentario - id da oferta para requisicao ajax
	$('.linha_detalhe button').live('click', function(e){		
		//$('.linha_detalhe button').click(function(){				
			var id = $(this).val();	
			var comentario = $("#textareacomentario"+id).val();						
			$.ajax({			
				type: "POST",			
				data:{				
				comentario: comentario, ids: id},			
				url: location.href,
				success: function(result){
										
			},
			error: function(XMLHttpRequest, textStatus, errorThrown){
				alert(errorThrown);
			}
			});		
			
		})
		
	$('.txtPesquisa').live('focus', function(e){						
		$(this).animate({width: '300px'},'fast');
		$(this).val('');		
	});
	
	$('.txtPesquisa').live('blur', function(e){		
		if($(this).val() == 'pesquise aqui' || $(this).val() == ''){
			$(this).animate({width: '120px'},'fast');
			$(this).val('pesquise aqui');
		}
	});	
	
	$('.txtPesquisa').keypress(function(e){			
		var pesquisa = $('.txtPesquisa').val();		
		$.ajax({			
			type: "POST",			
			data:{				
			pesquisa: pesquisa, ref: 'true'},			
			url: location.href,
			success: function(result){								
				$('#pesquisa-company').html(result)				
		},
		error: function(XMLHttpRequest, textStatus, errorThrown){
			alert(errorThrown);
		}
		});
	}); 

	
	
	$('.detalhe-endereco-entrega input').focus(function(){
		if($(this).val() == $(this).attr('alt')){
			$(this).val('');
		}
	}).blur(function(){
		if($(this).val() == '' || $(this).val() == ' '){
			$(this).val($(this).attr('alt'));
		}
	});

	$('#Numero').live('blur', function(e){	
		var numero = $('#Numero').val();		
		$.ajax({			
			type: "POST",			
			data:{				
			numero: numero},			
			url: location.href,
			success: function(result){
			liberaBoleto(false);										
		},
		error: function(XMLHttpRequest, textStatus, errorThrown){
			alert(errorThrown);
		}
	  });
	});	
	
	$('#Endereco').live('blur', function(e){	
		var endereco = $('#Endereco').val();		
		$.ajax({			
			type: "POST",			
			data:{				
			endereco: endereco},			
			url: location.href,
			success: function(result){
			liberaBoleto(false);										
		},
		error: function(XMLHttpRequest, textStatus, errorThrown){
			alert(errorThrown);
		}
	  });
	});	
	
	$('#Bairro').live('blur', function(e){	
		var bairro = $('#Bairro').val();		
		$.ajax({			
			type: "POST",			
			data:{				
			bairro: bairro},			
			url: location.href,
			success: function(result){
			liberaBoleto(false);										
		},
		error: function(XMLHttpRequest, textStatus, errorThrown){
			alert(errorThrown);
		}
	  });
	});	
	
	$('#Cidade').live('blur', function(e){	
		var cidade = $('#Cidade').val();		
		$.ajax({			
			type: "POST",			
			data:{				
			cidade: cidade},			
			url: location.href,
			success: function(result){
			liberaBoleto(false);										
		},
		error: function(XMLHttpRequest, textStatus, errorThrown){
			alert(errorThrown);
		}
	  });
	});	
	
	$('#Estado').live('blur', function(e){	
		var estado = $('#Estado').val();		
		$.ajax({			
			type: "POST",			
			data:{				
			estado: estado},			
			url: location.href,
			success: function(result){
			liberaBoleto(false);										
		},
		error: function(XMLHttpRequest, textStatus, errorThrown){
			alert(errorThrown);
		}
	  });
	});
	$('#Descricao').live('blur', function(e){	
		var descricao = $('#Descricao').val();		
		$.ajax({			
			type: "POST",			
			data:{				
			descricao: descricao},			
			url: location.href,
			success: function(result){
			liberaBoleto(false);										
		},
		error: function(XMLHttpRequest, textStatus, errorThrown){
			alert(errorThrown);
		}
	  });
	});
	$('#Complemento').live('blur', function(e){	
		var complemento = $('#Complemento').val();		
		$.ajax({			
			type: "POST",			
			data:{				
			complemento: complemento},			
			url: location.href,
			success: function(result){
			liberaBoleto(false);										
		},
		error: function(XMLHttpRequest, textStatus, errorThrown){
			alert(errorThrown);
		}
	  });
	});
	
	
	$("#CPF").mask("999.999.999-99");	
	$("#Expiracao").mask("99/99");	
	$("#DataNascimento").mask("99/99/9999");			
	$("#Telefone").mask("(99)999999999");
	$("#CEP").mask("99999-999");
	$("#data-nascimento").mask("99-99-9999");
	
	/* Validação Form Detalhes da Oferta */
	$('form#dadosCartaoCredito').validate({
		highlight: function(element) {
		     $(element).addClass('form-validation');
		},
		unhighlight: function(element, errorClass) {
		     $(element).removeClass('form-validation');
		},
		rules:{
			"instituicao":{required:true},
			"parcels":{required:true},
			"Numero":{required:true},
			"Expiracao":{required:true},
			"CodigoSeguranca":{required:true},
			"Portador":{required:true},
			"CPF":{required:true},
			"DataNascimento":{required:true},
			"Telefone":{required:true}
		},
		messages:{
			"instituicao":{required:""},
			"parcels":{required:""},
			"Numero":{required:""},
			"Expiracao":{required:""},
			"CodigoSeguranca":{required:""},
			"Portador":{required:""},
			"CPF":{required:""},
			"DataNascimento":{required:""},
			"Telefone":{required:""}
		}
	});
	
	
	
	
	/* Validação Form Detalhes da Oferta */
	$('form#UserDadosCadastraisForm').validate({
		highlight: function(element) {
		     $(element).addClass('form-validation');
		},
		unhighlight: function(element, errorClass) {
		     $(element).removeClass('form-validation');
		},
		rules:{
			"data[User][name]":{required:true},
			"data[User][birthday]":{required:true},
			"data[User][zip_code]":{required:true},
			"data[User][address]":{required:true},
			"data[User][number]":{required:true},
			"data[User][district]":{required:true},
			"data[User][state]":{required:true},
			"data[User][city]":{required:true}			
		},
		messages:{
			"data[User][name]":{required:""},
			"data[User][birthday]":{required:""},
			"data[User][zip_code]":{required:""},
			"data[User][address]":{required:""},
			"data[User][number]":{required:""},
			"data[User][district]":{required:""},
			"data[User][state]":{required:""},
			"data[User][city]":{required:""}
		}
	});
	
	
	function liberaBoleto(tipo){			
		var erro = false;
		var pesquisa = $('.erro').html().search('Ocorreu');
		var numero = isNumeric($('#Numero').val());
		
		if(pesquisa>0){
			$('#CEP').focus();
			$('#CEP').removeClass("textbox").addClass("textbox erro");
			erro = true;			
			//alert('1');
		}
		else if(jQuery.trim($("#Endereco").val()) == "" || $("#Endereco").val()=='Endereco'){
			erro = true;			
			$('#Endereco').removeClass("textbox").addClass("textbox erro");
			$("#Endereco").val('Endereco');
			
		}
		else if(jQuery.trim($("#Numero").val()) == "" || $("#Numero").val()=='Numero'){
			erro = true;
			$('#Numero').removeClass("textbox").addClass("textbox erro");
			$("#Numero").val('Numero');
			//alert('3');
		}		
		else if(jQuery.trim($("#Bairro").val()) == "" || $("#Bairro").val()=='Bairro'){
			erro = true;
			$('#Bairro').removeClass("textbox").addClass("textbox erro");
			$("#Bairro").val('Bairro');
			//$('#Bairro').focus();
			//alert('5');
		}
		else if(jQuery.trim($("#Cidade").val()) == "" || $("#Cidade").val()=='Cidade'){
			erro = true;
			$('#Cidade').removeClass("textbox").addClass("textbox erro");
			$("#Cidade").val('Cidade');
			//$('#Cidade').focus();
			//alert('6');
		}
		else if(jQuery.trim($("#Estado").val()) == "" || $("#Estado").val()=='Estado'){
			erro = true;
			$('#Estado').removeClass("textbox").addClass("textbox erro");
			$("#Estado").val('Estado');
			//$('#Estado').focus();
			//alert('7');
		}
		else if(jQuery.trim($("#Descricao").val()) == "" || $("#Descricao").val()=='Descricao do Endereco'){
			erro = true;
			$('#Descricao').removeClass("textbox").addClass("textbox erro");
			$("#Descricao").val('Descricao do Endereco');
			//$('#Estado').focus();
			//alert('7');
		}
		else if(numero==false){
			erro = true;
			$('#Numero').removeClass("textbox").addClass("textbox erro");
			$("#Numero").val('Numero');
			//alert('4');
		}
		if(erro==false){
			if(tipo==false){
				$('input').removeClass("erro");						
				$('#selecione-o-meio a').show();
			}			
		}else{
			$("#selecione-o-meio a").css("display", "none");
			return false;
		}
		
	}
	
	if($('#CEP').val().length == $('#CEP').attr('maxlength')){		
		$('.detalhes-do-endereco').slideDown('fast');
		$('.detalhes-do-endereco input').first();		
		liberaBoleto(false);	
	}
	
	$('#buscar_cep').live('click', function(e){							
		$('.detalhes-do-endereco').slideDown('fast');
		$('.detalhes-do-endereco input').first();
		var valor = $('#CEP').val();			
		refresh();
		$.ajax({			
			type: "POST",			
			data:{				
			cep: valor},			
			url: location.href,
			success: function(result){								
				$('#div-content').html(result)									
				liberaBoleto(false);
				fecharefresh();
		},
		error: function(XMLHttpRequest, textStatus, errorThrown){
			alert(errorThrown);
		}
	  });					
	});
	
	
	
	$('.selecione-metrica li div').live('click', function(e){		
		$(this).parent().find('ul').fadeIn();
		
	})

	
	$('.selecione-metrica li ul').width($('.selecione-metrica').width());
	//$('.selecione-metrica li ul').css('margin-top',$('.selecione-metrica').height());
	
	$('.selecione-metrica li ul a').live('click', function(e){	
		$(this).parent().parent().parent().find('input').val($(this).html());
		var chave = $(this).attr('tipo');
		var valor = $(this).html();		
		$.ajax({			
			type: "POST",			
			data:{				
			chave: chave, valor: valor},			
			url: location.href,
			success: function(result){				
				liberaBoleto(false);
		},
		error: function(XMLHttpRequest, textStatus, errorThrown){
			alert(errorThrown);
		}
	   });
		console.log($(this).parent().parent().parent().find('ul').fadeOut());
	});


	$('.menos').live('click', function(e){	
		var valor = $(this).parent().find('input').val();
		valor = valor - 1;
		if(valor == 0){
			valor = 1;
		}else{
			$(this).parent().find('input').val(valor);
		}
		refresh();
		$.ajax({			
			type: "POST",			
			data:{				
			quantidade: valor},			
			url: location.href,
			success: function(result){							
				$('#div-content').html(result)
				liberaBoleto(false);
				fecharefresh();
		},
		error: function(XMLHttpRequest, textStatus, errorThrown){
			alert(errorThrown);
		}
	  });		
	});
	
		
	$('.mais').live('click', function(e){
		var valor = $(this).parent().find('input').val();
		valor = ++valor;
		$(this).parent().find('input').val(valor);
		refresh();
		$.ajax({			
			type: "POST",			
			data:{				
			quantidade: valor},			
			url: location.href,
			success: function(result){				
				$('#div-content').html(result)
				liberaBoleto(false);
				fecharefresh();
		},
		error: function(XMLHttpRequest, textStatus, errorThrown){
			alert(errorThrown);
		}
	 });	 
	})
	
	
	//ajax quantidade	
	$('#purchase').live('blur', function(e) {
		var valor = $('#purchase').val();
		refresh();
		$.ajax({			
			type: "POST",			
			data:{				
			quantidade: valor},			
			url: location.href,
			success: function(result){				
				$('#div-content').html(result)		
				liberaBoleto(false);
				fecharefresh();
		},
		error: function(XMLHttpRequest, textStatus, errorThrown){
			alert(errorThrown);
		}
	 });
	});

	
	// Inicio de codigo da lista de ofertas
	$('.lista-de-ofertas .oferta-destaque .descricoes').prepend('<div class="seta-de-ofertas"><a href=""><img src="users/img/icones/seta-de-ofertas.png"></a><span>mais informacoes</span></div>');
	$('.lista-de-ofertas .oferta-destaque-principal .descricoes').prepend('<div class="seta-de-ofertas"><a href=""><img src="users/img/icones/seta-de-ofertas.png"></a><span>mais informacoes</span></div>');

	$('.oferta-destaque').each(function(e, a){
	if($(a).find('div').last().attr('class') == 'seta-de-ofertas'){
		$$(this).find('.seta-de-ofertas').addClass('lado-direito-esquerdo');
	};
	});	
	$('.seta-de-ofertas a').mouseover(function(){
		$(this).parent().find('span').fadeIn();
	}).mouseout(function(){
		$(this).parent().find('span').fadeOut();
	});
	

		
	
	$('.conteudo-completo h4').click(function(){
		$(this).parent().hide();		
	});
	
	$(".cep").mask("99999-999");
	$('.area-formulario').each(function(e, a){
		$(a).width($(this).attr('tamanho'));
	});
	$('.area-botoes').each(function(e, a){
		$(a).width($(this).attr('tamanho'));
	});


	// Codido de Abas
	$('.abas a').click(function(){
		$('.abas a').parent().removeClass('ativo');
		$(this).parent().addClass('ativo');
	
		$('.area-abas div').hide();
		var div = '.'+$(this).attr('alt');
	
		$(div).show();
		$('body').scrollTop($(div).offset().top);
	
	});	
		$('.ver-descricoes').click(function(){		
			$(window).scrollTop(500);
		});
	
		$( ".uf" ).autocomplete({
	      source: itensUF
	    });
	    $( ".cidade" ).autocomplete({
	      source: itensCidade
	    });
	    	    
	

	//Codigo de detalhes da Oferta
	$('.thumb-destaque').click(function(){
		$('.thumb-destaque').removeClass('ativa');
		$(this).addClass('ativa');
		$('.imagem-destaque').attr('src',$(this).attr('src'));
	});
	
	

	$('.linha_detalhe button').click(function(){
		$('.linha_detalhe button').removeClass('ativo');
		$(this).addClass('ativo');
	});

	$('.abriroquehadenovo').click(function(){
		if($('.oquehadenovo .conteudooquehadenovo').css('display','none')){
			$('.oquehadenovo .conteudooquehadenovo').slideDown();
		}
	});
	$('.conteudooquehadenovo h2 a').click(function(){
		$('.oquehadenovo .conteudooquehadenovo').fadeOut();
	});
	$('.oquehadenovo .conteudooquehadenovo').height($(window).height());
	

	$('.on-off').click(function(){		
		if($(this).find('a').css('left') == '30px'){
			$(this).find('a').animate({
				left: '0px'
			});
		}else{
			$(this).find('a').animate({
				left: '30px'
			});
		}
	});
	

	$('.area-esquerda2 li a').click(function(){
		$('.area-esquerda2 li').removeClass('ativo');	
		$(this).parent().addClass('ativo');
	})
	//$('h1').append('<img src="imagens/icones/icone-titulo.png">');	
	$('h2').append('<img src="users/img/icones/icone-titulo-h2.png">');
	$('h3').prepend('<div class="img"></div>');



	var multi = new Array();
	$('.multi-select a').click(function(){
		if($(this).attr('class') == 'ativo'){
			check.removeObjectWithValue($(this).attr('valor'));
			$(this).removeClass('ativo');
		}else{
			check.addObjectWithValue($(this).attr('valor'));
			$(this).addClass('ativo');	
		};	
		$(this).parent().parent().find('input').val($.stringify(check));	
	});


	$('.radiobutton a').click(function(){		
		$('.radiobutton a').removeClass('ativo');
		$(this).addClass('ativo');
		$(this).parent().parent().find('input').val($(this).attr('valor'));
	});	
	
	var check = new Array();

	$('.checkbox a').click(function(){
		if($(this).attr('class') == 'ativo'){
			check.removeObjectWithValue($(this).attr('valor'));
			$(this).removeClass('ativo');
		} else {
			check.addObjectWithValue($(this).attr('valor'));
			$(this).addClass('ativo');
		};
		$(this).parent().parent().find('input').val($.stringify(check));
	});
	
	//ajax CEP	
	$("#linkcep").click(function() {		
		var cep = $('#cep').val();				
		$.ajax({			
			type: "POST",			
			data:{				
			cep: cep},			
			url: location.href,
			success: function(result){		
				$('#ajax-cep').slideDown('fast');
				$('#ajax-cep').html(result);
							
		},
		error: function(XMLHttpRequest, textStatus, errorThrown){
			alert(errorThrown);
		}
		});
	});	
	
	//ajax quantidade		
	$('#purchase').live('blur', function(e) {
		var quantidade = $('#purchase').val();
		$.ajax({			
			type: "POST",			
			data:{				
			quantidade: quantidade},			
			url: location.href,
			success: function(result){				
				$('#content-form').html(result)				
		},
		error: function(XMLHttpRequest, textStatus, errorThrown){
			alert(errorThrown);
		}
		});
	});
	/*
	//ajax quantidade		
	$('#sendToMoip').live('click', function(e) {
		$.ajax({			
			type: "POST",			
			data:{			
			//tipo de pagamento 2 = CARTAO DE CREDITO MASTER CARD - 4 = CARTAO DE CREDITO VISA - 5 = CARTAO DE CREDITO AMERICAN EXPRESS
			tipo_pagamento: 1},			
			url: location.href,
			success: function(result){				
				alert(result);				
		},
		error: function(XMLHttpRequest, textStatus, errorThrown){
			alert(errorThrown);
		}
		});
	});
	*/
	
	$('#link-boleto a').live('click', function(e) {
		window.location = "/portal/user/retorno/compras";
	});
	
	$('#selecione-o-meio a').live('click', function(e) {		
		refresh();
		$.ajax({			
			type: "POST",			
			data:{				
			compra: true},			
			url: location.href,
			success: function(result){	
				//visualizar mensagem de erro do moip
				//alert(result);
				//$('#MoipWidget').html(result);
				if(result==''){
					alert('Ops!! Ocorreu um erro na compra. Verifique seus dados')
					window.location = location.href;
				}else{
					//window.location = location.href;
					$('#MoipWidget').attr('data-token', result);
				}
				
				fecharefresh();
		},
		error: function(XMLHttpRequest, textStatus, errorThrown){
			alert(errorThrown);
		}
		});
		
		//congelando a pagina hahhahahahahha eu sou muito mal
		$('#purchase').attr("disabled", true);
		$('#CEP').attr("disabled", true);
		$('#Endereco').attr("disabled", true);
		$('#Numero').attr("disabled", true);
		$('#Complemento').attr("disabled", true);
		$('#Bairro').attr("disabled", true);
		$('#Cidade').attr("disabled", true);
		$('#Estado').attr("disabled", true);
		$('#Descricao').attr("disabled", true);
		$('.add-endereco').attr("disabled", true);
		$('.linkpopup').attr("disabled", true);		
		$('.tabela-padrao *').click(function(){return false;})
		$('.detalhe-endereco-entrega *').click(function(){return false;})
		
		//chamando pagamento
		$(this).parent().slideUp('fast');
		$('#'+$(this).attr('caminho')).slideDown('fast');
	});

		$('#cartao input').first().css('margin-left','0px');
		$('#cartao input').focus(function(){
			if($(this).val() == $(this).attr('alt')){
				$(this).val('');
			}
		}).blur(function(){
			if($(this).val() == '' || $(this).val() == ' '){
				$(this).val($(this).attr('alt'));
			}
	});		
		
		$('.detalhe-endereco-entrega input').focus(function(){
			if($(this).val() == $(this).attr('alt')){
				$(this).val('');
			}
		}).blur(function(){
			if($(this).val() == '' || $(this).val() == ' '){
				$(this).val($(this).attr('alt'));
			}
		});
		
	
		
	function isNumeric(str)
	{
	  var er = /^[0-9]+$/;
	  return (er.test(str));
	}	
});
	

	
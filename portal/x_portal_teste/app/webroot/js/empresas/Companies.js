$(function(){   
	$('.usuario').live('click', function(e) {	
		$('#usuario_login').val('');
	});
	$('.senha').live('click', function(e) {	
		$('#usuario_senha').val('');
	});
//page = $.load(window.location.href);

//$.load(window.location.href, function(response, status, xhr) {
//	if(status == 'success'){
//		stop();
//	}
//});

$('.tip_true').mouseover(function(){
	console.log($(this).attr('title'));
	$(this).append('<div class="tip_true_content">'+$(this).attr('informacoes')+'</div>');
	$('.tip_true_content').fadeIn('fast');
});
$('.tip_true').mouseout(function(){
	$('.tip_true_content').fadeOut('fast');
	$('.tip_true_content').remove();
});

	$('.nao-logar-agora').mouseover(function(){
		$(this).animate({
			rotate: '+=360deg'
		},100);
	})
	$('.nao-logar-agora').mouseout(function(){
		$(this).animate({
			rotate: '-=360deg'
		},100);
	})	

	$(".cep").mask("99999-999");
	$('#Expiracao').focus(function(){
		$(this).mask('99/9999');
	})


	$('.abriropcoesusuario span').prepend('<img src="http://secure.trueone.com.br/portal/app/webroot/img/icones/arrow-usuario-logado.png">');
	$('.area-formulario').each(function(e, a){
		$(a).width($(this).attr('tamanho'));
	});
	$('.area-botoes').each(function(e, a){
		$(a).width($(this).attr('tamanho'));
	});

// Codido de Abas
$('.abas a').live('click', function(e) {	
	$('.abas a').parent().removeClass('ativo');
	$(this).parent().addClass('ativo');

	$('.area-abas div').slideUp();
	var div = '.'+$(this).attr('alt');
	$(div).slideDown();
	var altura = $(div).offset().top;
	$('body').animate({scrollTop: altura});


});	


	$( ".uf" ).autocomplete({
      source: itensUF
    });
    $( ".cidade" ).autocomplete({
      source: itensCidade
    });

	$('.comentario a').live('click', function(e) {	
		$('.linha_detalhe').hide();
		$(this).parent().parent().next('.linha_detalhe').show();
		
	});
$('.area-esquerda').tooltip({
	show: null,
	open: function( event, ui ) {
       	//ui.tooltip.animate({ left: ui.tooltip.position().left + 10 }, "fast" );
     },
	position: {
        my: "left+110",
        at: "left bottom-25"
    }
});



	//Codigo de detalhes da Oferta
	$('.thumb-destaque').live('click', function(e) {	
		$('.thumb-destaque').removeClass('ativa');
		$(this).addClass('ativa');
		$('.imagem-destaque').attr('src',$(this).attr('src'));
	});


	$('.detalhe-do-item .faltam-dias').css('height',$('.detalhe-do-item').css('height'));
	$('.detalhe-do-item .compre-agora').css('height',$('.detalhe-do-item').css('height'));




// Inicio de codigo da animacao da area-esquerda

	$('.area-esquerda li a').each(function(e, a){
		//$(a).attr('alt', $(this).attr('title'));		
	});
		
	$('.area-esquerda li a').live('click', function(e) {	

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






	$('.linha_detalhe button').live('click', function(e) {	
		$('.linha_detalhe button').removeClass('ativo');
		$(this).addClass('ativo');
	});

	$('.abriroquehadenovo').live('click', function(e) {	
		if($('.oquehadenovo .conteudooquehadenovo').css('display','none')){
			$('.oquehadenovo .conteudooquehadenovo').slideDown();
		}
	});
	$('.conteudooquehadenovo h2 a').live('click', function(e) {	
		$('.oquehadenovo .conteudooquehadenovo').fadeOut();
	});
	$('.oquehadenovo .conteudooquehadenovo').height($(window).height());



	$('.abriropcoesusuario').live('click', function(e) {	
		if($('.conteudoopcoesusuario').css('display') == 'block'){
			$('.conteudoopcoesusuario').slideUp('fast');
		}else{
			$('.conteudoopcoesusuario').slideDown('fast');
		}
	});


	// Inicio de codigo POPUP
		

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
	$('.fechar').live('click', function(e) {		
		$('.bg_popup').fadeOut();
		$('body').css('overflow','auto');	
	});


	$('.popup').mouseover(function(){
		var altura = ($('.bg_popup').height() / 2) - $('.popup').height() / 2;
		// $('.popup').css('margin-top', altura);		

		$('.popup').animate({
			marginTop: altura
		},'fast');

	});

	// Fim de codigo POPUP
	

	$('.area-esquerda2 li a').live('click', function(e) {	
		$('.area-esquerda2 li').removeClass('ativo');	
		$(this).parent().addClass('ativo');
	})


	//$('h1').append('<img src="imagens/icones/icone-titulo.png">');	
	$('h2').append('<img src="imagens/icones/icone-titulo-h2.png">');
	$('h3').prepend('<div class="img"></div>');


	
	$('.select').live('click', function(e) {	
		if($(this).find('ul').css('display') == 'block'){
			$(this).find('ul').slideToggle('fast');
		}else{
			$(this).find('ul').slideToggle('fast');
		}

		$(this).find('ul li').live('click', function(e) {	
			$(this).parent().parent().find('strong').html($(this).html());
			$(this).parent().parent().find('input').val($(this).attr('valor'));
		});
	});


	/*
	var multi = new Array();
	$('.multi-select a').live('click', function(e) {	
		if($(this).attr('class') == 'ativo'){
			check.removeObjectWithValue($(this).attr('valor'));
			$(this).removeClass('ativo');
		}else{
			check.addObjectWithValue($(this).attr('valor'));
			$(this).addClass('ativo');	
		};	
		$(this).parent().parent().find('input').val($.stringify(check));	
	});
*/

	$('.radiobutton a').live('click', function(e) {	
		$('.radiobutton a').removeClass('ativo');
		$(this).addClass('ativo');
		$(this).parent().parent().find('input').val($(this).attr('valor'));
	});	



	var check = new Array();

	$('.checkbox a').live('click', function(e) {	
		if($(this).attr('class') == 'ativo'){
			check.removeObjectWithValue($(this).attr('valor'));
			$(this).removeClass('ativo');
		} else {
			check.addObjectWithValue($(this).attr('valor'));
			$(this).addClass('ativo');
		};
		$(this).parent().parent().find('input').val($.stringify(check));
	});	




// Inicio de Codigo Formas de Pagamento Atualizado 21/01/2013
$('#selecione-o-meio a').live('click', function(e) {	
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

	$('#CEP').keyup(function(){
		if($(this).val().length == $(this).attr('maxlength')){
			$('.detalhes-do-endereco').slideDown('fast');
			$('.detalhes-do-endereco input').first();
		}
	});

	$('.selecione-metrica input').disableSelection();
	$('.selecione-metrica li div').live('click', function(e) {	
		if($(this).parent().find('ul').css('display') == 'block'){
			$(this).parent().find('ul').hide();
		}else{
			$('.selecione-metrica ul').hide();
			$(this).parent().find('ul').show();
		};
	})



	//$('.selecione-metrica li ul').width($('.selecione-metrica').width());
	$('.selecione-metrica li span').height($(this).parent().height());
	


	$('.selecione-metrica li ul a').live('click', function(e) {	
		$(this).parent().parent().parent().find('input').val($(this).html());
		console.log($(this).parent().parent().parent().find('ul').hide());
	});



	$('.menos').live('click', function(e) {	
		var valor = $(this).parent().find('input').val();
		valor = valor - 1;
		if(valor != 0){
			$(this).parent().find('input').val(valor);
		}
	});
	$('.mais').live('click', function(e) {	
		var valor = $(this).parent().find('input').val();
		valor = ++valor;
		$(this).parent().find('input').val(valor);
	});

// Inicio de codigo Botao Pesquisa na tabela Atualizado 22/01/2013
$('.txtPesquisa').focus(function(){
	$(this).animate({width: '300px'},'fast');
	$(this).val('');
});
$('.txtPesquisa').blur(function(){
	if($(this).val() == 'pesquise aqui' || $(this).val() == ''){
		$(this).animate({width: '120px'},'fast');
		$(this).val('pesquise aqui');
	}
});


//Inicio de Conteudo pagina Assinaturas Atualizado 22/01/2013
$('.ativo-inativo').live('click', function(e) {	
	if($(this).attr('alt') == 'ativado'){
		$(this).removeClass('ativado');
		$(this).attr('alt','inativado')
	}else{
		$(this).addClass('ativado');
		$(this).attr('alt','ativado')
	}
});

    $('.txtPesquisa').autocomplete({
      source: AssinarEmpresas
    })




// Inicio de codigo do login
	
	$('.text input').focus(function(){
		if($(this).attr('title') ==  $(this).val()){
			$(this).val('');
		};
	});
	$('.text input').blur(function(){
		if($(this).val() == '' || $(this).val() == ' '){
			$(this).val($(this).attr('title'));
		};
	})
	

//fim do $()
});
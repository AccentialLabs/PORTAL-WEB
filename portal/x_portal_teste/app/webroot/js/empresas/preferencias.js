$(function(){
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
	
	$('.on-off').live('click', function(e) {	
		if($(this).find('a').css('left') == '30px'){
			$(this).find('a').animate({
				left: '0px'
			},'fast');			
			$(this).parent().parent().find('input').attr('value','0');
			$('.Hidden_ValoresDatas').css("display","block");
		}else{
			$(this).find('a').animate({
				left: '30px'
			},'fast');
			$(this).parent().parent().find('input').attr('value','1');			
			$('.Hidden_ValoresDatas').css("display","none");
		}
	});
	
	$('#CNPJ').mask('999.999.999/9999-99',{reverse:true});
	$('#CEP').mask('99999-999',{reverse:true});
	$('#telefone').mask('99-9999-9999',{reverse:true});
	$('#cpf').mask('999.999.999-99',{reverse:true});
	$('#telefone').mask('(99)9999-9999');
	$('#telefone_2').mask('(99)9999-9999');	
	$('#celular').mask('(99)9-9999-9999');
	
	/* Validação Form Preferencias dados - cadastrais */
	$('form#CompanyPreferenciasForm').validate({			
		highlight: function(element) {
		     $(element).addClass('offer-detail-form-input-validation');
		},
		unhighlight: function(element, errorClass) {
		     $(element).removeClass('offer-detail-form-input-validation');
		},
		rules:{			
			"data[Company][fancy_name]":{required:true},
			"data[Company][cnpj]":{required:true},			
			"data[Company][email]":{required:true},
			"data[Company][responsible_name]":{required:true},
			"data[Company][zip_code]":{required:true},
			"data[Company][address]":{required:true},
			"data[Company][number]":{required:true},
			"data[Company][district]":{required:true},
			"data[Company][city]":{required:true},
			"data[Company][state]":{required:true},
			"data[Company][responsible_cpf]":{required:true},
			"data[Company][responsible_email]":{required:true},
			"data[Company][responsible_phone]":{required:true}
			
		},
		messages:{			
			"data[Company][fancy_name]":{required:""},
			"data[Company][cnpj]":{required:""},			
			"data[Company][email]":{required:""},
			"data[Company][responsible_name]":{required:""},
			"data[Company][zip_code]":{required:""},
			"data[Company][address]":{required:""},
			"data[Company][number]":{required:""},
			"data[Company][district]":{required:""},
			"data[Company][city]":{required:""},
			"data[Company][state]":{required:""},
			"data[Company][responsible_cpf]":{required:""},
			"data[Company][responsible_email]":{required:""},
			"data[Company][responsible_phone]":{required:""}
		}
	});
	
	
	/* Validação Form Preferencias dados - cadastrais */
	$('form#CompanyPreferencePreferenciasForm').validate({			
		highlight: function(element) {
		     $(element).addClass('offer-detail-form-input-validation');
		},
		unhighlight: function(element, errorClass) {
		     $(element).removeClass('offer-detail-form-input-validation');
		},
		rules:{			
			"data[CompanyPreference][agency]":{required:true},
			"data[CompanyPreference][account]":{required:true},
			"data[CompanyPreference][account_name]":{required:true},
			"data[CompanyPreference][cpf]":{required:true},			
			
		},
		messages:{			
			"data[CompanyPreference][agency]":{required:""},
			"data[CompanyPreference][account]":{required:""},			
			"data[CompanyPreference][account_name]":{required:""},
			"data[CompanyPreference][cpf]":{required:""}			
		}
	});
	
	/* Validação Form Preferencias dados - cadastrais */
	$('form#CompanySenhaPreferenciasForm').validate({			
		highlight: function(element) {
		     $(element).addClass('offer-detail-form-input-validation');
		},
		unhighlight: function(element, errorClass) {
		     $(element).removeClass('offer-detail-form-input-validation');
		},
		rules:{			
			"senha_atual":{required:true},
			"senha_nova":{required:true},
			"senha_nova_confirm":{required:true}								
		},
		messages:{			
			"senha_atual":{required:""},
			"senha_nova":{required:""},			
			"senha_nova_confirm":{required:""}				
		}
	});
	
	$('#envio_senha').live('click', function(e){					
		if($('#senha_nova').val()!=$('#senha_nova_confirm').val()){			
			$('#aviso').html("<font color='red'><b>ERRO NA CONFIRMACAO DE SENHA</b></font>");
			return false;
		}else if(md5($('#senha_atual').val())!=$('#hash').val()){
			$('#aviso').html("<font color='red'><b>SENHA ATUAL INCORRETA</b></font>");
			return false;
		}else{
			$('#aviso').html("");
		}
	});
	
	
	
	$('#CEP').live('keyup', function(e){		
		if($(this).val().length == 9){				
			var valor = $(this).val();
			valor = valor.replace('-', '');			
			$.ajax({			
				type: "POST",			
				data:{				
				cep: valor},			
				url: location.href,
				success: function(result){	
					//alert(result);
					var data = jQuery.parseJSON(result);
					$('#Endereco').focus();
					$('#Endereco').val(data.logradouro);
					$('#Bairro').val(data.bairro);
					$('#Estado').val(data.uf);
					$('#Cidade').val(data.localidade);
			},
			error: function(XMLHttpRequest, textStatus, errorThrown){
				alert(errorThrown);
			}
		  });
		}
	});	
});

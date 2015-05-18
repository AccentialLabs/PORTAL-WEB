$(document).ready(function(){

  $("#resultado").hide();

  $('#showXml').modal('toggle')

  $('#tabs a:first').tab('show');

  //Exibi o token no Form
  $("#token").val($("#MoipWidget").attr("data-token"));
  
  $("#sendToMoip").live('click', function(e){  
    sendToCreditCard();
  });

  $("#sendToCofre").click(function(){
    sendToCofre();
  });
  		  
  $("#boleto").live('click', function(e){	
	 //alert('gerando boleto');
    sendToBoleto();
  });

  $("#debit").click(function() {
    sendToDebit();
  });
  $("#calcular-btn").live('click', function(e){  
    $('ul.selecione-metrica').hide();
    if($("#parcelamento").val()=='ACTIVE' || $("#parcelamento_juros").val()=='ACTIVE'){    	
    	$('div#numParcelsMessage').show(); 	  
		calcular();      	
    }	
  });

  $("#trocar-token").click(function(){
    $("#MoipWidget").attr("data-token", $("#token").val());
  });

});

calcular = function() {
  var settings = {
    cofre: '',
    instituicao: $("#instituicao").val(),   
    callback: "retornoCalculoParcelamento"
  };

  MoipUtil.calcularParcela(settings);
};
/*
retornoCalculoParcelamento = function(data) {	
	$('div#numParcelsMessage').hide();
	var options = '';
	if(data.parcelas.length > 0) {		
		alert('oi');
		$.each(data.parcelas, function(index, value){
			options += "<option value='"+value.quantidade+"'>"+ value.quantidade +"x R$ "+ value.valor +"</option>";
		});	
	}else{
		alert('oi2');
		$.each(data.parcelas, function(index, value){
			options += "<option value='1'>1</option>";
		});		
	}
	alert(options);
	$('select#parcels').html(options);
	$('ul.selecione-metrica').show();
	
  alert(JSON.stringify(data));
};*/

var retornoCalculoParcelamento = function(data) {

    $('div#numParcelsMessage').hide();
    var options = '';
    if (data.parcelas.length > 0) {

        $.each(data.parcelas, function(index, value) {
            options += "<option value='" + value.quantidade + "'>" + value.quantidade + "x R$ " + value.valor + "</option>";
        });

    } else {
        options += "<option value='1'>1</option>";
    }

    $('select#parcels').html(options);
    $('ul.selecione-metrica').show();

    //$('#errorM').html($.stringify(data));
};

sendToCreditCard = function() {
	
	if($("#parcelamento").val()=='INACTIVE' && $("#parcelamento_juros").val()=='INACTIVE'){    	
		var parcelamento = 1;
	}else{
		var parcelamento = $("#parcels").val();
	}	
	
	if($("#instituicao").val()==null){
		$("#instituicao").val('Mastercard');
	}	
	
    var settings = {
        "Forma": "CartaoCredito",
        "Instituicao": $("#instituicao").val(),
        "Parcelas": parcelamento,
        "Recebimento": "AVista",
        "CartaoCredito": {
            "Numero": $("#NumeroCartao").val(),
            "Expiracao": $("#Expiracao").val(),
            "CodigoSeguranca": $("#CodigoSeguranca").val(),
            "Portador": {
                "Nome": $("#Portador").val(),
                "DataNascimento": $("#DataNascimento").val(),
                "Telefone": $("#Telefone").val(),
                "Identidade": $("#CPF").val()
            }
        }
    }

    $("#sendToMoip").attr("disabled", "disabled");
    MoipWidget(settings);
 };


sendToCofre = function() {
  var settings = {
      "Forma": "CartaoCredito",
      "Instituicao": "Visa",
      "Parcelas": $("input[name=Parcelas]").val(),
      "Recebimento": "AVista",
      "CartaoCredito": {
          "Cofre": $("input[name=Cofre]").val(),
          "CodigoSeguranca": $("input[name=CodigoSeguranca]").val()
      }
  }

    $("#sendToCofre").attr("disabled", "disabled");
    MoipWidget(settings);
 }


sendToBoleto = function() {
  var settings = {
    "Forma": "BoletoBancario",    
  };  
  MoipWidget(settings);
  
  $("#link-boleto").append("<a href='https://www.moip.com.br/Instrucao.do?token=" + $("#MoipWidget").attr("data-token") + "' rel='external' class='btn'>Imprima o Boleto Banc&aacute;rio</a>");
  //window.open('https://desenvolvedor.moip.com.br/sandbox/Instrucao.do?token=' + $("#MoipWidget").attr("data-token") + '');
  
}


var sucesso = function(data){			
	if(data.Status==null){
		refresh();
		$.ajax({			
			type: "POST",			
			data:{			
			//tipo de pagamento 1 = BOLETO BANCARIO
			tipo_pagamento: 1, vezes_pagamento: 1},			
			url: location.href,
			success: function(result){
				fecharefresh();
				//alert(result);				
		},
		error: function(XMLHttpRequest, textStatus, errorThrown){
			alert(errorThrown);
		}
	   });
	}else{		
		switch($("#instituicao").val()){
			case "Mastercard":
				var instituicao = 2;
			break;
			case "Visa":
				var instituicao = 4;
			break;
			case "AmericanExpress":
				var instituicao = 5;
			break;
		}
		$.ajax({			
			type: "POST",			
			data:{			
			//tipo de pagamento 2 = CARTAO DE CREDITO MASTER CARD - 4 = CARTAO DE CREDITO VISA - 5 = CARTAO DE CREDITO AMERICAN EXPRESS
			tipo_pagamento: instituicao, vezes_pagamento: $("#parcels").val()},			
			url: location.href,
			success: function(result){				
				//alert(result);				
		},
		error: function(XMLHttpRequest, textStatus, errorThrown){
			alert(errorThrown);
		}
	   });
		alert('Transacao iniciada com sucesso - Aguarde a validacao dos seus dados');
		window.location = "/portal/user/retorno/compras";	
	}
	/*
    alert(data.Mensagem +
        '\n\n Status: ' + data.Status +
        '\n ID Moip: ' + data.CodigoMoIP +
        '\n Valor Pago: ' + data.TotalPago +
        '\n Taxa Moip: ' + data.TaxaMoIP +
        '\n Cod. Operadora: ' + data.CodigoRetorno+
        '\n Cod. Operadora: ' + data.Url+
        '\n Cod. pagamento status: ' + data.StatusPagamento);
	*/
    $("#sendToMoip").removeAttr("disabled");
    $("#sendToCofre").removeAttr("disabled");      
};

var erroValidacao = function(data) {
	 //alert("Erro !\n\n" + JSON.stringify(data));
	alert('Ocorreu um erro. Verifique seus dados');
    $("#sendToMoip").removeAttr("disabled");
    $("#sendToCofre").removeAttr("disabled");
};

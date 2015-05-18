$(function() {

    $('#cnpj').mask('999.999.999/9999-99', {reverse: true});
    $('#CEP').mask('99999-999', {reverse: true});
    $('#cpf').mask('999.999.999-99', {reverse: true});
    $('#tel1').mask('(99)9999-9999');
    $('#tel2').mask('(99)9999-9999');
    $('#celular').mask('(99)9-9999-9999');

//	$('.busca_dados').live('click', function(e){			
//	$(".emailBoasVindas_search_content").show("slow");
//	});

    /* Valida��o Form Detalhes da Oferta */
    $('form#CompanySearchPreCadastroForm').validate({
        highlight: function(element) {
            $(element).addClass('offer-detail-form-input-validation');
        },
        unhighlight: function(element, errorClass) {
            $(element).removeClass('offer-detail-form-input-validation');
        },
        rules: {
            "cnpj": {required: true},
            "cpf": {required: true},
            "tel1": {required: true}
        },
        messages: {
            "cnpj": {required: ""},
            "cpf": {required: ""},
            "tel1": {required: ""}
        }
    });


    /* Valida��o Form Detalhes da Oferta */
    $('form#CompanyPreCadastroForm').validate({
        highlight: function(element) {
            $(element).addClass('offer-detail-form-input-validation');
        },
        unhighlight: function(element, errorClass) {
            $(element).removeClass('offer-detail-form-input-validation');
        },
        rules: {
            "data[Company][corporate_name]": {required: true},
            "data[Company][fancy_name]": {required: true},
            "data[Company][cnpj]": {required: true},
            "data[Company][email]": {required: true},
            "data[Company][responsible_name]": {required: true},
            "data[Company][zip_code]": {required: true},
            "data[Company][address]": {required: true},
            "data[Company][number]": {required: true},
            "data[Company][district]": {required: true},
            "data[Company][city]": {required: true},
            "data[Company][state]": {required: true},
            "data[Company][responsible_cpf]": {required: true},
            "data[Company][responsible_email]": {required: true},
            "data[Company][responsible_phone]": {required: true}
        },
        messages: {
            "data[Company][corporate_name]": {required: ""},
            "data[Company][fancy_name]": {required: ""},
            "data[Company][cnpj]": {required: ""},
            "data[Company][email]": {required: ""},
            "data[Company][responsible_name]": {required: ""},
            "data[Company][zip_code]": {required: ""},
            "data[Company][address]": {required: ""},
            "data[Company][number]": {required: ""},
            "data[Company][district]": {required: ""},
            "data[Company][city]": {required: ""},
            "data[Company][state]": {required: ""},
            "data[Company][responsible_cpf]": {required: ""},
            "data[Company][responsible_email]": {required: ""},
            "data[Company][responsible_phone]": {required: ""}
        }
    });

    $('#am_2').click(function() {
        $("#button_enviar").toggle(this.checked);
    });

    $('#CEP').live('keyup', function(e) {
        if ($(this).val().length == 9) {
            var valor = $(this).val();
            valor = valor.replace('-', '');
            $.ajax({
                type: "POST",
                data: {
                    cep: valor},
                url: location.href,
                success: function(result) {
                    //alert(result);
                    var data = jQuery.parseJSON(result);
                    $('#Endereco').focus();
                    $('#Endereco').val(data.logradouro);
                    $('#Bairro').val(data.bairro);
                    $('#Estado').val(data.uf);
                    $('#Cidade').val(data.localidade);
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    //alert(errorThrown);
                }
            });
        }
    });

    //funcao para mostrar e fechar div pop up
    $("#mostra").click(function() {

        var maskHeight = $(document).height();
        var maskWidth = $(window).width();

        $('#mask').css({'width': maskWidth, 'height': maskHeight});
        $("#mask").fadeIn();
        $("#pop").fadeIn();

    });

    $("#closePop").click(function() {
        $("#mask").fadeOut();
        $("#pop").fadeOut();
    });

    //Pre-cadastro da empresa
    //carrega sub categoria segundo categoria selecionada
    $('#categoria').click(function() {
        categoria = $("#teste").val();
        $.ajax({
            type: "POST",
            data: {
                categoria: categoria},
            url: location.href,
            success: function(result) {
                $('#categorias').html(result);
                alert(result);
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                alert(errorThrown);
            }
        });
    });
    $('#sub-categoria-ajax').live('click', function(e) {
        $('#sub-categoria-input').val($('#sub-categoria-ajax-input').val());
    });




});


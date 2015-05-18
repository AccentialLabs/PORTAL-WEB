/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
//redes sociais
function showBoxFbk(link) {
    window.open('http://www.facebook.com/sharer.php?u=' + link + '?title=facebook', 'ventanacompartir', 'toolbar=0, status=0, width=650, height=450');
}
function showBoxTwt(comentario, link) {
    window.open("https://twitter.com/intent/tweet?text='" + comentario + "'&url=" + link, 'ventanacompartir2', 'toolbar=0, status=0, width=650, height=450');
}

function showBoxGoogle(link) {
    window.open('https://plus.google.com/share?url=' + link, 'ventanacompartir3', 'toolbar=0, status=0, width=650, height=450');
}

function showBoxes(fbk, twt, gplus, comentario, id) {
    var link = 'http://localhost/work/x_portal_teste/user/offerDetail?offer=' + id;
    if (fbk == 'ACTIVE') {
        showBoxFbk(link);
    }
    if (twt == 'ACTIVE') {
        showBoxTwt(comentario, link);
    }
    if (gplus == 'ACTIVE') {
        showBoxGoogle(link);
    }

}

/*
 * Salva metricas do pedido trazidas pela pop up
 * @param {type} bandeira
 * @returns {undefined}
 */
function saveMetrics() {
    var tamanho = $("#metrica_tamanho").val();
    var cor = $("#metrica_cor").val();
    //alert(tamanho + " - " + cor);

    $.ajax({
        type: "POST",
        data: {
            tamanho: tamanho,
            cor: cor},
        url: location.href + "/saveMetrics",
        success: function(result) {
            //alert('sucesso');
            location.reload();
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            //alert(errorThrown);
        }
    });
}

function validaForm() {

}

function SomenteNumero(e) {
    var tecla = (window.event) ? event.keyCode : e.which;
    if ((tecla > 47 && tecla < 58))
        return true;
    else {
        if (tecla == 8 || tecla == 0)
            return true;
        else
            return false;
    }
}

function escolheEndMudaCep(cep) {
    //(cep);
    $("#destino").val(cep);
}

function mostraAreaCartao() {
    $("#area-cartoes").fadeIn();
}

function selecFlagCalculateParcels(bandeira) {
    var band = bandeira;
    $("#instituicao").val(band);
    $('.cartoes').css("border", "0px");
    document.getElementById(band).style.border = "3px solid #dcdcdc";
    //('func');
    var settings = {
        cofre: '',
        instituicao: band,
        callback: "backfunction"
    };

    MoipUtil.calcularParcela(settings);
    //('func2');
}

function payforCreditCard() {
    $("#loading").fadeIn();
    var settings = {
        "Forma": "CartaoCredito",
        "Instituicao": $("#instituicao").val(),
        "Parcelas": $("#parcels").val(),
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

    MoipWidget(settings);
    $("#loading").fadeOut(500);
}

function backfunction(data) {
    //('back');
    var options = '';
    if (data.parcelas.length > 0) {

        $.each(data.parcelas, function(index, value) {
            options += "<option value='" + value.quantidade + "'>" + value.quantidade + "x R$ " + value.valor + "</option>";
        });

    } else {
        options += "<option value='1'>1</option>";
    }

    $('select#parcels').html(options);
    $("#parcels").fadeIn(400);
}

function cancelaCompra() {
    var r = confirm("Deseja cancelar a Compra?");
    if (r == true) {
        history.go(-1);
    } else {
        //('cancelou');
    }
}

function escolheEnd(id) {
    $("#loading").fadeIn();
    var qtd = $("#input-qtd").val();
    var origem = $("#origem").val();
    $("#id-endereco").val(id);

    $.ajax({
        type: "POST",
        data: {
            quantidade: qtd,
            origem: origem,
            endereco: id},
        url: location.href + "/escolheEnd",
        success: function(result) {
            $('#table-frete').html(result);
            $("#loading").fadeOut();
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            //alert(errorThrown);
        }
    });
}

function addAddress() {
    //Capturando valores dos inputs
    cep = $('#zip_code').val();
    address = $('#address').val();
    city = $('#city').val();
    district = $('#district').val();
    number = $('#number').val();
    uf = $('#uf').val();
    label = $("#label").val();
    complement = $('#complement').val();

    $.ajax({
        type: "POST",
        data: {
            cep: cep,
            address: address,
            city: city,
            district: district,
            number: number,
            uf: uf,
            label: label,
            complement: complement},
        url: location.href + "/addAddress",
        success: function(result) {
            $('#table-endereco').html(result);
            $('#cont-info').toggle(500);
            $("#mask").fadeOut(100);
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            //alert(errorThrown);
        }
    });
}

function updateAddress() {
    //Capturando valores dos inputs
    id = $("#address-id").val();
    cep = $('#edit-zip_code').val();
    address = $('#edit-address').val();
    city = $('#edit-city').val();
    district = $('#edit-district').val();
    number = $('#edit-number').val();
    uf = $('#edit-uf').val();
    label = $("#edit-label").val();
    complement = $('#edit-complement').val();

    $.ajax({
        type: "POST",
        data: {
            id: id,
            cep: cep,
            address: address,
            city: city,
            district: district,
            number: number,
            uf: uf,
            label: label,
            complement: complement},
        url: location.href + "/updateAddress",
        success: function(result) {
            $('#table-endereco').html(result);
            $('#cont-editar').fadeOut(500);
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            //alert(errorThrown);
        }
    });

}

function editAddress(id) {
    $("#loading").fadeIn();
    $.ajax({
        type: "POST",
        data: {
            id: id},
        url: location.href + "/editAddress",
        success: function(result) {
            $("#loading").fadeOut();
            $("#cont-editar").fadeIn();
            $("#cont-editar").html(result);
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            //alert(errorThrown);
        }
    });
}

function fechar() {
    $("#cont-editar").fadeOut(500);
}
$(function() {

    $("#paga-cartao").click(function() {

    });

    //seleciona metrica tamanho ou cor
    $('.metrica-tamanho').click(function() {
        id = $(this).attr('id');
        $('.metrica-tamanho').css("border", "1px solid #dcdcdc");
        document.getElementById(id).style.border = "1px solid orange";
        $("#metrica_tamanho").val(id);
    });

    $('.metrica-cor').click(function() {
        id = $(this).attr('id');
        $('.metrica-cor').css("border", "1px solid #dcdcdc");
        document.getElementById(id).style.border = "1px solid orange";
        $("#metrica_cor").val(id);
    });

    $("#cancel-metrics").click(function() {
        history.back(1);
    });

    //$('.div-metrica-Tamanho').css("border", "1px solid #dcdcdc");
    //document.getElementById(meuid).style.border = "1px solid orange";

    $("#fechar-resumo").click(function() {
        $("#pop-ver-pedido").fadeOut(500);
    });

    $("#fechar-confirm").click(function() {
        $("#pop-confirm").fadeOut(500);
    });

    /*
     * Esconde tabela de entrega quando usuario seleciona 
     * mode de pagamento
     */
    $("#escolhe-pagamento").click(function() {
        //alert('clicou');
        $(".forma-entrega").fadeOut(250);
        $(".linha-endereco").fadeOut(250);

        $("#valor-do-frete").val("Frete: " + $("#frete-hidden").val());
        $("#valor-do-frete").fadeIn(600);
    });

    /*
     * Mostra tabela para selecionar forma de entrega (frete)
     */
    $(".title-entrega").click(function() {
        $(".forma-entrega").fadeIn(200);
    });

    var retornoCalculoParcel = function(data) {
        //alert('TEST');
        //$('div#numParcelsMessage').hide();
        var options = '';
        if (data.parcelas.length > 0) {

            $.each(data.parcelas, function(index, value) {
                options += "<option value='" + value.quantidade + "'>" + value.quantidade + "x R$ " + value.valor + "</option>";
            });

        } else {
            options += "<option value='1'>1</option>";
        }

        $('select#parcels').html(options);
        //$('ul.selecione-metrica').show();

        //$('#errorM').html($.stringify(data));
    };

    $("#add-endereco").click(function() {
        $("#mask").fadeIn(100);
        $("#cont-info").toggle(500);


    });

    $("#cancelar").click(function() {
        $("#mask").fadeOut(100);
        $("#cont-info").toggle(500);
    });

    $("#xis").click(function() {
        $("#cont-notification").fadeOut(500);
    });

    //mais
    $("#qtd-plus").click(function() {
        $("#loading").fadeIn();
        var qtd = $("#input-qtd").val();
        var destino = $("#destino").val();
        var origem = $("#origem").val();
        var valor = $("#valorOrig").val();
        qtd++;
        $("#valorAtual").val(valor * qtd);
        $("#input-qtd").val(qtd);
        $("#confirm-qtd").val($("#input-qtd").val());
        //alert(valor);
        $.ajax({
            type: "POST",
            data: {
                quantidade: qtd,
                origem: origem,
                destino: destino,
                valor: valor},
            url: location.href + "/mudaQtd",
            success: function(result) {
                var text = jQuery('<div>' + result + '</div>');
                $("#table-frete").html(text.find("#table-frete").html());
                $("#div-total").html(text.find("#total").html());
                $("#loading").fadeOut();

            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                //alert(errorThrown);
            }
        });
    });

    //menos
    $("#qtd-less").click(function() {
        //se a quantidade 
        if ($("#input-qtd").val() > 0) {
            $("#loading").fadeIn();
            $("#input-qtd").val($("#input-qtd").val() - 1);
            $("#confirm-qtd").val($("#input-qtd").val());
            var qtd = $("#input-qtd").val();
            var destino = $("#destino").val();
            var origem = $("#origem").val();
            var valor = $("#valorOrig").val();
            $("#valorAtual").val(valor * qtd);
            //
            $.ajax({
                type: "POST",
                data: {
                    quantidade: qtd,
                    origem: origem,
                    destino: destino,
                    valor: valor},
                url: location.href + "/mudaQtd",
                success: function(result) {
                    var text = jQuery('<div>' + result + '</div>');
                    $("#table-frete").html(text.find("#table-frete").html());
                    $("#div-total").html(text.find("#total").html());
                    $("#loading").fadeOut();

                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    //alert(errorThrown);
                }
            });
        }
    });


});

function excluir(id) {
    $("#linha-" + id).fadeOut(500);
}

function escolheFrete(frete, qtdDias) {
    $("#frete-hidden").val(frete);
    $("#loading").fadeIn();
    $("#qtd-dias-entrega").val(qtdDias);
    var valor = $("#valorAtual").val();
    $("#confirm-frete").val(valor);
    $.ajax({
        type: "POST",
        data: {
            frete: frete,
            valor: valor},
        url: location.href + "/mudaFrete",
        success: function(result) {
            $("#loading").fadeOut(500);
            $("#div-total").html(result);

        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            //alert(errorThrown);
        }
    });
}

function testeJs(){
    alert('tes');
}

  function generateTokenToCommunication(tipo) {
        
        var radioFrete = $('input[name="data[Carro][delivery_form]"]:checked').val();
        var radioEnd = $('input[name="data[Carro][end]"]:checked').val();
        //Se tiverem selecionado
        if (radioEnd || radioFrete) {

            var diasEntrega = $("#qtd-dias-entrega").val();
            var idEnd = $("#id-endereco").val();
            var frete = $("#frete-hidden").val();
            var qtd = $("#input-qtd").val();
            var total = $("#valorAtual").val();
            $("#confirm-total").val(total);
            $("#loading").fadeIn(300);
            $.ajax({
                type: "POST",
                data: {
                    endId: idEnd,
                    tipo: tipo,
                    frete: frete,
                    quantidade: qtd,
                    total: total,
                    diasEntrega: diasEntrega},
                url: location.href + "/geraTokenCompra",
                success: function(result) {
                    $("#loading").fadeOut(500);
                    $("#recebe-pagamentos-boleto").html(result);
                    $("#pop-confirm").fadeIn(500);
                           // showBoxes( < ?php echo "'{$fbk}', '{$twt}', '{$gplus}'"; ? > , 'Acabei de comprar: ', < ?php echo $offer['Offer']['id']; ? > );
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    //alert(errorThrown);
                }
            });
            //se não tiverem selecionado
        } else {
            $("#cont-notification").fadeIn(500);
        }
    }

//Cartão
function generateTokenToCommunicationCard(tipo) {

    var radioFrete = $('input[name="data[Carro][delivery_form]"]:checked').val();
    var radioEnd = $('input[name="data[Carro][end]"]:checked').val();

    //Se tiverem selecionado
    if (radioEnd || radioFrete) {

        var diasEntrega = $("#qtd-dias-entrega").val();
        var idEnd = $("#id-endereco").val();
        var frete = $("#frete-hidden").val();
        var qtd = $("#input-qtd").val();
        var total = $("#valorAtual").val();
        $("#confirm-total").val(total);
        $("#loading").fadeIn(300);
        $.ajax({
            type: "POST",
            data: {
                endId: idEnd,
                tipo: tipo,
                frete: frete,
                quantidade: qtd,
                total: total,
                diasEntrega: diasEntrega},
            url: location.href + "/geraTokenCompraPorCartao",
            success: function(result) {
                //alert('sucesso');
                $("#loading").fadeOut(500);
                $("#recebe-pagamentos").html(result);
                mostraAreaCartao();
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                //alert(errorThrown);
            }
        });

        //se não tiverem selecionado
    } else {
        $("#cont-notification").fadeIn(500);
    }
}

/*
 * Para ver resumo do pedido
 */
function resumeCheck() {
    //alert('resumeCheck');
    $("#loading").fadeIn();
    $.ajax({
        type: "POST",
        data: {
            pedido: 'ESSE É O PEDIDO TESTE'},
        url: location.href + "/resumeCheck",
        success: function(result) {
            var text = jQuery('<div>' + result + '</div>');
            // $("#teste-ajax").html(text.find("#tudo").html());
            $("#resume-frete").html("R$ " + text.find("#valor-frete").html());
            $("#resume-total").html("R$ " + text.find("#valor-total").html());
            $("#resume-tipo-pag").html(text.find("#tipo-de-pagamento").html());
            $("#resume-dias-entrega").html(text.find("#quantidade-entrega").html());
            $("#endereco").html(text.find("#div-endereco").html());
            $("#resume-valor-pedido").html("R$ " + ($("#valorOrig").val() * text.find("#quantidade").html()) + ",00");
            $("#resume-qtd").html(text.find("#quantidade").html());
            $("#loading").fadeOut(500);
            $("#pop-ver-pedido").fadeIn(500);
            //alert('TESTE');
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            alert('ERROR NONONO');
        }
    });
}


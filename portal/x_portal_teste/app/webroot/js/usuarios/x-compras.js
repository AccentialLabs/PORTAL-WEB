/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

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

$(function() {
    $("#fechar-boleto").click(function() {
        $("#mask").fadeOut(700);
        $("#pop-confirm").fadeOut(500);
    });

    $("#cancela-comment").click(function() {
        $("#pop-comment").fadeOut(500);
        $("#mask").fadeOut(500);
    });

    $("#fecha-comentado").click(function() {
        $("#ja-comentei").fadeOut(500);
    });



    $(".class-star").mouseover(function() {
        var id = $(this).attr('id');
        map = id.split("-");
        var i = map[2];
        while (i > 0) {
            $("#check-1-" + i).css("background", "red");
            i--;
        }

    });

    //mouse em cima da estrela
    $(".img-rating").mouseover(function() {
        var id = $(this).attr('id');
        $("#" + id).attr("src", "../img/icones/users-vitrine/star_complete.png");
        i = id;
        while (i > 0) {
            $("#" + i).attr("src", "../img/icones/users-vitrine/star_complete.png");
            i--;
        }

    });

    //retorna as imagens ao original caso mouse seja tirado
    $(".img-rating").mouseleave(function() {
        var id = $(this).attr('id');

        id++;
        $("#" + id).attr("src", "../img/icones/users-vitrine/star_empty.png");
        //this.setAttribute('src', '../img/icones/users-vitrine/star_empty.png');
    });

    //guarda o rating no input
    $(".img-rating").click(function() {
        $("#rating").val($(this).attr('id'));
    });

    //EDITANDO COMENTARIO
    //mouse em cima da estrela
    $(".img-rating-edit").mouseover(function() {
        var id = $(this).attr('id');
        $("#" + id).attr("src", "../img/icones/users-vitrine/star_complete.png");
        i = id;
        while (i > 0) {
            $("#" + i).attr("src", "../img/icones/users-vitrine/star_complete.png");
            i--;
        }

    });

    $(".img-rating-edit").click(function() {
        alert('tes');
    });

});

function rating(id) {
    alert('clicou na estrela ' + id);
    $("#check-1-" + id).css("background", "red");
}

function upComment() {
    id = $("#commented-id").val();
    title = $("#commented-title").val();
    description = $("#commented-desc").val();
    $("#loading").fadeIn(200);
    //alert(id+"-"+title+"-"+description);
    $.ajax({
        type: "POST",
        data: {
            id: id,
            title: title,
            desc: description
        },
        url: location.href + "/update-comment",
        success: function(result) {
            $("#loading").fadeOut(200);
            $("#ja-comentei").fadeOut(200);
            $("#pop-comment-comment").fadeIn(1000);
            $("#pop-comment-comment").fadeOut(3500);
            //showBoxes(<?php echo "'{$fbk}', '{$twt}', '{$gplus}'"; ?>);

        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            //alert(errorThrown);
            alert('falha');
        }
    });
}

/**
 * Consulto se já existe comentario do usuario para a oferta
 * Caso tenha, usuario nao pode comentar
 * Caso nao, usuario pode comentar
 * @param {type} id
 * @returns {undefined}
 */
function validaComentario(id, titulo) {
    $("#offer-title-comment").val(titulo + "...");

    $("#loading").fadeIn(500);
    $("#comment-offer-id").val(id);
    $.ajax({
        type: "POST",
        data: {
            oferta_id: id},
        url: location.href + "/valida-comentario",
        success: function(result) {
            var text = jQuery('<div>' + result + '</div>');
            var valida = text.find("#valida").html();
            $("#ja-comentei-body").html(text.find("#existent-comment").html());
            //$("#commented-title").html(text.find("#title").html());
            //$("#commented-desc").html(text.find("#desc").html());
            $("#loading").fadeOut(500);
            //$("#mask").fadeIn(100);
            if (valida == 'nao') {
                mostraComentar();
            } else {
                $("#ja-comentei").fadeIn(800);
            }
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            //alert(errorThrown);
        }
    });
}

function mostraComentar() {

    $("#mask").fadeIn(500);
    $("#pop-comment").fadeIn(1000);

}

function regerarBoleto(id) {
    $("#loading").fadeIn(500);
    var checkId = id;
    $.ajax({
        type: "POST",
        data: {
            id: checkId},
        url: location.href + "/regenerateBillet",
        success: function(result) {
            var text = jQuery('<div>' + result + '</div>');
            //$("#table-frete").html(text.find("#table-frete").html());
            $("#loading").fadeOut(500);
            $('#popbody-confirm').html(result);
            $("#mask").fadeIn(500);
            $("#pop-confirm").fadeIn(700);

        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            //alert(errorThrown);
        }
    });
}

function fechaResumo() {
    $("#pop-ver-pedido").fadeOut(500);
    $("#mask").fadeOut(700);

}

function mostraLinha(id) {
    var id2 = $(this).attr(id);
    var iddadiv = "details" + id;

    $("#" + id).css("backgound", "red");
    $('.td-detalhe').fadeOut(400);
    $("#" + iddadiv).fadeIn(500);
}

//    var funcaoSucesso = function(data) {
//        //alert('Sucesso\n' + JSON.stringify(data));
//        window.open(data.url);
//    };
//
//    var funcaoFalha = function(data) {
//        alert("Ops... Ocorreu um erro para regerar seu boleto.");
//    };
//
//    pagarBoleto = function() {
//        var settings = {
//            "Forma": "BoletoBancario"
//        }
//        MoipWidget(settings);
//    }

function testeHtml() {
    $("#teste-recebe").html('<span>Aqui o teste recebe o texto</span>');
}

function mostraResumoPedido(valUnit, qtd, tipoPag, valFrete,
        valTotal, endRua, endNum, endBairro, endCep, endComple, endCity, endState, dtPedido, tituloOfer) {

    $("#resume-unit-val").html('<span>R$' + valUnit + '</span>');
    $("#resume-qtd").html('<span>' + qtd + '</span>');
    $("#resume-tipo-pag").html('<span>' + tipoPag + '</span>');
    $("#resume-valor-pedido").html('<span>' + (qtd * valUnit) + '</span>');
    $("#resume-frete").html('<span>' + valFrete + '</span>');
    $("#resume-total").html('<span>' + valTotal + '</span>');
    $("#endereco").html('<span>' + endRua + '</span>');
    $("#numero").html('<span>' + endNum + '</span>');
    $("#cep").html('<span>' + endCep + '</span>');
    $("#complemento").html('<span>' + endComple + '</span>');
    $("#cidade").html('<span>' + endCity + '</span>');
    $("#estado").html('<span>' + endState + '</span>');
    $("#bairro").html('<span>' + endBairro + '</span>');
    $("#resume-data").html('<span>' + dtPedido + '</span>');
    $("#resume-offer-name").html("<span>" + tituloOfer + "</span>")

    $("#mask").fadeIn(500);
    $("#pop-ver-pedido").fadeIn(500);
}


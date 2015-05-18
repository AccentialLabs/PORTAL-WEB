/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
function ativaInativa(cod, status, img, offerId) {

    if (cod == 1) {
        $.ajax({
            type: "POST",
            data: {
                offerId: offerId,
                status: status},
            url: location.href + "/offerStatus",
            success: function(result) {
                //alert("Sucesso - DESATIVA");
                $(img).attr('src', '../img/icones/ofertas/play.png');
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                alert(errorThrown);
            }
        });

    }
    if (cod == 2) {
        $.ajax({
            type: "POST",
            data: {
                offerId: offerId,
                status: 'INATIVO'},
            url: location.href + "/offerStatus",
            success: function(result) {
                //alert('Sucesso - ATIVA');
                $(img).attr('src', '../img/icones/ofertas/pause.png');
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                alert(errorThrown);
            }
        });

    }

}

$(document).ready(function() {


    $("#essa").click(function() {
        alert('tes');
    });

    $(".pause-play").click(function() {
        $(this).attr('src', '../img/icones/ofertas/play.png');
    });

    //tabela de compras concluídas
    $("#btn-todas").click(function() {
        $("#table-ativas").fadeOut(0);
        $("#table-inativas").fadeOut(0);
        $("#table-todas").fadeIn(1000);

        $("#btn-todas").addClass("vendas-inferior-botao-hover");
        $("#btn-ativas").removeClass("vendas-inferior-botao-hover");
        $("#btn-inativas").removeClass("vendas-inferior-botao-hover");

        $("#ativas").fadeOut();
        $("#inativas").fadeOut();
        $("#total").fadeIn();
    });

    //tabela de compras pendentes
    $("#btn-ativas").click(function() {
        $("#table-inativas").fadeOut(0);
        $("#table-todas").fadeOut(0);
        $("#table-ativas").fadeIn(1000);

        $("#inativas").fadeOut();
        $("#total").fadeOut();
        $("#ativas").fadeIn();

        $("#btn-todas").removeClass("vendas-inferior-botao-hover");
        $("#btn-ativas").addClass("vendas-inferior-botao-hover");
        $("#btn-inativas").removeClass("vendas-inferior-botao-hover");
    });

    //tabela de compras 
    $("#btn-inativas").click(function() {
        $("#table-todas").fadeOut(0);
        $("#table-ativas").fadeOut(0);
        $("#table-inativas").fadeIn(1000);

        $("#btn-todas").removeClass("vendas-inferior-botao-hover");
        $("#btn-ativas").removeClass("vendas-inferior-botao-hover");
        $("#btn-inativas").addClass("vendas-inferior-botao-hover");

        $("#total").fadeOut();
        $("#ativas").fadeOut();
        $("#inativas").fadeIn();
    });
});


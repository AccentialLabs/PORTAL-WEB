/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 * 
 * Created on : 20/10/2014, 11:07:21
 * Author     : Matheus Odilon
 */

/*
 * 
 * Seleciona desejo e abre pop up para editar os dados
 * 
 */
function editar(desejoId) {
    $("#loading").fadeIn();
    $.ajax({
        type: "POST",
        data: {
            id: desejoId},
        url: location.href + "/editar",
        success: function(result) {
            $("#popbody").html(result);
            $("#loading").fadeOut();
            $("#cont-notification").toggle(500);
            $("#wish-mask").fadeIn(200);
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            alert(errorThrown);
        }
    });
}

/*
 * 
 * Salva os dados editados do desejo
 * 
 */
function updateWish() {
    $("#loading").fadeIn();
    var id = $("#edit-id").val();
    var titulo = $("#edit-titulo").val();
    var descricao = $("#edit-desc").val();
    var data = $("#edit-datalimite").val;

    $.ajax({
        type: "POST",
        data: {
            id: id,
            titulo: titulo,
            descricao: descricao,
            data: data},
        url: location.href + "/updateWish",
        success: function(result) {
            $("#loading").fadeOut(200);
            $("#cont-notification").fadeOut(500);
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            alert(errorThrown);
        }
    });
}

function cancelEdit() {
    $("#wish-mask").fadeOut(200);
    $("#cont-notification").fadeOut(500);
}

$(function() {

    //fecha a popup clicando no X
    $("#xis").click(function() {
        $("#cont-notification").fadeOut(500);
    });

    //fecha a popup de edicao clicando no cancelar
    //esse id corresponde ao botao criado na tela
    //'ajax_wish_edita'
//        $("#cancel-edit").click(function() {
//            $("#cont-notification").fadeOut(500);
//        });

    //categorias e subcategorias wishlist
    $('#categoria').change(function() {
        $("#loading").fadeIn();
        categoria = $("#categoria").val();
        $.ajax({
            type: "POST",
            data: {
                categoria: categoria},
            url: location.href + "/requisicao",
            success: function(result) {
                $('#categorias').html(result);
                $("#loading").fadeOut();
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                alert(errorThrown);
            }
        });
    });

    $('.exclui').click(function() {
        $("#loading").fadeIn();
        var href = $(this).attr('link');
        var parent = $(this).attr('id');
        $("#" + parent).slideUp(2000, function() {
            $("#" + parent).remove();
            $("#loading").fadeOut();
        });
        $.ajax({url: href, success: function(result) {
            }});

    });
});



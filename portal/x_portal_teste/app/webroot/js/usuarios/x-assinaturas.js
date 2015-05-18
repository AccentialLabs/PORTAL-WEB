/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 * 
 *    Created on : 20/10/2014, 11:18:00
 *    Author     : Matheus Odilon
 */
function mostraLinha(id) {
    var id2 = $(this).attr(id);
    var iddadiv = "details" + id;

    $("#" + id).css("backgound", "red");
    $('.td-detalhe').fadeOut(00);
    $("#" + iddadiv).fadeIn(1000);
}

function assinar(link) {
    //alert(link);
    $("#loading").fadeIn();
    $.ajax({url: link, success: function(result) {
            var text = jQuery('<div>' + result + '</div>');
            $('#testa').html(text.find("#ajax-table").html());
            num = $("#qtd-signs").val();
            num++;
            $("#qtd-signs").val(num);
            $("#loading").fadeOut();
        }});
}

$(function() {

    $('#busca-empresa').keyup(function() {
        busca = $("#busca-empresa").val();
        $("#loading").fadeIn(500);
        $.ajax({
            type: "POST",
            data: {
                busca: busca},
            url: location.href + "/requisicao",
            success: function(result) {

                var text = jQuery('<div>' + result + '</div>');
                $('#testa').html(text.find("#ajax-table").html());
                $("#loading").fadeOut(500);
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                //alert(errorThrown);
            }
        });
    });
    $(".exclui").click(function() {
        var href = $(this).attr('link');
        var parent = $(this).attr('id');
        $("#loading").fadeIn();
        $.ajax({url: href, success: function(result) {
                $("#" + parent).slideUp(500, function() {
                    $("#" + parent).remove();
                });
                num = $("#qtd-signs").val();
                num--;
                $("#qtd-signs").val(num);
                $("#loading").fadeOut();
            }});
    });
    /*
     * Nesta função o id é referente a empresa, 
     * diferente da anterior que é referente ao id da ASSINATURA
     * Tratar o uso de "#" na url
     */
//         $(".assina").click(function(){
//           var href = $(this).attr('link');
//           //var parent = $(this).attr('id');
//          alert(href);
//            //$.ajax({url:href,success:function(result){
//                    //$("#"+parent).slideUp(2000,function() {
//			//		$("#"+parent).remove();
//			//	});
//  //}});
//        });
});

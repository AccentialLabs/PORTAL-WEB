/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
function showBoxFbk() {
    window.open('http://www.facebook.com/sharer.php?u=https://frasedeserie.tumblr.com?title=facebook', 'ventanacompartir', 'toolbar=0, status=0, width=650, height=450');
}
function showBoxTwt() {
    window.open('https://twitter.com/intent/tweet?text=Comentei esta oferta em:&url=https://frasedeserie.tumblr.com', 'ventanacompartir2', 'toolbar=0, status=0, width=650, height=450');
}

function showBoxGoogle() {
    window.open('https://plus.google.com/share?url=https://frasedeserie.tumblr.com', 'ventanacompartir3', 'toolbar=0, status=0, width=650, height=450');
}

function showBox() {
    showBoxFbk();
    showBoxTwt();
    showBoxGoogle();
}

//slide fotos
$(document).ready(function() {

    $("#tabela-dominios").click(function() {
        $("#cont-notification").fadeOut(500);
        $("#img-seta").fadeOut(500);
    });

//Velocidade da rotação e contador
    var speed = 6000;
    var run = setInterval('rotate()', speed);

//Pega o valor da largura e calcular o valor da posição da esquerda
    var item_width = $('#slides li').outerWidth();
    var left_value = item_width * (-1);

//Coloca o último item antes do primeiro item, caso o usuário clique no botão de ANTERIOR
    $('#slides li:first').before($('#slides li:last'));

//Coloca o item atual na posição correta
    $('#slides ul').css({'left': left_value});

//Se o usuário clica no botão ANTERIOR
    $('#prev').click(function() {

//Pega a posição da direita
        var left_indent = parseInt($('#slides ul').css('left')) + item_width;

//Move o item
        $('#slides ul').animate({'left': left_indent}, 400, function() {

//Move o último item e o coloca como o primeiro
            $('#slides li:first').before($('#slides li:last'));

//Coloca o item atual na posição correta
            $('#slides ul').css({'left': left_value});

        });

//Cancela o comportamento do click
        return false;

    });

//Se o usuário clica no botão PROXIMO
    $('#next').click(function() {

//Pega a posição da direita
        var left_indent = parseInt($('#slides ul').css('left')) - item_width;

//Move o item
        $('#slides ul').animate({'left': left_indent}, 400, function() {

//Move o último item e o coloca como o primeiro
            $('#slides li:last').after($('#slides li:first'));

//Coloca o item atual na posição correta
            $('#slides ul').css({'left': left_value});

        });

//Cancela o comportamento do click
        return false;

    });

//Se o usuário está com o mouse sob a imagem, para a rotacao, caso contrário, continua
    $('#slides').hover(
            function() {
                clearInterval(run);
            },
            function() {
                run = setInterval('rotate()', speed);
            }
    );

});

//O temporatizador chamará essa função e a rotação será feita
function rotate() {
    $('#next').click();
}

function zoomImg() {
    alert('zoom');
    //var img = document.getElementById('image-zoom');
    //img.src = link;
    //$('#photo-zoom').fadeIn();
}

/*
 * Cancela envio do form caso metrica não estejam preenchidas
 */
function validaForm(frm) {

    var metricaX = $("#metricaY").val();
    var metricaY = $("#metricaX").val();

    //caso metricas estejam vazias - não valida form
    if (metricaX.length == 0 || metricaY.length == 0) {
        $("#cont-notification").fadeIn(200);
       
        return false;
        //mas se estiverem cheias - valida form   
    } else {
        frm.submit();
    }
}

function mostraMetrica(metrica) {
    var meuid = metrica;

    $('.metricas').fadeOut(0);
    $("#" + meuid).fadeIn(1000);
}

function escolheTamanho(tamanho) {
    var meuid = tamanho;

    $('.div-metrica-Tamanho').css("border", "1px solid #dcdcdc");
    document.getElementById(meuid).style.border = "1px solid orange";

    $("#metricaY").val(meuid);

    alert("");
}

function escolheCor(cor) {
    var meuid = cor;
    $('.cores').css("border", "0px");
    document.getElementById(meuid).style.border = "1px solid orange";
    $("#metricaX").val(meuid);
}

function offerMenu(td) {
    $('.td-offer-menu').css("background", "#FFF180");
    document.getElementById(td).style.background = "#FFE155";

    $("#cont-opn").fadeOut(100);
    $("#cont-info").fadeOut(100);
    $("#cont-espe").fadeOut(100);

    $("#cont-" + td).fadeIn(500);
}

function naoVazio() {
    alert('usuario não vazio');
}

function fechaPop(id) {

    $("#mask").fadeOut(500);
    $("#" + id).fadeOut(1000);
}

function mostraPopEmail() {
    $("#mask").fadeIn(1000);
    $("#pop").fadeIn(1500);
    $("#pop").animate({"top": "25%"});
}

function sendEmail() {
    offerId = $("#id").val();
    email = $("#email").val();
    minhaUrl = 'http://localhost/work/x_portal_teste/user/enviaEmail/shareOffer';
    link = location.href;
    $.ajax({
        type: "POST",
        data: {
            offerId: offerId,
            email: email,
            link: link},
        url: minhaUrl,
        success: function(result) {
            $("#mask").fadeOut(1500);
            $("#pop").fadeOut(1000);
            $("#notify-email").fadeIn();
            $("#notify-email").animate({"left": "3%"});
            $("#notify-email").fadeOut(3000);
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            //alert(errorThrown);
        }
    });
}

$(function() {

    //fecha o zoom da imagem
    $("#fecha-img-zoom").click(function() {
        $("#img-zoom").toggle(300);
        $("#mask").fadeOut(200);
    });

    $("#closePop").click(function() {

        $("#mask").fadeOut(1500);
        $("#pop").fadeOut(1000);

    });

    //quando clicar no seletor de imagens
    $(".seletor-img").click(function() {
        var id = $(this).attr("id");
        $(".offer-imgs").fadeOut(0);
        $("#img-" + id).fadeIn(300);

        $(".seletor-img").css("opacity", "0.5");
        $(this).css("opacity", "0.8");
    });

    $(".photo-zoom-menu").click(function() {
        var src = $(this).attr("src");
        $("#photo-zoom").attr("src", src);
        //alert(src);
    });

    //horizontal
    $(".lupa").mouseover(function() {
        $(".lupa").css("opacity", "0.8");
    });

    $(".lupa").mouseleave(function() {
        $(".lupa").css("opacity", "0.3");
    });

    $(".lupa").click(function() {
        $("#mask").fadeIn();
        $("#img-zoom").toggle(400);
    });

    //vertical
    $(".lupa-vert").mouseover(function() {
        $(".lupa-vert").css("opacity", "0.8");
    });

    $(".lupa-vert").mouseleave(function() {
        $(".lupa-vert").css("opacity", "0.3");
    });

    $(".lupa-vert").click(function() {
        $("#mask").fadeIn();
        $("#img-zoom").toggle(400);
    });

});

//MOSTRA TELA INICIO
function login() {
    $("#mask").fadeIn(500);
    $.ajax({
        type: "POST",
        data: {},
        url: 'http://localhost/work/x_portal_teste/user/ajaxLogin/first',
        success: function(result) {
            $('#pop-login-body').html(result);
            $('#pop-login').fadeIn(500);
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            //alert(errorThrown);
        }
    });
}

//MOSTRA TELA LOGIN
function vazio() {
    $("#mask").fadeIn(500);
    $('#pop-login').fadeIn(500);
    minhaUrl = 'http://localhost/work/x_portal_teste/user/ajaxLogin/login';
    $.ajax({
        type: "POST",
        data: {},
        url: minhaUrl,
        success: function(result) {
            $('#pop-login-body').html(result);
            $('#pop-login').fadeIn(1000);
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            //alert(errorThrown);
        }
    });
}

function logar2() {
    var email = $("#email2").val();
    pass = $("#pass2").val();
    minhaUrl = 'http://localhost/work/x_portal_teste/user/ajaxLogin/logar';
    $.ajax({
        type: "POST",
        data: {
            email: email,
            pass: pass},
        url: minhaUrl,
        success: function(result) {
            document.location.reload();
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) {
            //alert(errorThrown);
        }
    });
}


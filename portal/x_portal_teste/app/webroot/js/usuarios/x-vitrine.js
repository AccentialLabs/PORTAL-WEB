/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

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

$(function() {

    $("#fecha-perfil-ofertas").click(function() {
        $("#mask").fadeOut(300);
        $("#perfil-ofertas").toggle(400);
    });

    $("#fecha-categorias-ofertas").click(function() {
        $("#mask").fadeOut(100);
        $("#categorias-ofertas").toggle(400);
        $(".aba-lateral").css("border", "1px solid orange");
    });

    //mouseover
    $(".aba-lateral").mouseover(function() {
        var id = $(this).attr("id");
        $("#" + id).css("left", "-50px");
        $("#" + id).css("opacity", "1");
    });

    $(".aba-lateral").mouseout(function() {
        var id = $(this).attr("id");
        $("#" + id).css("left", "-130px");
        $("#" + id).css("opacity", "0.5");
    });

    //click
    $(".aba-lateral").click(function() {
        var id = $(this).attr("id");
        $(".aba-lateral").css("border", "1px solid orange");
        $("#" + id).css("left", "-50px");
        $("#" + id).css("border", "2px solid #fff");
        $("#" + id).css("opacity", "1");

        if (id == 'filtro-categoria') {
            $("#mask").fadeIn(200);
            $("#categorias-ofertas").toggle(400);
        }
        if (id == 'filtro-perfil') {
            $("#mask").fadeIn(300);
            $("#perfil-ofertas").toggle(400);
        }
    });


    var qtd = 7;
    $(".knob").knob({
        /*change : function (value) {
         //console.log("change : " + value);
         },
         release : function (value) {
         console.log("release : " + value);
         },
         cancel : function () {
         console.log("cancel : " + this.value);
         },*/
        draw: function() {

            // "tron" case
            if (this.$.data('skin') == 'tron') {

                var a = this.angle(this.cv)  // Angle
                        , sa = this.startAngle          // Previous start angle
                        , sat = this.startAngle         // Start angle
                        , ea                            // Previous end angle
                        , eat = sat + a                 // End angle
                        , r = true;

                this.g.lineWidth = this.lineWidth;

                this.o.cursor
                        && (sat = eat - 0.3)
                        && (eat = eat + 0.3);

                if (this.o.displayPrevious) {
                    ea = this.startAngle + this.angle(this.value);
                    this.o.cursor
                            && (sa = ea - 0.3)
                            && (ea = ea + 0.3);
                    this.g.beginPath();
                    this.g.strokeStyle = this.previousColor;
                    this.g.arc(this.xy, this.xy, this.radius - this.lineWidth, sa, ea, false);
                    this.g.stroke();
                }

                this.g.beginPath();
                this.g.strokeStyle = r ? this.o.fgColor : this.fgColor;
                this.g.arc(this.xy, this.xy, this.radius - this.lineWidth, sat, eat, false);
                this.g.stroke();

                this.g.lineWidth = 2;
                this.g.beginPath();
                this.g.strokeStyle = this.o.fgColor;
                this.g.arc(this.xy, this.xy, this.radius - this.lineWidth + 1 + this.lineWidth * 2 / 3, 0, 2 * Math.PI, false);
                this.g.stroke();

                return false;
            }
        }
    });

// Example of infinite knob, iPod click wheel
    var v, up = 0, down = 0, i = 0
            , $idir = $("div.idir")
            , $ival = $("div.ival")
            , incr = function() {
                i++;
                $idir.show().html("+").fadeOut();
                $ival.html(i);
            }
    , decr = function() {
        i--;
        $idir.show().html("-").fadeOut();
        $ival.html(i);
    };
    $("input.infinite").knob(
            {
                min: 0
                , max: 20
                , stopper: false
                , change: function() {
                    if (v > this.cv) {
                        if (up) {
                            decr();
                            up = 0;
                        } else {
                            up = 1;
                            down = 0;
                        }
                    } else {
                        if (v < this.cv) {
                            if (down) {
                                incr();
                                down = 0;
                            } else {
                                down = 1;
                                up = 0;
                            }
                        }
                    }
                    v = this.cv;
                }
            });

    //ajax para carregar as ofertas
    $("#content-offers").scroll(function() {
        if ($(this).scrollTop() + $(this).height() == $(this).get(0).scrollHeight) {
            $("#loading").fadeIn(100);
            $("#input-limite").val(10);
            //alert(qtd);
            $.ajax({
                type: "POST",
                data: {
                    limite: qtd
                },
                url: location.href + "/ajax-vitrine",
                success: function(result) {

                    var text = jQuery('<div>' + result + '</div>');
                    //alert('sucesso');
                    $("#content-offers").html(text.find("#cont").html());
                    $("#input-limite").html(text.find("#limite").html());
                    $("#loading").fadeOut(100);
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    alert('ERROR');
                }
            });
            qtd++;
        }
    });



    //filtragem de ofertas por categorias
    $(".caixa-categoria").click(function() {
        $("#loading").fadeIn(100);
        var id = $(this).attr("id");

        $.ajax({
            type: "POST",
            data: {
                id: id
            },
            url: location.href + "/offers-category",
            success: function(result) {

                var text = jQuery('<div>' + result + '</div>');
                //alert('sucesso');
                $("#content-offers").html(text.find("#cont").html());
                $("#input-limite").html(text.find("#limite").html());
                $("#loading").fadeOut(100);
                $("#mask").fadeOut(200);
                $("#categorias-ofertas").toggle(400);
                $("#voltar-vitrine").toggle(600);
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                alert('ERROR');
            }
        });
    });

    $("#pesquisa-por-perfil").click(function() {
        $("#loading").fadeIn(200);
        var gender = $("#gender").val();
        var religion = $("#religion").val();
        var political = $("#political").val();
        var age = $("#age").val();
        var locat = $("#location").val();
        var status = $("#status").val();
        //alert(gender + " - " + religion + " - " + political + " - " + locat + " - " + status);
        $.ajax({
            type: "POST",
            data: {
                gender: gender,
                religion: religion,
                political: political,
                age: age,
                locat: locat,
                status: status},
            url: location.href + "/offers-profile",
            success: function(result) {
                //alert(result);
                var text = jQuery('<div>' + result + '</div>');
                //alert('sucesso');
                $("#content-offers").html(text.find("#cont").html());
                $("#input-limite").html(text.find("#limite").html());
                $("#loading").fadeOut(100);
                $("#mask").fadeOut(200);
                $("#perfil-ofertas").toggle(400);
                $("#voltar-vitrine").toggle(600);
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                //alert(errorThrown);
            }
        });
    });

    $("#cancel-pesquisa-perfil").click(function() {
        $("#mask").fadeOut(200);
        $("#perfil-ofertas").toggle(400);
    });

    $("#voltar-vitrine").click(function() {
        location.reload();
    });

});



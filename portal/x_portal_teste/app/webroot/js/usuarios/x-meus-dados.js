/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

//valida imagem
function verificaExtencao() {
    var extensoes = "gif, png, jpg, jpeg";
    var extensao = $('#imgInp').val().split(".")[1].toLowerCase();

    if (extensoes.indexOf(extensao) == -1) {
        var htm = "<span>Formato inválido da imagem. Verifique se o formato está dentro de nosso padrão de uso.<br/> (Gif, PNG, JPG, Jpeg)</span>";
        $("#valida-img-error").html(htm);
        $(".valida-error").fadeIn(600);
        //$(".valida-error").fadeOut(4000);
        return false;
    } else {
        //alert("FORMATO VÁLIDO. VERIFICA PESO...");
        verificaTamanho();
    }
}

function verificaTamanho() {
    tamanhoArqMax = 1000;
    var arq = document.getElementById('imgInp').files[0].size / 1024;

    if (arq < 0) {
        alert("Essa arquivo é muito leve.");
        return false;
    } else if (arq > tamanhoArqMax) {
        //alert("Essa imagem é muito pesada. ");
        var htm = "<span>Essa imagem é <br/>muito pesada.</span>";
        $("#valida-img-error").html(htm);
        $(".valida-error").fadeIn(600);
        //$(".valida-error").fadeOut(4000);
        return false;
    } else {
        $("#myForm").submit();
    }

}

$(function() {

    $("#button-ok-valida-img").click(function() {
        $(".valida-error").fadeOut(400);
    });

    $(".social-check").change(function() {
        var id = $(this).attr("id");
        var check = $("#" + id).is(":checked");
        var status = '';
        if (check == true) {
            status = 'ACTIVE';
        } else {
            status = 'INACTIVE';
        }

        $("#input-" + id).val(status);

    });

    $("#background").mouseover(function() {
        //$("#upload-image-back").fadeIn(200);
    });

    //mouse fora da foto do usuario
    //esconde foto de upload
    $("#background").mouseout(function() {
        // $("#upload-image-back").fadeOut(200);
    });

    //executando input file por clique na foto
    $("#background").bind('click', function() {
        $('#imgInp').click( );
    });


    //--------------------

    //mouse em cima da foto do usario 
    // mostra foto de upload
    $("#user-photo").mouseover(function() {
        //$("#upload-image").fadeIn(200);
    });

    //mouse fora da foto do usuario
    //esconde foto de upload
    $("#user-photo").mouseout(function() {
        // $("#upload-image").fadeOut(200);
    });

    //executando input file por clique na foto
    $("#user-photo").bind('click', function() {
        $('#user-photo-select').click( );
    });

    //mudar foto de usuario quando selecionada
    $("#user-photo-select").change(function() {
        readURLUserPhoto(this);
    });

    //compara nova senha com a confirmação
    $("#input-confirma-senha").keyup(function() {
        var novaSenha = $("#input-nova-senha").val();
        var confirma = $("#input-confirma-senha").val();

        if (novaSenha == confirma) {
            $("#input-confirma-senha").css("border", "1px solid #3CB371");
            $("#input-confirma-senha").css("outline-color", "#3CB371");
            $("#salvar-nova-senha").fadeIn();
        } else {
            $("#input-confirma-senha").css("border", "1px solid red");
            $("#input-confirma-senha").css("outline-color", "red");
            $("#salvar-nova-senha").fadeOut();
        }
    });

    $("#input-velha-senha").focusout(function() {
        $(".img-loading-pass").fadeIn();
        var senha = $("#input-velha-senha").val();

        $.ajax({
            type: "POST",
            data: {
                senha: senha},
            url: location.href + "/valida-senha",
            success: function(result) {

                var retorno = result;
                $(".img-loading-pass").fadeOut();
                if (retorno.indexOf("OK") >= 0) {
                    $("#input-velha-senha").css("border", "1px solid #3CB371");
                } else {
                    $("#input-velha-senha").css("border", "1px solid red");
                }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                //alert(errorThrown);
            }
        });
    });

    $(".botao-salvar-ends").click(function() {
        $("#loading").fadeIn();
        var id = $(this).attr("value");
        var cep = $("input[name=cep-" + id + "]").val();
        var endereco = $("input[name=endereco-" + id + "]").val();
        var numero = $("input[name=numero-" + id + "]").val();
        var bairro = $("input[name=bairro-" + id + "]").val();
        var complemento = $("input[name=comple-" + id + "]").val();
        var cidade = $("input[name=cidade-" + id + "]").val();
        var estado = $("input[name=estado-" + id + "]").val();

        $.ajax({
            type: "POST",
            data: {
                id: id,
                cep: cep,
                endereco: endereco,
                numero: numero,
                bairro: bairro,
                complemento: complemento,
                cidade: cidade,
                estado: estado},
            url: location.href + "/update-aditional-address",
            success: function(result) {
                //alert(result);
                $("#loading").fadeOut();
                window.location.reload();
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                //alert(errorThrown);
            }
        });

    });

    $("#cancelar-pop").click(function() {
        $("#pop-cad-end").fadeOut(200);
        $("#mask").fadeOut(500);
    });

    $("#xis-pop-cad-end").click(function() {
        $("#pop-cad-end").fadeOut(200);
        $("#mask").fadeOut(500);
    });

    $("#mostra-pop-cad-end").click(function() {
        $("#mask").fadeIn(200);
        $("#pop-cad-end").fadeIn(500);
    });

    $("#title-end-principal").click(function() {
        $("#meus-dados-item-end").slideToggle();
        $(".meus-dados-end-adicional").fadeOut(50);
    });

    $(".linha-end-adicional").click(function() {
        $(".meus-dados-end-adicional").fadeOut(50);
        //$("#meus-dados-item-end").slideToggle();
        $("#meus-dados-item-end").fadeOut(0);
        var id = $(this).attr('id');
        $("#meus-dados-end-adicional-" + id).slideToggle();
    });

    /*
     * Pesquisa endereço por CEP
     */
    $('#ceppop').live('keyup', function(e) {
        if ($(this).val().length == 9) {
            var valor = $(this).val();
            valor = valor.replace('-', '');
            //alert(valor);
            $.ajax({
                type: "POST",
                data: {
                    cep: valor},
                url: location.href + "/ajax-cep",
                success: function(result) {
                    //alert(result);
                    var data = jQuery.parseJSON(result);
                    $('#enderecopop').focus();
                    $('#enderecopop').val(data.logradouro);
                    $('#bairropop').val(data.bairro);
                    $('#estadopop').val(data.uf);
                    $('#cidadepop').val(data.localidade);
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    //alert(errorThrown);
                }
            });
        }
    });

    /*
     * Quando usuario clicar em salvar endereço novo
     */
    $("#salvar-pop").click(function() {
        $("#loading").fadeIn();
        var cep = $("#ceppop").val();
        var label = $("#labelpop").val();
        var endereco = $("#enderecopop").val();
        var numero = $("#numeropop").val();
        var bairro = $("#bairropop").val();
        var comple = $("#complepop").val();
        var cidade = $("#cidadepop").val();
        var estado = $("#estadopop").val();

        $.ajax({
            type: "POST",
            data: {
                cep: cep.replace('-', ''),
                label: label,
                address: endereco,
                number: numero,
                district: bairro,
                complement: comple,
                city: cidade,
                uf: estado},
            url: location.href + "/save-aditional",
            success: function(result) {
                $("#loading").fadeOut();
                $("#pop-cad-end").fadeOut(200);
                $("#mask").fadeOut(500);
                window.location.reload();
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                //alert(errorThrown);
            }
        });
    });

    $(".exclui-end-adicional").click(function() {
        var id = $(this).attr('id');
        $("#loading").fadeIn();
        $.ajax({
            type: "POST",
            data: {
                id: id},
            url: location.href + "/delete-aditional",
            success: function(result) {
                $("#loading").fadeOut();
                window.location.reload();

            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                //alert(errorThrown);
            }
        });
    });

    $("#salvar").click(function() {

        $("#loading").fadeIn(500);
        var nome = $("#nome").val();
        var dtNascimento = $("#dtNascimento").val();
        var sexo = $("#sexo").val();
        var email = $("#email").val();
        var cep = $("#cep").val();
        var endereco = $("#endereco").val();
        var numero = $("#numero").val();
        var bairro = $("#bairro").val();
        var comple = $("#comple").val();
        var cidade = $("#cidade").val();
        var estado = $("#estado").val();

        $.ajax({
            type: "POST",
            data: {
                name: nome,
                birthday: dtNascimento,
                gender: sexo,
                email: email,
                cep: cep.replace('-', ''),
                address: endereco,
                number: numero,
                district: bairro,
                complement: comple,
                city: cidade,
                uf: estado},
            url: location.href + "/save-principal",
            success: function(result) {
                $("#loading").fadeOut();
                window.location.reload();
                //alert("sucesso");
                //document.forms['up-user-photo'].submit();
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                //alert(errorThrown);
            }
        });

    });

    /*
     * Quando houver clique em salvar na aba "Dados adicionais"
     */
    $("#salvar-adicional").click(function() {
        $("#loading").fadeIn();
        var politica = $("#political").val();
        var religion = $("#religion").val();
        var relationship = $("#relationship").val();
        var facebook = $("#facebook").val();

        $.ajax({
            type: "POST",
            data: {
                political: politica,
                religion: religion,
                relationship: relationship,
                facebook: facebook
            },
            url: location.href + "/save-facebook-infos",
            success: function(result) {
                $("#loading").fadeOut();
                window.location.reload();
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                //alert(errorThrown);
            }
        });
    });

    $("#salvar-nova-senha").click(function() {
        var senha = $("#input-nova-senha").val();
        $("#loading").fadeIn();
        $.ajax({
            type: "POST",
            data: {
                senha: senha
            },
            url: location.href + "/save-new-pass",
            success: function(result) {
                $("#loading").fadeOut();
                window.location.reload();
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                //alert(errorThrown);
            }
        });

    });

    /*
     * Salvar Redes Sociais
     */
    $("#salvar-social").click(function() {
        $("#loading").fadeIn();
        //fbk
        var fbkCheck = $("#input-fbkChecks").val();
        var fbkComment = $("#input-fbkComments").val();
        var fbkWishes = $("#input-fbkWishes").val();
        var fbkLink = $("#face").val();
        //twitter
        var twtLink = $("#twitter").val();
        var twtCheck = $("#input-twtChecks").val();
        var twtComment = $("#input-twtComments").val();
        var twtWishes = $("#input-twtWishes").val();
        //googlePlus
        var gLink = $("#gplus").val();
        var gCheck = $("#input-gplusChecks").val();
        var gComment = $("#input-gplusComments").val();
        var gWishe = $("#input-gplusWishes").val();

        $.ajax({
            type: "POST",
            data: {
                fbkLink: fbkLink,
                fbkComment: fbkComment,
                fbkWishes: fbkWishes,
                fbkCheck: fbkCheck,
                twtLink: twtLink,
                twtCheck: twtCheck,
                twtComment: twtComment,
                twtWishes: twtWishes,
                gLink: gLink,
                gCheck: gCheck,
                gComment: gComment,
                gWishe: gWishe
            },
            url: location.href + "/save-social-networks",
            success: function(result) {
                $("#loading").fadeOut();
                window.location.reload();
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                //alert(errorThrown);
            }
        });

    });

    //mostra conteudo do form dados principais
    $("#btn-dados-principais").click(function() {
        trocaTemaBtn();
        $(this).addClass('botao-menu-default');
        hideElements('meus-dados-form');
    });

    //mostra conteduo do form dados adicionais
    $("#btn-dados-adicionais").click(function() {
        trocaTemaBtn();
        $(this).addClass('botao-menu-default');
        hideElements('meus-dados-add');
    });

    //mostra conteudo de troca de senha
    $("#btn-troca-senha").click(function() {
        trocaTemaBtn();
        $(this).addClass('botao-menu-default');
        hideElements('troca-senha-body');
    });

    $("#btn-redes-socias").click(function() {
        trocaTemaBtn();
        $(this).addClass('botao-menu-default');
        hideElements('redes-sociais');
    });

    $("#btn-aparencia").click(function() {
        trocaTemaBtn();
        $(this).addClass('botao-menu-default');
        hideElements('aparencia-corpo');
    });

    $("#btn-notifications").click(function() {
        trocaTemaBtn();
        $(this).addClass('botao-menu-default');
        hideElements('notifications-corpo');
    });

    $("#imgInp").change(function() {
        readURL(this);
    });
});
function readURL(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function(e) {
            $('#background').attr('src', e.target.result);
        }

        reader.readAsDataURL(input.files[0]);
    }
}

function readURLUserPhoto(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function(e) {
            $('#user-photo').attr('src', e.target.result);
        }

        reader.readAsDataURL(input.files[0]);
    }
}

function hideElements(id) {
    $("#meus-dados-form").fadeOut(0);
    $("#meus-dados-add").fadeOut(0);
    $("#troca-senha-body").fadeOut(0);
    $("#redes-sociais").fadeOut(0);
    $("#aparencia-corpo").fadeOut(0);
    $("#notifications-corpo").fadeOut(0);
    $("#" + id).slideToggle();
}

function trocaTemaBtn() {
    $("#btn-dados-adicionais").removeClass('botao-menu-default');
    $("#btn-dados-adicionais").addClass('botao-menu');

    $("#btn-dados-principais").removeClass('botao-menu-default');
    $("#btn-dados-principais").addClass('botao-menu');

    $("#btn-troca-senha").removeClass('botao-menu-default');
    $("#btn-troca-senha").addClass('botao-menu');

    $("#btn-redes-socias").removeClass('botao-menu-default');
    $("#btn-redes-socias").addClass('botao-menu');

    $("#btn-aparencia").removeClass('botao-menu-default');
    $("#btn-aparencia").addClass('botao-menu');

    $("#btn-notifications").removeClass('botao-menu-default');
    $("#btn-notifications").addClass('botao-menu');
}



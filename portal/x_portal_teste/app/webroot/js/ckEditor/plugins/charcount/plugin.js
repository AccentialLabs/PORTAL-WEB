/*******************************************************************************
Create Date : 28/01/2010
----------------------------------------------------------------------
Plugin name : charcount
Version : 1.1
Author : Frommelt Yoann
Description : Permet de compter le nombre de caracter dans CKEditor v 3
********************************************************************************/
CKEDITOR.plugins.add('charcount',{
  init:function(editor){
    // si les elements compteur et maxlength son declarer alors on compte
    if (editor.config.MaxLength) {
      var stri_label_charcount = (editor.lang.labelCharcount) ? editor.lang.labelCharcount : "Character counter";
      addEventOnkey(editor);
    }
    else {
      stri_label_charcount = "";
    }
    editor.ui.addButton('CharCount',{
      label:stri_label_charcount, 
      command:'charcount'
    });
  }
});

function addEventOnkey(editor) {
  // compte a la fois a la saisi et sur le collé
  editor.on( 'contentDom', function() {
    document.getElementById(editor.name+'_cke_button_charcount_label').innerHTML = (editor.config.MaxLength)-(editor.getData().length);
    editor.document.on( 'keyup', function(event) {
      compteur(editor);
    });
  });
}

function compteur(editor) {
  //alert(editor.name);
  var obj_compteur = document.getElementById(editor.name+'_cke_button_charcount_label');
  obj_compteur.innerHTML = editor.config.MaxLength-(editor.getData().length);
  
  // si le compteur de caractere atteind 0 on tronque le text et on previent l'utilisateur
  if (obj_compteur.innerHTML < 0) {
    // on prend la valeur max et on tronque a 10 caractere de moins (balise HTML + marge)
    editor.setData(editor.getData().substr(0,(editor.config.MaxLength-10)));
    
    // on afficher le compteur a 10 car il replace automatiquement la balise fermante ex : </p>
    obj_compteur.innerHTML = "10";
    
    // on cherche le texte d'alert dans le fichier lang de l'utilisateur de CKEditor
    var stri_alert_text = (editor.lang.limiteAtteinte) ? editor.lang.limiteAtteinte : "Text too long !"
     alert(stri_alert_text);
     
     editor.focus();
  }
}


$(document).ready(function(){
 $('.contenu').hide();
 $("a.afficher").click(function () {
  $(this).next("div.contenu").slideToggle("slow");
  return false;
 });
});
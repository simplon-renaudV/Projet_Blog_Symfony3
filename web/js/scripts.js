

$(document).ready(function(){

 // Afficher / Masquer  article admin
 $('.contenu').hide();
 $('.interface-admin').hide();
 $(".afficher").click(function () {
  $(this).next("div.contenu").toggle("slow");
  return false;
 });

// Afficher menu admin
 $(".menu_admin").click(function () {
  $(this).next("div.interface-admin").toggle("slow");
  if ($(this).hasClass('down')) {
   $(this).removeClass('down');
   $(this).addClass('up');
  }
  else {
   $(this).removeClass('up');
   $(this).addClass('down');
  }
  return false;
 });

});
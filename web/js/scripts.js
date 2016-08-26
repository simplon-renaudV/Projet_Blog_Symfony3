

$(document).ready(function(){
 $('.contenu').hide();
 $('.interface-admin').hide();

 $("a.afficher").click(function () {
  $(this).next("div.contenu").toggle("slow");
  return false;
 });

 $(".menu_admin").click(function () {
  $(this).next("div.interface-admin").toggle("slow");
  return false;
 });

 $(".close").click(function () {
  $(".interface-admin").toggle("slow");
  return false;
 });
});
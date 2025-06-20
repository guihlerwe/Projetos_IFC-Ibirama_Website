const header = document.querySelector("header");

document.querySelector("#projetos-nav").addEventListener("click", function() {
  window.location.href = "../telaPrincipal/principal.html";
});

document.querySelector("#monitoria-nav").addEventListener("click", function() {
  window.location.href = "../telaMonitorias/telaMonitorias.html";
});

document.querySelector("#sobre-nav").addEventListener("click", function() {
  window.location.href = "../telaSobre/sobre.html";
});

document.querySelector("#login-nav").addEventListener("click", function() {
  window.location.href = "../telaLoginAluno/loginAluno.html";
});


var projetos = [];

var nome = document.getElementById("txtNumero").value;
var anoIncio = document.getElementById("anoInicio").value;
var categoria = document.getElementsById("categoria").value;
var coordenador = document.getElementById("coordenador").value;
var bolsista = document.getElementById("bolsista").value;
var txtLinkInscricao = document.getElementsByClassName("txtLinkInscricao =").value;
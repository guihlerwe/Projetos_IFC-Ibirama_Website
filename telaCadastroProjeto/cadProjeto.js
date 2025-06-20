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


let projetos = [];

const nome = document.getElementById("txtNumero");
const eixo = document.getElementsById("eixo");
const categoria = document.getElementById("categoria");
const anoIncio = document.getElementById("anoInicio");
const coordenador = document.getElementById("nome-coordenador");
const bolsista = document.getElementById("nome-bolsista");
const txtLinkInscricao = document.getElementsById("txt-link-inscricao =");
const txtSobre = document.getElementById("descricao");
const txtLinkSite = document.getElementById("site-projeto");
const email = document.getElementById("email");
const numeroTelefone = document.getElementById("numero-telefone");
const instagram = document.getElementById("instagram");
const btCriarProjeto = document.getElementById("bt-criar-projeto");

btCriarProjeto.addEventListener ("click", function () {
  const novoProjeto = {
    Nome: nome.value,
    Eixo: eixo.value,
    Categoria: categoria.value,
    AnoInicio: parseInt(anoIncio.value),
    Coordenador: coordenador.value,
    Bolsista: bolsista.value,
    LinkInscrição: txtLinkInscricao.value,
    Sobre: txtSobre.value,
    LinkSite: txtLinkSite.value,
    Email: email.value,
    NumeroTelefone: parseInt(numeroTelefone.value),
    Instagram: instagram.value,
  };

  const projetosSalvos = JSON.parse(localStorage.getItem("projetos")) || [];
  projetosSalvos.push(novoProjeto);
  localStorage.setItem("projetos"; JSON.stringify(projetosSalvos));
  
  console.log("Projeto adicionado: ", projeto);
});




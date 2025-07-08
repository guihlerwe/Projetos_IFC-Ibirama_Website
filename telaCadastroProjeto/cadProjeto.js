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

document.getElementById("botao").addEventListener("click", criarProjeto);

function criarProjeto(event) {
  event.preventDefault();

  const projeto = {
    nome: document.getElementById("nome-projeto").value,
    eixo: document.getElementById("eixo").value,
    categoria: document.getElementById("categoria").value,
    anoInicio: document.getElementById("ano-inicio").value,
    coordenador: document.getElementById("nome-coordenador").value,
    bolsista: document.getElementById("nome-bolsista").value,
    linkInscricao: document.getElementById("txt-link-inscricao").value,
    sobre: document.getElementById("descricao").value,
    linkSite: document.getElementById("site-projeto").value,
    email: document.getElementById("email").value,
    telefone: document.getElementById("numero-telefone").value,
    instagram: document.getElementById("instagram").value,
  };

  salvarProjetoLocal(projeto);
  alert("Projeto salvo com sucesso!");
  // window.location.href = "../telaPrincipal/principal.html"; // redirecionar se quiser
}

function salvarProjetoLocal(projeto) {
  let projetos = JSON.parse(localStorage.getItem("projetos")) || [];
  projetos.push(projeto);
  localStorage.setItem("projetos", JSON.stringify(projetos));
}




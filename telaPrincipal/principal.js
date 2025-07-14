const header = document.querySelector("header");

document.querySelector("#monitoria-nav").addEventListener("click", function() {
  window.location.href = "../telaMonitorias/telaMonitorias.php";
});

document.querySelector("#sobre-nav").addEventListener("click", function() {
  window.location.href = "../telaSobre/sobre.php";
});

document.querySelector("#login-nav").addEventListener("click", function() {
  window.location.href = "../telaLogin/login.html";
});

window.addEventListener("scroll", () => {
  if (window.scrollY > 0) {
      header.classList.add("com-sombra");
  } else {
      header.classList.remove("com-sombra");
  }
});

document.addEventListener("DOMContentLoaded", function () {
  const projetosSalvos = JSON.parse(localStorage.getItem("projetos")) || [];
  const container = document.querySelector(".projects-grid");

  projetosSalvos.forEach(projeto => {
    const card = document.createElement("div");
    card.classList.add("project-card", projeto.Categoria.toLowerCase());

    const imagem = document.createElement("img");
    imagem.src = projeto.LinkSite || "campus-image.jpg"; // caso não tenha imagem
    imagem.alt = projeto.Nome;
    imagem.classList.add("project-image");

    const label = document.createElement("div");
    label.classList.add("project-label");

    // Cor de acordo com o eixo
    if (projeto.Eixo === "pesquisa") label.classList.add("azul");
    if (projeto.Eixo === "ensino") label.classList.add("verde");
    if (projeto.Eixo === "extensao") label.classList.add("vermelho");

    label.textContent = projeto.Nome;

    card.appendChild(imagem);
    card.appendChild(label);
    container.appendChild(card);
  });
});

document.querySelector(".btn-filtrar.pesquisa").addEventListener("click", () => {
  document.querySelectorAll(".project-card").forEach(card => {
    card.style.display = card.classList.contains("pesquisa") ? "block" : "none";
  });
});

card.addEventListener("click", () => {
  localStorage.setItem("projetoSelecionado", JSON.stringify(projeto));
  window.location.href = "../telaProjeto/projeto.html";
});

const nome = sessionStorage.getItem("usuarioLogado");
const tipo = sessionStorage.getItem("tipoUsuario");

if (nome) {
    document.getElementById("login-nav").style.display = "none";
    document.getElementById("user-menu").classList.remove("hidden");
    document.getElementById("user-button").textContent = `👤 ${nome}`;
    
    if (tipo === "coordenador") {
        document.getElementById("dropdown").innerHTML = `
            <div onclick="location.href='../telaCadProjeto/cadProjeto.html'">Criar projeto</div>
            <div onclick="location.href='../telaPainelCoodernador/painelCoodernador.html'">Seus projetos</div>
            <div onclick="location.href='#'">Dados da conta</div>
        `;
    } else if (tipo === "bolsista") {
        document.getElementById("dropdown").innerHTML = `
            <div onclick="location.href='../telaPainelBolsista/painelBolsista.html'">Seus projetos</div>
            <div onclick="location.href='#'">Dados da conta</div>
        `;
    } else {
        document.getElementById("user-button").style.cursor = "default";
    }
}

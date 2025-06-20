const header = document.querySelector("header");

document.querySelector("#monitoria-nav").addEventListener("click", function() {
  window.location.href = "../telaMonitorias/telaMonitorias.html";
});

document.querySelector("#sobre-nav").addEventListener("click", function() {
  window.location.href = "../telaSobre/sobre.html";
});

document.querySelector("#login-nav").addEventListener("click", function() {
  window.location.href = "../telaLoginAluno/loginAluno.html";
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
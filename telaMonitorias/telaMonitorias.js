const header = document.querySelector("header");

const loginBtn = document.querySelector(".login-nav");
if (loginBtn) {
  loginBtn.addEventListener("click", function () {
    window.location.href = "../telaCadastroAluno/cadAluno.html";
  });
}
  
document.querySelector(".projetos-nav").addEventListener("click", function () {
  const tipo = sessionStorage.getItem("tipo");

  if (tipo === "coordenador") {
      window.location.href = "../telaPrincipal/painelCoordenador.php";
  } else if (tipo === "bolsista") {
      window.location.href = "../telaParincipal/painelBolsista.php";
  } else {
      window.location.href = "../telaPrincipal/principal.php";
  }
});
  
document.querySelector(".sobre-nav").addEventListener("click", function() {
    window.location.href = "../telaSobre/sobre.php";
});

window.addEventListener("scroll", () => {
  if (window.scrollY > 0) {
      header.classList.add("com-sombra");
  } else {
      header.classList.remove("com-sombra");
  }
});
  
// Seleciona todos os cards
document.querySelectorAll(".project-card").forEach(card => {
  card.addEventListener("click", () => {
    const label = card.querySelector(".project-label").innerText.trim();

    switch (label) {
      case "Administração":
        window.location.href = "monitor.php";
        break;
      case "Informática":
        window.location.href = "monitor.php";
        break;
      case "Vestuário":
        window.location.href = "monitor.php";
        break;
      case "Moda":
        window.location.href = "monitor.php";
        break;
      case "Ciências Humanas":
        window.location.href = "monitor.php";
        break;
      case "Ciências da Natureza":
        window.location.href = "monitor.php";
        break;
      case "Linguagens":
        window.location.href = "monitor.php";
        break;
      case "Matemática":
        window.location.href = "monitor.php";
        break;
      default:
        alert("Monitoria ainda não cadastrada!");
    }
  });
});
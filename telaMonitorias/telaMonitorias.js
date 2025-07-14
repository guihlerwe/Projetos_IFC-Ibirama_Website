const header = document.querySelector("header");

const loginBtn = document.querySelector(".login-nav");
if (loginBtn) {
  loginBtn.addEventListener("click", function () {
    window.location.href = "../telaLogin/login.html";
  });
}
  
document.querySelector(".projetos-nav").addEventListener("click", function () {
  const tipo = sessionStorage.getItem("tipoUsuario");

  if (tipo === "coordenador") {
      window.location.href = "../telaPainelCoordenador/painelCoordenador.php";
  } else if (tipo === "bolsista") {
      window.location.href = "../telaPainelBolsista/painelBolsista.php";
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
  
  
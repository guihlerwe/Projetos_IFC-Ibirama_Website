const header = document.querySelector("header");

document.querySelector(".login-nav").addEventListener("click", function() {
    window.location.href = "../telaLogin/login.html";
});
  
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
    window.location.href = "../telaSobre/sobre.html";
});

window.addEventListener("scroll", () => {
  if (window.scrollY > 0) {
      header.classList.add("com-sombra");
  } else {
      header.classList.remove("com-sombra");
  }
});
  
  
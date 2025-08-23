const loginBtn = document.querySelector(".login-nav");
if (loginBtn) {
  loginBtn.addEventListener("click", function () {
    window.location.href = "../telaLogin/login.html";
  });
}

document.querySelector(".monitoria-nav").addEventListener("click", function () {
  window.location.href = "../telaMonitorias/telaMonitorias.php";
});

document.querySelector(".projetos-nav").addEventListener("click", function () {
  const tipo = sessionStorage.getItem("tipoUsuario");

  if (tipo === "coordenador") {
    window.location.href = "../telaPrincipal/painelCoordenador.php";
  } else if (tipo === "bolsista") {
    window.location.href = "../telaPrincipalainelBolsista/painelBolsista.php";
  } else {
    window.location.href = "../telaPrincipal/principal.php";
  }
});

const header = document.querySelector("header");
window.addEventListener("scroll", () => {
  if (header) {
    if (window.scrollY > 0) {
        header.classList.add("com-sombra");
    } else {
        header.classList.remove("com-sombra");
    }
  }
});


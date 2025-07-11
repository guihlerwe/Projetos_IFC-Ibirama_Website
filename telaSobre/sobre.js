document.querySelector(".login-nav").addEventListener("click", function() {
  window.location.href = "../telaLogin/login.html";
});

document.querySelector(".monitoria-nav").addEventListener("click", function() {
  window.location.href = "../telaMonitorias/telaMonitorias.html";
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


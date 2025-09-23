<!-- Exemplo para um card -->
<div class="project-card" data-monitor-id="1">
    <img src="icones/adm.png" alt="Campus Ibirama" class="project-image">
    <div class="project-label">Administração</div>
</div>

  document.addEventListener("DOMContentLoaded", function () {
    
    // Gerenciar menu do usuário logado
    const nome = sessionStorage.getItem("usuarioLogado");
    const tipo = sessionStorage.getItem("tipoUsuario");

    if (nome) {
        const loginNav = document.getElementById("login-nav");
        if (loginNav) {
            loginNav.style.display = "none";
        }
    }
};

document.querySelector(".sobre-nav").addEventListener("click", function() {
    window.location.href = "./telaSobre/sobre.php";
});

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
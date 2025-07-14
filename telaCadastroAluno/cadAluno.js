const header = document.querySelector("header");

document.querySelector(".projetos-nav").addEventListener("click", function() {
    window.location.href = "../telaPrincipal/principal.php";
  });
  
  document.querySelector(".monitoria-nav").addEventListener("click", function() {
    window.location.href = "../telaMonitorias/telaMonitorias.php";
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

const header = document.querySelector("header");

document.querySelector(".login-nav").addEventListener("click", function() {
    window.location.href = "../telaLoginAluno/loginAluno.html";
  });
  
  document.querySelector(".projetos-nav").addEventListener("click", function() {
    window.location.href = "../telaPrincipal/principal.html";
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
  
  
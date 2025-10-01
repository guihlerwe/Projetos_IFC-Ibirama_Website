document.addEventListener('DOMContentLoaded', function() {
  console.log('DOM totalmente carregado e analisado');

  const header = document.querySelector("header");

  // Efeito de sombra no header ao fazer scroll
  window.addEventListener("scroll", () => {
    if (window.scrollY > 0) {
        header.classList.add("com-sombra");
    } else {
        header.classList.remove("com-sombra");
    }
  });

  document.querySelector(".projetos-nav").addEventListener("click", function() {
    window.location.href = "principal.php";
  });
  
  document.querySelector(".monitoria-nav").addEventListener("click", function() {
    window.location.href = "monitorias.php";
  });
    
  document.querySelector(".sobre-nav").addEventListener("click", function() {
    window.location.href = "sobre.php";
  });

  document.querySelector(".login-nav").addEventListener("click", function() {
    window.location.href = "login.php";
  });
});
  // Troca logo do header conforme modo claro/escuro
  function trocaLogoHeader() {
    const logoHeader = document.getElementById('icone-ifc');
    if (!logoHeader) return;
    const isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    logoHeader.src = isDark
      ? '../assets/photos/ifc-logo-branco.png'
      : '../assets/photos/ifc-logo-preto.png';
  }
  trocaLogoHeader();
  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', trocaLogoHeader);

  
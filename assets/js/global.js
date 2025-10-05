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
    
  document.querySelector(".Sobre").addEventListener("click", function() {
    window.location.href = "sobre.php";
  });

  const loginNav = document.querySelector(".login-nav");
  if (loginNav) {
    const menuBtn = loginNav.querySelector('.menu-btn');
    const dropdownContent = loginNav.querySelector('.dropdown-content');
    
    if (menuBtn && dropdownContent) {
      menuBtn.addEventListener('click', function(e) {
        e.preventDefault();
        dropdownContent.classList.toggle('active');
      });

      // Fechar dropdown quando clicar fora
      document.addEventListener('click', function(e) {
        if (!loginNav.contains(e.target)) {
          dropdownContent.classList.remove('active');
        }
      });
    } else if (!menuBtn) {
      // Se não tiver menu-btn, significa que é o link de login
      loginNav.addEventListener("click", function() {
        window.location.href = "login.php";
      });
    }
  }
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

  
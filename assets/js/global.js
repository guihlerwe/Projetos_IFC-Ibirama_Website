// Copyright (c) [year] [fullname]
// 
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

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

  // Verificar se o usuário está logado
  const usuarioLogado = sessionStorage.getItem('usuarioLogado');
  const monitoriaNav = document.querySelector(".monitoria-nav");
  
  // Ocultar botão de monitoria se não estiver logado
  if (!usuarioLogado && monitoriaNav) {
    monitoriaNav.style.display = 'none';
  }

  document.querySelector(".projetos-nav").addEventListener("click", function() {
    window.location.href = "principal.php";
  });
  
  if (monitoriaNav) {
    monitoriaNav.addEventListener("click", function() {
      window.location.href = "monitorias.php";
    });
  }
    
  const sobreLink = document.querySelector(".Sobre");
    if (sobreLink) {
      sobreLink.addEventListener("click", function() {
        window.location.href = "sobre.php";
      });
  }

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

  
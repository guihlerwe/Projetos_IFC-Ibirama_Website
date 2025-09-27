/* 
   ARQUIVO: assets/js/theme-manager.js
   
   Este arquivo deve ser incluído em TODAS as páginas do projeto.
   Coloque este JS em uma pasta compartilhada como assets/js/
*/

class GlobalThemeManager {
  

  init() {
    // Inicializa o tema assim que a classe é instanciada
    this.applyInitialTheme();
    
    // REMOVA ou COMENTE esta linha:
    // this.createThemeToggle();
    
    // Escuta mudanças no sistema
    this.watchSystemTheme();
    
    // Atualiza a meta tag para mobile
    this.updateThemeColorMeta();
  }

  applyInitialTheme() {
    const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    this.setTheme(systemPrefersDark ? 'dark' : 'light');
  }

  setTheme(theme) {
    if (theme === 'dark') {
      document.documentElement.setAttribute('data-theme', 'dark');
    } else {
      document.documentElement.removeAttribute('data-theme');
    }
    
    this.updateThemeColorMeta();
    this.updateThemeToggleButton();
  }

  getCurrentTheme() {
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
  }

  toggleTheme() {
    const current = this.getCurrentTheme();
    const newTheme = current === 'dark' ? 'light' : 'dark';
    
    this.setTheme(newTheme);
    localStorage.setItem(this.storageKey, newTheme);
    
    // Dispatch evento customizado para outras partes da aplicação
    window.dispatchEvent(new CustomEvent('themeChanged', { 
      detail: { theme: newTheme } 
    }));
  }

  watchSystemTheme() {
    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
    
    mediaQuery.addEventListener('change', (e) => {
      this.setTheme(e.matches ? 'dark' : 'light');
    });
  }

  



  updateThemeColorMeta() {
    let metaTag = document.querySelector('#theme-color-meta');
    
    if (!metaTag) {
      metaTag = document.createElement('meta');
      metaTag.name = 'theme-color';
      metaTag.id = 'theme-color-meta';
      document.head.appendChild(metaTag);
    }

    const theme = this.getCurrentTheme();
    metaTag.content = theme === 'dark' ? '#121212' : '#ffffff';
  }

  // Método para aplicar tema específico programaticamente
  forceTheme(theme) {
    this.setTheme(theme);
    localStorage.setItem(this.storageKey, theme);
  }

  // Método para resetar para preferência do sistema
  resetToSystem() {
    localStorage.removeItem(this.storageKey);
    this.applyInitialTheme();
  }

  // Método para verificar se está no modo escuro
  isDarkMode() {
    return this.getCurrentTheme() === 'dark';
  }
}

// Auto-inicializar quando o DOM estiver pronto
function initThemeManager() {
  // Evita criar múltiplas instâncias
  if (window.themeManager) {
    return window.themeManager;
  }

  window.themeManager = new GlobalThemeManager();
  return window.themeManager;
}

// Inicializa imediatamente se o DOM já estiver carregado
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initThemeManager);
} else {
  initThemeManager();
}

// Exporta para uso em outros scripts (se necessário)
if (typeof module !== 'undefined' && module.exports) {
  module.exports = GlobalThemeManager;
}

function trocaLogoIFCComTema(theme) {
  const logoHeader = document.getElementById('icone-ifc');
  if (logoHeader) {
    logoHeader.src = theme === 'dark'
      ? '../assets/photos/ifc-logo-branco.png'
      : '../assets/photos/ifc-logo-preto.png';
  }
}

// Troca logo ao iniciar
document.addEventListener('DOMContentLoaded', function() {
  const theme = window.themeManager?.getCurrentTheme?.() || 'light';
  trocaLogoIFCComTema(theme);
});

// Troca logo quando o tema muda
window.addEventListener('themeChanged', function(e) {
  trocaLogoIFCComTema(e.detail.theme);
});
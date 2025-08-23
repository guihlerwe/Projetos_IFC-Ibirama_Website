/* 
   ARQUIVO: assets/js/theme-manager.js
   
   Este arquivo deve ser incluído em TODAS as páginas do projeto.
   Coloque este JS em uma pasta compartilhada como assets/js/
*/

class GlobalThemeManager {
  constructor() {
    this.storageKey = 'projeto-ifc-theme';
    this.init();
  }

  init() {
    // Inicializa o tema assim que a classe é instanciada
    this.applyInitialTheme();
    
    // Cria o botão de alternância
    this.createThemeToggle();
    
    // Escuta mudanças no sistema
    this.watchSystemTheme();
    
    // Atualiza a meta tag para mobile
    this.updateThemeColorMeta();
  }

  applyInitialTheme() {
    const savedTheme = localStorage.getItem(this.storageKey);
    
    if (savedTheme) {
      this.setTheme(savedTheme);
    } else {
      // Se não tem preferência salva, usa a do sistema
      const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
      this.setTheme(systemPrefersDark ? 'dark' : 'light');
    }
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
    const saved = localStorage.getItem(this.storageKey);
    if (saved) return saved;
    
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
      // Só muda automaticamente se não há preferência manual salva
      if (!localStorage.getItem(this.storageKey)) {
        this.setTheme(e.matches ? 'dark' : 'light');
      }
    });
  }

  createThemeToggle() {
    // Verifica se já existe um botão na página
    if (document.querySelector('.theme-toggle')) {
      this.updateThemeToggleButton();
      return;
    }

    const button = document.createElement('button');
    button.className = 'theme-toggle';
    button.setAttribute('aria-label', 'Alternar tema');
    button.setAttribute('title', 'Alternar entre modo claro e escuro');
    
    // Adiciona evento
    button.addEventListener('click', () => this.toggleTheme());

    // Tenta encontrar onde inserir o botão
    this.insertThemeButton(button);
    
    this.updateThemeToggleButton();
  }

  insertThemeButton(button) {
    // Lista de possíveis locais para inserir o botão (em ordem de prioridade)
    const possibleContainers = [
      '.navegador',           // Para a página sobre
      '.header-nav',          // Para outras páginas
      '.navbar',              // Bootstrap navbar
      '.nav',                 // Navegação genérica
      'header',               // Qualquer header
      '.top-bar',             // Barra superior
      '.main-nav'             // Navegação principal
    ];

    for (const selector of possibleContainers) {
      const container = document.querySelector(selector);
      if (container) {
        container.appendChild(button);
        return;
      }
    }

    // Se não encontrou nenhum container, adiciona no body como fallback
    const fallbackContainer = document.createElement('div');
    fallbackContainer.style.cssText = `
      position: fixed;
      top: 20px;
      right: 20px;
      z-index: 9999;
    `;
    fallbackContainer.appendChild(button);
    document.body.appendChild(fallbackContainer);
  }

  updateThemeToggleButton() {
    const button = document.querySelector('.theme-toggle');
    if (!button) return;

    const currentTheme = this.getCurrentTheme();
    
    if (currentTheme === 'dark') {
      button.innerHTML = '☀️';
      button.setAttribute('title', 'Mudar para modo claro');
    } else {
      button.innerHTML = '🌙';
      button.setAttribute('title', 'Mudar para modo escuro');
    }
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
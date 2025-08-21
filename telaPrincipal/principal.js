const header = document.querySelector("header");

// Navegação entre páginas
document.querySelector("#monitoria-nav").addEventListener("click", function() {
    window.location.href = "../telaMonitorias/telaMonitorias.php";
});

document.querySelector("#sobre-nav").addEventListener("click", function() {
    window.location.href = "../telaSobre/sobre.php";
});

// Efeito de sombra no header ao fazer scroll
window.addEventListener("scroll", () => {
    if (window.scrollY > 0) {
        header.classList.add("com-sombra");
    } else {
        header.classList.remove("com-sombra");
    }
});

// Funcionalidade quando o DOM carregar
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

    // Adicionar evento de clique nos cards de projeto
    const projectCards = document.querySelectorAll('.project-card');
    projectCards.forEach(card => {
        card.addEventListener('click', function() {
            const projetoId = this.getAttribute('data-id');
            // Redirecionar para página de detalhes do projeto
            window.location.href = `../telaProjeto/projeto.php?id=${projetoId}`;
        });
    });

    // Funcionalidade de filtros
    setupFiltros();
    setupPesquisa();
});

function setupFiltros() {
    // Filtros por tipo de projeto
    const botoesFiltro = document.querySelectorAll('.btn-filtrar[data-filtro]');
    const projectCards = document.querySelectorAll('.project-card');

    botoesFiltro.forEach(botao => {
        botao.addEventListener('click', function() {
            const filtro = this.getAttribute('data-filtro');
            
            // Remover classe ativa de todos os botões
            botoesFiltro.forEach(btn => btn.classList.remove('filtro-ativo'));
            // Adicionar classe ativa ao botão clicado
            this.classList.add('filtro-ativo');
            
            projectCards.forEach(card => {
                if (filtro === '' || card.classList.contains(filtro)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });

    // Filtro por categoria
    const selectCategoria = document.getElementById('categorias-filtrar');
    selectCategoria.addEventListener('change', function() {
        const categoriaFiltro = this.value;
        
        projectCards.forEach(card => {
            if (categoriaFiltro === '' || card.getAttribute('data-categoria') === categoriaFiltro) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    });
}

function setupPesquisa() {
    const inputPesquisa = document.getElementById('input-pesquisa');
    const projectCards = document.querySelectorAll('.project-card');
    
    inputPesquisa.addEventListener('input', function() {
        const termoPesquisa = this.value.toLowerCase();
        
        projectCards.forEach(card => {
            const nomeLabel = card.querySelector('.project-label');
            const nomeProjeto = nomeLabel.textContent.toLowerCase();
            
            if (nomeProjeto.includes(termoPesquisa)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    });
}
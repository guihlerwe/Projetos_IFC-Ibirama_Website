const header = document.querySelector("header");

// Navegação entre páginas
document.querySelector("#monitoria-nav").addEventListener("click", function() {
    window.location.href = "../telaMonitorias/telaMonitorias.php";
});

document.querySelector("#sobre-nav").addEventListener("click", function() {
    window.location.href = "../telaSobre/sobre.php";
});

document.querySelector("#login-nav").addEventListener("click", function() {
    window.location.href = "../telaCadastroAluno/cadAluno.html";
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
    // Filtros por tipo de projeto - MELHORADO
    const botoesFiltro = document.querySelectorAll('.btn-filtrar[data-filtro]');
    const projectCards = document.querySelectorAll('.project-card');
    let filtroAtivo = ''; // variável para controlar o filtro ativo

    botoesFiltro.forEach(botao => {
        botao.addEventListener('click', function() {
            const filtro = this.getAttribute('data-filtro');
            
            // Se clicar no mesmo filtro ativo, desativa (volta para "todos")
            if (filtroAtivo === filtro && filtro !== '') {
                // Desativar filtro atual
                filtroAtivo = '';
                botoesFiltro.forEach(btn => btn.classList.remove('filtro-ativo'));
                
                // Mostrar todos os cards
                projectCards.forEach(card => {
                    card.style.display = 'block';
                });
            } else {
                // Ativar novo filtro
                filtroAtivo = filtro;
                
                // Remover classe ativa de todos os botões
                botoesFiltro.forEach(btn => btn.classList.remove('filtro-ativo'));
                // Adicionar classe ativa ao botão clicado
                this.classList.add('filtro-ativo');
                
                // Aplicar filtro
                projectCards.forEach(card => {
                    if (filtro === '' || card.classList.contains(filtro)) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            }
        });
    });

    // Filtro por categoria - MELHORADO
    const selectCategoria = document.getElementById('categorias-filtrar');
    if (selectCategoria) {
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
}

function setupPesquisa() {
    const inputPesquisa = document.getElementById('input-pesquisa');
    const projectCards = document.querySelectorAll('.project-card');
    
    if (inputPesquisa) {
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
}

// Custom dropdown categorias - MELHORADO
document.addEventListener("DOMContentLoaded", () => {
    const selectBox = document.getElementById("categorias-filtrar");
    
    // Verificar se é um select customizado ou nativo
    if (selectBox && selectBox.classList.contains('custom-select')) {
        const selected = selectBox.querySelector(".select-selected");
        const optionsContainer = selectBox.querySelector(".select-items");
        const options = optionsContainer.querySelectorAll("div");

        // Abrir/fechar dropdown
        selected.addEventListener("click", () => {
            selectBox.classList.toggle("open");
        });

        // Selecionar opção
        options.forEach(option => {
            option.addEventListener("click", () => {
                const value = option.getAttribute("data-value");
                const text = option.textContent;
                
                // Não mostrar "Categorias" como selecionado
                if (value === '') {
                    selected.textContent = "Categorias";
                } else {
                    selected.textContent = text;
                }
                
                selectBox.classList.remove("open");

                // Filtro dos cards
                const projectCards = document.querySelectorAll('.project-card');
                projectCards.forEach(card => {
                    if (value === "" || card.getAttribute("data-categoria") === value) {
                        card.style.display = "block";
                    } else {
                        card.style.display = "none";
                    }
                });
            });
        });

        // Fechar dropdown ao clicar fora
        document.addEventListener("click", (e) => {
            if (!selectBox.contains(e.target)) {
                selectBox.classList.remove("open");
            }
        });
    }
});
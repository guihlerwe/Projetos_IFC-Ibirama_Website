// Funcionalidade quando o DOM carregar
document.addEventListener("DOMContentLoaded", function () {
    
    // ===== Gerenciar menu do usuário logado =====
    const nome = sessionStorage.getItem("usuarioLogado");
    const loginNav = document.querySelector('.login-nav');

    // Não ocultar o menu do usuário para logados

    // ===== Adicionar evento de clique nos cards de projeto =====
    const projectCards = document.querySelectorAll('.project-card');
    projectCards.forEach(card => {
        card.addEventListener('click', function() {
            const idProjeto = this.getAttribute('data-id');
            // Redirecionar para página de detalhes do projeto
            window.location.href = `projeto.php?id=${idProjeto}`;
        });
    });

    // ===== Funcionalidade de filtros e pesquisa =====
    setupFiltros();
    setupPesquisa();

    // ===== Custom dropdown categorias =====
    const selectBox = document.getElementById("categorias-filtrar");
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
                selected.textContent = value === '' ? "Categorias" : text;
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

function normalizeString(str) {
    return str
        .toLowerCase()
        .normalize("NFD") // tira acentos
        .replace(/[\u0300-\u036f]/g, "")
        .replace(/\s+/g, "-"); // troca espaço por hífen
}

function setupFiltros() {
    const botoesFiltro = document.querySelectorAll('.btn-filtrar[data-filtro]');
    const projectCards = document.querySelectorAll('.project-card');
    let filtroAtivo = '';

    // ===== Filtro por tipo (ensino, pesquisa, extensão) =====
    botoesFiltro.forEach(botao => {
        botao.addEventListener('click', function() {
            const filtro = this.getAttribute('data-filtro');
            
            if (filtroAtivo === filtro && filtro !== '') {
                filtroAtivo = '';
                botoesFiltro.forEach(btn => btn.classList.remove('filtro-ativo'));
                projectCards.forEach(card => card.style.display = 'block');
            } else {
                filtroAtivo = filtro;
                botoesFiltro.forEach(btn => btn.classList.remove('filtro-ativo'));
                this.classList.add('filtro-ativo');
                
                projectCards.forEach(card => {
                    const tipo = card.getAttribute("data-tipo"); 
                    if (filtro === '' || tipo === filtro) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            }
        });
    });

    // ===== Filtro por categoria =====
    const selectCategoria = document.getElementById('categorias-filtrar');
    if (selectCategoria) {
        selectCategoria.addEventListener('change', function() {
            const categoriaFiltro = normalizeString(this.value);
            
            projectCards.forEach(card => {
                const categoriaCard = normalizeString(card.getAttribute('data-categoria'));
                if (categoriaFiltro === '' || categoriaCard === categoriaFiltro) {
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

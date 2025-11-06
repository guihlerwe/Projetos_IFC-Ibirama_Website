// Funcionalidade quando o DOM carregar
document.addEventListener("DOMContentLoaded", function () {
    
    // ===== Gerenciar menu do usuário logado =====
    const nome = sessionStorage.getItem("usuarioLogado");
    const loginNav = document.querySelector('.login-nav');

    // ===== Adicionar evento de clique nos cards de projeto =====
    const projectCards = document.querySelectorAll('.project-card');
    projectCards.forEach(card => {
        card.addEventListener('click', function() {
            const viewUrl = this.dataset.viewUrl;
            if (viewUrl) {
                window.location.href = viewUrl;
            }
        });
    });

    // ===== Funcionalidade de filtros e pesquisa =====
    setupFiltros();
    setupPesquisa();
    setupCategorias();
    setupBotaoLimpar();
});

function normalizeString(str) {
    return str
        .toLowerCase()
        .normalize("NFD") // tira acentos
        .replace(/[\u0300-\u036f]/g, "")
        .replace(/\s+/g, "-"); // troca espaço por hífen
}

// ===== Variáveis globais para filtros =====
let filtroAtivoTipo = '';
let filtroAtivoCategoria = '';

// ===== Filtro por tipo (ensino, pesquisa, extensão) =====
function setupFiltros() {
    const botoesFiltro = document.querySelectorAll('.btn-filtrar[data-filtro]');

    botoesFiltro.forEach(botao => {
        botao.addEventListener('click', function() {
            const filtro = this.getAttribute('data-filtro');
            
            if (filtroAtivoTipo === filtro && filtro !== '') {
                filtroAtivoTipo = '';
                botoesFiltro.forEach(btn => btn.classList.remove('filtro-ativo'));
            } else {
                filtroAtivoTipo = filtro;
                botoesFiltro.forEach(btn => btn.classList.remove('filtro-ativo'));
                this.classList.add('filtro-ativo');
            }

            aplicarFiltros();
        });
    });
}

// ===== Pesquisa por nome =====
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

// ===== Dropdown de categorias =====
function setupCategorias() {
    const selectBox = document.getElementById("categorias-filtrar");
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
            let value = option.getAttribute("data-value");

            // Normalizar categorias (trocar underline por hífen e remover acento)
            value = value.replace(/_/g, "-"); 
            value = normalizeString(value);

            filtroAtivoCategoria = value;

            selected.textContent = option.textContent;
            selectBox.classList.remove("open");

            aplicarFiltros();
        });
    });

    // Fechar dropdown ao clicar fora
    document.addEventListener("click", (e) => {
        if (!selectBox.contains(e.target)) {
            selectBox.classList.remove("open");
        }
    });
}

// ===== Botão para limpar filtros =====
function setupBotaoLimpar() {
    const btnLimpar = document.getElementById("limpar-filtros");
    if (btnLimpar) {
        // começa oculto
        btnLimpar.style.display = "none";

        btnLimpar.addEventListener("click", () => {
            filtroAtivoTipo = '';
            filtroAtivoCategoria = '';

            // Resetar UI
            document.querySelectorAll('.btn-filtrar').forEach(btn => btn.classList.remove('filtro-ativo'));
            const selected = document.querySelector("#categorias-filtrar .select-selected");
            if (selected) selected.textContent = "Categorias";

            aplicarFiltros();
        });
    }
}

// ===== Aplicar filtros combinados =====
function aplicarFiltros() {
    const projectCards = document.querySelectorAll('.project-card');
    projectCards.forEach(card => {
        const tipo = card.getAttribute("data-tipo");
        const categoria = card.getAttribute("data-categoria");

        const passaFiltroTipo = (filtroAtivoTipo === '' || tipo === filtroAtivoTipo);
        const passaFiltroCategoria = (filtroAtivoCategoria === '' || categoria === filtroAtivoCategoria);

        if (passaFiltroTipo && passaFiltroCategoria) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });

    // Mostrar ou esconder botão "limpar filtros"
    const btnLimpar = document.getElementById("limpar-filtros");
    if (btnLimpar) {
        if (filtroAtivoTipo !== '' || filtroAtivoCategoria !== '') {
            btnLimpar.style.display = "inline-block";
        } else {
            btnLimpar.style.display = "none";
        }
    }
}

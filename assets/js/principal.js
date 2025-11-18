// Copyright (c) [year] [fullname]
// 
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

// Funcionalidade quando o DOM carregar
document.addEventListener("DOMContentLoaded", function () {
    
    //Gerenciar menu do usuário logado
    const nome = sessionStorage.getItem("usuarioLogado");
    const loginNav = document.querySelector('.login-nav');

    //Adicionar evento de clique nos cards de projeto
    const projectCards = document.querySelectorAll('.project-card');
    projectCards.forEach(card => {
        card.addEventListener('click', function() {
            const viewUrl = this.dataset.viewUrl;
            if (viewUrl) {
                window.location.href = viewUrl;
            }
        });
    });

    //Funcionalidade de filtros e pesquisa
    setupFiltros();
    setupPesquisa();
    setupCategorias();
    setupBotaoLimpar();
    setupDeleteModal();
});

function normalizeString(str) {
    return str
        .toLowerCase()
        .normalize("NFD") // tira acentos
        .replace(/[\u0300-\u036f]/g, "")
        .replace(/\s+/g, "-"); // troca espaço por hífen
}

//Variáveis globais para filtros
let filtroAtivoTipo = '';
let filtroAtivoCategoria = '';

// Filtro por tipo (ensino, pesquisa, extensão)
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

// Pesquisa por nome 
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

// Dropdown de categorias
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

// Botão para limpar filtros 
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

// Aplicar filtros combinados 
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

function setupDeleteModal() {
    const modal = document.getElementById('delete-modal');
    const deleteButtons = document.querySelectorAll('.project-delete-btn');
    if (!modal || deleteButtons.length === 0) {
        return;
    }

    const projectIdInput = document.getElementById('delete-project-id');
    const confirmInput = document.getElementById('delete-confirm-input');
    const errorBox = document.getElementById('delete-error');
    const modalTitle = document.getElementById('delete-modal-title');
    const closeBtn = document.getElementById('delete-modal-close');
    const cancelBtn = document.getElementById('delete-modal-cancel');
    const form = document.getElementById('delete-form');

    if (!projectIdInput || !confirmInput || !errorBox || !form) {
        return;
    }

    const openModal = (projectId, projectName) => {
        projectIdInput.value = projectId;
        confirmInput.value = '';
        errorBox.textContent = '';
        if (modalTitle) {
            modalTitle.textContent = `Excluir "${projectName}"`;
        }
        modal.classList.add('open');
        modal.setAttribute('aria-hidden', 'false');
        confirmInput.focus();
    };

    const closeModal = () => {
        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');
    };

    deleteButtons.forEach(button => {
        button.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();

            const projectId = button.getAttribute('data-project-id');
            const projectName = button.getAttribute('data-project-name') || '';
            openModal(projectId, projectName);
        });
    });

    closeBtn?.addEventListener('click', closeModal);
    cancelBtn?.addEventListener('click', (event) => {
        event.preventDefault();
        closeModal();
    });

    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeModal();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal.classList.contains('open')) {
            closeModal();
        }
    });

    form?.addEventListener('submit', (event) => {
        if (confirmInput.value.trim().toLowerCase() !== 'confirmar') {
            event.preventDefault();
            errorBox.textContent = 'Digite "confirmar" para excluir o projeto.';
            confirmInput.focus();
        }
    });
}

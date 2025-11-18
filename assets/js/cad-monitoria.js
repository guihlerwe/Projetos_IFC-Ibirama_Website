// Copyright (c) [year] [fullname]
// 
// This source code is licensed under the MIT license found in the
// LICENSE file in the root directory of this source tree.

document.addEventListener('DOMContentLoaded', function() {
    // Variáveis para armazenar seleções
    let monitorSelecionado = null;
    const FOTO_PLACEHOLDER = '../assets/photos/fotos_perfil/sem_foto_perfil.jpg';
    
    // Configurar custom select para tipo de monitoria
    setupCustomSelect('tipo-select', 'tipo-monitoria');
    
    // Preview da capa
    const fotoCapa = document.getElementById('foto-capa');
    const capaPreview = document.getElementById('capa-preview');
    const capaIcon = document.getElementById('capa-icon');
    const capaPlaceholder = document.getElementById('capa-placeholder');
    
    if (fotoCapa) {
        fotoCapa.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    capaPreview.src = e.target.result;
                    capaPreview.style.display = 'block';
                    if (capaIcon) capaIcon.style.display = 'none';
                    if (capaPlaceholder) capaPlaceholder.style.display = 'none';
                };
                reader.readAsDataURL(file);
            }
            else {
                if (capaPreview) {
                    capaPreview.style.display = 'none';
                    capaPreview.removeAttribute('src');
                }
                if (capaIcon) capaIcon.style.display = 'block';
                if (capaPlaceholder) capaPlaceholder.style.display = 'block';
            }
        });
    }
    
    // Contador de caracteres da descrição
    const descricao = document.getElementById('descricao');
    const charCount = document.getElementById('char-count');
    
    if (descricao && charCount) {
        // Atualizar contador inicial
        charCount.textContent = descricao.value.length;
        
        descricao.addEventListener('input', function() {
            charCount.textContent = this.value.length;
        });
    }
    
    // Configuração do modal de seleção de monitor
    const monitorContainer = document.getElementById('monitor-container');
    const addMonitorCard = document.getElementById('add-monitor');
    const monitorIdInput = document.getElementById('monitor_id');
    
    if (addMonitorCard) {
        addMonitorCard.addEventListener('click', function() {
            abrirModalMonitor();
        });
    }
    
    // Função para abrir modal de seleção de monitor
    function abrirModalMonitor() {
        // Criar modal
        const modal = document.createElement('div');
        modal.className = 'modal-overlay';
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Selecionar Monitor(a)</h2>
                    <button type="button" class="btn-fechar-modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="text" id="busca-monitor" placeholder="Buscar por nome..." class="input-busca">
                    <div class="lista-monitores" id="lista-monitores"></div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Preencher lista de monitores
        preencherListaMonitores();
        
        // Busca em tempo real
        const inputBusca = document.getElementById('busca-monitor');
        if (inputBusca) {
            inputBusca.addEventListener('input', function() {
                preencherListaMonitores(this.value.toLowerCase());
            });
        }
        
        // Fechar modal
        const btnFechar = modal.querySelector('.btn-fechar-modal');
        if (btnFechar) {
            btnFechar.addEventListener('click', function() {
                document.body.removeChild(modal);
            });
        }
        
        // Fechar ao clicar fora
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                document.body.removeChild(modal);
            }
        });
    }
    
    // Função para preencher lista de monitores
    function preencherListaMonitores(filtro = '') {
        const listaMonitores = document.getElementById('lista-monitores');
        if (!listaMonitores) return;
        
        listaMonitores.innerHTML = '';
        
        const monitoresFiltrados = monitoresData.filter(monitor => {
            const nomeCompleto = `${monitor.nome} ${monitor.sobrenome}`.toLowerCase();
            return nomeCompleto.includes(filtro);
        });
        
        if (monitoresFiltrados.length === 0) {
            listaMonitores.innerHTML = '<p class="sem-resultados">Nenhum monitor encontrado</p>';
            return;
        }
        
        monitoresFiltrados.forEach(monitor => {
            const cursoFormatado = formatarCurso(monitor.curso);
            
            const monitorCard = document.createElement('div');
            monitorCard.className = 'monitor-card-modal';
            monitorCard.innerHTML = `
                <img src="${monitor.foto_src}" alt="${monitor.nome}" class="monitor-foto-modal">
                <div class="monitor-info-modal">
                    <div class="monitor-nome-modal">${monitor.nome} ${monitor.sobrenome}</div>
                    ${cursoFormatado ? `<div class="monitor-curso-modal">${cursoFormatado}</div>` : ''}
                </div>
                <button type="button" class="btn-selecionar-modal">Selecionar</button>
            `;
            
            const btnSelecionar = monitorCard.querySelector('.btn-selecionar-modal');
            btnSelecionar.addEventListener('click', function() {
                selecionarMonitor(monitor);
                const modal = document.querySelector('.modal-overlay');
                if (modal) document.body.removeChild(modal);
            });
            
            listaMonitores.appendChild(monitorCard);
        });
    }
    
    // Função para selecionar monitor
    function selecionarMonitor(monitor) {
        monitorSelecionado = monitor;
        monitorIdInput.value = monitor.idPessoa;
        renderizarMonitorSelecionado();
    }

    function renderizarMonitorSelecionado() {
        if (!monitorContainer) return;

        monitorContainer.querySelectorAll('.membro.monitor-ativo').forEach(card => card.remove());

        if (!monitorSelecionado) {
            toggleAddMonitor(true);
            return;
        }

        const card = document.createElement('div');
        card.className = 'membro monitor-ativo';
        card.dataset.id = monitorSelecionado.idPessoa;
        const fotoSrc = monitorSelecionado.foto_src && monitorSelecionado.foto_src !== 'null'
            ? monitorSelecionado.foto_src
            : FOTO_PLACEHOLDER;
        const sobrenome = monitorSelecionado.sobrenome ? ` ${monitorSelecionado.sobrenome}` : '';
        const nomeCompleto = `${monitorSelecionado.nome || ''}${sobrenome}`.trim();
        const nomeCurto = nomeCompleto.length > 16 ? `${nomeCompleto.substring(0, 13)}...` : nomeCompleto || 'Monitor';
        const cursoFormatado = formatarCurso(monitorSelecionado.curso);

        card.innerHTML = `
            <div class="foto-membro-wrapper">
                <div class="foto-membro">
                    <img src="${fotoSrc}" alt="${nomeCompleto}" onerror="this.onerror=null; this.src='${FOTO_PLACEHOLDER}';">
                </div>
                <div class="btn-remover" title="Remover monitor">
                    <span>➖</span>
                </div>
            </div>
            <div class="nome-membro" title="${nomeCompleto}">${nomeCurto}</div>
            ${cursoFormatado ? `<div class="monitor-mini-curso">${cursoFormatado}</div>` : ''}
        `;

        const btnRemover = card.querySelector('.btn-remover');
        if (btnRemover) {
            btnRemover.addEventListener('click', removerMonitor);
        }

        if (monitorContainer && addMonitorCard) {
            monitorContainer.insertBefore(card, addMonitorCard);
        } else if (monitorContainer) {
            monitorContainer.appendChild(card);
        }

        toggleAddMonitor(false);
    }

    function removerMonitor() {
        monitorSelecionado = null;
        monitorIdInput.value = '';
        monitorContainer && monitorContainer.querySelectorAll('.membro.monitor-ativo').forEach(card => card.remove());
        toggleAddMonitor(true);
    }

    function toggleAddMonitor(mostrar) {
        if (addMonitorCard) {
            addMonitorCard.style.display = mostrar ? 'flex' : 'none';
        }
    }
    
    // Função auxiliar para formatar nome do curso
    function formatarCurso(curso) {
        const cursoMap = {
            'administracao': 'Administração',
            'informatica': 'Informática',
            'vestuario': 'Vestuário',
            'moda': 'Moda'
        };
        return cursoMap[curso] || curso;
    }
    
    // Carregar monitor se estiver em modo de edição
    if (monitoresMonitoriaSelecionados && monitoresMonitoriaSelecionados.length > 0) {
        selecionarMonitor(monitoresMonitoriaSelecionados[0]);
    }
    
    // Validação do formulário
    const formulario = document.getElementById('formulario');
    if (formulario) {
        formulario.addEventListener('submit', function(e) {
            // Validar se pelo menos um dia foi selecionado
            const diasSelecionados = document.querySelectorAll('.dia-checkbox:checked');
            if (diasSelecionados.length === 0) {
                e.preventDefault();
                alert('Por favor, selecione pelo menos um dia de atendimento.');
                return false;
            }
            
            // Validar se um monitor foi selecionado
            if (!monitorIdInput.value) {
                e.preventDefault();
                alert('Por favor, selecione um monitor para a monitoria.');
                return false;
            }
            
            return true;
        });
    }
});

// Função para configurar custom select
function setupCustomSelect(selectId, inputId) {
    const customSelect = document.getElementById(selectId);
    if (!customSelect) return;
    
    const selectSelected = customSelect.querySelector('.select-selected');
    const selectItems = customSelect.querySelector('.select-items');
    const hiddenInput = document.getElementById(inputId);
    
    if (!selectSelected || !selectItems || !hiddenInput) return;
    
    // Toggle dropdown
    selectSelected.addEventListener('click', function(e) {
        e.stopPropagation();
        const isOpen = selectItems.classList.toggle('active');
        customSelect.classList.toggle('open', isOpen);
    });
    
    // Selecionar item
    const items = selectItems.querySelectorAll('div');
    items.forEach(item => {
        item.addEventListener('click', function(e) {
            e.stopPropagation();
            const value = this.getAttribute('data-value');
            const text = this.textContent;
            
            selectSelected.textContent = text;
            hiddenInput.value = value;
            selectItems.classList.remove('active');
            customSelect.classList.remove('open');
        });
    });
    
    // Fechar ao clicar fora
    document.addEventListener('click', function() {
        selectItems.classList.remove('active');
        customSelect.classList.remove('open');
    });
}
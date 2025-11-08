document.addEventListener('DOMContentLoaded', function() {
    // Variáveis para armazenar seleções
    let coordenadoresSelecionados = [];
    let monitorSelecionado = null;
    const FOTO_PLACEHOLDER = '../assets/photos/fotos_perfil/sem_foto_perfil.jpg';
    
    // Configurar custom select para tipo de monitoria
    setupCustomSelect('tipo-select', 'tipo-monitoria');
    
    // Preview da capa
    const fotoCapa = document.getElementById('foto-capa');
    const capaPreview = document.getElementById('capa-preview');
    const capaIcon = document.getElementById('capa-icon');
    
    if (fotoCapa) {
        fotoCapa.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    capaPreview.src = e.target.result;
                    capaPreview.style.display = 'block';
                    if (capaIcon) capaIcon.style.display = 'none';
                };
                reader.readAsDataURL(file);
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
    const btnSelecionarMonitor = document.querySelector('.btn-selecionar');
    const monitorInfoContainer = document.getElementById('monitor-info-container');
    const monitorIdInput = document.getElementById('monitor_id');
    
    if (btnSelecionarMonitor) {
        btnSelecionarMonitor.addEventListener('click', function() {
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
        
        const cursoFormatado = formatarCurso(monitor.curso);
        
        // Atualizar container com o monitor selecionado
        monitorInfoContainer.innerHTML = `
            <div class="monitor-selecionado-preview">
                <div class="monitor-foto-preview">
                    <img src="${monitor.foto_src}" alt="${monitor.nome}">
                </div>
                <div class="monitor-dados-preview">
                    <div class="monitor-nome-preview">${monitor.nome} ${monitor.sobrenome}</div>
                    ${cursoFormatado ? `<div class="monitor-curso-preview">${cursoFormatado}</div>` : ''}
                </div>
                <button type="button" class="btn-remover-monitor">Remover</button>
            </div>
        `;
        
        // Adicionar evento de remover
        const btnRemover = monitorInfoContainer.querySelector('.btn-remover-monitor');
        if (btnRemover) {
            btnRemover.addEventListener('click', function() {
                removerMonitor();
            });
        }
    }
    
    // Função para remover monitor
    function removerMonitor() {
        monitorSelecionado = null;
        monitorIdInput.value = '';
        
        monitorInfoContainer.innerHTML = `
            <div class="selecionar-monitor" id="selecionar-monitor">
                <button type="button" class="btn-selecionar">+ Selecionar Monitor(a)</button>
            </div>
        `;
        
        // Re-adicionar evento de clique
        const btnSelecionarNovo = monitorInfoContainer.querySelector('.btn-selecionar');
        if (btnSelecionarNovo) {
            btnSelecionarNovo.addEventListener('click', function() {
                abrirModalMonitor();
            });
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
        selectItems.classList.toggle('active');
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
        });
    });
    
    // Fechar ao clicar fora
    document.addEventListener('click', function() {
        selectItems.classList.remove('active');
    });
}
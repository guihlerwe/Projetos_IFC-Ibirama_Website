document.addEventListener('DOMContentLoaded', function() {
    // Variável para armazenar seleção
    let monitorSelecionado = null;
    const FOTO_PLACEHOLDER = '../assets/photos/fotos_perfil/sem_foto_perfil.jpg';
    
    // Configurar custom select para tipo de monitoria
    setupCustomSelect('tipo-select', 'tipo-monitoria');
    
    // Preview da capa
    const fotoCapa = document.getElementById('foto-capa');
    const capaPreview = document.getElementById('capa-preview');
    const capaIcon = document.getElementById('capa-icon');
    
    if (fotoCapa) {
        fotoCapa.addEventListener('change', function() {
            const file = this.files[0];
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
    
    // Função para criar card do monitor
    function criarMonitorCard(pessoa) {
        const card = document.createElement('div');
        card.className = 'monitor-card';
        card.dataset.id = pessoa.idPessoa;
        
        const fotoSrc = pessoa.foto_src && pessoa.foto_src !== 'null' ? pessoa.foto_src : FOTO_PLACEHOLDER;
        const nomeCompleto = pessoa.nome + ' ' + pessoa.sobrenome;
        const curso = pessoa.curso ? formatarCurso(pessoa.curso) : 'Curso não informado';
        
        card.innerHTML = `
            <div class="monitor-card-foto">
                <img src="${fotoSrc}" alt="${nomeCompleto}" onerror="this.onerror=null; this.src='${FOTO_PLACEHOLDER}';">
            </div>
            <div class="monitor-card-info">
                <div class="monitor-card-nome">${nomeCompleto}</div>
                <div class="monitor-card-email">${pessoa.email}</div>
                <div class="monitor-card-curso">${curso}</div>
            </div>
            <button type="button" class="btn-remover-monitor" title="Remover monitor">×</button>
        `;
        
        card.querySelector('.btn-remover-monitor').addEventListener('click', function() {
            removerMonitor();
        });
        
        return card;
    }
    
    // Função para formatar nome do curso
    function formatarCurso(curso) {
        const mapa = {
            'administracao': 'Administração',
            'informatica': 'Informática',
            'vestuario': 'Vestuário',
            'moda': 'Moda'
        };
        return mapa[curso.toLowerCase()] || curso.charAt(0).toUpperCase() + curso.slice(1);
    }
    
    // Modal de seleção de monitor
    function abrirModalMonitor() {
        const dados = monitoresData;
        
        const modal = document.createElement('div');
        modal.className = 'modal-selecao';
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Selecione um Monitor</h3>
                    <span class="modal-close">&times;</span>
                </div>
                <div class="modal-body">
                    <input type="text" class="modal-search" placeholder="Buscar por nome, email ou curso...">
                    <div class="modal-lista"></div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        const lista = modal.querySelector('.modal-lista');
        const searchInput = modal.querySelector('.modal-search');
        
        function renderizarLista(filtro = '') {
            lista.innerHTML = '';
            
            const dadosFiltrados = dados.filter(pessoa => {
                if (filtro) {
                    const curso = pessoa.curso || '';
                    const texto = `${pessoa.nome} ${pessoa.sobrenome} ${pessoa.email} ${curso}`.toLowerCase();
                    return texto.includes(filtro.toLowerCase());
                }
                return true;
            });
            
            if (dadosFiltrados.length === 0) {
                lista.innerHTML = '<div class="sem-resultados">Nenhum monitor disponível</div>';
                return;
            }
            
            dadosFiltrados.forEach(pessoa => {
                const item = document.createElement('div');
                item.className = 'modal-item';
                
                const fotoSrc = pessoa.foto_src && pessoa.foto_src !== 'null' ? pessoa.foto_src : FOTO_PLACEHOLDER;
                const curso = pessoa.curso ? formatarCurso(pessoa.curso) : 'Curso não informado';
                
                item.innerHTML = `
                    <div class="modal-item-foto">
                        <img src="${fotoSrc}" alt="${pessoa.nome}" onerror="this.onerror=null; this.src='${FOTO_PLACEHOLDER}';">
                    </div>
                    <div class="modal-item-info">
                        <div class="modal-item-nome">${pessoa.nome} ${pessoa.sobrenome}</div>
                        <div class="modal-item-email">${pessoa.email}</div>
                        <div class="modal-item-curso" style="font-size: 12px; color: var(--text-secondary); font-style: italic;">${curso}</div>
                    </div>
                `;
                
                item.addEventListener('click', function() {
                    adicionarMonitor(pessoa);
                    document.body.removeChild(modal);
                });
                
                lista.appendChild(item);
            });
        }
        
        renderizarLista();
        
        searchInput.addEventListener('input', function() {
            renderizarLista(this.value);
        });
        
        const closeBtn = modal.querySelector('.modal-close');
        closeBtn.addEventListener('click', function() {
            document.body.removeChild(modal);
        });
        
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                document.body.removeChild(modal);
            }
        });
        
        setTimeout(() => searchInput.focus(), 100);
    }
    
    // Funções de adicionar/remover
    function adicionarMonitor(pessoa) {
        monitorSelecionado = pessoa;
        const container = document.getElementById('monitor-info-container');
        container.innerHTML = '';
        container.appendChild(criarMonitorCard(pessoa));
        atualizarInputMonitor();
    }
    
    function removerMonitor() {
        monitorSelecionado = null;
        const container = document.getElementById('monitor-info-container');
        container.innerHTML = `
            <div class="selecionar-monitor" id="selecionar-monitor">
                <button type="button" class="btn-selecionar">Selecionar Monitor(a)</button>
            </div>
        `;
        
        // Re-adicionar evento
        document.getElementById('selecionar-monitor').querySelector('.btn-selecionar').addEventListener('click', abrirModalMonitor);
        
        atualizarInputMonitor();
    }
    
    function atualizarInputMonitor() {
        document.getElementById('monitor_id').value = monitorSelecionado ? monitorSelecionado.idPessoa : '';
    }
    
    // Carregar dados se estiver editando
    if (monitoriaSelecionada && monitoresMonitoriaSelecionados.length > 0) {
        adicionarMonitor(monitoresMonitoriaSelecionados[0]);
    }
    
    // Eventos
    const btnSelecionarMonitor = document.querySelector('#selecionar-monitor .btn-selecionar');
    if (btnSelecionarMonitor) {
        btnSelecionarMonitor.addEventListener('click', abrirModalMonitor);
    }
    
    // Validação do formulário
    document.getElementById('formulario').addEventListener('submit', function(e) {
        const diasSelecionados = document.querySelectorAll('input[name="dias-semana[]"]:checked');
        if (diasSelecionados.length === 0) {
            alert('Selecione pelo menos um dia da semana!');
            e.preventDefault();
            return false;
        }
    });
    
    // Função para configurar custom select
    function setupCustomSelect(selectId, hiddenInputId) {
        const customSelect = document.getElementById(selectId);
        if (!customSelect) return;
        
        const selectedDiv = customSelect.querySelector('.select-selected');
        const itemsDiv = customSelect.querySelector('.select-items');
        const hiddenInput = document.getElementById(hiddenInputId);
        
        if (!selectedDiv || !itemsDiv || !hiddenInput) return;
        
        selectedDiv.addEventListener('click', function(e) {
            e.stopPropagation();
            document.querySelectorAll('.custom-select.open').forEach(other => {
                if (other !== customSelect) other.classList.remove('open');
            });
            customSelect.classList.toggle('open');
        });
        
        itemsDiv.querySelectorAll('div').forEach(item => {
            item.addEventListener('click', function(e) {
                e.stopPropagation();
                const value = this.getAttribute('data-value');
                const text = this.textContent;
                
                selectedDiv.textContent = text;
                hiddenInput.value = value;
                
                customSelect.classList.remove('open');
            });
        });
    }
    
    // Fechar dropdowns ao clicar fora
    document.addEventListener('click', function() {
        document.querySelectorAll('.custom-select.open').forEach(select => {
            select.classList.remove('open');
        });
    });
});
// Variável para armazenar o filtro ativo
let filtroAtivo = 'todos';

// Função para aplicar filtros
function aplicarFiltro(categoria) {
    console.log('Aplicando filtro:', categoria);
    filtroAtivo = categoria;
    const cards = document.querySelectorAll('.project-card');
    const searchInput = document.querySelector('.input-pesquisar');
    const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
    
    cards.forEach(card => {
        const cardCategoria = card.getAttribute('data-categoria');
        const cardLabel = card.querySelector('.project-label').textContent.toLowerCase();
        
        // Verifica se o card corresponde ao filtro de categoria
        const matchCategoria = (categoria === 'todos') || (cardCategoria === categoria);
        
        // Verifica se o card corresponde à pesquisa
        const matchPesquisa = !searchTerm || cardLabel.includes(searchTerm);
        
        // Mostra o card apenas se corresponder a ambos os critérios
        if (matchCategoria && matchPesquisa) {
            card.style.display = 'block';
            // Animação suave ao aparecer
            card.style.animation = 'fadeIn 0.3s ease';
        } else {
            card.style.display = 'none';
        }
    });
    
    // Atualiza estado visual dos botões
    atualizarBotoesFiltro(categoria);
}

// Função para atualizar o estado visual dos botões de filtro
function atualizarBotoesFiltro(categoriaAtiva) {
    console.log('Atualizando botões para categoria:', categoriaAtiva);
    const botoes = document.querySelectorAll('.btn-filtrar');
    
    botoes.forEach(botao => {
        // Remove classe ativo de todos
        botao.classList.remove('ativo');
        
        // Adiciona classe ativo apenas ao botão correspondente
        if (categoriaAtiva === 'tecnico' && botao.classList.contains('tecnico')) {
            botao.classList.add('ativo');
            console.log('Botão técnico ativado:', botao.classList.toString());
        } else if (categoriaAtiva === 'geral' && botao.classList.contains('geral')) {
            botao.classList.add('ativo');
            console.log('Botão geral ativado:', botao.classList.toString());
        } else if (categoriaAtiva === 'superior' && botao.classList.contains('superior')) {
            botao.classList.add('ativo');
            console.log('Botão superior ativado:', botao.classList.toString());
        }
    });
}

// Event listeners para os botões de filtro
document.addEventListener('DOMContentLoaded', function() {
    const projectCards = document.querySelectorAll('.project-card');
    const inputPesquisa = document.getElementById('input-pesquisa');
    const btnsFiltrar = document.querySelectorAll('.btn-filtrar');
    const btnLimparFiltros = document.getElementById('limpar-filtros');
    
    let filtroTipoAtivo = null;
    
    // Inicialmente esconde o botão limpar filtros
    if (btnLimparFiltros) {
        btnLimparFiltros.style.display = 'none';
    }
    
    // Adicionar clique aos cards para abrir a monitoria
    projectCards.forEach(card => {
        card.addEventListener('click', function() {
            const viewUrl = this.getAttribute('data-view-url');
            if (viewUrl) {
                window.location.href = viewUrl;
            }
        });
        
        // Adicionar efeito hover
        card.style.cursor = 'pointer';
    });
    
    // Função para atualizar visibilidade do botão limpar
    function atualizarBotaoLimpar() {
        if (btnLimparFiltros) {
            const temFiltroAtivo = filtroTipoAtivo !== null;
            const temPesquisa = inputPesquisa && inputPesquisa.value.trim() !== '';
            
            if (temFiltroAtivo || temPesquisa) {
                btnLimparFiltros.style.display = 'inline-block';
            } else {
                btnLimparFiltros.style.display = 'none';
            }
        }
    }
    
    // Função para filtrar monitorias
    function filtrarMonitorias() {
        const termoPesquisa = inputPesquisa ? inputPesquisa.value.toLowerCase() : '';
        
        projectCards.forEach(card => {
            const nomeMonitoria = card.querySelector('.project-label').textContent.toLowerCase();
            const tipoMonitoria = card.getAttribute('data-tipo');
            
            let mostrar = true;
            
            // Filtro de pesquisa por texto
            if (termoPesquisa && !nomeMonitoria.includes(termoPesquisa)) {
                mostrar = false;
            }
            
            // Filtro por tipo
            if (filtroTipoAtivo && tipoMonitoria !== filtroTipoAtivo) {
                mostrar = false;
            }
            
            card.style.display = mostrar ? 'block' : 'none';
        });
        
        // Atualiza visibilidade do botão limpar
        atualizarBotaoLimpar();
    }
    
    // Event listener para pesquisa
    if (inputPesquisa) {
        inputPesquisa.addEventListener('input', function() {
            filtrarMonitorias();
        });
    }
    
    // Event listeners para botões de filtro
    btnsFiltrar.forEach(btn => {
        btn.addEventListener('click', function() {
            const filtro = this.getAttribute('data-filtro');
            
            // Toggle do filtro
            if (filtroTipoAtivo === filtro) {
                filtroTipoAtivo = null;
                this.classList.remove('active');
            } else {
                // Remove active de todos os botões
                btnsFiltrar.forEach(b => b.classList.remove('active'));
                
                filtroTipoAtivo = filtro;
                this.classList.add('active');
            }
            
            filtrarMonitorias();
        });
    });
    
    // Event listener para limpar filtros
    if (btnLimparFiltros) {
        btnLimparFiltros.addEventListener('click', function() {
            // Limpar input de pesquisa
            if (inputPesquisa) {
                inputPesquisa.value = '';
            }
            
            // Limpar filtro de tipo
            filtroTipoAtivo = null;
            
            // Remover classe active de todos os botões
            btnsFiltrar.forEach(btn => btn.classList.remove('active'));
            
            // Mostrar todos os cards
            projectCards.forEach(card => {
                card.style.display = 'block';
            });
            
            // Esconder o botão limpar
            this.style.display = 'none';
        });
    }
});

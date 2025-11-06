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
    console.log('DOM carregado, configurando filtros...');
    
    // Filtro Área Técnica Integrada
    const btnTecnico = document.querySelector('.btn-filtrar.tecnico');
    if (btnTecnico) {
        console.log('Botão técnico encontrado');
        btnTecnico.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Clique no botão técnico, filtroAtivo atual:', filtroAtivo);
            if (filtroAtivo === 'tecnico') {
                aplicarFiltro('todos');
            } else {
                aplicarFiltro('tecnico');
            }
        });
    }
    
    // Filtro Ensino Médio
    const btnGeral = document.querySelector('.btn-filtrar.geral');
    if (btnGeral) {
        console.log('Botão geral encontrado');
        btnGeral.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Clique no botão geral, filtroAtivo atual:', filtroAtivo);
            if (filtroAtivo === 'geral') {
                aplicarFiltro('todos');
            } else {
                aplicarFiltro('geral');
            }
        });
    }
    
    // Filtro Superior
    const btnSuperior = document.querySelector('.btn-filtrar.superior');
    if (btnSuperior) {
        console.log('Botão superior encontrado');
        btnSuperior.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Clique no botão superior, filtroAtivo atual:', filtroAtivo);
            if (filtroAtivo === 'superior') {
                aplicarFiltro('todos');
            } else {
                aplicarFiltro('superior');
            }
        });
    }
    
    // Event listener para a barra de pesquisa
    const inputPesquisar = document.querySelector('.input-pesquisar');
    if (inputPesquisar) {
        inputPesquisar.addEventListener('input', function() {
            aplicarFiltro(filtroAtivo);
        });
    }
    
    // Seleciona todos os cards para navegação
    document.querySelectorAll(".project-card").forEach(card => {
        card.addEventListener("click", () => {
            const label = card.querySelector(".project-label").innerText.trim();

            switch (label) {
                case "Administração":
                    window.location.href = "monitor.php";
                    break;
                case "Informática":
                    window.location.href = "monitor.php";
                    break;
                case "Vestuário":
                    window.location.href = "monitor.php";
                    break;
                case "Moda":
                    window.location.href = "monitor.php";
                    break;
                case "Gestão Comercial":
                    window.location.href = "monitor.php";
                    break;
                case "Ciências Humanas":
                    window.location.href = "monitor.php";
                    break;
                case "Ciências da Natureza":
                    window.location.href = "monitor.php";
                    break;
                case "Linguagens e suas Tecnologias":
                    window.location.href = "monitor.php";
                    break;
                case "Matemática e suas Tecnologias":
                    window.location.href = "monitor.php";
                    break;
                default:
                    alert("Monitoria ainda não cadastrada!");
            }
        });
    });
});

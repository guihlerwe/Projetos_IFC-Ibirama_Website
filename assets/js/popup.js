// Gerenciador do popup de informações do usuário

let popupElement = null;
let currentOpenUserId = null;
let currentTargetElement = null;

// Criar o elemento do popup uma vez quando a página carregar
document.addEventListener('DOMContentLoaded', function() {
    createPopupElement();
});

// Função para criar o elemento do popup
function createPopupElement() {
    if (popupElement) return;
    
    popupElement = document.createElement('div');
    popupElement.className = 'user-info-popup';
    popupElement.id = 'userInfoPopup';
    document.body.appendChild(popupElement);
}

// Função para lidar com clicks nas fotos
function toggleUserInfo(idPessoa, event) {
    event.stopPropagation();
    
    // Se clicar no mesmo usuário que já está aberto, fecha
    if (currentOpenUserId === idPessoa && popupElement.classList.contains('show')) {
        closePopup();
        return;
    }
    
    currentOpenUserId = idPessoa;
    currentTargetElement = event.target;
    
    // Buscar informações do usuário
    fetch(`usuario-popup.php?idPessoa=${idPessoa}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error('Erro ao buscar informações:', data.error);
                return;
            }
            
            displayPopup(data);
            positionPopup(event.target);
        })
        .catch(error => {
            console.error('Erro na requisição:', error);
        });
}

// Função para exibir o popup com os dados
function displayPopup(userData) {
    if (!popupElement) createPopupElement();
    
    // Montar o HTML do popup com botão de fechar
    let html = `
        <button class="popup-close-btn" title="Fechar">×</button>
        <div class="popup-header">
            <div class="popup-foto">
                <img src="${userData.foto}" alt="Foto de ${userData.nome}">
            </div>
            <div class="popup-info-principal">
                <div class="popup-nome">${escapeHtml(userData.nome)} ${escapeHtml(userData.sobrenome)}</div>
                <div class="popup-email">${escapeHtml(userData.email)}</div>
                ${userData.tipo === 'coordenador' && userData.area ? 
                    `<div class="popup-curso-area"><strong>Área:</strong> ${formatarArea(userData.area)}</div>` : 
                    userData.curso ? 
                    `<div class="popup-curso-area"><strong>Curso:</strong> ${formatarCurso(userData.curso)}</div>` : 
                    ''}
            </div>
        </div>
    `;
    
    // Adicionar descrição se existir
    if (userData.descricao && userData.descricao.trim() !== '') {
        html += `
            <div class="popup-descricao">
                <div class="popup-descricao-titulo">Sobre</div>
                <div class="popup-descricao-texto">${escapeHtml(userData.descricao)}</div>
            </div>
        `;
    }
    
    popupElement.innerHTML = html;
    popupElement.classList.add('show');
    
    // Adicionar evento de click no botão de fechar
    const closeBtn = popupElement.querySelector('.popup-close-btn');
    if (closeBtn) {
        closeBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            closePopup();
        });
    }
}

// Função para posicionar o popup próximo à foto clicada
function positionPopup(targetElement) {
    if (!popupElement || !targetElement) return;
    
    // Obter posição do elemento em relação ao documento
    const rect = targetElement.getBoundingClientRect();
    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;
    
    // Posição absoluta do elemento no documento
    const targetTop = rect.top + scrollTop;
    const targetLeft = rect.left + scrollLeft;
    const targetRight = targetLeft + rect.width;
    
    const popupWidth = 400;
    const popupHeight = popupElement.offsetHeight;
    const spacing = 10;
    
    // Calcula posição inicial (à direita da foto)
    let left = targetRight + spacing;
    let top = targetTop;
    
    // Verifica se o popup sairia da tela à direita
    const viewportWidth = window.innerWidth;
    if (left + popupWidth > scrollLeft + viewportWidth) {
        // Posiciona à esquerda da foto
        left = targetLeft - popupWidth - spacing;
    }
    
    // Verifica se o popup sairia da tela à esquerda
    if (left < scrollLeft) {
        // Centraliza horizontalmente na viewport
        left = scrollLeft + (viewportWidth - popupWidth) / 2;
    }
    
    // Ajusta a posição vertical se necessário
    const viewportHeight = window.innerHeight;
    if (top - scrollTop + popupHeight > viewportHeight) {
        top = scrollTop + viewportHeight - popupHeight - 10;
    }
    
    if (top < scrollTop) {
        top = scrollTop + 10;
    }
    
    // Usa posição absoluta para o popup acompanhar a página
    popupElement.style.position = 'absolute';
    popupElement.style.left = `${left}px`;
    popupElement.style.top = `${top}px`;
    popupElement.style.transform = 'none';
}

// Função para verificar se o elemento target está visível na viewport
function isElementVisible(element) {
    if (!element) return false;
    
    const rect = element.getBoundingClientRect();
    const header = document.querySelector('header');
    const headerHeight = header ? header.offsetHeight : 0;
    
    // Considera visível se pelo menos parte do elemento está na viewport
    // (excluindo a área do header)
    return (
        rect.bottom > headerHeight &&
        rect.top < window.innerHeight &&
        rect.right > 0 &&
        rect.left < window.innerWidth
    );
}

// Função para fechar o popup
function closePopup() {
    if (popupElement) {
        popupElement.classList.remove('show');
        popupElement.style.visibility = 'visible'; // Garante que volta a visível
        currentOpenUserId = null;
        currentTargetElement = null;
    }
}

// Listener para reposicionar o popup ao rolar a página
window.addEventListener('scroll', function() {
    if (popupElement && popupElement.classList.contains('show') && currentTargetElement) {
        // Verifica se o elemento alvo ainda está visível
        if (!isElementVisible(currentTargetElement)) {
            // Se não estiver visível, esconde o popup temporariamente usando visibility
            popupElement.style.visibility = 'hidden';
        } else {
            // Se estiver visível, mostra o popup e reposiciona
            popupElement.style.visibility = 'visible';
            positionPopup(currentTargetElement);
        }
    }
});

// Listener para reposicionar o popup ao redimensionar a janela
window.addEventListener('resize', function() {
    if (popupElement && popupElement.classList.contains('show') && currentTargetElement) {
        positionPopup(currentTargetElement);
    }
});

// Função auxiliar para formatar curso
function formatarCurso(curso) {
    const mapa = {
        'administracao': 'Administração',
        'informatica': 'Informática',
        'vestuario': 'Vestuário',
        'moda': 'Moda',
        'gestao comercial': 'Gestão Comercial',
        'gestaocomercial': 'Gestão Comercial'
    };
    
    const cursoLower = curso.toLowerCase().trim();
    return mapa[cursoLower] || capitalizeFirst(curso);
}

// Função auxiliar para formatar área
function formatarArea(area) {
    const mapa = {
        'ciencias naturais': 'Ciências Naturais',
        'ciencias humanas': 'Ciências Humanas',
        'linguagens': 'Linguagens',
        'matematica': 'Matemática',
        'administracao': 'Administração',
        'informatica': 'Informática',
        'vestuario': 'Vestuário',
        'tecnico administrativo': 'Técnico Administrativo'
    };
    
    const areaLower = area.toLowerCase().trim();
    return mapa[areaLower] || capitalizeFirst(area);
}

// Função para capitalizar primeira letra
function capitalizeFirst(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

// Função para escapar HTML e prevenir XSS
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML.replace(/\n/g, '<br>');
}

// Fechar popup ao clicar fora dele
document.addEventListener('click', function(event) {
    if (popupElement && 
        popupElement.classList.contains('show') && 
        !popupElement.contains(event.target) &&
        !event.target.classList.contains('foto-perfil') &&
        !event.target.closest('.foto-membro')) {
        closePopup();
    }
});

// Fechar popup ao pressionar ESC
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape' && popupElement && popupElement.classList.contains('show')) {
        closePopup();
    }
});
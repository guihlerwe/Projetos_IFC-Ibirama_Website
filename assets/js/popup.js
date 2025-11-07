// Gerenciador do popup de informações do usuário

let popupElement = null;
let hideTimeout = null;
let currentHoveredElement = null;

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
    
    // Eventos para manter o popup visível quando o mouse estiver sobre ele
    popupElement.addEventListener('mouseenter', function() {
        clearTimeout(hideTimeout);
    });
    
    popupElement.addEventListener('mouseleave', function() {
        hidePopup();
    });
}

// Função principal chamada no onmouseover das fotos
function showUserInfo(idPessoa) {
    clearTimeout(hideTimeout);
    
    // Buscar informações do usuário
    fetch(`usuario-popup.php?idPessoa=${idPessoa}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error('Erro ao buscar informações:', data.error);
                return;
            }
            
            displayPopup(data);
            positionPopup(event);
        })
        .catch(error => {
            console.error('Erro na requisição:', error);
        });
}

// Função para exibir o popup com os dados
function displayPopup(userData) {
    if (!popupElement) createPopupElement();
    
    // Montar o HTML do popup
    let html = `
        <div class="popup-header">
            <div class="popup-foto">
                <img src="${userData.foto}" alt="Foto de ${userData.nome}">
            </div>
            <div class="popup-info-principal">
                <div class="popup-nome">${userData.nome} ${userData.sobrenome}</div>
                <div class="popup-email">${userData.email}</div>
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
}

// Função para posicionar o popup próximo ao cursor
function positionPopup(event) {
    if (!popupElement || !event) return;
    
    const mouseX = event.clientX || event.pageX;
    const mouseY = event.clientY || event.pageY;
    
    const offset = 15;
    let left = mouseX + offset;
    let top = mouseY + offset;
    
    // Garantir que o popup não saia da tela
    const popupRect = popupElement.getBoundingClientRect();
    const viewportWidth = window.innerWidth;
    const viewportHeight = window.innerHeight;
    
    // Ajustar horizontalmente
    if (left + popupRect.width > viewportWidth) {
        left = mouseX - popupRect.width - offset;
    }
    
    // Ajustar verticalmente
    if (top + popupRect.height > viewportHeight) {
        top = mouseY - popupRect.height - offset;
    }
    
    // Garantir que não fique fora da tela na esquerda/topo
    left = Math.max(10, left);
    top = Math.max(10, top);
    
    popupElement.style.left = `${left}px`;
    popupElement.style.top = `${top}px`;
}

// Função para esconder o popup
function hidePopup() {
    hideTimeout = setTimeout(() => {
        if (popupElement) {
            popupElement.classList.remove('show');
        }
    }, 200); // Delay de 200ms antes de esconder
}

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

// Adicionar evento de mouseleave nas fotos para esconder o popup
document.addEventListener('DOMContentLoaded', function() {
    // Adicionar eventos em todas as fotos de perfil
    document.querySelectorAll('.foto-perfil').forEach(foto => {
        foto.addEventListener('mouseleave', function() {
            hidePopup();
        });
    });
});

document.addEventListener('mousemove', function(event) {
    if (popupElement && popupElement.classList.contains('show')) {
        positionPopup(event);
    }
});
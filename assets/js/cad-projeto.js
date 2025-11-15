console.log('🚀 Iniciando cadProjeto.js...');

// ================================
// VARIÁVEIS GLOBAIS
// ================================
let coordenadoresSelecionados = [];
let bolsistasSelecionados = [];
let voluntariosSelecionados = [];

// ================================
// INICIALIZAÇÃO
// ================================
document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ DOM carregado - cad-projeto.js inicializando...');
    
    // NÃO configurar custom selects aqui - já está sendo feito no HTML inline
    // NÃO configurar botões de adicionar - já está sendo feito no HTML inline
    
    // Apenas configurar event listeners de imagem e validação
    inicializarEventListenersImagens();
});

// ================================
// INICIALIZAR EVENT LISTENERS DE IMAGENS
// ================================
function inicializarEventListenersImagens() {
    // Preview do banner
    const bannerInput = document.getElementById('banner-projeto');
    if (bannerInput) {
        bannerInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            const bannerPreview = document.getElementById('banner-preview');
            const bannerText = document.getElementById('banner-text');
            
            if (file) {
                const tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                if (!tiposPermitidos.includes(file.type)) {
                    alert('Por favor, selecione apenas arquivos de imagem (JPG, PNG, GIF, WebP)');
                    this.value = '';
                    return;
                }
                
                if (file.size > 5 * 1024 * 1024) {
                    alert('A imagem deve ter no máximo 5MB');
                    this.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    if (bannerPreview) {
                        bannerPreview.src = e.target.result;
                        bannerPreview.style.display = 'block';
                    }
                    if (bannerText) {
                        bannerText.style.display = 'none';
                    }
                }
                reader.readAsDataURL(file);
            }
        });
    }

    // Preview da capa
    const capaInput = document.getElementById('foto-capa');
    if (capaInput) {
        capaInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            const capaPreview = document.getElementById('capa-preview');
            const capaIcon = document.getElementById('capa-icon');
            
            if (file) {
                const tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                if (!tiposPermitidos.includes(file.type)) {
                    alert('Por favor, selecione apenas arquivos de imagem (JPG, PNG, GIF, WebP)');
                    this.value = '';
                    return;
                }
                
                if (file.size > 2 * 1024 * 1024) {
                    alert('A imagem da capa deve ter no máximo 2MB');
                    this.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    if (capaPreview) {
                        capaPreview.src = e.target.result;
                        capaPreview.style.display = 'block';
                    }
                    if (capaIcon) {
                        capaIcon.style.display = 'none';
                    }
                }
                reader.readAsDataURL(file);
            }
        });
    }

    console.log('✅ Event listeners de imagens configurados');
}

console.log('✅ cadProjeto.js carregado com sucesso!');
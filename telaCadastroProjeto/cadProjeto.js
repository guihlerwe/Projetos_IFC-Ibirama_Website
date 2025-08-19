console.log('Iniciando cadProjeto.js...');

// Aguardar carregamento completo da página
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM carregado!');
    
    // Navegação
    const loginBtn = document.querySelector(".login-nav");
    if (loginBtn) {
        loginBtn.addEventListener("click", function () {
            window.location.href = "../telaLogin/login.html";
        });
    }

    const projetosNav = document.querySelector("#projetos-nav");
    if (projetosNav) {
        projetosNav.addEventListener("click", function() {
            window.location.href = "../telaPrincipal/principal.php";
        });
    }

    const monitoriaNav = document.querySelector("#monitoria-nav");
    if (monitoriaNav) {
        monitoriaNav.addEventListener("click", function() {
            window.location.href = "../telaMonitorias/telaMonitorias.php";
        });
    }

    const sobreNav = document.querySelector("#sobre-nav");
    if (sobreNav) {
        sobreNav.addEventListener("click", function() {
            window.location.href = "../telaSobre/sobre.php";
        });
    }

    // PREVIEW DO BANNER
    const bannerInput = document.getElementById('banner-projeto');
    if (bannerInput) {
        bannerInput.addEventListener('change', function(e) {
            console.log('Banner selecionado');
            const file = e.target.files[0];
            const bannerPreview = document.getElementById('banner-preview');
            const bannerText = document.getElementById('banner-text');
            
            if (file) {
                // Validar tipo de arquivo
                const tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                if (!tiposPermitidos.includes(file.type)) {
                    alert('Por favor, selecione apenas arquivos de imagem (JPG, PNG, GIF, WebP)');
                    this.value = '';
                    return;
                }
                
                // Validar tamanho (5MB max)
                if (file.size > 5 * 1024 * 1024) {
                    alert('A imagem deve ter no máximo 5MB');
                    this.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    if (bannerPreview) {
                        bannerPreview.src = e.target.result;
                        bannerPreview.style.cssText = `
                            display: block !important;
                            width: 100% !important;
                            height: 100% !important;
                            object-fit: cover !important;
                            position: absolute !important;
                            top: 0 !important;
                            left: 0 !important;
                            z-index: 5 !important;
                        `;
                    }
                    if (bannerText) {
                        bannerText.style.display = 'none';
                    }
                }
                reader.readAsDataURL(file);
            } else {
                if (bannerPreview) {
                    bannerPreview.style.display = 'none';
                }
                if (bannerText) {
                    bannerText.style.display = 'block';
                }
            }
        });
    }

    // PREVIEW DA CAPA
    const capaInput = document.getElementById('foto-capa');
    if (capaInput) {
        capaInput.addEventListener('change', function(e) {
            console.log('Capa selecionada');
            const file = e.target.files[0];
            const capaPreview = document.getElementById('capa-preview');
            const capaIcon = document.getElementById('capa-icon');
            
            if (file) {
                // Validar tipo de arquivo
                const tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                if (!tiposPermitidos.includes(file.type)) {
                    alert('Por favor, selecione apenas arquivos de imagem (JPG, PNG, GIF, WebP)');
                    this.value = '';
                    return;
                }
                
                // Validar tamanho (2MB max para capa)
                if (file.size > 2 * 1024 * 1024) {
                    alert('A imagem da capa deve ter no máximo 2MB');
                    this.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    if (capaPreview) {
                        capaPreview.src = e.target.result;
                        capaPreview.style.cssText = `
                            display: block !important;
                            width: 100%;
                            height: 100%;
                            object-fit: cover;
                        `;
                    }
                    if (capaIcon) {
                        capaIcon.style.display = 'none';
                    }
                }
                reader.readAsDataURL(file);
            } else {
                if (capaPreview) {
                    capaPreview.style.display = 'none';
                }
                if (capaIcon) {
                    capaIcon.style.display = 'block';
                }
            }
        });
    }

    // PREVIEW PARA FOTO DO COORDENADOR - MANTENDO COMPORTAMENTO ORIGINAL
    const coordenadorInput = document.querySelector('input[name="foto-coordenador"]');
    if (coordenadorInput) {
        coordenadorInput.addEventListener('change', function(e) {
            console.log('Foto coordenador selecionada');
            const file = e.target.files[0];
            const label = this.parentElement;
            
            if (file) {
                // Validar arquivo
                const tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                if (!tiposPermitidos.includes(file.type)) {
                    alert('Por favor, selecione apenas arquivos de imagem para a foto do coordenador');
                    this.value = '';
                    return;
                }
                
                if (file.size > 1 * 1024 * 1024) {
                    alert('A foto do coordenador deve ter no máximo 1MB');
                    this.value = '';
                    return;
                }
                
                // Remover preview anterior
                const previewExistente = label.querySelector('.preview-coordenador');
                if (previewExistente) {
                    previewExistente.remove();
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    const imgPreview = document.createElement('img');
                    imgPreview.src = e.target.result;
                    imgPreview.className = 'preview-coordenador';
                    imgPreview.style.cssText = `
                        width: 100%;
                        height: 100%;
                        object-fit: cover;
                        border-radius: 50%;
                    `;
                    
                    // Esconder o ícone
                    const icone = label.querySelector('span');
                    if (icone) {
                        icone.style.display = 'none';
                    }
                    
                    label.appendChild(imgPreview);
                }
                reader.readAsDataURL(file);
            } else {
                // Remover preview
                const preview = label.querySelector('.preview-coordenador');
                if (preview) {
                    preview.remove();
                }
                const icone = label.querySelector('span');
                if (icone) {
                    icone.style.display = 'block';
                }
            }
        });
    }

    // PREVIEW PARA FOTO DO BOLSISTA - MANTENDO COMPORTAMENTO ORIGINAL
    const bolsistaInput = document.querySelector('input[name="foto-bolsista"]');
    if (bolsistaInput) {
        bolsistaInput.addEventListener('change', function(e) {
            console.log('Foto bolsista selecionada');
            const file = e.target.files[0];
            const label = this.parentElement;
            
            if (file) {
                // Validar arquivo
                const tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                if (!tiposPermitidos.includes(file.type)) {
                    alert('Por favor, selecione apenas arquivos de imagem para a foto do bolsista');
                    this.value = '';
                    return;
                }
                
                if (file.size > 1 * 1024 * 1024) {
                    alert('A foto do bolsista deve ter no máximo 1MB');
                    this.value = '';
                    return;
                }
                
                // Remover preview anterior
                const previewExistente = label.querySelector('.preview-bolsista');
                if (previewExistente) {
                    previewExistente.remove();
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    const imgPreview = document.createElement('img');
                    imgPreview.src = e.target.result;
                    imgPreview.className = 'preview-bolsista';
                    imgPreview.style.cssText = `
                        width: 100%;
                        height: 100%;
                        object-fit: cover;
                        border-radius: 50%;
                    `;
                    
                    // Esconder o ícone
                    const icone = label.querySelector('span');
                    if (icone) {
                        icone.style.display = 'none';
                    }
                    
                    label.appendChild(imgPreview);
                }
                reader.readAsDataURL(file);
            } else {
                // Remover preview
                const preview = label.querySelector('.preview-bolsista');
                if (preview) {
                    preview.remove();
                }
                const icone = label.querySelector('span');
                if (icone) {
                    icone.style.display = 'block';
                }
            }
        });
    }

    // CONTADOR DE CARACTERES PARA DESCRIÇÃO
    const descricaoTextarea = document.getElementById('descricao');
    if (descricaoTextarea) {
        const contador = document.createElement('div');
        contador.id = 'contador-chars';
        contador.style.cssText = `
            text-align: right;
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        `;
        contador.textContent = '0 / 2000';
        
        descricaoTextarea.parentNode.insertBefore(contador, descricaoTextarea.nextSibling);
        
        descricaoTextarea.addEventListener('input', function() {
            const atual = this.value.length;
            contador.textContent = `${atual} / 2000`;
            
            if (atual > 1900) {
                contador.style.color = '#ff6b6b';
            } else {
                contador.style.color = '#666';
            }
        });
    }

    // VALIDAÇÃO DO FORMULÁRIO
    const formulario = document.getElementById('formulario');
    if (formulario) {
        formulario.addEventListener('submit', function(e) {
            console.log('Formulário sendo enviado...');
            
            const nomeProjeto = document.getElementById('nome-projeto').value.trim();
            const eixo = document.getElementById('eixo').value;
            const categoria = document.getElementById('categoria').value;
            const capa = document.getElementById('foto-capa').files[0];
            
            // Validações obrigatórias
            if (!nomeProjeto) {
                alert('Nome do projeto é obrigatório!');
                e.preventDefault();
                return false;
            }
            
            if (!eixo) {
                alert('Tipo de projeto é obrigatório!');
                e.preventDefault();
                return false;
            }
            
            if (!categoria) {
                alert('Categoria é obrigatória!');
                e.preventDefault();
                return false;
            }
            
            if (!capa) {
                alert('Imagem de capa é obrigatória!');
                e.preventDefault();
                return false;
            }
            
            console.log('Validações OK, enviando formulário...');
            
            // Mostrar indicador de carregamento
            const botaoSubmit = document.getElementById('bt-criar-projeto');
            if (botaoSubmit) {
                botaoSubmit.textContent = 'Criando projeto...';
                botaoSubmit.disabled = true;
            }
            
            return true;
        });
    }

    // VALIDAÇÃO DE URLs
    const siteInput = document.getElementById('site-projeto');
    if (siteInput) {
        siteInput.addEventListener('blur', function() {
            if (this.value && !this.value.match(/^https?:\/\/.+/)) {
                alert('Link do site deve começar com http:// ou https://');
                this.focus();
            }
        });
    }

    const linkBolsistaInput = document.getElementById('link-bolsista');
    if (linkBolsistaInput) {
        linkBolsistaInput.addEventListener('blur', function() {
            if (this.value && !this.value.match(/^https?:\/\/.+/)) {
                alert('Link para bolsista deve começar com http:// ou https://');
                this.focus();
            }
        });
    }

    const linkInscricaoInput = document.getElementById('txt-link-inscricao');
    if (linkInscricaoInput) {
        linkInscricaoInput.addEventListener('blur', function() {
            if (this.value && !this.value.match(/^https?:\/\/.+/)) {
                alert('Link de inscrição deve começar com http:// ou https://');
                this.focus();
            }
        });
    }

    console.log('Todos os event listeners configurados!');
});

console.log('cadProjeto.js carregado com sucesso!');
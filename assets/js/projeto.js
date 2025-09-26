console.log('Iniciando visualizarProjeto.js...');

// Esperando carregar página completa
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM carregado!');
    
    // Navegação - links para outras telas
    const loginBtn = document.querySelector("#login-nav");
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

    // Adiciona sombra ao header quando scrolla
    const header = document.querySelector('header');
    if (header) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 10) {
                header.classList.add('com-sombra');
            } else {
                header.classList.remove('com-sombra');
            }
        });
    }

    // Smooth scroll para links internos (se houver)
    const linksInternos = document.querySelectorAll('a[href^="#"]');
    linksInternos.forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Animação de entrada para os elementos da página
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Aplica animação para seções
    const secoes = document.querySelectorAll('.secao');
    secoes.forEach(function(secao) {
        secao.style.opacity = '0';
        secao.style.transform = 'translateY(20px)';
        secao.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(secao);
    });

    // Animação para info-projeto
    const infoProjeto = document.querySelector('#info-projeto');
    if (infoProjeto) {
        infoProjeto.style.opacity = '0';
        infoProjeto.style.transform = 'translateY(30px)';
        infoProjeto.style.transition = 'opacity 0.8s ease, transform 0.8s ease';
        
        setTimeout(function() {
            infoProjeto.style.opacity = '1';
            infoProjeto.style.transform = 'translateY(0)';
        }, 200);
    }

    // Adiciona efeito hover suave nos botões de link
    const botoesLink = document.querySelectorAll('.btn-link');
    botoesLink.forEach(function(botao) {
        botao.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px) scale(1.02)';
        });
        
        botao.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });

    // Efeito hover para itens de contato
    const itensContato = document.querySelectorAll('.contato-item');
    itensContato.forEach(function(item) {
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateX(5px)';
            this.style.transition = 'all 0.3s ease';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.transform = 'translateX(0)';
        });
    });

    // Validação e formatação de links externos
    const linksExternos = document.querySelectorAll('a[target="_blank"]');
    linksExternos.forEach(function(link) {
        // Adiciona um ícone visual para links externos
        link.addEventListener('mouseenter', function() {
            if (!this.querySelector('.icone-externo')) {
                const icone = document.createElement('span');
                icone.className = 'icone-externo';
                icone.innerHTML = ' ↗';
                icone.style.fontSize = '12px';
                icone.style.opacity = '0.7';
                this.appendChild(icone);
            }
        });
    });

    // Tratamento de erros para imagens
    const imagens = document.querySelectorAll('img');
    imagens.forEach(function(img) {
        img.addEventListener('error', function() {
            console.log('Erro ao carregar imagem:', this.src);
            
            // Se for uma foto de pessoa, substitui por um ícone padrão
            if (this.closest('.foto-membro')) {
                this.style.display = 'none';
                const container = this.parentElement;
                if (!container.querySelector('.icone-pessoa-erro')) {
                    const icone = document.createElement('span');
                    icone.className = 'icone-pessoa-erro';
                    icone.innerHTML = '👤';
                    icone.style.fontSize = '24px';
                    icone.style.color = '#999';
                    container.appendChild(icone);
                }
            }
            
            // Se for banner ou capa, substitui por placeholder
            else if (this.id === 'banner-img' || this.id === 'capa-img') {
                this.style.display = 'none';
                const container = this.parentElement;
                if (!container.querySelector('.erro-imagem')) {
                    const placeholder = document.createElement('div');
                    placeholder.className = 'erro-imagem';
                    placeholder.innerHTML = '🖼️ Imagem não encontrada';
                    placeholder.style.cssText = `
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        color: #999;
                        font-size: 14px;
                        width: 100%;
                        height: 100%;
                    `;
                    container.appendChild(placeholder);
                }
            }
        });
    });

    // Log para debug
    console.log('Projeto carregado:', document.title);
    console.log('Seções encontradas:', secoes.length);
    console.log('Links externos:', linksExternos.length);
    
    console.log('Todos os event listeners configurados para visualização!');
});

    // Função para copiar link do projeto (pode ser útil para compartilhamento)
    function copiarLinkProjeto() {
        const url = window.location.href;
        if (navigator.clipboard) {
            navigator.clipboard.writeText(url).then(function() {
                console.log('Link copiado:', url);
                // Pode adicionar uma notificação visual aqui se desejar
            }).catch(function(err) {
                console.error('Erro ao copiar link:', err);
            });
        }
    }

    // Adiciona função global para compartilhamento (se necessário)
    window.copiarLinkProjeto = copiarLinkProjeto;

    // Easter egg: Log do nome do projeto no console
    const nomeProjeto = document.querySelector('#nome-projeto');
    if (nomeProjeto) {
        console.log(`📚 Visualizando projeto: "${nomeProjeto.textContent}"`);
    }

    // Otimização: Lazy loading para imagens (se necessário no futuro)
    function implementarLazyLoading() {
        const imagensLazy = document.querySelectorAll('img[data-src]');
        const imageObserver = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    imageObserver.unobserve(img);
                }
            });
        });

        imagensLazy.forEach(function(img) {
            imageObserver.observe(img);
        });
    }

    // Chama lazy loading se houver imagens configuradas para isso
    implementarLazyLoading();

console.log('visualizarProjeto.js carregado com sucesso!');
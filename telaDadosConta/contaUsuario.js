// Variáveis globais
let modoEdicao = false;
let dadosOriginais = {};

// Elementos do DOM
const formulario = document.getElementById('formulario-perfil');
const inputFoto = document.getElementById('input-foto');
const fotoPerfil = document.getElementById('foto-perfil');
const placeholderFoto = document.getElementById('placeholder-foto');
const botaoAlterarFoto = document.getElementById('botao-alterar-foto');
const botaoRemoverFoto = document.getElementById('botao-remover-foto');
const botaoEditar = document.getElementById('botao-editar');
const botaoSalvar = document.getElementById('botao-salvar');
const botaoCancelar = document.getElementById('botao-cancelar');
const botaoExcluirConta = document.getElementById('botao-excluir-conta');
const modal = document.getElementById('modal-confirmacao');
const header = document.querySelector("header");
const descricaoTextarea = document.getElementById('descricao-perfil');
const contadorChars = document.getElementById('contador-chars');

// Inicialização quando a página carrega
document.addEventListener('DOMContentLoaded', function() {
    carregarDadosUsuario();
    inicializarEventListeners();
    atualizarContadorCaracteres();
});

// Carregar dados do usuário do servidor
async function carregarDadosUsuario() {
    try {
        mostrarLoading(true);
        const response = await fetch('contaBD.php', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        const dados = await response.json();
        
        if (dados.erro) {
            mostrarMensagem(dados.erro, 'erro');
            return;
        }
        
        // Preencher campos
        document.getElementById('nome-perfil').value = dados.nome || '';
        document.getElementById('sobrenome-perfil').value = dados.sobrenome || '';
        document.getElementById('email-perfil').value = dados.email || '';
        document.getElementById('descricao-perfil').value = dados.descricao || '';
        
        // Atualizar contador de caracteres
        atualizarContadorCaracteres();
        
        // Carregar foto se existir
        if (dados.foto_perfil) {
            mostrarFoto(dados.foto_perfil);
        }
        
    } catch (error) {
        console.error('Erro ao carregar dados:', error);
        mostrarMensagem('Erro ao carregar dados do usuário', 'erro');
    } finally {
        mostrarLoading(false);
    }
}

// Mostrar foto
function mostrarFoto(srcFoto) {
    fotoPerfil.src = srcFoto;
    fotoPerfil.style.display = 'block';
    placeholderFoto.style.display = 'none';
    botaoRemoverFoto.style.display = 'inline-block';
}

// Esconder foto
function esconderFoto() {
    fotoPerfil.style.display = 'none';
    placeholderFoto.style.display = 'flex';
    botaoRemoverFoto.style.display = 'none';
    fotoPerfil.src = '';
}

// Atualizar contador de caracteres
function atualizarContadorCaracteres() {
    const texto = descricaoTextarea.value;
    const contador = texto.length;
    contadorChars.textContent = contador;
    
    const contadorDiv = document.querySelector('.contador-caracteres');
    contadorDiv.classList.remove('limite-proximo', 'limite-excedido');
    
    if (contador > 1000) {
        contadorDiv.classList.add('limite-excedido');
    } else if (contador > 800) {
        contadorDiv.classList.add('limite-proximo');
    }
}

// Alternar modo de edição
function alternarModoEdicao(editando) {
    modoEdicao = editando;
    const campos = document.querySelectorAll('.campo-perfil');

    if (editando) {
        // Salvar dados originais para restaurar se cancelar
        salvarDadosOriginais();
        
        // Habilitar edição
        campos.forEach(campo => {
            if (campo.id !== 'senha-perfil') {
                campo.removeAttribute('readonly');
                campo.classList.add('editavel');
            }
        });
        
        // Habilitar edição da descrição
        descricaoTextarea.removeAttribute('readonly');
        descricaoTextarea.classList.add('editavel');
        
        // Mostrar/esconder botões
        botaoEditar.style.display = 'none';
        botaoSalvar.style.display = 'inline-block';
        botaoCancelar.style.display = 'inline-block';
        botaoExcluirConta.style.display = 'none';
        
    } else {
        // Desabilitar edição
        campos.forEach(campo => {
            campo.setAttribute('readonly', true);
            campo.classList.remove('editavel');
        });
        
        // Desabilitar edição da descrição
        descricaoTextarea.setAttribute('readonly', true);
        descricaoTextarea.classList.remove('editavel');
        
        // Mostrar/esconder botões
        botaoEditar.style.display = 'inline-block';
        botaoSalvar.style.display = 'none';
        botaoCancelar.style.display = 'none';
        botaoExcluirConta.style.display = 'inline-block';
    }
}

// Salvar dados originais para poder cancelar
function salvarDadosOriginais() {
    dadosOriginais = {
        nome: document.getElementById('nome-perfil').value,
        sobrenome: document.getElementById('sobrenome-perfil').value,
        email: document.getElementById('email-perfil').value,
        descricao: document.getElementById('descricao-perfil').value
    };
}

// Restaurar dados originais
function restaurarDadosOriginais() {
    document.getElementById('nome-perfil').value = dadosOriginais.nome;
    document.getElementById('sobrenome-perfil').value = dadosOriginais.sobrenome;
    document.getElementById('email-perfil').value = dadosOriginais.email;
    document.getElementById('descricao-perfil').value = dadosOriginais.descricao;
    atualizarContadorCaracteres();
}

// Salvar alterações
async function salvarAlteracoes() {
    try {
        // Validar campos
        const nome = document.getElementById('nome-perfil').value.trim();
        const sobrenome = document.getElementById('sobrenome-perfil').value.trim();
        const email = document.getElementById('email-perfil').value.trim();
        const descricao = document.getElementById('descricao-perfil').value.trim();
        
        if (!nome || !sobrenome || !email) {
            mostrarMensagem('Todos os campos obrigatórios devem ser preenchidos', 'erro');
            return;
        }
        
        if (!validarEmail(email)) {
            mostrarMensagem('Email inválido', 'erro');
            return;
        }
        
        if (descricao.length > 1000) {
            mostrarMensagem('A descrição não pode ter mais de 1000 caracteres', 'erro');
            return;
        }
        
        mostrarLoading(true);
        
        // Preparar dados para envio
        const formData = new FormData();
        formData.append('acao', 'atualizar_perfil');
        formData.append('nome', nome);
        formData.append('sobrenome', sobrenome);
        formData.append('email', email);
        formData.append('descricao', descricao);
        
        const response = await fetch('contaBD.php', {
            method: 'POST',
            body: formData
        });
        
        const resultado = await response.json();
        
        if (resultado.sucesso) {
            mostrarMensagem(resultado.sucesso, 'sucesso');
            alternarModoEdicao(false);
        } else {
            mostrarMensagem(resultado.erro || 'Erro ao salvar alterações', 'erro');
        }
        
    } catch (error) {
        console.error('Erro ao salvar:', error);
        mostrarMensagem('Erro ao salvar alterações', 'erro');
    } finally {
        mostrarLoading(false);
    }
}

// Upload de foto
async function uploadFoto(arquivo) {
    try {
        mostrarLoading(true);
        
        const formData = new FormData();
        formData.append('acao', 'upload_foto');
        formData.append('foto', arquivo);
        
        const response = await fetch('contaBD.php', {
            method: 'POST',
            body: formData
        });
        
        const resultado = await response.json();
        
        if (resultado.sucesso) {
            mostrarMensagem(resultado.sucesso, 'sucesso');
            if (resultado.caminho_foto) {
                mostrarFoto(resultado.caminho_foto);
            }
        } else {
            mostrarMensagem(resultado.erro || 'Erro ao fazer upload da foto', 'erro');
        }
        
    } catch (error) {
        console.error('Erro no upload:', error);
        mostrarMensagem('Erro ao fazer upload da foto', 'erro');
    } finally {
        mostrarLoading(false);
    }
}

// Remover foto
async function removerFoto() {
    try {
        mostrarLoading(true);
        
        const formData = new FormData();
        formData.append('acao', 'remover_foto');
        
        const response = await fetch('contaBD.php', {
            method: 'POST',
            body: formData
        });
        
        const resultado = await response.json();
        
        if (resultado.sucesso) {
            mostrarMensagem(resultado.sucesso, 'sucesso');
            esconderFoto();
            inputFoto.value = '';
        } else {
            mostrarMensagem(resultado.erro || 'Erro ao remover foto', 'erro');
        }
        
    } catch (error) {
        console.error('Erro ao remover foto:', error);
        mostrarMensagem('Erro ao remover foto', 'erro');
    } finally {
        mostrarLoading(false);
    }
}

// Excluir conta
async function excluirConta() {
    try {
        mostrarLoading(true);
        
        const formData = new FormData();
        formData.append('acao', 'excluir_conta');
        
        const response = await fetch('contaBD.php', {
            method: 'POST',
            body: formData
        });
        
        const resultado = await response.json();
        
        if (resultado.sucesso) {
            mostrarMensagem(resultado.sucesso, 'sucesso');
            setTimeout(() => {
                window.location.href = '../telaPrincipal/principal.php';
            }, 2000);
        } else {
            mostrarMensagem(resultado.erro || 'Erro ao excluir conta', 'erro');
        }
        
    } catch (error) {
        console.error('Erro ao excluir conta:', error);
        mostrarMensagem('Erro ao excluir conta', 'erro');
    } finally {
        mostrarLoading(false);
    }
}

// Mostrar/esconder loading
function mostrarLoading(mostrar) {
    const container = document.querySelector('.container-perfil');
    if (mostrar) {
        container.classList.add('loading');
    } else {
        container.classList.remove('loading');
    }
}

// Mostrar mensagem de feedback
function mostrarMensagem(mensagem, tipo) {
    // Remover mensagens existentes
    const mensagensExistentes = document.querySelectorAll('.mensagem-sucesso, .mensagem-erro');
    mensagensExistentes.forEach(msg => msg.remove());
    
    // Criar nova mensagem
    const divMensagem = document.createElement('div');
    divMensagem.className = tipo === 'sucesso' ? 'mensagem-sucesso' : 'mensagem-erro';
    divMensagem.textContent = mensagem;
    divMensagem.style.cssText = `
        padding: 12px 20px;
        margin-bottom: 20px;
        border-radius: 8px;
        font-weight: bold;
        text-align: center;
        ${tipo === 'sucesso' ? 
            'background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb;' : 
            'background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;'
        }
    `;
    
    // Inserir no início do container
    const container = document.querySelector('.conteudo-perfil');
    container.insertBefore(divMensagem, container.firstChild);
    
    // Remover após 5 segundos
    setTimeout(() => {
        divMensagem.remove();
    }, 5000);
}

// Validar email
function validarEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

// Validar arquivo de imagem
function validarArquivoImagem(arquivo) {
    const tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    const tamanhoMaximo = 5 * 1024 * 1024; // 5MB
    
    if (!tiposPermitidos.includes(arquivo.type)) {
        mostrarMensagem('Tipo de arquivo não permitido. Use JPG, PNG, GIF ou WebP', 'erro');
        return false;
    }
    
    if (arquivo.size > tamanhoMaximo) {
        mostrarMensagem('Arquivo muito grande. Máximo 5MB', 'erro');
        return false;
    }
    
    return true;
}

// Inicializar event listeners
function inicializarEventListeners() {
    // contador de caracteres da descrição
        const descricao = document.getElementById("descricao");
        const contador = document.querySelector(".contador-caracteres");

        descricao.addEventListener("input", () => {
            contador.textContent = `${descricao.value.length} / 500`;
        });
    
    // Botão alterar foto
    botaoAlterarFoto.addEventListener('click', () => {
        inputFoto.click();
    });
    
    // Input de arquivo para foto
    inputFoto.addEventListener('change', (e) => {
        const arquivo = e.target.files[0];
        if (arquivo && validarArquivoImagem(arquivo)) {
            uploadFoto(arquivo);
        }
    });
    
    // Botão remover foto
    botaoRemoverFoto.addEventListener('click', () => {
        if (confirm('Tem certeza que deseja remover sua foto de perfil?')) {
            removerFoto();
        }
    });
    
    // Botão editar
    botaoEditar.addEventListener('click', () => {
        alternarModoEdicao(true);
    });
    
    // Botão salvar
    botaoSalvar.addEventListener('click', () => {
        salvarAlteracoes();
    });
    
    // Botão cancelar
    botaoCancelar.addEventListener('click', () => {
        restaurarDadosOriginais();
        alternarModoEdicao(false);
    });
    
    // Botão excluir conta
    botaoExcluirConta.addEventListener('click', () => {
        modal.style.display = 'flex';
    });
    
    // Confirmar exclusão
    document.getElementById('confirmar-exclusao').addEventListener('click', () => {
        modal.style.display = 'none';
        excluirConta();
    });
    
    // Cancelar exclusão
    document.getElementById('cancelar-exclusao').addEventListener('click', () => {
        modal.style.display = 'none';
    });
    
    // Fechar modal clicando fora dele
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });
    
    // Navegação do header
    document.querySelector(".projetos-nav").addEventListener("click", function() {
        if (modoEdicao && !confirm('Você tem alterações não salvas. Deseja realmente sair?')) {
            return;
        }
        window.location.href = "../telaPrincipal/principal.php";
    });
    
    document.querySelector(".monitoria-nav").addEventListener("click", function() {
        if (modoEdicao && !confirm('Você tem alterações não salvas. Deseja realmente sair?')) {
            return;
        }
        window.location.href = "../telaMonitorias/telaMonitorias.php";
    });
    
    document.querySelector(".sair-nav").addEventListener("click", function() {
        if (confirm('Deseja realmente sair?')) {
            window.location.href = "../telaLogin/login.html";
        }
    });
    
    // Scroll do header
    window.addEventListener("scroll", () => {
        if (window.scrollY > 0) {
            header.classList.add("com-sombra");
        } else {
            header.classList.remove("com-sombra");
        }
    });
    
    // Prevenir saída acidental durante edição
    window.addEventListener('beforeunload', (e) => {
        if (modoEdicao) {
            e.preventDefault();
            e.returnValue = 'Você tem alterações não salvas. Tem certeza que deseja sair?';
            return e.returnValue;
        }
    });
    
    // Atalhos do teclado
    document.addEventListener('keydown', (e) => {
        // ESC para cancelar edição
        if (e.key === 'Escape' && modoEdicao) {
            e.preventDefault();
            restaurarDadosOriginais();
            alternarModoEdicao(false);
        }
        
        // Ctrl+S para salvar (durante edição)
        if (e.ctrlKey && e.key === 's' && modoEdicao) {
            e.preventDefault();
            salvarAlteracoes();
        }
        
        // Fechar modal com ESC
        if (e.key === 'Escape' && modal.style.display === 'flex') {
            e.preventDefault();
            modal.style.display = 'none';
        }
    });
}

// Utilitários para debug (remover em produção)
window.debugPerfil = {
    carregarDados: carregarDadosUsuario,
    alternarEdicao: alternarModoEdicao,
    salvar: salvarAlteracoes,
    mostrarMensagem: mostrarMensagem
};
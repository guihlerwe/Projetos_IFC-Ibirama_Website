<?php
session_start();
$nome = $_SESSION['nome'] ?? '';
$tipo = $_SESSION['tipo'] ?? '';

// Conexão com o banco de dados (reaproveitado de outros arquivos do projeto)
$host = 'localhost';
$usuario = 'root';
//$senha = 'root';
$senha = 'Gui@15600';
$banco = 'website';

$conn = new mysqli($host, $usuario, $senha, $banco);
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

$conn->set_charset("utf8");

// Buscar coordenadores para popular o select
$coordenadores = [];
$sql = "SELECT idPessoa, nome, sobrenome, email, foto_perfil FROM pessoa WHERE tipo = 'coordenador' ORDER BY nome, sobrenome";
if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $coordenadores[] = $row;
    }
    $result->free();
}

$bolsistas = [];
$sqlb = "SELECT idPessoa, nome, sobrenome, email, foto_perfil FROM pessoa WHERE tipo = 'bolsista' ORDER BY nome, sobrenome";
if ($result = $conn->query($sqlb)) {
    while ($row = $result->fetch_assoc()) {
        $bolsistas[] = $row;
    }
    $result->free();
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/tema-global.css">
    <link rel="stylesheet" href="../assets/css/cad-projeto.css">
    <title>Criar/Editar Projeto</title>
</head>
<body>
<script>
    sessionStorage.setItem('usuarioLogado', '<?php echo $nome; ?>');
    sessionStorage.setItem('tipoUsuario', '<?php echo $tipo; ?>');
</script>

<div class="container">
    <header>
        <div class="logo">
            <div class="icone-nav">
                <img src="../assets/photos/ifc-logo-preto.png" id="icone-ifc">
            </div>
            Projetos do Campus Ibirama
        </div>
        <div class="navegador">
            <div class="projetos-nav">Projetos</div>
            <div class="monitoria-nav">Monitoria</div>
            <div class="login-nav"> <?php include 'menuUsuario.php'; ?> </div>
        </div>
    </header>

    <form id="formulario" action="cad-projetoBD.php" method="POST" enctype="multipart/form-data">
        
        <div id="banner" style="position: relative; width: 100%; height: 200px; background-color: #f0f0f0; overflow: hidden;">
            <label id="upload-banner" style="display: block; width: 100%; height: 100%; cursor: pointer; position: relative;">
                <input type="file" id="banner-projeto" name="banner" accept="image/*" hidden>
                <span id="banner-text" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 2; color: #666; font-size: 16px;">Clique para adicionar banner</span>
                <img id="banner-preview" style="display: none;">
            </label>
        </div>

        <div id="info-projeto">
            <div id="div-capa">
                <label id="upload-capa">
                    <input type="file" id="foto-capa" name="capa" accept="image/*" hidden required>
                    <span id="capa-icon">📷</span>
                    <img id="capa-preview" style="display: none;">
                </label>
            </div>
            <div id="dados-projeto">
                <div class="div-eixo--categoria-ano">
                    <select id="eixo" name="eixo" required>
                        <option value="">Tipo</option>
                        <option value="ensino">Ensino</option>
                        <option value="pesquisa">Pesquisa</option>
                        <option value="extensao">Extensão</option>
                    </select>
                    <select id="categoria" name="categoria" required>
                        <option value="">Área de estudo</option>
                        <option value="ciencias_naturais">Ciências Naturais</option>
                        <option value="ciencias_humanas">Ciências Humanas</option>
                        <option value="linguagens">Linguagens</option>
                        <option value="matematica">Matemática</option>
                        <option value="administracao">Administração</option>
                        <option value="informatica">Informática</option>
                        <option value="vestuario">Vestuário</option>
                    </select>
                    <input type="text" id="ano-inicio" name="ano-inicio" placeholder="Desde (ano)">
                </div>
                <input type="text" id="nome-projeto" name="nome-projeto" placeholder="Nome do Projeto" required>
            </div>
            <input type="text" id="txt-link-inscricao" name="txt-link-inscricao" placeholder="Link p/ formulário de inscrição">
        </div>

        <div id="conteudo">
            <h2 class="subtitulo">Sobre (2000 max.)</h2>
            <textarea id="descricao" name="descricao" maxlength="2000" placeholder="Descreva o projeto..."></textarea>

            <input type="text" id="site-projeto" name="site-projeto" placeholder="Insira Link do site">

            <div class="equipe">
                <h2 class="subtitulo">Coordenadores(as)</h2>
                <div class="membros">
                    <div class="membro">
                        <div class="foto-membro">
                            <span>👤</span>
                            <!-- A imagem será inserida aqui via JavaScript -->
                        </div>
                            <div class="custom-select">
                            <div class="select-selected">Selecione um coordenador(a)</div>
                            <div class="select-items">
                                <?php foreach ($coordenadores as $coord): ?>
                                    <?php 
                                    $foto_path = $coord['foto_perfil'];
                                    // Se a foto não começar com '/' ou '../', adiciona o caminho base
                                    if ($foto_path && !preg_match('/^(\/|\.\.\/)/', $foto_path)) {
                                        $foto_path = '../assets/photos/fotos_perfil/' . $foto_path;
                                    }
                                    ?>
                                    <div data-value="<?php echo $coord['idPessoa']; ?>" 
                                         data-foto="<?php echo htmlspecialchars($foto_path); ?>">
                                        <?php echo htmlspecialchars($coord['nome'] . ' ' . $coord['sobrenome'] . ' (' . $coord['email'] . ')'); ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <input type="hidden" name="coordenador_id" id="coordenador_id" required>
                            </div>
                        </div>
                    </div>
            </div>

            <div class="equipe">
                <h2 class="subtitulo">Bolsistas</h2>
                <div class="membros">
                    <div class="membro">
                        <div class="foto-membro">
                            <span>👤</span>
                            <!-- A imagem será inserida aqui via JavaScript -->
                        </div>

                        <div class="custom-select">
                            <div class="select-selected">Selecione um bolsista...</div>
                            <div class="select-items">
                                <?php foreach ($bolsistas as $bolsista): ?>
                                    <?php 
                                    $foto_path = $bolsista['foto_perfil'];
                                    // Se a foto não começar com '/' ou '../', adiciona o caminho base
                                    if ($foto_path && !preg_match('/^(\/|\.\.\/)/', $foto_path)) {
                                        $foto_path = '../assets/photos/fotos_perfil/' . $foto_path;
                                    }
                                    ?>
                                    <div data-value="<?php echo $bolsista['idPessoa']; ?>"
                                         data-foto="<?php echo htmlspecialchars($foto_path); ?>">
                                        <?php echo htmlspecialchars($bolsista['nome'] . ' ' . $bolsista['sobrenome'] . ' (' . $bolsista['email'] . ')'); ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <input type="hidden" name="bolsista_id" id="bolsista_id">
                            </div>
                        </div>                            
                    </div>
                </div>
            </div>

            <input type="text" id="link-bolsista" name="link-bolsista" placeholder="Se há vagas para bolsistas, cole o link para inscrição aqui">

            <div id="contato">
                <h2 class="subtitulo">Contato com Projeto</h2>
                <input type="email" id="email" name="email" placeholder="E-mail para o projeto">
                <input type="text" id="numero-telefone" name="numero-telefone" placeholder="Número para contato (opcional)">
                <input type="text" id="instagram" name="instagram" placeholder="Instagram do projeto (opcional)">
            </div>

            <button type="submit" id="bt-criar-projeto">Criar Projeto</button>
        </div>
    </form>
</div>
<script src="../assets/js/global.js"></script>
<script src="../assets/js/cad-projeto.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const customSelects = document.querySelectorAll('.custom-select');
    customSelects.forEach((select) => {
        const selectedDiv = select.querySelector('.select-selected');
        const itemsDiv = select.querySelector('.select-items');
        const hiddenInput = select.querySelector('input[type="hidden"]');
        if (!selectedDiv || !itemsDiv) return;
        selectedDiv.addEventListener('click', function(e) {
            e.stopPropagation();
            document.querySelectorAll('.custom-select.open').forEach((other) => {
                if (other !== select) {
                    other.classList.remove('open');
                    const otherItems = other.querySelector('.select-items');
                    if (otherItems) otherItems.style.display = 'none';
                }
            });
            const isOpen = select.classList.toggle('open');
            itemsDiv.style.display = isOpen ? 'block' : 'none';
        });
        itemsDiv.querySelectorAll('div').forEach((item) => {
            item.addEventListener('click', function(e) {
                e.stopPropagation();
                selectedDiv.textContent = this.textContent;
                if (hiddenInput) hiddenInput.value = this.getAttribute('data-value');
                
                // Extrair apenas o nome do texto completo (remove o email)
                const textoCompleto = this.textContent;
                const nomeSemEmail = textoCompleto.split('(')[0].trim();
                selectedDiv.textContent = nomeSemEmail;
                
                // Atualizar a foto do membro selecionado
                const fotoMembro = this.getAttribute('data-foto');
                const container = select.closest('.membro');
                const fotoContainer = container.querySelector('.foto-membro');
                const fotoLabel = fotoContainer.querySelector('label');
                
                if (fotoMembro && fotoMembro !== 'null') {
                    // Se já existe uma imagem, atualiza a src
                    let img = fotoContainer.querySelector('img');
                    if (!img) {
                        // Se não existe imagem, cria uma nova
                        img = document.createElement('img');
                        img.style.width = '100%';
                        img.style.height = '100%';
                        img.style.objectFit = 'cover';
                        fotoContainer.appendChild(img);
                    }
                    // Usa o caminho completo se fornecido, ou constrói o caminho
                    img.src = fotoMembro.startsWith('/') || fotoMembro.startsWith('../') ? 
                             fotoMembro : 
                             '../assets/photos/fotos_perfil/' + fotoMembro;
                    img.style.display = 'block';
                    fotoContainer.querySelector('span').style.display = 'none';
                    
                    // Adiciona tratamento de erro para a imagem
                    img.onerror = function() {
                        console.log('Erro ao carregar a imagem:', this.src);
                        this.style.display = 'none';
                        fotoContainer.querySelector('span').style.display = 'block';
                    };
                } else {
                    // Se não tem foto, mostra o ícone padrão
                    const span = fotoContainer.querySelector('span');
                    const img = fotoContainer.querySelector('img');
                    if (img) {
                        fotoContainer.removeChild(img);
                    }
                    span.style.display = 'block';
                }
                
                // Debug para verificar o caminho da foto
                console.log('Valor do data-foto:', fotoMembro);
                if (fotoMembro && fotoMembro !== 'null') {
                    console.log('Caminho construído:', fotoMembro.startsWith('/') || fotoMembro.startsWith('../') ? 
                        fotoMembro : '../assets/photos/fotos_perfil/' + fotoMembro);
                }
                
                itemsDiv.style.display = 'none';
                select.classList.remove('open');
            });
        });
    });
    document.addEventListener('click', function() {
        document.querySelectorAll('.custom-select.open').forEach((s) => {
            s.classList.remove('open');
            const items = s.querySelector('.select-items');
            if (items) items.style.display = 'none';
        });
    });

    // Validação para garantir que um coordenador e bolsista foram selecionados
    const formulario = document.getElementById('formulario');
    if (formulario) {
        formulario.addEventListener('submit', function(e) {
            const coordId = document.getElementById('coordenador_id').value;
            if (!coordId) {
                alert('Selecione um coordenador!');
                e.preventDefault();
                return false;
            }
        });
    }
});
</script>
<?php
// Fechar conexão com o banco
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?>

<footer>
    <div class="linha">
        <div class="footer-container">
            <div class="Recursos">
                <h2>Recursos</h2>
                <ul>
                    <li><a href="https://ibirama.ifc.edu.br/">Site IF Ibirama</a></li>
                    <li><a href="https://ensino.ifc.edu.br/calendarios-academicos/">Calendários Acadêmicos</a></li>
                    <li><a href="https://ifc.edu.br/portal-do-estudante/">Políticas e Programas Estudantis</a></li>
                    <li><a href="https://ingresso.ifc.edu.br/">Portal de Ingresso IFC</a></li>
                    <li><a href="https://estudante.ifc.edu.br/2017/03/21/regulamento-de-conduta-discente/">Regulamento da Conduta Discente</a></li>
                    <li><a href="http://sig.ifc.edu.br/sigaa">SIGAA</a></li>
                </ul>
            </div>
            <div class="Comunidade">
                <h2>Comunidade</h2>
                <ul>
                    <li><a href="http://acessoainformacao.ifc.edu.br/">Acesso à Informação</a></li>
                    <li><a href="https://ifc.edu.br/comite-de-crise/">Calendários Acadêmicos</a></li>
                    <li><a href="https://cepsh.ifc.edu.br/">CEPSH</a></li>
                    <li><a href="https://consuper.ifc.edu.br/">Conselho Superior</a></li>
                    <li><a href="https://sig.ifc.edu.br/public/jsp/portal.jsf">Portal Público</a></li>
                    <li><a href="https://editais.ifc.edu.br/">Editais IFC</a></li>
                    <li><a href="http://www.camboriu.ifc.edu.br/pos-graduacao/treinador-e-instrutor-de-caes-guia/">Projetos Cães-guia</a></li>
                    <li><a href="https://trabalheconosco.ifc.edu.br/">Trabalhe no IFC</a></li>
                </ul>
            </div>
            <div class="Servidor">
                <h2>Servidor</h2>
                <ul>
                    <li><a href="https://ifc.edu.br/desenvolvimento-do-servidor/">Desenvolvimento do Servidor</a></li>
                    <li><a href="https://manualdoservidor.ifc.edu.br/">Manual do Servidor</a></li>
                    <li><a href="https://www.siapenet.gov.br/Portal/Servico/Apresentacao.asp">Portal SIAPENET</a></li>
                    <li><a href="http://suporte.ifc.edu.br/">Suporte TI</a></li>
                    <li><a href="https://sig.ifc.edu.br/sigrh/public/home.jsf">Sistema Integrado de Gestão (SIG)</a></li>
                    <li><a href="https://mail.google.com/mail/u/0/#inbox">Webmail</a></li>
                </ul>
            </div>
            <div class="Sites-Relacionados">
                <h2>Sites Relacionados</h2>
                <ul>
                    <li><a href="https://www.gov.br/pt-br">Brasil - GOV</a></li>
                    <li><a href="https://www.gov.br/capes/pt-br">CAPES - Chamadas Públicas</a></li>
                    <li><a href="https://www-periodicos-capes-gov-br.ez317.periodicos.capes.gov.br/index.php?">Capes - Portal de Periódicos</a></li>
                    <li><a href="https://www.gov.br/cnpq/pt-br">CNPq - Chamadas Públicas</a></li>
                    <li><a href="http://informativo.ifc.edu.br/">Informativo IFC</a></li>
                    <li><a href="https://www.gov.br/mec/pt-br">MEC - Ministério da Educação</a></li>
                    <li><a href="https://www.transparencia.gov.br/">Transparência Pública</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="Sobre">
  <h2 id="Sobre">
    Sobre este site
    <span class="arrow">↗</span>
  </h2>
  <span id="License"><i>Licença M.I.T.2025</i></span>
</div>
    
    </div>
    <div class="acesso-info">
        <a href="https://www.gov.br/acessoainformacao/pt-br">
            <img src="../assets/photos/icones/logo-acesso-informacao.png" alt="Logo Acesso à Informação">
        </a>
    </div>
</footer>
</body>
</html>
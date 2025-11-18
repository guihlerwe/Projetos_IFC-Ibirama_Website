/*
    Copyright (c) 2025 Guilherme Raimundo & Gabriella Schmilla Sandner
    
    This source code is licensed under the MIT license found in the
    LICENSE file in the root directory of this source tree.
*/


<?php
session_start();
$nome = $_SESSION['nome'] ?? '';
$tipo = $_SESSION['tipo'] ?? '';

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

$idProjetoEditar = isset($_GET['idProjeto']) ? (int) $_GET['idProjeto'] : 0;

// Buscar coordenadores para popular o select, excluindo os já vinculados ao projeto
$coordenadores = [];
$sql = "SELECT p.idPessoa, p.nome, p.sobrenome, p.email, p.foto_perfil, p.curso, p.tipo 
        FROM pessoa p 
        WHERE p.tipo = 'coordenador' 
        AND (? = 0 OR p.idPessoa NOT IN (
            SELECT pp.idPessoa 
            FROM pessoa_projeto pp 
            WHERE pp.idProjeto = ? 
            AND pp.tipoPessoa = 'coordenador'
        ))
        ORDER BY p.nome, p.sobrenome";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $idProjetoEditar, $idProjetoEditar);
if ($stmt->execute()) {
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $coordenadores[] = $row;
    }
}
$stmt->close();

// Buscar bolsistas para popular o select, excluindo os já vinculados ao projeto
$bolsistas = [];
$sqlb = "SELECT p.idPessoa, p.nome, p.sobrenome, p.email, p.foto_perfil, p.curso, p.tipo 
         FROM pessoa p 
         WHERE p.tipo IN ('bolsista', 'aluno')
         AND (? = 0 OR p.idPessoa NOT IN (
             SELECT pp.idPessoa 
             FROM pessoa_projeto pp 
             WHERE pp.idProjeto = ? 
             AND pp.tipoPessoa = 'bolsista'
         ))
         ORDER BY p.nome, p.sobrenome";

$stmt = $conn->prepare($sqlb);
$stmt->bind_param("ii", $idProjetoEditar, $idProjetoEditar);
if ($stmt->execute()) {
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $bolsistas[] = $row;
    }
}
$stmt->close();

// Buscar voluntários elegíveis (alunos, voluntários ou bolsistas) para o projeto
$voluntarios = [];
$sqlv = "SELECT p.idPessoa, p.nome, p.sobrenome, p.email, p.foto_perfil, p.curso, p.tipo 
         FROM pessoa p 
         WHERE p.tipo IN ('aluno')
         AND (? = 0 OR p.idPessoa NOT IN (
             SELECT pp.idPessoa 
             FROM pessoa_projeto pp 
             WHERE pp.idProjeto = ? 
             AND pp.tipoPessoa = 'voluntario'
         ))
         ORDER BY p.nome, p.sobrenome";

$stmt = $conn->prepare($sqlv);
$stmt->bind_param("ii", $idProjetoEditar, $idProjetoEditar);
if ($stmt->execute()) {
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $voluntarios[] = $row;
    }
}
$stmt->close();

$placeholderPerfil = '../assets/photos/fotos_perfil/sem_foto_perfil.jpg';

function gerarSrcPerfil(?string $foto, string $placeholder)
{
    if ($foto === null) {
        return $placeholder;
    }

    $foto = trim((string) $foto);
    if ($foto === '') {
        return $placeholder;
    }

    if (stripos($foto, 'data:image/') === 0 || stripos($foto, 'http://') === 0 || stripos($foto, 'https://') === 0) {
        return $foto;
    }

    if (strpos($foto, '../') === 0 || strpos($foto, '/assets') === 0 || strpos($foto, 'assets/') === 0) {
        return $foto;
    }

    if (!ctype_print($foto)) {
        static $finfo = null;
        if ($finfo === null && class_exists('finfo')) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
        }
        $mimeType = ($finfo instanceof finfo) ? $finfo->buffer($foto) : null;
        if (!$mimeType || strpos($mimeType, 'image/') !== 0) {
            $mimeType = 'image/jpeg';
        }

        return 'data:' . $mimeType . ';base64,' . base64_encode($foto);
    }

    return '../assets/photos/fotos_perfil/' . ltrim($foto, '/');
}

foreach ($coordenadores as &$coord) {
    $coord['foto_src'] = gerarSrcPerfil($coord['foto_perfil'] ?? null, $placeholderPerfil);
    if (isset($coord['foto_perfil']) && !mb_check_encoding($coord['foto_perfil'], 'UTF-8')) {
        unset($coord['foto_perfil']);
    }
}
unset($coord);

foreach ($bolsistas as &$bol) {
    $bol['foto_src'] = gerarSrcPerfil($bol['foto_perfil'] ?? null, $placeholderPerfil);
    if (isset($bol['foto_perfil']) && !mb_check_encoding($bol['foto_perfil'], 'UTF-8')) {
        unset($bol['foto_perfil']);
    }
}
unset($bol);

foreach ($voluntarios as &$vol) {
    $vol['foto_src'] = gerarSrcPerfil($vol['foto_perfil'] ?? null, $placeholderPerfil);
    if (isset($vol['foto_perfil']) && !mb_check_encoding($vol['foto_perfil'], 'UTF-8')) {
        unset($vol['foto_perfil']);
    }
}
unset($vol);

$idPessoaLogado = $_SESSION['idPessoa'] ?? null;
$modoEdicao = false;
$projetoSelecionado = null;
$bannerAtual = null;
$capaAtual = null;
$coordenadoresProjeto = [];
$bolsistasProjeto = [];
$voluntariosProjeto = [];

$categoriaLabelMap = [
    'ciencias-naturais' => 'Ciências Naturais',
    'ciencias-humanas' => 'Ciências Humanas',
    'linguagens' => 'Linguagens',
    'matematica' => 'Matemática',
    'administracao' => 'Administração',
    'informatica' => 'Informática',
    'vestuario' => 'Vestuário',
    'moda' => 'Moda',
];

function buscarImagemProjeto(?string $pasta, string $prefixo): ?string
{
    if (!$pasta) {
        return null;
    }

    $baseDir = __DIR__ . '/assets/photos/projetos/' . $pasta . '/';
    if (!is_dir($baseDir)) {
        return null;
    }

    $arquivos = glob($baseDir . $prefixo . '.*');
    if (!$arquivos) {
        return null;
    }

    $arquivo = basename($arquivos[0]);
    return '../assets/photos/projetos/' . $pasta . '/' . $arquivo;
}

if ($idProjetoEditar > 0 && $idPessoaLogado && $tipo === 'coordenador') {
    $stmtProjeto = $conn->prepare("SELECT p.* FROM projeto p INNER JOIN pessoa_projeto pp ON pp.idProjeto = p.idProjeto AND pp.tipoPessoa = 'coordenador' WHERE p.idProjeto = ? AND pp.idPessoa = ?");
    if ($stmtProjeto) {
        $stmtProjeto->bind_param('ii', $idProjetoEditar, $idPessoaLogado);
        if ($stmtProjeto->execute()) {
            $resultadoProjeto = $stmtProjeto->get_result();
            if ($dadosProjeto = $resultadoProjeto->fetch_assoc()) {
                $modoEdicao = true;
                $projetoSelecionado = $dadosProjeto;
                $bannerAtual = buscarImagemProjeto($dadosProjeto['banner'] ?? null, 'banner');
                $capaAtual = buscarImagemProjeto($dadosProjeto['capa'] ?? null, 'capa');

                $stmtEquipe = $conn->prepare("SELECT pe.idPessoa, pe.nome, pe.sobrenome, pe.email, pe.foto_perfil, pe.curso, pe.tipo, pp.tipoPessoa FROM pessoa_projeto pp INNER JOIN pessoa pe ON pe.idPessoa = pp.idPessoa WHERE pp.idProjeto = ? ORDER BY pe.nome, pe.sobrenome");
                if ($stmtEquipe) {
                    $stmtEquipe->bind_param('i', $idProjetoEditar);
                    if ($stmtEquipe->execute()) {
                        $resultadoEquipe = $stmtEquipe->get_result();
                        while ($linha = $resultadoEquipe->fetch_assoc()) {
                            $linha['foto_src'] = gerarSrcPerfil($linha['foto_perfil'] ?? null, $placeholderPerfil);
                            if (isset($linha['foto_perfil']) && !mb_check_encoding($linha['foto_perfil'], 'UTF-8')) {
                                unset($linha['foto_perfil']);
                            }
                            if ($linha['tipoPessoa'] === 'coordenador') {
                                $coordenadoresProjeto[] = $linha;
                            } elseif ($linha['tipoPessoa'] === 'bolsista') {
                                $bolsistasProjeto[] = $linha;
                            } elseif ($linha['tipoPessoa'] === 'voluntario') {
                                $voluntariosProjeto[] = $linha;
                            }
                        }
                    }
                    $stmtEquipe->close();
                }
            }
        }
        $stmtProjeto->close();
    }
}

$projetoEditData = null;
if ($modoEdicao && $projetoSelecionado) {
    $projetoEditData = [
        'idProjeto' => (int) $projetoSelecionado['idProjeto'],
        'nome' => $projetoSelecionado['nome'] ?? '',
        'tipo' => $projetoSelecionado['tipo'] ?? '',
        'tipoLabel' => ucfirst($projetoSelecionado['tipo'] ?? ''),
        'categoria' => $projetoSelecionado['categoria'] ?? '',
        'categoriaOption' => str_replace('-', '_', $projetoSelecionado['categoria'] ?? ''),
        'categoriaLabel' => $categoriaLabelMap[$projetoSelecionado['categoria'] ?? ''] ?? ucwords(str_replace('-', ' ', $projetoSelecionado['categoria'] ?? '')),
        'anoInicio' => $projetoSelecionado['anoInicio'] ?? '',
        'linkInscricao' => $projetoSelecionado['linkParaInscricao'] ?? '',
        'descricao' => $projetoSelecionado['textoSobre'] ?? '',
        'linkSite' => $projetoSelecionado['linkSite'] ?? '',
        'linkBolsista' => $projetoSelecionado['linkBolsista'] ?? '',
        'email' => $projetoSelecionado['email'] ?? '',
        'numero' => $projetoSelecionado['numero'] ?? '',
        'instagram' => $projetoSelecionado['linkInstagram'] ?? '',
        'bannerPath' => $bannerAtual,
        'capaPath' => $capaAtual,
    ];
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
    
    // Dados dos coordenadores, bolsistas e voluntários para JavaScript
    const coordenadoresData = <?php echo json_encode($coordenadores); ?>;
    const bolsistasData = <?php echo json_encode($bolsistas); ?>;
    const voluntariosData = <?php echo json_encode($voluntarios); ?>;
    const projetoSelecionado = <?php echo $projetoEditData ? json_encode($projetoEditData, JSON_UNESCAPED_UNICODE) : 'null'; ?>;
    const coordenadoresProjetoSelecionados = <?php echo $modoEdicao ? json_encode(array_values($coordenadoresProjeto), JSON_UNESCAPED_UNICODE) : '[]'; ?>;
    const bolsistasProjetoSelecionados = <?php echo $modoEdicao ? json_encode(array_values($bolsistasProjeto), JSON_UNESCAPED_UNICODE) : '[]'; ?>;
    const voluntariosProjetoSelecionados = <?php echo $modoEdicao ? json_encode(array_values($voluntariosProjeto), JSON_UNESCAPED_UNICODE) : '[]'; ?>;

    // Expõe os dados para scripts externos (ex.: cad-projeto.js)
    window.coordenadoresData = coordenadoresData;
    window.bolsistasData = bolsistasData;
    window.voluntariosData = voluntariosData;
    window.projetoSelecionado = projetoSelecionado;
    window.coordenadoresProjetoSelecionados = coordenadoresProjetoSelecionados;
    window.bolsistasProjetoSelecionados = bolsistasProjetoSelecionados;
    window.voluntariosProjetoSelecionados = voluntariosProjetoSelecionados;
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
        <input type="hidden" name="id-projeto" id="id-projeto"
            value="<?php echo $modoEdicao ? (int) $idProjetoEditar : ''; ?>">

        
        <div id="banner" style="position: relative; width: 100%; height: 200px; overflow: hidden;">
            <label id="upload-banner" style="display: block; width: 100%; height: 100%; cursor: pointer; position: relative;">
                <input type="file" id="banner-projeto" name="banner" accept="image/*" hidden>
                <span id="banner-text" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 2; font-size: 16px;<?php echo ($modoEdicao && $bannerAtual) ? ' display:none;' : ''; ?>">
                    <?php echo $modoEdicao ? 'Clique para alterar banner' : 'Clique para adicionar banner'; ?>
                </span>
                <img id="banner-preview" <?php if ($modoEdicao && $bannerAtual) { echo 'src="' . htmlspecialchars($bannerAtual) . '" style="display:block; width:100%; height:100%; object-fit:cover; position:absolute; top:0; left:0; z-index:5;"'; } else { echo 'style="display: none;"'; } ?>>
            </label>
        </div>

        <div id="info-projeto">
            <div id="div-capa">
                <label id="upload-capa">
                    <input type="file" id="foto-capa" name="capa" accept="image/*" hidden <?php echo !$modoEdicao ? 'required' : ''; ?>>                    <span id="capa-icon"<?php echo ($modoEdicao && $capaAtual) ? ' style="display:none;"' : ''; ?>>📷</span>
                    <img id="capa-preview" <?php if ($modoEdicao && $capaAtual) { echo 'src="' . htmlspecialchars($capaAtual) . '" style="display:block; width:100%; height:100%; object-fit:cover;"'; } else { echo 'style="display: none;"'; } ?>>
                </label>
            </div>
            <div id="dados-projeto">
                <div class="div-eixo--categoria-ano">
                    <div class="custom-select" id="eixo-select">
                        <div class="select-selected"><?php echo ($modoEdicao && $projetoEditData) ? htmlspecialchars($projetoEditData['tipoLabel']) : 'Tipo'; ?></div>
                        <div class="select-items">
                            <div data-value="ensino">Ensino</div>
                            <div data-value="pesquisa">Pesquisa</div>
                            <div data-value="extensao">Extensão</div>
                        </div>
                    </div>
                    <input type="hidden" id="eixo" name="eixo" value="<?php echo ($modoEdicao && $projetoEditData) ? htmlspecialchars($projetoEditData['tipo']) : ''; ?>" required>
                    
                    <div class="custom-select" id="categoria-select">
                        <div class="select-selected"><?php echo ($modoEdicao && $projetoEditData) ? htmlspecialchars($projetoEditData['categoriaLabel']) : 'Área de estudo'; ?></div>
                        <div class="select-items">
                            <div data-value="ciencias_naturais">Ciências Naturais</div>
                            <div data-value="ciencias_humanas">Ciências Humanas</div>
                            <div data-value="linguagens">Linguagens</div>
                            <div data-value="matematica">Matemática</div>
                            <div data-value="administracao">Administração</div>
                            <div data-value="informatica">Informática</div>
                            <div data-value="vestuario">Vestuário</div>
                        </div>
                    </div>
                    <input type="hidden" id="categoria" name="categoria" value="<?php echo ($modoEdicao && $projetoEditData) ? htmlspecialchars($projetoEditData['categoriaOption']) : ''; ?>" required>
                    
                    <input type="text" id="ano-inicio" name="ano-inicio" placeholder="Desde (ano)" value="<?php echo ($modoEdicao && $projetoEditData) ? htmlspecialchars((string) $projetoEditData['anoInicio']) : ''; ?>">
                </div>
                <input type="text" id="nome-projeto" name="nome-projeto" placeholder="Nome do Projeto" value="<?php echo ($modoEdicao && $projetoEditData) ? htmlspecialchars($projetoEditData['nome']) : ''; ?>" required>
            </div>
            <input type="text" id="txt-link-inscricao" name="txt-link-inscricao" placeholder="Link p/ formulário de inscrição" value="<?php echo ($modoEdicao && $projetoEditData) ? htmlspecialchars($projetoEditData['linkInscricao']) : ''; ?>">
        </div>

        <div id="conteudo">
            <h2 class="subtitulo">Sobre (2000 max.)</h2>
            <textarea id="descricao" name="descricao" maxlength="2000" placeholder="Descreva o projeto..."><?php echo ($modoEdicao && $projetoEditData) ? htmlspecialchars($projetoEditData['descricao']) : ''; ?></textarea>

            <input type="text" id="site-projeto" name="site-projeto" placeholder="Insira Link do site" value="<?php echo ($modoEdicao && $projetoEditData) ? htmlspecialchars($projetoEditData['linkSite']) : ''; ?>">

            <div class="equipe">
                <h2 class="subtitulo">Coordenadores(as)</h2>
                <div class="membros" id="coordenadores-container">
                    <!-- Coordenadores serão adicionados aqui dinamicamente -->
                    <div class="membro add-membro" id="add-coordenador">
                        <div class="foto-membro foto-add">
                            <span>➕</span>
                        </div>
                        <div class="nome-membro">Adicionar</div>
                    </div>
                </div>
                <input type="hidden" name="coordenadores_ids" id="coordenadores_ids">
            </div>

            <div class="equipe">
                <h2 class="subtitulo">Bolsistas</h2>
                <div class="membros" id="bolsistas-container">
                    <!-- Bolsistas serão adicionados aqui dinamicamente -->
                    <div class="membro add-membro" id="add-bolsista">
                        <div class="foto-membro foto-add">
                            <span>➕</span>
                        </div>
                        <div class="nome-membro">Adicionar</div>
                    </div>
                </div>
                <input type="hidden" name="bolsistas_ids" id="bolsistas_ids">
            </div>

            <div class="equipe">
                <h2 class="subtitulo">Voluntários</h2>
                <div class="membros" id="voluntarios-container">
                    <!-- Voluntários serão adicionados aqui dinamicamente -->
                    <div class="membro add-membro" id="add-voluntario">
                        <div class="foto-membro foto-add">
                            <span>➕</span>
                        </div>
                        <div class="nome-membro">Adicionar</div>
                    </div>
                </div>
                <input type="hidden" name="voluntarios_ids" id="voluntarios_ids">
            </div>

                <input type="text" id="link-bolsista" name="link-bolsista" placeholder="Se há vagas para bolsistas, cole o link para inscrição aqui" value="<?php echo ($modoEdicao && $projetoEditData) ? htmlspecialchars($projetoEditData['linkBolsista']) : ''; ?>">

            <div id="contato">
                <h2 class="subtitulo">Contato com Projeto</h2>
                    <input type="email" id="email" name="email" placeholder="E-mail para o projeto" value="<?php echo ($modoEdicao && $projetoEditData) ? htmlspecialchars($projetoEditData['email']) : ''; ?>">
                    <input type="text" id="numero-telefone" name="numero-telefone" placeholder="Número para contato (opcional)" value="<?php echo ($modoEdicao && $projetoEditData) ? htmlspecialchars($projetoEditData['numero']) : ''; ?>">
                    <input type="text" id="instagram" name="instagram" placeholder="Instagram do projeto (opcional)" value="<?php echo ($modoEdicao && $projetoEditData) ? htmlspecialchars($projetoEditData['instagram']) : ''; ?>">
            </div>

                <button type="submit" id="bt-criar-projeto"><?php echo $modoEdicao ? 'Salvar alterações' : 'Criar Projeto'; ?></button>
        </div>
    </form>
</div>
<script src="../assets/js/global.js"></script>
<script src="../assets/js/cad-projeto.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Arrays para armazenar os IDs selecionados
    let coordenadoresSelecionados = [];
    let bolsistasSelecionados = [];
    let voluntariosSelecionados = [];
    const FOTO_PLACEHOLDER = '../assets/photos/fotos_perfil/sem_foto_perfil.jpg';
    
    // Função para criar um membro na interface
    function criarMembroHTML(pessoa, tipo) {
        const membro = document.createElement('div');
        membro.className = 'membro';
        membro.dataset.id = pessoa.idPessoa;
        membro.dataset.tipo = tipo;
        
        const fotoSrc = pessoa.foto_src && pessoa.foto_src !== 'null' ? pessoa.foto_src : FOTO_PLACEHOLDER;
        
        // Nome completo
        const nomeCompleto = pessoa.nome + ' ' + pessoa.sobrenome;
        const nomeCurto = nomeCompleto.length > 15 ? nomeCompleto.substring(0, 12) + '...' : nomeCompleto;
        
        membro.innerHTML = `
            <div class="foto-membro-wrapper">
                <div class="foto-membro">
                    <img src="${fotoSrc}" alt="${nomeCompleto}" onerror="this.onerror=null; this.src='${FOTO_PLACEHOLDER}';">
                </div>
                <div class="btn-remover" title="Remover">
                    <span>➖</span>
                </div>
            </div>
            <div class="nome-membro" title="${nomeCompleto}">${nomeCurto}</div>
        `;
        
        // Evento de remover
        const btnRemover = membro.querySelector('.btn-remover');
        btnRemover.addEventListener('click', function() {
            removerMembro(pessoa.idPessoa, tipo);
        });
        
        return membro;
    }
    
    // Função para abrir modal de seleção
    function abrirModalSelecao(tipo) {
        let dados;
        let jaSelecionados;

        if (tipo === 'coordenador') {
            dados = coordenadoresData;
            jaSelecionados = coordenadoresSelecionados;
        } else if (tipo === 'bolsista') {
            dados = bolsistasData;
            jaSelecionados = bolsistasSelecionados;
        } else {
            dados = voluntariosData;
            jaSelecionados = voluntariosSelecionados;
        }
        
        // Criar modal
        const modal = document.createElement('div');
        modal.className = 'modal-selecao';
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Selecione ${tipo === 'coordenador' ? 'um Coordenador' : tipo === 'bolsista' ? 'um Bolsista' : 'um Voluntário'}</h3>
                    <span class="modal-close">&times;</span>
                </div>
                <div class="modal-body">
                    <input type="text" class="modal-search" placeholder="Buscar por nome ou email...">
                    <div class="modal-lista"></div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        const lista = modal.querySelector('.modal-lista');
        const searchInput = modal.querySelector('.modal-search');
        
        // Função para renderizar a lista
        function renderizarLista(filtro = '') {
            lista.innerHTML = '';
            
            const dadosFiltrados = dados.filter(pessoa => {
                if (jaSelecionados.includes(pessoa.idPessoa)) return false;
                
                if (filtro) {
                    const texto = `${pessoa.nome} ${pessoa.sobrenome} ${pessoa.email}`.toLowerCase();
                    return texto.includes(filtro.toLowerCase());
                }
                return true;
            });
            
            if (dadosFiltrados.length === 0) {
                lista.innerHTML = '<div class="sem-resultados">Nenhuma pessoa disponível</div>';
                return;
            }
            
            dadosFiltrados.forEach(pessoa => {
                const item = document.createElement('div');
                item.className = 'modal-item';
                
                const fotoSrc = pessoa.foto_src && pessoa.foto_src !== 'null' ? pessoa.foto_src : FOTO_PLACEHOLDER;
                
                item.innerHTML = `
                    <div class="modal-item-foto">
                        <img src="${fotoSrc}" alt="${pessoa.nome}" onerror="this.onerror=null; this.src='${FOTO_PLACEHOLDER}';">
                    </div>
                    <div class="modal-item-info">
                        <div class="modal-item-nome">${pessoa.nome} ${pessoa.sobrenome}</div>
                        <div class="modal-item-email">${pessoa.email}</div>
                    </div>
                `;
                
                item.addEventListener('click', function() {
                    adicionarMembro(pessoa, tipo);
                    document.body.removeChild(modal);
                });
                
                lista.appendChild(item);
            });
        }
        
        renderizarLista();
        
        // Busca
        searchInput.addEventListener('input', function() {
            renderizarLista(this.value);
        });
        
        // Fechar modal
        const closeBtn = modal.querySelector('.modal-close');
        closeBtn.addEventListener('click', function() {
            document.body.removeChild(modal);
        });
        
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                document.body.removeChild(modal);
            }
        });
        
        // Focar no input de busca
        setTimeout(() => searchInput.focus(), 100);
    }
    
    // Função para adicionar membro
    function adicionarMembro(pessoa, tipo) {
        if (tipo === 'coordenador') {
            coordenadoresSelecionados.push(pessoa.idPessoa);
            const container = document.getElementById('coordenadores-container');
            const addBtn = document.getElementById('add-coordenador');
            container.insertBefore(criarMembroHTML(pessoa, 'coordenador'), addBtn);
        } else if (tipo === 'bolsista') {
            bolsistasSelecionados.push(pessoa.idPessoa);
            const container = document.getElementById('bolsistas-container');
            const addBtn = document.getElementById('add-bolsista');
            container.insertBefore(criarMembroHTML(pessoa, 'bolsista'), addBtn);
        } else {
            voluntariosSelecionados.push(pessoa.idPessoa);
            const container = document.getElementById('voluntarios-container');
            const addBtn = document.getElementById('add-voluntario');
            container.insertBefore(criarMembroHTML(pessoa, 'voluntario'), addBtn);
        }
        
        atualizarInputsHidden();
    }
    
    // Função para remover membro
    function removerMembro(id, tipo) {
        if (tipo === 'coordenador') {
            coordenadoresSelecionados = coordenadoresSelecionados.filter(item => item != id);
            const membro = document.querySelector(`#coordenadores-container .membro[data-id="${id}"]`);
            if (membro) membro.remove();
        } else if (tipo === 'bolsista') {
            bolsistasSelecionados = bolsistasSelecionados.filter(item => item != id);
            const membro = document.querySelector(`#bolsistas-container .membro[data-id="${id}"]`);
            if (membro) membro.remove();
        } else {
            voluntariosSelecionados = voluntariosSelecionados.filter(item => item != id);
            const membro = document.querySelector(`#voluntarios-container .membro[data-id="${id}"]`);
            if (membro) membro.remove();
        }
        
        atualizarInputsHidden();
    }
    
    // Função para atualizar inputs hidden
    function atualizarInputsHidden() {
        document.getElementById('coordenadores_ids').value = coordenadoresSelecionados.join(',');
        document.getElementById('bolsistas_ids').value = bolsistasSelecionados.join(',');
        document.getElementById('voluntarios_ids').value = voluntariosSelecionados.join(',');
    }

    if (Array.isArray(coordenadoresProjetoSelecionados) && coordenadoresProjetoSelecionados.length > 0) {
        coordenadoresProjetoSelecionados.forEach((pessoa) => {
            if (!coordenadoresSelecionados.includes(pessoa.idPessoa)) {
                adicionarMembro(pessoa, 'coordenador');
            }
        });
    }

    if (Array.isArray(bolsistasProjetoSelecionados) && bolsistasProjetoSelecionados.length > 0) {
        bolsistasProjetoSelecionados.forEach((pessoa) => {
            if (!bolsistasSelecionados.includes(pessoa.idPessoa)) {
                adicionarMembro(pessoa, 'bolsista');
            }
        });
    }

    if (Array.isArray(voluntariosProjetoSelecionados) && voluntariosProjetoSelecionados.length > 0) {
        voluntariosProjetoSelecionados.forEach((pessoa) => {
            if (!voluntariosSelecionados.includes(pessoa.idPessoa)) {
                adicionarMembro(pessoa, 'voluntario');
            }
        });
    }
    
    // Eventos dos botões adicionar
    document.getElementById('add-coordenador').addEventListener('click', function() {
        abrirModalSelecao('coordenador');
    });
    
    document.getElementById('add-bolsista').addEventListener('click', function() {
        abrirModalSelecao('bolsista');
    });

    document.getElementById('add-voluntario').addEventListener('click', function() {
        abrirModalSelecao('voluntario');
    });

    // Validação para garantir que pelo menos um coordenador foi selecionado
    const formulario = document.getElementById('formulario');
    if (formulario) {
        formulario.addEventListener('submit', function(e) {
            if (coordenadoresSelecionados.length === 0) {
                alert('Selecione pelo menos um coordenador!');
                e.preventDefault();
                return false;
            }
        });
    }
    
    // Configurar custom selects para Tipo e Área de Estudo
    function setupCustomSelect(selectId, hiddenInputId) {
        const customSelect = document.getElementById(selectId);
        if (!customSelect) return;
        
        const selectedDiv = customSelect.querySelector('.select-selected');
        const itemsDiv = customSelect.querySelector('.select-items');
        const hiddenInput = document.getElementById(hiddenInputId);
        
        if (!selectedDiv || !itemsDiv || !hiddenInput) return;
        
        // Abrir/fechar dropdown
        selectedDiv.addEventListener('click', function(e) {
            e.stopPropagation();
            
            // Fechar outros dropdowns
            document.querySelectorAll('.custom-select.open').forEach((other) => {
                if (other !== customSelect) {
                    other.classList.remove('open');
                }
            });
            
            // Toggle este dropdown
            customSelect.classList.toggle('open');
        });
        
        // Selecionar item
        itemsDiv.querySelectorAll('div').forEach((item) => {
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
    
    // Inicializar custom selects
    setupCustomSelect('eixo-select', 'eixo');
    setupCustomSelect('categoria-select', 'categoria');
    
    // Fechar dropdowns ao clicar fora
    document.addEventListener('click', function() {
        document.querySelectorAll('.custom-select.open').forEach((select) => {
            select.classList.remove('open');
        });
    });
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
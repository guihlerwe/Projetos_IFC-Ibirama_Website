<?php
/*
    Copyright (c) 2025 Guilherme Raimundo & Gabriella Schmilla Sandner
    
    This source code is licensed under the MIT license found in the
    LICENSE file in the root directory of this source tree.
*/



session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// banco de dados
$host = 'localhost';
$usuario = 'root';
$senha = 'YOUR_PASSWORD';
$banco = 'website';

// conexão com o banco
$conn = new mysqli($host, $usuario, $senha, $banco);
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

$conn->set_charset("utf8");

$idPessoaLogado = $_SESSION['idPessoa'] ?? null;

// ================================
// EXCLUSÃO DE PROJETO (VERIFICAR PRIMEIRO)
// ================================
if (isset($_POST['acao']) && $_POST['acao'] === 'excluir') {
    $idProjetoExcluir = isset($_POST['idProjeto']) ? (int)$_POST['idProjeto'] : 0;
    
    if ($idProjetoExcluir <= 0) {
        echo "<script>alert('ID do projeto inválido!'); history.back();</script>";
        exit;
    }
    
    // Verificar se o coordenador logado tem permissão para excluir este projeto
    $stmtVerifica = $conn->prepare("SELECT pp.idPessoa FROM pessoa_projeto pp WHERE pp.idProjeto = ? AND pp.idPessoa = ? AND pp.tipoPessoa = 'coordenador'");
    $stmtVerifica->bind_param('ii', $idProjetoExcluir, $idPessoaLogado);
    $stmtVerifica->execute();
    $resultVerifica = $stmtVerifica->get_result();
    
    if ($resultVerifica->num_rows === 0) {
        $stmtVerifica->close();
        $conn->close();
        echo "<script>alert('Você não tem permissão para excluir este projeto!'); history.back();</script>";
        exit;
    }
    $stmtVerifica->close();
    
    // PASSO 1: Buscar bolsistas e voluntários vinculados ao projeto e REVERTER para aluno
    $stmtBuscarPessoas = $conn->prepare("
        SELECT pp.idPessoa, p.tipo as tipoAtualPessoa
        FROM pessoa_projeto pp
        INNER JOIN pessoa p ON p.idPessoa = pp.idPessoa
        WHERE pp.idProjeto = ? 
        AND pp.tipoPessoa IN ('bolsista', 'voluntario')
        AND p.tipo IN ('bolsista', 'voluntario')
    ");
    
    $stmtBuscarPessoas->bind_param('i', $idProjetoExcluir);
    $stmtBuscarPessoas->execute();
    $resultPessoas = $stmtBuscarPessoas->get_result();
    
    $pessoasParaReverter = [];
    while ($rowPessoa = $resultPessoas->fetch_assoc()) {
        $pessoasParaReverter[] = [
            'idPessoa' => (int)$rowPessoa['idPessoa'],
            'tipoAtualPessoa' => $rowPessoa['tipoAtualPessoa']
        ];
    }
    $stmtBuscarPessoas->close();
    
    error_log("📋 Projeto {$idProjetoExcluir} sendo excluído. Pessoas a reverter: " . count($pessoasParaReverter));
    
    // PASSO 2: Reverter TODOS para aluno (bolsista e voluntário só podem estar em 1 projeto)
    foreach ($pessoasParaReverter as $pessoa) {
        $idPessoa = $pessoa['idPessoa'];
        $tipoAtualPessoa = $pessoa['tipoAtualPessoa'];
        
        if ($tipoAtualPessoa === 'bolsista' || $tipoAtualPessoa === 'voluntario') {
            $stmtReverter = $conn->prepare("UPDATE pessoa SET tipo = 'aluno' WHERE idPessoa = ?");
            if ($stmtReverter) {
                $stmtReverter->bind_param('i', $idPessoa);
                if ($stmtReverter->execute()) {
                    error_log("✅ Pessoa ID {$idPessoa} (tipo: {$tipoAtualPessoa}) revertida para 'aluno' após exclusão do projeto {$idProjetoExcluir}");
                } else {
                    error_log("❌ Erro ao reverter pessoa ID {$idPessoa}: " . $stmtReverter->error);
                }
                $stmtReverter->close();
            }
        }
    }
    
    // PASSO 3: Buscar informações do projeto para excluir arquivos
    $stmtProjeto = $conn->prepare("SELECT banner, capa FROM projeto WHERE idProjeto = ?");
    $stmtProjeto->bind_param('i', $idProjetoExcluir);
    $stmtProjeto->execute();
    $resultProjeto = $stmtProjeto->get_result();
    $dadosProjeto = $resultProjeto->fetch_assoc();
    $stmtProjeto->close();
    
    // PASSO 4: Excluir pasta de imagens do projeto
    if ($dadosProjeto) {
        $pastaProjeto = $dadosProjeto['banner'] ?: $dadosProjeto['capa'];
        if ($pastaProjeto) {
            $caminhoCompleto = __DIR__ . '/assets/photos/projetos/' . $pastaProjeto;
            if (is_dir($caminhoCompleto)) {
                // Excluir todos os arquivos da pasta
                $arquivos = glob($caminhoCompleto . '/*');
                foreach ($arquivos as $arquivo) {
                    if (is_file($arquivo)) {
                        @unlink($arquivo);
                    }
                }
                // Excluir a pasta
                @rmdir($caminhoCompleto);
                error_log("📁 Pasta do projeto excluída: {$caminhoCompleto}");
            }
        }
    }
    
    // PASSO 5: Excluir vínculos na tabela pessoa_projeto
    $stmtDeletePessoas = $conn->prepare("DELETE FROM pessoa_projeto WHERE idProjeto = ?");
    $stmtDeletePessoas->bind_param('i', $idProjetoExcluir);
    $stmtDeletePessoas->execute();
    $linhasAfetadas = $stmtDeletePessoas->affected_rows;
    $stmtDeletePessoas->close();
    error_log("🔗 {$linhasAfetadas} vínculos pessoa_projeto excluídos");
    
    // PASSO 6: Excluir o projeto
    $stmtDeleteProjeto = $conn->prepare("DELETE FROM projeto WHERE idProjeto = ?");
    $stmtDeleteProjeto->bind_param('i', $idProjetoExcluir);
    
    if ($stmtDeleteProjeto->execute()) {
        $stmtDeleteProjeto->close();
        $conn->close();
        error_log("✅ Projeto ID {$idProjetoExcluir} excluído com sucesso!");
        echo "<script>alert('✅ Projeto excluído com sucesso!'); window.location.href='menuEditProjetos.php';</script>";
        exit;
    } else {
        $erro = $stmtDeleteProjeto->error;
        $stmtDeleteProjeto->close();
        $conn->close();
        error_log("❌ Erro ao excluir projeto ID {$idProjetoExcluir}: " . $erro);
        echo "<script>alert('❌ Erro ao excluir projeto: " . addslashes($erro) . "'); history.back();</script>";
        exit;
    }
}

// ================================
// CONTINUA O RESTO DO CÓDIGO (CADASTRO/EDIÇÃO)
// ================================

// pasta base para guardar as imagens dos projetos
$pastaBaseImagens = __DIR__ . '/assets/photos/projetos/';
$pastaTempImagens = __DIR__ . '/assets/photos/temp/';

// Função para limpar pasta temporária
function limparPastaTemp($pasta) {
    if (is_dir($pasta)) {
        $files = glob($pasta . '*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}

// função que salva as imagens em uma pasta específica
function salvarImagemTemp($campoArquivo, $pastaTempDestino) {
    if (!isset($_FILES[$campoArquivo]) || $_FILES[$campoArquivo]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $nomeTemp = $_FILES[$campoArquivo]['tmp_name'];
    $nomeOriginal = basename($_FILES[$campoArquivo]['name']);
    
    $tiposPermitidos = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $extensao = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));
    
    if (!in_array($extensao, $tiposPermitidos)) {
        return null;
    }
    
    $nomeFinal = ($campoArquivo === 'capa' ? 'capa' : 'banner') . '.' . $extensao;
    $caminhoTempCompleto = $pastaTempDestino . $nomeFinal;
    
    if (!is_dir($pastaTempDestino)) {
        if (!mkdir($pastaTempDestino, 0777, true)) {
            error_log("Erro ao criar diretório temporário: " . $pastaTempDestino);
            return null;
        }
    }
    
    if (move_uploaded_file($nomeTemp, $caminhoTempCompleto)) {
        chmod($caminhoTempCompleto, 0644);
        return $nomeFinal;
    }
    
    return null;
}

// Função para mover imagens da pasta temporária para a pasta final do projeto
function moverImagensParaProjeto($nomeArquivo, $pastaTempDestino, $pastaFinalDestino) {
    if ($nomeArquivo && file_exists($pastaTempDestino . $nomeArquivo)) {
        if (!is_dir($pastaFinalDestino)) {
            if (!mkdir($pastaFinalDestino, 0777, true)) {
                error_log("Erro ao criar diretório: " . $pastaFinalDestino);
                return false;
            }
        }

        // Remove arquivo antigo se existir
        if (preg_match('/^(capa|banner)\./', $nomeArquivo, $matches)) {
            $prefixo = $matches[1];
            $existentes = glob($pastaFinalDestino . $prefixo . '.*');
            foreach ($existentes as $arquivoExistente) {
                if (file_exists($arquivoExistente)) {
                    unlink($arquivoExistente);
                }
            }
        }

        // Move o novo arquivo
        if (copy($pastaTempDestino . $nomeArquivo, $pastaFinalDestino . $nomeArquivo)) {
            unlink($pastaTempDestino . $nomeArquivo);
            return true;
        }
    }
    return false;
}

// verifica se é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Erro: Método de requisição inválido.");
}

// Captura e sanitiza o nome do projeto ANTES de tudo
$nomeProjeto = trim($_POST['nome-projeto'] ?? '');
 
if (empty($nomeProjeto)) {
    die("Erro: Nome do projeto é obrigatório.");
}

// Sanitiza o nome do projeto para criar o nome da pasta
$nomeProjetoSanitizado = preg_replace('/[^a-zA-Z0-9_-]/', '_', $nomeProjeto);
$nomeProjetoSanitizado = preg_replace('/_+/', '_', $nomeProjetoSanitizado);
$nomeProjetoSanitizado = trim($nomeProjetoSanitizado, '_');

$idProjeto = isset($_POST['id-projeto']) ? (int) $_POST['id-projeto'] : 0;
$modoEdicao = $idProjeto > 0;
$idPessoaLogado = $_SESSION['idPessoa'] ?? null;
$dadosProjetoAnterior = null;
$pastaProjetoExistente = null;

// Verifica se é modo de edição
if ($modoEdicao) {
    // Buscar dados anteriores do projeto
    $stmt = $conn->prepare("SELECT capa, banner FROM projeto WHERE idProjeto = ?");
    $stmt->bind_param("i", $idProjeto);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $dadosProjetoAnterior = $resultado->fetch_assoc();
    $stmt->close();

    // Corrige: obtém o nome da pasta a partir do nome do arquivo anterior (sem extensão)
    if (!empty($dadosProjetoAnterior['capa'])) {
        $pastaProjetoExistente = pathinfo($dadosProjetoAnterior['capa'], PATHINFO_FILENAME);
    } elseif (!empty($dadosProjetoAnterior['banner'])) {
        $pastaProjetoExistente = pathinfo($dadosProjetoAnterior['banner'], PATHINFO_FILENAME);
    } else {
        $pastaProjetoExistente = null;
    }

    // Define o nome da pasta a ser usada (mantém a antiga se existir)
    $nomeProjetoPasta = $pastaProjetoExistente ?: $nomeProjetoSanitizado;

    // Caminho final para salvar as imagens
    $pastaImagensProjeto = $pastaBaseImagens . $nomeProjetoPasta . '/';

    // Garante que a pasta exista
    if (!is_dir($pastaImagensProjeto)) {
        mkdir($pastaImagensProjeto, 0777, true);
    }

    // Processa o banner
    if (isset($_FILES['banner']) && $_FILES['banner']['error'] !== UPLOAD_ERR_NO_FILE) {
        $nomeBannerArquivo = salvarImagemTemp('banner', $pastaTempImagens);
        $nomeBanner = $nomeBannerArquivo ? $nomeProjetoPasta : $dadosProjetoAnterior['banner'];
    } else {
        $nomeBanner = $dadosProjetoAnterior['banner'];
    }

    // Processa a capa
    if (isset($_FILES['capa']) && $_FILES['capa']['error'] !== UPLOAD_ERR_NO_FILE) {
        $nomeCapaArquivo = salvarImagemTemp('capa', $pastaTempImagens);
        $nomeCapa = $nomeCapaArquivo ? $nomeProjetoPasta : $dadosProjetoAnterior['capa'];
    } else {
        $nomeCapa = $dadosProjetoAnterior['capa'];
    }
} else {

// Cadastro novo: cria pasta baseada no nome sanitizado
$nomeProjetoPasta = $nomeProjetoSanitizado;
$pastaImagensProjeto = $pastaBaseImagens . $nomeProjetoPasta . '/';

if (!is_dir($pastaImagensProjeto)) {
    mkdir($pastaImagensProjeto, 0777, true);
}
}

$pastaImagensProjeto = $pastaBaseImagens . $nomeProjetoPasta . '/';

if (!is_dir($pastaTempImagens)) {
    mkdir($pastaTempImagens, 0777, true);
}

limparPastaTemp($pastaTempImagens);

// Processamento das imagens
$nomeBannerArquivo = null;
$nomeCapaArquivo = null;
$nomeBanner = null;
$nomeCapa = null;

if ($modoEdicao) {
    // Em modo de edição, usar os dados anteriores como base
    $nomeBanner = $dadosProjetoAnterior['banner'];
    $nomeCapa = $dadosProjetoAnterior['capa'];

    // Processar novas imagens apenas se foram enviadas
    if (isset($_FILES['banner']) && $_FILES['banner']['error'] === UPLOAD_ERR_OK) {
        $nomeBannerArquivo = salvarImagemTemp('banner', $pastaTempImagens);
        if ($nomeBannerArquivo) {
            $nomeBanner = $nomeProjetoPasta;
        }
    }

    if (isset($_FILES['capa']) && $_FILES['capa']['error'] === UPLOAD_ERR_OK) {
        $nomeCapaArquivo = salvarImagemTemp('capa', $pastaTempImagens);
        if ($nomeCapaArquivo) {
            $nomeCapa = $nomeProjetoPasta;
        }
    }
} else {
    // Em novo cadastro
    $nomeBannerArquivo = salvarImagemTemp('banner', $pastaTempImagens);
    $nomeCapaArquivo = salvarImagemTemp('capa', $pastaTempImagens);
    
    if (!$nomeCapaArquivo) {
        die("Erro: É necessário adicionar uma capa para o projeto.");
    }
    
    $nomeBanner = $nomeBannerArquivo ? $nomeProjetoPasta : null;
    $nomeCapa = $nomeProjetoPasta;
}

// Adicionar esta função de debug logo após o trecho acima
function debugImagemStatus($prefix, $arquivo, $modo) {
    error_log(sprintf(
        "%s - Arquivo: %s, Modo: %s, FILES: %s",
        $prefix,
        $arquivo,
        $modo,
        print_r($_FILES, true)
    ));
}

// Chamar a função de debug
debugImagemStatus('Status Imagens', $nomeCapaArquivo, $modoEdicao ? 'edicao' : 'novo');

$tipo = $_POST["eixo"] ?? '';
$categoriaOriginal = $_POST["categoria"] ?? '';

$categoriasMap = [
    'ciencias_naturais' => 'ciencias-naturais',
    'ciencias_humanas' => 'ciencias-humanas',
    'linguagens' => 'linguagens',
    'matematica' => 'matematica',
    'administracao' => 'administracao',
    'informatica' => 'informatica',
    'vestuario' => 'vestuario',
    'moda' => 'moda',
    'Ciências Naturais' => 'ciencias-naturais',
    'Ciências Humanas' => 'ciencias-humanas',
    'Linguagens' => 'linguagens',
    'Matemática' => 'matematica',
    'Administração' => 'administracao',
    'Informática' => 'informatica',
    'Vestuário' => 'vestuario',
    'Moda' => 'moda'
];

$categoria = $categoriasMap[$categoriaOriginal] ?? strtolower(str_replace(' ', '-', $categoriaOriginal));

$anoAtual = (int) date('Y');
$anoInicioRaw = trim($_POST["ano-inicio"] ?? '');
$anoInicio = $modoEdicao && isset($dadosProjetoAnterior['anoInicio']) ? (int) $dadosProjetoAnterior['anoInicio'] : $anoAtual;

if (!empty($anoInicioRaw)) {
    preg_match('/\d{4}/', $anoInicioRaw, $matches);
    if (!empty($matches)) {
        $anoTemp = (int) $matches[0];
        if ($anoTemp >= 2010 && $anoTemp <= $anoAtual) {
            $anoInicio = $anoTemp;
        }
    }
}

$txtLinkInscricao = trim($_POST["txt-link-inscricao"] ?? '');
$txtSobre = trim($_POST["descricao"] ?? '');
$txtLinkSite = trim($_POST["site-projeto"] ?? '');
$txtLinkBolsista = trim($_POST["link-bolsista"] ?? '');
$email = trim($_POST["email"] ?? '');
$numeroTelefone = trim($_POST["numero-telefone"] ?? '');
$instagram = trim($_POST["instagram"] ?? '');

$coordenadoresIdsRaw = $_POST['coordenadores_ids'] ?? '';
$bolsistasIdsRaw = $_POST['bolsistas_ids'] ?? '';
$voluntariosIdsRaw = $_POST['voluntarios_ids'] ?? '';

$coordenadoresArray = [];
foreach (array_filter(array_map('trim', explode(',', $coordenadoresIdsRaw))) as $idCoordenador) {
    if ($idCoordenador !== '' && ctype_digit($idCoordenador)) {
        $coordenadoresArray[] = (int) $idCoordenador;
    }
}
$coordenadoresArray = array_values(array_unique($coordenadoresArray));

if ($modoEdicao && $idPessoaLogado && !in_array((int) $idPessoaLogado, $coordenadoresArray, true)) {
    $coordenadoresArray[] = (int) $idPessoaLogado;
}

$bolsistasArray = [];
foreach (array_filter(array_map('trim', explode(',', $bolsistasIdsRaw))) as $idBolsista) {
    if ($idBolsista !== '' && ctype_digit($idBolsista)) {
        $bolsistasArray[] = (int) $idBolsista;
    }
}
$bolsistasArray = array_values(array_unique($bolsistasArray));

$voluntariosArray = [];
foreach (array_filter(array_map('trim', explode(',', $voluntariosIdsRaw))) as $idVoluntario) {
    if ($idVoluntario !== '' && ctype_digit($idVoluntario)) {
        $voluntariosArray[] = (int) $idVoluntario;
    }
}
$voluntariosArray = array_values(array_unique($voluntariosArray));

if (empty($tipo)) {
    die("Erro: Tipo de projeto é obrigatório.");
}

if (empty($categoria)) {
    die("Erro: Categoria é obrigatória.");
}

if (count($coordenadoresArray) === 0) {
    die("Erro: selecione ao menos um coordenador para o projeto.");
}

$result = $conn->query("DESCRIBE projeto");
if (!$result) {
    die("Erro: Tabela 'projeto' não encontrada. " . $conn->error);
}

if ($modoEdicao) {
    $stmt = $conn->prepare("UPDATE projeto SET nome = ?, tipo = ?, categoria = ?, anoInicio = ?, linkParaInscricao = ?, textoSobre = ?, linkSite = ?, email = ?, numero = ?, linkInstagram = ?, capa = ?, banner = ?, linkBolsista = ? WHERE idProjeto = ?");
    if (!$stmt) {
        die("Erro na preparação da consulta de atualização: " . $conn->error);
    }

    $stmt->bind_param(
        "sssisssssssssi",
        $nomeProjeto,
        $tipo,
        $categoria,
        $anoInicio,
        $txtLinkInscricao,
        $txtSobre,
        $txtLinkSite,
        $email,
        $numeroTelefone,
        $instagram,
        $nomeCapa,
        $nomeBanner,
        $txtLinkBolsista,
        $idProjeto
    );
} else {
    $stmt = $conn->prepare("INSERT INTO projeto (nome, tipo, categoria, anoInicio, linkParaInscricao, textoSobre, linkSite, email, numero, linkInstagram, capa, banner, linkBolsista) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        die("Erro na preparação da consulta de inserção: " . $conn->error);
    }

    $stmt->bind_param(
        "sssisssssssss",
        $nomeProjeto,
        $tipo,
        $categoria,
        $anoInicio,
        $txtLinkInscricao,
        $txtSobre,
        $txtLinkSite,
        $email,
        $numeroTelefone,
        $instagram,
        $nomeCapa,
        $nomeBanner,
        $txtLinkBolsista
    );
}

if ($stmt->execute()) {
    $idProjetoExecutado = $modoEdicao ? $idProjeto : $conn->insert_id;

    $pastaFinalProjeto = $pastaBaseImagens . $nomeProjetoPasta . '/';
    if (!is_dir($pastaFinalProjeto)) {
        mkdir($pastaFinalProjeto, 0777, true);
    }

    if ($nomeBannerArquivo) {
        moverImagensParaProjeto($nomeBannerArquivo, $pastaTempImagens, $pastaFinalProjeto);
    }
    if ($nomeCapaArquivo) {
        moverImagensParaProjeto($nomeCapaArquivo, $pastaTempImagens, $pastaFinalProjeto);
    }

    limparPastaTemp($pastaTempImagens);

    if ($modoEdicao) {
        // NOVO: Antes de excluir os vínculos, identificar bolsistas e voluntários que serão removidos
        $stmtPessoasAntigas = $conn->prepare("
            SELECT pp.idPessoa, pp.tipoPessoa, p.tipo as tipoAtualPessoa
            FROM pessoa_projeto pp
            INNER JOIN pessoa p ON p.idPessoa = pp.idPessoa
            WHERE pp.idProjeto = ? 
            AND pp.tipoPessoa IN ('bolsista', 'voluntario')
            AND p.tipo IN ('bolsista', 'voluntario')
        ");
        
        $pessoasAntigas = [];
        if ($stmtPessoasAntigas) {
            $stmtPessoasAntigas->bind_param('i', $idProjetoExecutado);
            $stmtPessoasAntigas->execute();
            $resultPessoasAntigas = $stmtPessoasAntigas->get_result();
            
            while ($rowPessoaAntiga = $resultPessoasAntigas->fetch_assoc()) {
                $pessoasAntigas[] = [
                    'idPessoa' => (int)$rowPessoaAntiga['idPessoa'],
                    'tipoPessoa' => $rowPessoaAntiga['tipoPessoa'],
                    'tipoAtualPessoa' => $rowPessoaAntiga['tipoAtualPessoa']
                ];
            }
            $stmtPessoasAntigas->close();
        }
        
        // Excluir todos os vínculos antigos
        $stmtDelete = $conn->prepare("DELETE FROM pessoa_projeto WHERE idProjeto = ?");
        if ($stmtDelete) {
            $stmtDelete->bind_param('i', $idProjetoExecutado);
            $stmtDelete->execute();
            $stmtDelete->close();
        }
        
        // NOVO: Identificar quem foi desvinculado e reverter tipo
        $pessoasNovasIds = array_merge($bolsistasArray, $voluntariosArray);
        
        foreach ($pessoasAntigas as $pessoaAntiga) {
            $idPessoaAntiga = $pessoaAntiga['idPessoa'];
            $tipoAtualPessoa = $pessoaAntiga['tipoAtualPessoa'];
            
            // Se a pessoa NÃO está na nova lista de bolsistas/voluntários
            if (!in_array($idPessoaAntiga, $pessoasNovasIds)) {
                // Reverter SEMPRE para aluno (bolsista e voluntário só podem estar em 1 projeto)
                if ($tipoAtualPessoa === 'bolsista' || $tipoAtualPessoa === 'voluntario') {
                    $stmtReverter = $conn->prepare("UPDATE pessoa SET tipo = 'aluno' WHERE idPessoa = ?");
                    if ($stmtReverter) {
                        $stmtReverter->bind_param('i', $idPessoaAntiga);
                        if ($stmtReverter->execute()) {
                            error_log("✅ Pessoa ID {$idPessoaAntiga} revertida para 'aluno' após ser desvinculada do projeto {$idProjetoExecutado}");
                        }
                        $stmtReverter->close();
                    }
                }
            }
        }
    } // <-- FECHA O if ($modoEdicao)

    // Array para rastrear pessoas já adicionadas e evitar duplicatas
    $pessoasJaAdicionadas = [];

    // Inserir Coordenadores
    foreach ($coordenadoresArray as $coordenadorId) {
        if (in_array($coordenadorId, $pessoasJaAdicionadas)) {
            continue;
        }
        
        $stmtPessoa = $conn->prepare("INSERT INTO pessoa_projeto (idPessoa, idProjeto, tipoPessoa) VALUES (?, ?, 'coordenador')");
        if ($stmtPessoa) {
            $stmtPessoa->bind_param('ii', $coordenadorId, $idProjetoExecutado);
            if ($stmtPessoa->execute()) {
                $pessoasJaAdicionadas[] = $coordenadorId;
            }
            $stmtPessoa->close();
        }
    }

    // Inserir Bolsistas
    foreach ($bolsistasArray as $bolsistaId) {
        if (in_array($bolsistaId, $pessoasJaAdicionadas)) {
            continue;
        }
        
        // Verificar e atualizar tipo de usuário
        $stmtVerificaTipo = $conn->prepare("SELECT tipo FROM pessoa WHERE idPessoa = ?");
        if ($stmtVerificaTipo) {
            $stmtVerificaTipo->bind_param('i', $bolsistaId);
            $stmtVerificaTipo->execute();
            $resultado = $stmtVerificaTipo->get_result();
            $usuarioAtual = $resultado->fetch_assoc();
            $stmtVerificaTipo->close();

            // Se for aluno ou voluntario, atualiza para bolsista
            if ($usuarioAtual && ($usuarioAtual['tipo'] === 'aluno' || $usuarioAtual['tipo'] === 'voluntario')) {
                $stmtAtualizaTipo = $conn->prepare("UPDATE pessoa SET tipo = 'bolsista' WHERE idPessoa = ?");
                if ($stmtAtualizaTipo) {
                    $stmtAtualizaTipo->bind_param('i', $bolsistaId);
                    $stmtAtualizaTipo->execute();
                    $stmtAtualizaTipo->close();
                    
                    error_log("Pessoa ID {$bolsistaId} atualizada para 'bolsista' no projeto {$idProjetoExecutado}");
                }
            }
        }

        // Inserir na tabela pessoa_projeto
        $stmtPessoa = $conn->prepare("INSERT INTO pessoa_projeto (idPessoa, idProjeto, tipoPessoa) VALUES (?, ?, 'bolsista')");
        if ($stmtPessoa) {
            $stmtPessoa->bind_param('ii', $bolsistaId, $idProjetoExecutado);
            if ($stmtPessoa->execute()) {
                $pessoasJaAdicionadas[] = $bolsistaId;
            }
            $stmtPessoa->close();
        }
    }

    // Inserir Voluntários
    foreach ($voluntariosArray as $voluntarioId) {
        if (in_array($voluntarioId, $pessoasJaAdicionadas)) {
            continue;
        }
        
        // Verificar e atualizar tipo de usuário
        $stmtVerificaTipo = $conn->prepare("SELECT tipo FROM pessoa WHERE idPessoa = ?");
        if ($stmtVerificaTipo) {
            $stmtVerificaTipo->bind_param('i', $voluntarioId);
            $stmtVerificaTipo->execute();
            $resultado = $stmtVerificaTipo->get_result();
            $usuarioAtual = $resultado->fetch_assoc();
            $stmtVerificaTipo->close();

            // Se for aluno ou bolsista, atualiza para voluntario
            if ($usuarioAtual && ($usuarioAtual['tipo'] === 'aluno' || $usuarioAtual['tipo'] === 'bolsista')) {
                $stmtAtualizaTipo = $conn->prepare("UPDATE pessoa SET tipo = 'voluntario' WHERE idPessoa = ?");
                if ($stmtAtualizaTipo) {
                    $stmtAtualizaTipo->bind_param('i', $voluntarioId);
                    $stmtAtualizaTipo->execute();
                    $stmtAtualizaTipo->close();
                    
                    error_log("Pessoa ID {$voluntarioId} atualizada para 'voluntario' no projeto {$idProjetoExecutado}");
                }
            }
        }

        // Inserir na tabela pessoa_projeto
        $stmtPessoa = $conn->prepare("INSERT INTO pessoa_projeto (idPessoa, idProjeto, tipoPessoa) VALUES (?, ?, 'voluntario')");
        if ($stmtPessoa) {
            $stmtPessoa->bind_param('ii', $voluntarioId, $idProjetoExecutado);
            if ($stmtPessoa->execute()) {
                $pessoasJaAdicionadas[] = $voluntarioId;
            }
            $stmtPessoa->close();
        }
    }

    $mensagem = $modoEdicao ? 'Projeto atualizado com sucesso!' : 'Projeto cadastrado com sucesso!';
    echo "<div style='color: green; font-weight: bold;'>✅ {$mensagem}</div>";
    echo "<script>alert('{$mensagem}'); window.location.href='menuEditProjetos.php';</script>";
} else {
    limparPastaTemp($pastaTempImagens);
    $acao = $modoEdicao ? 'atualizar' : 'cadastrar';
    echo "<div style='color: red; font-weight: bold;'>❌ Erro ao {$acao} projeto:</div>";
    echo "<p>Erro MySQL: " . $stmt->error . "</p>";
    echo "<p>Errno: " . $stmt->errno . "</p>";
}

$stmt->close();
$conn->close();
?>

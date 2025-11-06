<?php
    session_start();

    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // banco de dados
    $host = 'localhost';
    $usuario = 'root';
    $senha = 'Gui@15600';
    //$senha = 'root';
    $banco = 'website';

    // conexão com o banco
    $conn = new mysqli($host, $usuario, $senha, $banco);
    if ($conn->connect_error) {
        die("Erro na conexão: " . $conn->connect_error);
    }

    $conn->set_charset("utf8");

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
        if (isset($_FILES[$campoArquivo]) && $_FILES[$campoArquivo]['error'] === UPLOAD_ERR_OK) {
            $nomeTemp = $_FILES[$campoArquivo]['tmp_name'];
            $nomeOriginal = basename($_FILES[$campoArquivo]['name']);
            
            // tipo de arquivo
            $tiposPermitidos = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $extensao = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));
            if (!in_array($extensao, $tiposPermitidos)) {
                return null;
            }
            
            // Define o nome do arquivo baseado no tipo (capa ou banner)
            $nomeFinal = ($campoArquivo === 'capa' ? 'capa' : 'banner') . '.' . $extensao;
            $caminhoTempCompleto = $pastaTempDestino . $nomeFinal;
            
            // cria a pasta temporária se não existir
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
        }
        return null;
    }

    // Função para mover imagens da pasta temporária para a pasta final do projeto
    function moverImagensParaProjeto($nomeArquivo, $pastaTempDestino, $pastaFinalDestino) {
        if ($nomeArquivo && file_exists($pastaTempDestino . $nomeArquivo)) {
            if (!is_dir($pastaFinalDestino)) {
                mkdir($pastaFinalDestino, 0777, true);
            }
                if (preg_match('/^(capa|banner)\./', $nomeArquivo, $matches)) {
                    $prefixo = $matches[1];
                    $existentes = glob($pastaFinalDestino . $prefixo . '.*');
                    if ($existentes) {
                        foreach ($existentes as $arquivoExistente) {
                            @unlink($arquivoExistente);
                        }
                    }
                }

                return rename(
                    $pastaTempDestino . $nomeArquivo,
                    $pastaFinalDestino . $nomeArquivo
                );
        }
        return false;
    }

    // verifica se é POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        die("Erro: Método de requisição inválido.");
    }

    $idProjeto = isset($_POST['id-projeto']) ? (int) $_POST['id-projeto'] : 0;
    $modoEdicao = $idProjeto > 0;
    $idPessoaLogado = $_SESSION['idPessoa'] ?? null;
    $dadosProjetoAnterior = null;
    $pastaProjetoExistente = null;

    if ($modoEdicao) {
        if (!$idPessoaLogado) {
            die("Erro: usuário não autenticado.");
        }

        $stmtProjeto = $conn->prepare("SELECT p.* FROM projeto p INNER JOIN pessoa_projeto pp ON pp.idProjeto = p.idProjeto AND pp.tipoPessoa = 'coordenador' WHERE p.idProjeto = ? AND pp.idPessoa = ?");
        if (!$stmtProjeto) {
            die("Erro interno ao preparar consulta de projeto: " . $conn->error);
        }

        $stmtProjeto->bind_param('ii', $idProjeto, $idPessoaLogado);
        $stmtProjeto->execute();
        $resultadoProjeto = $stmtProjeto->get_result();
        if (!$resultadoProjeto || $resultadoProjeto->num_rows === 0) {
            $stmtProjeto->close();
            die("Erro: projeto não encontrado ou você não possui permissão para editá-lo.");
        }

        $dadosProjetoAnterior = $resultadoProjeto->fetch_assoc();
        $pastaProjetoExistente = $dadosProjetoAnterior['capa'] ?? $dadosProjetoAnterior['banner'] ?? null;
        $stmtProjeto->close();
    }

    $nomeProjeto = trim($_POST["nome-projeto"] ?? '');
    $nomeProjetoSanitizado = preg_replace('/[^a-zA-Z0-9_-]/', '_', strtolower($nomeProjeto));
    if ($nomeProjetoSanitizado === '') {
        $nomeProjetoSanitizado = 'projeto_' . ($modoEdicao ? $idProjeto : time());
    }

    $nomeProjetoPasta = $modoEdicao ? ($pastaProjetoExistente ?: $nomeProjetoSanitizado) : $nomeProjetoSanitizado;
    $pastaImagensProjeto = $pastaBaseImagens . $nomeProjetoPasta . '/';

    if (!is_dir($pastaTempImagens)) {
        mkdir($pastaTempImagens, 0777, true);
    }

    limparPastaTemp($pastaTempImagens);

    $nomeBannerArquivo = salvarImagemTemp('banner', $pastaTempImagens);
    $nomeCapaArquivo = salvarImagemTemp('capa', $pastaTempImagens);

    $nomeBanner = $modoEdicao ? ($dadosProjetoAnterior['banner'] ?? null) : null;
    $nomeCapa = $modoEdicao ? ($dadosProjetoAnterior['capa'] ?? null) : null;

    if ($nomeBannerArquivo) {
        $nomeBanner = $nomeProjetoPasta;
    }
    if ($nomeCapaArquivo) {
        $nomeCapa = $nomeProjetoPasta;
    }

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

    if (empty($nomeProjeto)) {
        die("Erro: Nome do projeto é obrigatório.");
    }

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
            $stmtDelete = $conn->prepare("DELETE FROM pessoa_projeto WHERE idProjeto = ?");
            if ($stmtDelete) {
                $stmtDelete->bind_param('i', $idProjetoExecutado);
                $stmtDelete->execute();
                $stmtDelete->close();
            }
        }

        foreach ($coordenadoresArray as $coordenadorId) {
            $stmtPessoa = $conn->prepare("INSERT INTO pessoa_projeto (idPessoa, idProjeto, tipoPessoa) VALUES (?, ?, 'coordenador')");
            if ($stmtPessoa) {
                $stmtPessoa->bind_param('ii', $coordenadorId, $idProjetoExecutado);
                $stmtPessoa->execute();
                $stmtPessoa->close();
            }
        }

        foreach ($bolsistasArray as $bolsistaId) {
            $stmtPessoa = $conn->prepare("INSERT INTO pessoa_projeto (idPessoa, idProjeto, tipoPessoa) VALUES (?, ?, 'bolsista')");
            if ($stmtPessoa) {
                $stmtPessoa->bind_param('ii', $bolsistaId, $idProjetoExecutado);
                $stmtPessoa->execute();
                $stmtPessoa->close();
            }
        }

        $mensagem = $modoEdicao ? 'Projeto atualizado com sucesso!' : 'Projeto cadastrado com sucesso!';
        echo "<div style='color: green; font-weight: bold;'>✅ {$mensagem}</div>";
        echo "<script>alert('{$mensagem}'); window.location.href='menuProjetos.php';</script>";
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
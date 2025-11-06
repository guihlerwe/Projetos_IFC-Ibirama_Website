<?php
    session_start();

    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // banco de dados
    $host = 'localhost';
    $usuario = 'root';
    //$senha = 'Gui@15600';
    $senha = 'root';
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

    $idProjeto = isset($_POST['id-projeto']) ? (int) $_POST['id-projeto'] : 0;
    $modoEdicao = $idProjeto > 0;
    $idPessoaLogado = $_SESSION['idPessoa'] ?? null;
    $dadosProjetoAnterior = null;
    $pastaProjetoExistente = null;

    // Verifica se é modo de edição
    if ($modoEdicao) {
        // Buscar dados anteriores do projeto
        $stmt = $conn->prepare("SELECT capa, banner FROM projetos WHERE idProjeto = ?");
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
            // Primeiro verifica o tipo atual do usuário
            $stmtVerificaTipo = $conn->prepare("SELECT tipo FROM pessoa WHERE idPessoa = ?");
            if ($stmtVerificaTipo) {
                $stmtVerificaTipo->bind_param('i', $bolsistaId);
                $stmtVerificaTipo->execute();
                $resultado = $stmtVerificaTipo->get_result();
                $usuarioAtual = $resultado->fetch_assoc();
                $stmtVerificaTipo->close();

                // Se for aluno, atualiza para bolsista
                if ($usuarioAtual && $usuarioAtual['tipo'] === 'aluno') {
                    $stmtAtualizaTipo = $conn->prepare("UPDATE pessoa SET tipo = 'bolsista' WHERE idPessoa = ?");
                    if ($stmtAtualizaTipo) {
                        $stmtAtualizaTipo->bind_param('i', $bolsistaId);
                        $stmtAtualizaTipo->execute();
                        $stmtAtualizaTipo->close();
                    }
                }
            }

            // Insere na tabela pessoa_projeto
            $stmtPessoa = $conn->prepare("INSERT INTO pessoa_projeto (idPessoa, idProjeto, tipoPessoa) VALUES (?, ?, 'bolsista')");
            if ($stmtPessoa) {
                $stmtPessoa->bind_param('ii', $bolsistaId, $idProjetoExecutado);
                $stmtPessoa->execute();
                $stmtPessoa->close();
            }
        }

        $mensagem = $modoEdicao ? 'Projeto atualizado com sucesso!' : 'Projeto cadastrado com sucesso!';
        echo "<div style='color: green; font-weight: bold;'>✅ {$mensagem}</div>";
        echo "<script>alert('{$mensagem}'); window.location.href='menuEditarProjetos.php';</script>";
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
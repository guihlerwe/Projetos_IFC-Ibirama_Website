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
            
            // nome único para as imagens
            $nomeFinal = uniqid() . '-' . time() . '.' . $extensao;
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

    // cria uma pasta única para o projeto (usa nome do projeto ou um id único)
    $nomeProjeto = trim($_POST["nome-projeto"] ?? '');
    $nomeProjetoPasta = preg_replace('/[^a-zA-Z0-9_-]/', '_', strtolower($nomeProjeto));
    $pastaImagensProjeto = $pastaBaseImagens . $nomeProjetoPasta . '/';
    // Criar pasta temporária se não existir
    if (!is_dir($pastaTempImagens)) {
        mkdir($pastaTempImagens, 0777, true);
    }
    
    // Limpar arquivos antigos da pasta temporária
    limparPastaTemp($pastaTempImagens);

    // processa uploads de imagens para pasta temporária
    $nomeBannerArquivo = salvarImagemTemp('banner', $pastaTempImagens);
    $nomeCapaArquivo = salvarImagemTemp('capa', $pastaTempImagens);
    
    // prepara nomes dos arquivos para o banco (caminho relativo a partir de assets/photos/projetos/)
    $nomeBanner = $nomeBannerArquivo ? ('projetos/' . $nomeProjetoPasta . '/' . $nomeBannerArquivo) : null;
    $nomeCapa = $nomeCapaArquivo ? ('projetos/' . $nomeProjetoPasta . '/' . $nomeCapaArquivo) : null;

    // captura dados do formulário com validação
    // $nomeProjeto já definido acima
    $tipo = $_POST["eixo"] ?? '';

    //  categoria IMEDIATAMENTE após receber do formulário
    $categoriaOriginal = $_POST["categoria"] ?? '';

    // Mapear valores do formulário para os valores do ENUM
    // Mapear valores do formulário para os valores do ENUM
    $categoriasMap = [
        'ciencias_naturais' => 'ciencias-naturais',      // underscore → hífen
        'ciencias_humanas' => 'ciencias-humanas',        // underscore → hífen
        'linguagens' => 'linguagens',                    // igual
        'matematica' => 'matematica',                    // igual
        'administracao' => 'administracao',              // igual
        'informatica' => 'informatica',                  // igual
        'vestuario' => 'vestuario',                      // igual
        'moda' => 'moda',                                // igual
        
        // Manter também os valores com acentos caso venham do formulário
        'Ciências Naturais' => 'ciencias-naturais',
        'Ciências Humanas' => 'ciencias-humanas',
        'Linguagens' => 'linguagens',
        'Matemática' => 'matematica',
        'Administração' => 'administracao',
        'Informática' => 'informatica',
        'Vestuário' => 'vestuario',
        'Moda' => 'moda'
    ];

    // Converter o valor recebido para o formato do banco
    $categoria = $categoriasMap[$categoriaOriginal] ?? strtolower(str_replace(' ', '-', $categoriaOriginal));

    // Tratamento do ano de início
    $anoAtual = (int)date('Y');
    $anoInicioRaw = trim($_POST["ano-inicio"] ?? '');
    $anoInicio = $anoAtual; // valor padrão é o ano atual
    
    if (!empty($anoInicioRaw)) {
        // extrai apenas números da variável ano-inicio
        preg_match('/\d{4}/', $anoInicioRaw, $matches);
        if (!empty($matches)) {
            $anoTemp = (int)$matches[0];
            // valida se é um ano válido (entre 2010 e ano atual)
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
    $nomeCoordenador = trim($_POST["nome-coordenador"] ?? '');
    $nomeBolsista = trim($_POST["nome-bolsista"] ?? '');

    // validações das variaveis obrigatórias
    if (empty($nomeProjeto)) {
        die("Erro: Nome do projeto é obrigatório.");
    }

    if (empty($tipo)) {
        die("Erro: Tipo de projeto é obrigatório.");
    }

    if (empty($categoria)) {
        die("Erro: Categoria é obrigatória.");
    }

    // Verificar se a tabela existe e sua estrutura
    $result = $conn->query("DESCRIBE projeto");
    if (!$result) {
        die("Erro: Tabela 'projeto' não encontrada. " . $conn->error);
    }

    // prepara e executa a consulta
    $stmt = $conn->prepare("
        INSERT INTO projeto 
        (nome, tipo, categoria, anoInicio, linkParaInscricao, textoSobre, linkSite, email, numero, linkInstagram, capa, banner, linkBolsista) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        die("Erro na preparação da consulta: " . $conn->error);
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

    if ($stmt->execute()) {
        $idProjeto = $conn->insert_id;
        
        // Criar pasta final do projeto
        $pastaFinalProjeto = $pastaBaseImagens . $nomeProjetoPasta . '/';
        if (!is_dir($pastaFinalProjeto)) {
            mkdir($pastaFinalProjeto, 0777, true);
        }

        // Mover imagens da pasta temporária para a pasta final
        if ($nomeBannerArquivo) {
            moverImagensParaProjeto($nomeBannerArquivo, $pastaTempImagens, $pastaFinalProjeto);
        }
        if ($nomeCapaArquivo) {
            moverImagensParaProjeto($nomeCapaArquivo, $pastaTempImagens, $pastaFinalProjeto);
        }

        // Salvar relação com coordenador e bolsista na tabela pessoa_projeto
        $coordenadorId = $_POST['coordenador_id'] ?? null;
        $bolsistaId = $_POST['bolsista_id'] ?? null;
        if ($coordenadorId) {
            $stmtPessoa = $conn->prepare("INSERT INTO pessoa_projeto (idPessoa, idProjeto, tipoPessoa) VALUES (?, ?, 'coordenador')");
            $stmtPessoa->bind_param("ii", $coordenadorId, $idProjeto);
            $stmtPessoa->execute();
            $stmtPessoa->close();
        }
        if ($bolsistaId) {
            $stmtPessoa = $conn->prepare("INSERT INTO pessoa_projeto (idPessoa, idProjeto, tipoPessoa) VALUES (?, ?, 'bolsista')");
            $stmtPessoa->bind_param("ii", $bolsistaId, $idProjeto);
            $stmtPessoa->execute();
            $stmtPessoa->close();
        }

        // Limpar pasta temporária
        limparPastaTemp($pastaTempImagens);

        echo "<div style='color: green; font-weight: bold;'>✅ Projeto cadastrado com sucesso!</div>";
        echo "<script>alert('Projeto cadastrado com sucesso!'); window.location.href='principal.php';</script>";
    } else {
        // Se falhou, limpar arquivos temporários
        limparPastaTemp($pastaTempImagens);
        
        echo "<div style='color: red; font-weight: bold;'>❌ Erro ao cadastrar projeto:</div>";
        echo "<p>Erro MySQL: " . $stmt->error . "</p>";
        echo "<p>Errno: " . $stmt->errno . "</p>";
    }

    $stmt->close();
    $conn->close();
?>
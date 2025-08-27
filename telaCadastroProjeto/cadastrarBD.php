<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// banco de dados
$host = 'localhost';
$usuario = 'root';
$senha = 'Gui@15600';
$banco = 'website';

// conexão com o banco
$conn = new mysqli($host, $usuario, $senha, $banco);
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

$conn->set_charset("utf8");

// pasta que guarda as imagens
$pastaImagens = '../telaPrincipal/img/';

// função que salva as imagens
function salvarImagem($campoArquivo, $pastaDestino) {
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
        $caminhoCompleto = $pastaDestino . $nomeFinal;

        if (move_uploaded_file($nomeTemp, $caminhoCompleto)) {
            return $nomeFinal;
        }
    }
    return null;
}

// verifica se é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Erro: Método de requisição inválido.");
}

// processa uploads de imagens
$nomeBanner = salvarImagem('banner', $pastaImagens);
$nomeCapa = salvarImagem('capa', $pastaImagens);
$fotoCoordenador = salvarImagem('foto-coordenador', $pastaImagens);
$fotoBolsista = salvarImagem('foto-bolsista', $pastaImagens);

// captura dados do formulário com validação
$nomeProjeto = trim($_POST["nome-projeto"] ?? '');
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

$anoInicioRaw = trim($_POST["ano-inicio"] ?? '');
$anoInicio = null;
if (!empty($anoInicioRaw)) {
    // extrai apenas números da variável ano-inicio
    preg_match('/\d{4}/', $anoInicioRaw, $matches);
    if (!empty($matches)) {
        $anoInicio = (int)$matches[0];
        // valida se é um ano válido 
        $anoAtual = date('Y');
        if ($anoInicio < 2010 || $anoInicio > ($anoAtual)) {
            $anoInicio = null;
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
    (nome, tipo, categoria, anoInicio, linkParaInscricao, textoSobre, linkSite, email, numero, linkInstagram, capa, banner, nomeCoordenador, fotoCoordenador, nomeBolsista, fotoBolsista, linkBolsista) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

if (!$stmt) {
    die("Erro na preparação da consulta: " . $conn->error);
}

$stmt->bind_param(
    "sssisssssssssssss",
    $nomeProjeto, 
    $tipo, 
    $categoria,  // <- AGORA USA O VALOR JÁ MAPEADO
    $anoInicio,  
    $txtLinkInscricao,
    $txtSobre, 
    $txtLinkSite, 
    $email, 
    $numeroTelefone, 
    $instagram, 
    $nomeCapa, 
    $nomeBanner,
    $nomeCoordenador,
    $fotoCoordenador,
    $nomeBolsista,
    $fotoBolsista,
    $txtLinkBolsista
);

if ($stmt->execute()) {
    echo "<div style='color: green; font-weight: bold;'>✅ Projeto cadastrado com sucesso!</div>";
    
    echo "<script>alert('Projeto cadastrado com sucesso!'); window.location.href='../telaPrincipal/painelCoordenador.php';</script>";
    
} else {
    echo "<div style='color: red; font-weight: bold;'>❌ Erro ao cadastrar projeto:</div>";
    echo "<p>Erro MySQL: " . $stmt->error . "</p>";
    echo "<p>Errno: " . $stmt->errno . "</p>";
}

$stmt->close();
$conn->close();
?>
<?php
session_start();

// Ativar exibição de erros para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuração do banco de dados
$host = 'localhost';
$usuario = 'root';
$senha = 'root';
$banco = 'website';

// Conexão com o banco
$conn = new mysqli($host, $usuario, $senha, $banco);
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

// Configuração do charset
$conn->set_charset("utf8");

// Configuração do diretório de imagens
$pastaImagens = '../telaPrincipal/img/';

// Verificar se o diretório existe, se não, criar
if (!is_dir($pastaImagens)) {
    if (!mkdir($pastaImagens, 0755, true)) {
        die("Erro: Não foi possível criar o diretório de imagens.");
    }
}

// Função para salvar imagens
function salvarImagem($campoArquivo, $pastaDestino) {
    if (isset($_FILES[$campoArquivo]) && $_FILES[$campoArquivo]['error'] === UPLOAD_ERR_OK) {
        $nomeTemp = $_FILES[$campoArquivo]['tmp_name'];
        $nomeOriginal = basename($_FILES[$campoArquivo]['name']);
        
        // Validar tipo de arquivo
        $tiposPermitidos = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $extensao = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));
        
        if (!in_array($extensao, $tiposPermitidos)) {
            return null;
        }
        
        // Gerar nome único
        $nomeFinal = uniqid() . '-' . time() . '.' . $extensao;
        $caminhoCompleto = $pastaDestino . $nomeFinal;

        if (move_uploaded_file($nomeTemp, $caminhoCompleto)) {
            return $nomeFinal;
        }
    }
    return null;
}

// Verificar se é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Erro: Método de requisição inválido.");
}

// Debug: mostrar dados recebidos
echo "<h3>Debug - Dados POST recebidos:</h3>";
echo "<pre>";
print_r($_POST);
echo "</pre>";

echo "<h3>Debug - Arquivos recebidos:</h3>";
echo "<pre>";
print_r($_FILES);
echo "</pre>";

// Processar uploads de imagens
$nomeBanner = salvarImagem('banner', $pastaImagens);
$nomeCapa = salvarImagem('capa', $pastaImagens);
$fotoCoordenador = salvarImagem('foto-coordenador', $pastaImagens);
$fotoBolsista = salvarImagem('foto-bolsista', $pastaImagens);

// Capturar dados do formulário com validação
$nomeProjeto = trim($_POST["nome-projeto"] ?? '');
$tipo = $_POST["eixo"] ?? '';
$categoria = $_POST["categoria"] ?? '';

// Processar ano de início - extrair apenas números
$anoInicioRaw = trim($_POST["ano-inicio"] ?? '');
$anoInicio = null;
if (!empty($anoInicioRaw)) {
    // Extrair apenas números do campo
    preg_match('/\d{4}/', $anoInicioRaw, $matches);
    if (!empty($matches)) {
        $anoInicio = (int)$matches[0];
        // Validar se é um ano válido (entre 1900 e ano atual + 10)
        $anoAtual = date('Y');
        if ($anoInicio < 1900 || $anoInicio > ($anoAtual + 10)) {
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

// Debug: mostrar dados processados
echo "<h3>Debug - Dados processados:</h3>";
echo "Nome: " . $nomeProjeto . "<br>";
echo "Tipo: " . $tipo . "<br>";
echo "Categoria: " . $categoria . "<br>";
echo "Ano: " . $anoInicio . "<br>";
echo "Banner: " . ($nomeBanner ?: 'Não enviado') . "<br>";
echo "Capa: " . ($nomeCapa ?: 'Não enviado') . "<br>";

// Validações básicas
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

echo "<h3>Debug - Estrutura da tabela:</h3>";
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . " (" . $row['Type'] . ")<br>";
}

// Preparar e executar a query
$stmt = $conn->prepare("
    INSERT INTO projeto 
    (nome, tipo, categoria, anoInicio, linkParaInscricao, textoSobre, linkSite, email, numero, linkInstagram, capa, banner, nomeCoordenador, fotoCoordenador, nomeBolsista, fotoBolsista, linkBolsista) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

if (!$stmt) {
    die("Erro na preparação da query: " . $conn->error);
}

// Bind dos parâmetros - usando "i" para integer no anoInicio
$stmt->bind_param(
    "sssisssssssssssss",
    $nomeProjeto, 
    $tipo, 
    $categoria, 
    $anoInicio,  // Este agora é um integer ou null
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

echo "<h3>Executando query...</h3>";

if ($stmt->execute()) {
    echo "<div style='color: green; font-weight: bold;'>✅ Projeto cadastrado com sucesso!</div>";
    echo "<p>ID do projeto: " . $stmt->insert_id . "</p>";
    
    // Remover o redirect para ver se há outros erros
    echo "<p><a href='../telaPainelCoordenador/painelCoordenador.php'>Voltar ao painel</a></p>";
    
    // Descomente a linha abaixo quando estiver funcionando
    // echo "<script>alert('Projeto cadastrado com sucesso!'); window.location.href='../telaPainelCoordenador/painelCoordenador.php';</script>";
} else {
    echo "<div style='color: red; font-weight: bold;'>❌ Erro ao cadastrar projeto:</div>";
    echo "<p>Erro MySQL: " . $stmt->error . "</p>";
    echo "<p>Errno: " . $stmt->errno . "</p>";
}

$stmt->close();
$conn->close();
?>
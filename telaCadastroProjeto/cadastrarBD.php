<?php
$host = 'localhost';
$usuario = 'root';
$senha = 'root';
$banco = 'website';

$conn = new mysqli($host, $usuario, $senha, $banco);
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

// pasta para salvar as imagens
$pastaImagens = '../telaPrincipal/img/';

function salvarImagem($campoArquivo, $pastaDestino) {
    if (isset($_FILES[$campoArquivo]) && $_FILES[$campoArquivo]['error'] === UPLOAD_ERR_OK) {
        $nomeTemp = $_FILES[$campoArquivo]['tmp_name'];
        $nomeOriginal = basename($_FILES[$campoArquivo]['name']);
        $nomeFinal = uniqid() . '-' . $nomeOriginal; // Evita nome repetido
        $caminhoCompleto = $pastaDestino . $nomeFinal;

        if (move_uploaded_file($nomeTemp, $caminhoCompleto)) {
            return $nomeFinal; // Armazenar apenas o nome, não o caminho inteiro
        }
    }
    return null;
}

// salvando as imagens e obtendo nomes
$nomeBanner = salvarImagem('banner', $pastaImagens);
$nomeCapa = salvarImagem('capa', $pastaImagens);

// formulário
$nomeProjeto = $_POST["nome-projeto"];
$tipo = $_POST["eixo"];
$categoria = $_POST["categoria"];
$anoInicio = $_POST["ano-inicio"];
$txtLinkInscricao = $_POST["txt-link-inscricao"];
$txtSobre = $_POST["descricao"];
$txtLinkSite = $_POST["site-projeto"];
$email = $_POST["email"];
$numeroTelefone = $_POST["numero-telefone"];
$instagram = $_POST["instagram"];

// inserção
$stmt = $conn->prepare("INSERT INTO projeto (nome, tipo, categoria, anoInicio, linkParaInscricao, textoSobre, linkSite, email, numero, linkInstagram, capa, banner) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param("ssssssssssss", $nomeProjeto, $tipo, $categoria, $anoInicio, $txtLinkInscricao, $txtSobre, $txtLinkSite, $email, $numeroTelefone, $instagram, $nomeCapa, $nomeBanner);

if ($stmt->execute()) {
    echo "Projeto cadastrado com sucesso! Redirecionando...";
    header("refresh:2; url=../telaPainelCoordenador/painelCoordenador.php");
    exit();
} else {
    echo "Erro ao cadastrar: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>

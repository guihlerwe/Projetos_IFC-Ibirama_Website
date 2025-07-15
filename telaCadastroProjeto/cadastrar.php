<?php
    // conectando com o banco
    $host = 'localhost';
    $usuario = 'root';
    $senha = 'root';
    $banco = 'website';

    $conn = new mysqli($host, $usuario, $senha, $banco);

    // verificando conexão
    if ($conn->connect_error) {
        die("Erro na conexão: " . $conn->connect_error);
    }

    // recebe os dados do formulário
    $nomeProjeto = $_POST["nome-projeto"];
    $eixo = $_POST["eixo"];
    $categoria = $_POST["categoria"];
    $anoInicio = $_POST["ano-inicio"];
    $nomeCoordenador = $_POST["nome-coordenador"];
    $txtLinkInscricao = $_POST["txt-link-inscricao"];
    $txtSobre = $_POST["descricao"];
    $txtLinkSite = $_POST["site-projeto"];
    $email = $_POST["email"];
    $numeroTelefone = $_POST["numero-telefone"];
    $instagram = $_POST['instragram'];

    // inserção
    $stmt = $conn->prepare("INSERT INTO projeto (nomeProjeto, eixo, categoria, anoInicio, nomeCoordenador, txtLinkInscricao, txtSobre, txtLinkSite, email, numeroTelefone, instagram) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssssss", $nomeProjeto, $eixo, $categoria, $anoInicio, $nomeCoordenador, $txtLinkInscricao, $txtSobre, $txtLinkSite, $email, $numeroTelefone, $instagram);

    if ($stmt->execute()) {
        echo "Cadastro realizado com sucesso! Redirecionando...";
        header("refresh:2; url=../telaPainelCoordenador/painelCoordenador.php");
        exit();
    } else {
        echo "Erro ao cadastrar: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
?>
<?php
session_start();

// conexão
$host = 'localhost';
$usuario = 'root';
//$senha = 'Gui@15600';
$senha = 'root';
$banco = 'website';

$conn = new mysqli($host, $usuario, $senha, $banco);

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/tema-global.css"> 
    <link rel="stylesheet" href="assets/css/login.css">
    <title>Entrar</title>
</head>
<body>

    <div class="container">
        <!-- Formulário -->
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
                <div class="login-nav">Entrar</div>
            </div>
        </header>
        

        <!-- Formulário -->
        <div class="container-formulario">
            <div class="divformulario">
                <form action="login.php" method="POST">            
                    <div class="imagem-container">
                        <img src="../assets/photos/campus-image.jpg" id="foto-ifc">
                        <h1 class="titulo-sobre-imagem">Login</h1>
                    </div>
                    <p class="subtitulo">Preencha os dados para entrar.</p>
                    <fieldset class="grupo">
                        <div class="campoEsquerda">
                            <input type="email" name="email" class="campo" id="email" placeholder="E-mail" required>
                        </div>
                        <div class="campoDireita">
                            <input type="password" name="senha" class="campo" id="senha" placeholder="Senha" required>
                        </div>
                    </fieldset>
                    <button type="submit" class="botao">Entrar</button>
                </form>
            </div>
            <div class="cadastrar">
                <p class="cadastro">Ainda não possui cadastro?</p>
                <p class="cadastro-sublinhado"> <a href="cad-usuario.php"> Clique aqui para cadastrar-se!</p>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/global.js"></script>
</body>
</html>

<?php
// Conexão com o banco
$host = 'localhost';
$usuario = 'root';
$senha = 'root';
$banco = 'website';
$conn = new mysqli($host, $usuario, $senha, $banco);

// Verificar conexão
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $senhaDigitada = $_POST['senha'] ?? '';

    // Buscar usuário pelo e-mail
    $sql = "SELECT * FROM pessoa WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $usuario = $resultado->fetch_assoc();

        // Verificar a senha
        if (password_verify($senhaDigitada, $usuario['senha'])) {
            // Login bem-sucedido
            session_start();
            $_SESSION['nome'] = $usuario['nome'];
            $_SESSION['tipo'] = $usuario['tipo'];

            echo "Login realizado com sucesso!";
            header("Location: principal.php");
        } else {
            echo "Senha incorreta!";
        }
    } else {
        echo "E-mail não encontrado!";
    }

    $stmt->close();
    $conn->close();
}
?>

<?php
session_start();

// conexão
$host = 'localhost';
$usuario = 'root';
$senha = 'Gui@15600';
//$senha = 'root';
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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // pegando os dados do formulário
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    // consultando no banco se já existe um usuário cadastrado
    $sql = "SELECT * FROM pessoa WHERE email = ? and senha = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $senha);
    $stmt->execute();
        
    $result = $stmt->get_result();

    if($result->num_rows > 0){
        $usuario = $result->fetch_assoc();

        $_SESSION['idPessoa'] = $usuario['idPessoa'];
        $_SESSION['nome'] = $usuario['nome'];
        $_SESSION['tipo'] = $usuario['tipo'];
        
        if ($usuario['tipo'] == 'coordenador') {
            header("Location: principal.php");
        } elseif ($usuario['tipo'] === 'bolsista') {
            header("Location: principal.php");
        } else {
            header("Location: principal.php");
        }

    } else {
        echo "Usuário não encontrado!";
    }

    $stmt->close();
    $conn->close();
}
?>

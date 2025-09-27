<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["tipo"])) {
    echo '<div id="login-nav">Entrar</div>';
    return;
}

$tipo = $_SESSION["tipo"];

echo '
<div class="menu-dropdown">
    <button class="menu-btn">
        <div class="hamburger-icon">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </button>
    <div class="dropdown-content">
';

if ($tipo === "coordenador") {
    echo '
        <a href="cad-projeto.php">Criar projeto</a>
        <a href="#">Seus projetos</a>
        <a href="usuario.php">Editar Monitorias</a>
        <a href="usuario.php">Dados da conta</a>
        
    ';
} elseif ($tipo === "bolsista") {
    echo '
        <a href="#">Seus projetos</a>
        <a href="usuario.php">Dados da conta</a>
    ';
} else { // aluno
    echo '
        <a href="usuario.php">Dados da conta</a>
    ';
}

echo '
    </div>
</div>'

?>

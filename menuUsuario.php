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
        <a href="menuCad-projeto.php">Criar projeto</a>
        <a href="menuEditarProjetos.php">Seus projetos</a>
        <a href="menuMonitorias.php">Editar Monitorias</a>
        <a href="menuConta.php">Dados da conta</a>
        <a href="logout.php" class="sair">Sair</a>
    ';
} elseif ($tipo === "bolsista") {
    echo '
        <a href="#">Seus projetos</a>
        <a href="menuConta.php">Dados da conta</a>
        <a href="logout.php" class="sair">Sair</a>
    ';
} else { // aluno
    echo '
        <a href="menuConta.php">Dados da conta</a>
        <a href="logout.php" class="sair">Sair</a>
    ';
}

echo '
    </div>
</div>';
?>

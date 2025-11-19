<?php

/*
    Copyright (c) 2025 Guilherme Raimundo & Gabriella Schmilla Sandner
    
    This source code is licensed under the MIT license found in the
    LICENSE file in the root directory of this source tree.
*/



if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["tipo"])) {
    echo '<div id="login-nav">Entrar</div>';
    return;
}

$tipo = $_SESSION["tipo"];
$email = $_SESSION["email"] ?? '';

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
        <a href="menuEditProjetos.php">Seus projetos</a>
    ';
    
    // Mostrar opções de monitoria apenas para o e-mail institucional autorizado
    // Removendo espaços em branco e convertendo para minúsculas
    $emailLimpo = strtolower(trim($email));
    
    if ($emailLimpo === 'cge.ibirama@ifc.edu.br') {
        echo '
        <a href="menuCad-monitoria.php">Criar monitoria</a>
        <a href="menuEditMonitorias.php">Editar monitorias</a>
        ';
    }
    
    echo '
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

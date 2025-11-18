/*
    Copyright (c) 2025 Guilherme Raimundo & Gabriella Schmilla Sandner
    
    This source code is licensed under the MIT license found in the
    LICENSE file in the root directory of this source tree.
*/


<?php
session_start();

// Remove todas as variáveis de sessão
session_unset();

// Destroi a sessão
session_destroy();

// Redireciona para a tela de login
header("Location: login.php");
exit;
?>

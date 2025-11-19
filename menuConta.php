<?php
/*
    Copyright (c) 2025 Guilherme Raimundo & Gabriella Schmilla Sandner
    
    This source code is licensed under the MIT license found in the
    LICENSE file in the root directory of this source tree.
*/


session_start();
$nome = $_SESSION['nome'] ?? '';
$tipo = $_SESSION['tipo'] ?? '';
$idPessoa = $_SESSION['idPessoa'] ?? '';

// Conexão com o banco de dados
$host = 'localhost';
$usuario = 'root';
$senha = 'Gui@15600';
//$senha = 'root';
$banco = 'website';

$conn = new mysqli($host, $usuario, $senha, $banco);
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

$conn->set_charset("utf8");

// Imagem padrão (avatar cinza)
$imagemPadraoBase64 = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgZmlsbD0iI2RkZCIvPjxjaXJjbGUgY3g9IjEwMCIgY3k9IjgwIiByPSI0MCIgZmlsbD0iIzk5OSIvPjxwYXRoIGQ9Ik01MCAxNjAgUTUwIDEyMCAxMDAgMTIwIFQxNTAgMTYwIiBmaWxsPSIjOTk5Ii8+PC9zdmc+';

$fotoAtual = $imagemPadraoBase64;
$descricaoAtual = '';

// Buscar dados do usuário
$stmt = $conn->prepare("SELECT nome, sobrenome, email, foto_perfil, descricao, curso, matricula, area FROM pessoa WHERE idPessoa = ?");
$stmt->bind_param("i", $idPessoa);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();

if ($usuario) {
    $descricaoAtual = $usuario['descricao'] ?? '';
    
    // Se tem foto no banco
    if ($usuario['foto_perfil'] && !empty(trim($usuario['foto_perfil']))) {
        $fotoDB = $usuario['foto_perfil'];
        
        // Caminho físico do arquivo (para verificar se existe)
        $caminhoFisico = __DIR__ . '/' . $fotoDB;
        
        if (file_exists($caminhoFisico)) {
            // Usa o caminho relativo para o HTML
            $fotoAtual = $fotoDB;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" id="favicon" href="" type="image/png">
    <title>Minha Conta</title>
    <link rel="stylesheet" href="assets/css/tema-global.css">
    <link rel="stylesheet" href="assets/css/conta.css">
    <script>
        (function() {
            const favicon = document.getElementById('favicon');
            const updateFavicon = () => {
                const isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                favicon.href = isDark ? 'assets/photos/ifc-logo-branco.png' : 'assets/photos/ifc-logo-preto.png';
            };
            updateFavicon();
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', updateFavicon);
        })();
    </script>
</head>
<body>
    <header>
        <div class="logo">
            <div class="grid-icon">
                <img src="assets/photos/ifc-logo-preto.png" id="icone-ifc">
            </div>
            Projetos do Campus Ibirama
        </div>
        <div class="navegador">
            <div class="projetos-nav">Projetos</div>
            <div class="monitoria-nav">Monitoria</div>
            <div class="login-nav"> <?php include 'menuUsuario.php'; ?> </div>
        </div>
    </header>
    
    <div class="container-conta">
        <h2 class="titulo-conta">Minha Conta</h2>

        <div class="conteudo-conta">
            <!-- Foto de perfil -->
            <div class="coluna-foto">
                <div class="foto-perfil">
                    <img src="<?php echo htmlspecialchars($fotoAtual); ?>" alt="Foto de perfil" id="fotoPreview">
                </div>
                <input type="file" id="inputFotoPerfil" name="foto" accept="image/jpeg,image/png,image/gif,image/webp" style="display: none;">
                <button type="button" class="btn-foto" id="btnAlterarFoto">Alterar Foto</button>
                <div id="mensagemFoto" style="margin-top: 10px; font-size: 0.9em; text-align: center;"></div>
            </div>

            <!-- Formulário -->
            <form class="coluna-formulario" id="formConta" enctype="multipart/form-data">
                <div class="linha">
                    <input type="text" id="nome" name="nome" placeholder="Nome" value="<?php echo htmlspecialchars($usuario['nome'] ?? ''); ?>" required>
                    <input type="text" id="sobrenome" name="sobrenome" placeholder="Sobrenome" value="<?php echo htmlspecialchars($usuario['sobrenome'] ?? ''); ?>" required>
                </div>

                <div class="linha linha-reset">
                    <input type="email" id="email" name="email" placeholder="E-mail" value="<?php echo htmlspecialchars($usuario['email'] ?? ''); ?>" required>
                    <button type="button" class="btn-reset-senha" id="btnResetSenha">
                        Enviar link de redefinição
                    </button>
                </div>
                <div id="mensagemResetSenha" class="mensagem-reset" role="status" aria-live="polite"></div>

                <div class="linha">
                    <?php if ($tipo === 'aluno' || $tipo === 'bolsista'): ?>
                        <?php
                        $cursoSalvoRaw = $usuario['curso'] ?? '';

                        function remover_acentos_e_normalizar($str) {
                            if (function_exists('transliterator_transliterate')) {
                                $r = transliterator_transliterate('Any-Latin; Latin-ASCII;', $str);
                            } else {
                                $r = @iconv('UTF-8', 'ASCII//TRANSLIT', $str);
                                if ($r === false) $r = $str;
                            }
                            $r = strtolower(trim($r));
                            $r = preg_replace('/[^a-z0-9 ]+/', '', $r);
                            return $r;
                        }

                        function formatarCursoExibicao($nomeRaw) {
                            $mapa = [
                                'administracao' => 'Administração',
                                'informatica' => 'Informática',
                                'vestuario' => 'Vestuário',
                                'moda' => 'Moda',
                                'gestao comercial' => 'Gestão Comercial',
                                'gestaocomercial' => 'Gestão Comercial',
                            ];

                            $key = remover_acentos_e_normalizar($nomeRaw);
                            $keySemEspaco = str_replace(' ', '', $key);

                            if (isset($mapa[$key])) return $mapa[$key];
                            if (isset($mapa[$keySemEspaco])) return $mapa[$keySemEspaco];
                            return mb_convert_case(trim($nomeRaw), MB_CASE_TITLE, "UTF-8");
                        }

                        $cursoSalvo = htmlspecialchars($cursoSalvoRaw ?? '');
                        $cursoFormatado = $cursoSalvo ? formatarCursoExibicao($cursoSalvoRaw) : 'Curso';
                        ?>

                        <div class="custom-select" id="curso-perfil">
                            <div class="select-selected" data-value="<?php echo $cursoSalvo ?: ''; ?>">
                                <?php echo $cursoFormatado; ?>
                            </div>
                            <div class="select-items">
                                <div data-value="administracao">Administração</div>
                                <div data-value="informatica">Informática</div>
                                <div data-value="vestuario">Vestuário</div>
                                <div data-value="moda">Moda</div>
                                <div data-value="gestao comercial">Gestão Comercial</div>
                            </div>
                        </div>
                        <input type="hidden" name="curso" id="inputCursoPerfil" value="<?php echo $cursoSalvo; ?>">

                        <input type="text" name="matricula" id="matricula" placeholder="Matrícula"
                                value="<?php echo htmlspecialchars($usuario['matricula'] ?? ''); ?>">

                    <?php elseif ($tipo === 'coordenador'): ?>
                        <?php $areaSalva = htmlspecialchars(trim(strtolower($usuario['area'] ?? ''))); ?>
                        <div class="custom-select" id="area-perfil">
                            <div class="select-selected" data-value="<?php echo $areaSalva ?: ''; ?>">
                                <?php echo $areaSalva ? ucfirst($areaSalva) : 'Área de estudo'; ?>
                            </div>
                            <div class="select-items">
                                <div data-value="ciências naturais">Ciências Naturais</div>
                                <div data-value="ciências humanas">Ciências Humanas</div>
                                <div data-value="linguagens">Linguagens</div>
                                <div data-value="matemática">Matemática</div>
                                <div data-value="administração">Administração</div>
                                <div data-value="informática">Informática</div>
                                <div data-value="vestuário">Vestuário</div>
                                <div data-value="coordenação">Coordenação</div>
                                <div data-value="técnico administrativo">Técnico Administrativo</div>
                            </div>
                        </div>
                        <input type="hidden" name="area" id="inputAreaPerfil" value="<?php echo $areaSalva; ?>">
                    <?php endif; ?>
                </div>
            </form>
        </div>    

        <!-- Descrição -->
        <div class="descricao">
            <textarea id="descricao" name="descricao" maxlength="1000" placeholder="Escreva algo sobre você..."><?php echo htmlspecialchars($descricaoAtual); ?></textarea>
            <small id="contador"><?php echo strlen($descricaoAtual); ?>/1000</small>
        </div>

        <!-- Botões -->
        <div class="botoes">
            <button type="button" class="btn-salvar" id="btnSalvar">Salvar Alterações</button>
            <button type="button" class="btn-excluir" id="btnExcluir">Excluir Conta</button>
        </div>
    </div>

    <footer>
        <div class="linha">
            <div class="footer-container">
                <div class="Recursos">
                    <h2>Recursos</h2>
                    <ul>
                        <li><a href="https://ibirama.ifc.edu.br/">Site IF Ibirama</a></li>
                        <li><a href="https://ensino.ifc.edu.br/calendarios-academicos/">Calendários Acadêmicos</a></li>
                        <li><a href="https://ifc.edu.br/portal-do-estudante/">Políticas e Programas Estudantis</a></li>
                        <li><a href="https://ingresso.ifc.edu.br/">Portal de Ingresso IFC</a></li>
                        <li><a href="https://estudante.ifc.edu.br/2017/03/21/regulamento-de-conduta-discente/">Regulamento da Conduta Discente</a></li>
                        <li><a href="http://sig.ifc.edu.br/sigaa">SIGAA</a></li>
                    </ul>
                </div>
                <div class="Comunidade">
                    <h2>Comunidade</h2>
                    <ul>
                        <li><a href="http://acessoainformacao.ifc.edu.br/">Acesso à Informação</a></li>
                        <li><a href="https://ifc.edu.br/comite-de-crise/">Calendários Acadêmicos</a></li>
                        <li><a href="https://cepsh.ifc.edu.br/">CEPSH</a></li>
                        <li><a href="https://consuper.ifc.edu.br/">Conselho Superior</a></li>
                        <li><a href="https://sig.ifc.edu.br/public/jsp/portal.jsf">Portal Público</a></li>
                        <li><a href="https://editais.ifc.edu.br/">Editais IFC</a></li>
                        <li><a href="http://www.camboriu.ifc.edu.br/pos-graduacao/treinador-e-instrutor-de-caes-guia/">Projetos Cães-guia</a></li>
                        <li><a href="https://trabalheconosco.ifc.edu.br/">Trabalhe no IFC</a></li>
                    </ul>
                </div>
                <div class="Servidor">
                    <h2>Servidor</h2>
                    <ul>
                        <li><a href="https://ifc.edu.br/desenvolvimento-do-servidor/">Desenvolvimento do Servidor</a></li>
                        <li><a href="https://manualdoservidor.ifc.edu.br/">Manual do Servidor</a></li>
                        <li><a href="https://www.siapenet.gov.br/Portal/Servico/Apresentacao.asp">Portal SIAPENET</a></li>
                        <li><a href="http://suporte.ifc.edu.br/">Suporte TI</a></li>
                        <li><a href="https://sig.ifc.edu.br/sigrh/public/home.jsf">Sistema Integrado de Gestão (SIG)</a></li>
                        <li><a href="https://mail.google.com/mail/u/0/#inbox">Webmail</a></li>
                    </ul>
                </div>
                <div class="Sites-Relacionados">
                    <h2>Sites Relacionados</h2>
                    <ul>
                        <li><a href="https://www.gov.br/pt-br">Brasil - GOV</a></li>
                        <li><a href="https://www.gov.br/capes/pt-br">CAPES - Chamadas Públicas</a></li>
                        <li><a href="https://www-periodicos-capes-gov-br.ez317.periodicos.capes.gov.br/index.php?">Capes - Portal de Periódicos</a></li>
                        <li><a href="https://www.gov.br/cnpq/pt-br">CNPq - Chamadas Públicas</a></li>
                        <li><a href="http://informativo.ifc.edu.br/">Informativo IFC</a></li>
                        <li><a href="https://www.gov.br/mec/pt-br">MEC - Ministério da Educação</a></li>
                        <li><a href="https://www.transparencia.gov.br/">Transparência Pública</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="Sobre">
            <h2>Sobre este site</h2>
            <span id="License"><i>Licença M.I.T.2025</i></span>
        </div>
        <div class="acesso-info">
            <a href="https://www.gov.br/acessoainformacao/pt-br">
                <img src="assets/photos/icones/logo-acesso-informacao.png" alt="Logo Acesso à Informação">
            </a>
        </div>
    </footer>
    
    <script src="assets/js/conta.js"></script>
    <script src="assets/js/global.js"></script>
    
    <div id="modalConfirmarSenha" class="modal-senha" aria-hidden="true">
        <div class="modal-senha__conteudo">
            <h3>Confirme sua senha</h3>
            <p>Por segurança, digite sua senha atual para salvar as alterações da conta.</p>
            <input type="password" id="inputSenhaConfirmacao" placeholder="Senha" autocomplete="current-password">
            <div class="modal-senha__acoes">
                <button type="button" class="btn-secundario" id="btnCancelarSenha">Cancelar</button>
                <button type="button" class="btn-primario" id="btnConfirmarSenha">Confirmar</button>
            </div>
        </div>
    </div>
    <script>
        // Variável global para armazenar o arquivo da foto
        let arquivoFotoSelecionado = null;

        // Contador de caracteres da descrição
        const descricao = document.getElementById('descricao');
        const contador = document.getElementById('contador');
        
        if (descricao && contador) {
            descricao.addEventListener('input', function() {
                contador.textContent = this.value.length + '/1000';
            });
        }

        // Botão de alterar foto - apenas preview
        const btnAlterarFoto = document.getElementById('btnAlterarFoto');
        const inputFotoPerfil = document.getElementById('inputFotoPerfil');
        const fotoPreview = document.getElementById('fotoPreview');
        const mensagemFoto = document.getElementById('mensagemFoto');

        if (btnAlterarFoto && inputFotoPerfil) {
            btnAlterarFoto.addEventListener('click', function() {
                inputFotoPerfil.click();
            });

            inputFotoPerfil.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (!file) return;

                // Validar tipo de arquivo
                const tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!tiposPermitidos.includes(file.type)) {
                    mensagemFoto.innerHTML = '<span style="color:red">❌ Tipo de arquivo não permitido. Use JPG, PNG, GIF ou WEBP.</span>';
                    setTimeout(() => { mensagemFoto.innerHTML = ''; }, 3000);
                    return;
                }

                // Validar tamanho (5MB)
                if (file.size > 5 * 1024 * 1024) {
                    mensagemFoto.innerHTML = '<span style="color:red">❌ Arquivo muito grande. Máximo 5MB.</span>';
                    setTimeout(() => { mensagemFoto.innerHTML = ''; }, 3000);
                    return;
                }

                // Armazena o arquivo para enviar junto com o formulário
                arquivoFotoSelecionado = file;

                // Preview da imagem
                const reader = new FileReader();
                reader.onload = function(event) {
                    fotoPreview.src = event.target.result;
                };
                reader.readAsDataURL(file);

                mensagemFoto.innerHTML = '<span style="color:green">✅ Foto selecionada! Clique em "Salvar Alterações" para confirmar.</span>';
                setTimeout(() => { mensagemFoto.innerHTML = ''; }, 5000);
            });
        }
    </script>
</body>
</html>
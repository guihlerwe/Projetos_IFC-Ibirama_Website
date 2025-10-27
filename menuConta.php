<?php
session_start();
$nome = $_SESSION['nome'] ?? '';
$tipo = $_SESSION['tipo'] ?? '';
$idPessoa = $_SESSION['idPessoa'] ?? '';

// Conexão com o banco de dados
$host = 'localhost';
$usuario = 'root';
$senha = 'Gui@15600';
$banco = 'website';

$conn = new mysqli($host, $usuario, $senha, $banco);
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

$conn->set_charset("utf8");

// Buscar dados do usuário
$fotoAtual = '../assets/photos/sem_foto_perfil.png';
$stmt = $conn->prepare("SELECT nome, sobrenome, email, foto FROM pessoa WHERE idPessoa = ?");
$stmt->bind_param("i", $idPessoa);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();

if ($usuario && $usuario['foto']) {
    $fotoAtual = $usuario['foto'];
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minha Conta</title>
    <link rel="stylesheet" href="assets/css/tema-global.css">
    <link rel="stylesheet" href="assets/css/conta.css">
</head>
<body>
    <header>
        <div class="logo">
            <div class="grid-icon">
                <img src="../assets/photos/ifc-logo-preto.png" id="icone-ifc">
            </div>
            Projetos do Campus Ibirama
        </div>
        <div class="navegador">
            <div class="projetos-nav">Projetos</div>
            <div class="monitoria-nav">Monitoria</div>
            <div class="login-nav"> <?php include 'menuUsuario.php'; ?> </div>
        </div>
    </header>
    <main class="container-perfil">
        
        <h1>Minha Conta</h1>
        <br>

        <!-- Parte superior: foto + formulário -->
        <section class="secao-superior">
            <!-- Foto -->
            <div class="secao-foto">
                <img src="<?php echo htmlspecialchars($fotoAtual); ?>" alt="Foto de Perfil" class="foto-perfil" id="previewFoto">
                <form id="formFoto" enctype="multipart/form-data" style="display: none;">
                    <input type="file" id="inputFoto" name="foto" accept="image/*" required>
                </form>
                <button type="button" class="btn" onclick="document.getElementById('inputFoto').click()">Alterar Foto</button>
                <div id="mensagemFoto"></div>
            </div>

            <!-- Formulário -->
            <form class="formulario-perfil" id="formularioPerfil">
                <label class="campoEsquerda">
                    Nome:
                    <input type="text" name="nome" placeholder="Digite seu nome" value="<?php echo htmlspecialchars($usuario['nome'] ?? ''); ?>">
                </label>

                <label class="campoDireita">
                    Sobrenome:
                    <input type="text" name="sobrenome" placeholder="Digite seu sobrenome" value="<?php echo htmlspecialchars($usuario['sobrenome'] ?? ''); ?>">
                </label>

                <label class="campoEsquerda">
                    E-mail:
                    <input type="email" name="email" placeholder="Digite seu e-mail" value="<?php echo htmlspecialchars($usuario['email'] ?? ''); ?>">
                </label>

                <label class="campoDireita">
                    Senha:
                    <input type="password" name="senha" placeholder="Digite sua nova senha (deixe em branco para não alterar)">
                </label>
            </form>
        </section>

        <!-- Descrição -->
        <section class="secao-descricao">
            <label>
                Descrição:
                <textarea class="campo-descricao" id="descricao" maxlength="1000" placeholder="Escreva algo sobre você..."></textarea>
            </label>
            <div class="contador-caracteres"><span id="contadorAtual">0</span>/1000</div>
        </section>

        <!-- Botões -->
        <div class="area-botoes">
            <button class="btn editar" onclick="salvarPerfil()">Salvar</button>
            <button class="btn excluir" onclick="excluirConta()">Excluir Conta</button>
        </div>
    </main>

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
                <img src="../assets/photos/icones/logo-acesso-informacao.png" alt="Logo Acesso à Informação">
            </a>
        </div>
    </footer>
    <script src="../assets/js/global.js"></script>
    <script src="../assets/js/conta.js"></script>
    <script>
        // Preview da foto antes de enviar
        document.getElementById('inputFoto').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    document.getElementById('previewFoto').src = event.target.result;
                };
                reader.readAsDataURL(file);
                
                // Enviar foto via AJAX
                enviarFoto(file);
            }
        });

        function enviarFoto(file) {
            const formData = new FormData();
            formData.append('foto', file);

            fetch('processar_foto.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const msg = document.getElementById('mensagemFoto');
                if (data.sucesso) {
                    msg.innerHTML = '<span style="color: green;">Foto enviada com sucesso!</span>';
                    setTimeout(() => msg.innerHTML = '', 3000);
                } else {
                    msg.innerHTML = '<span style="color: red;">Erro: ' + data.erro + '</span>';
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                document.getElementById('mensagemFoto').innerHTML = '<span style="color: red;">Erro ao enviar foto</span>';
            });
        }

        // Contador de caracteres
        document.getElementById('descricao').addEventListener('input', function() {
            document.getElementById('contadorAtual').textContent = this.value.length;
        });

        function salvarPerfil() {
            alert('Perfil salvo com sucesso!');
        }

        function excluirConta() {
            if (confirm('Tem certeza que deseja excluir sua conta? Esta ação não pode ser desfeita.')) {
                alert('Conta excluída');
            }
        }
    </script>
</body>
</html>
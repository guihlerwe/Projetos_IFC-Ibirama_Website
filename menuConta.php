<?php
session_start();
$nome = $_SESSION['nome'] ?? '';
$tipo = $_SESSION['tipo'] ?? '';
$idPessoa = $_SESSION['idPessoa'] ?? '';

// Conexão com o banco de dados
$host = 'localhost';
$usuario = 'root';
//$senha = 'root';
$senha = 'Gui@15600';
$banco = 'website';

$conn = new mysqli($host, $usuario, $senha, $banco);
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

$conn->set_charset("utf8");

// Buscar dados do usuário
$fotoAtual = '../assets/photos/sem_foto_perfil.png';
$stmt = $conn->prepare("SELECT nome, sobrenome, email, foto_perfil, curso, matricula, area FROM pessoa WHERE idPessoa = ?");
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

    <div class="container-conta">
  <h2 class="titulo-conta">Minha Conta</h2>

  <div class="conteudo-conta">
    <!-- Foto de perfil -->
    <div class="coluna-foto">
      <div class="foto-perfil">
        <img src="caminho/para/foto.jpg" alt="Foto de perfil" id="fotoPreview">
      </div>
      <button type="button" class="btn-foto" id="btnAlterarFoto">Alterar Foto</button>
    </div>

    <!-- Formulário -->
    <form class="coluna-formulario" id="formConta">
        <div class="linha">
            <input type="text" id="nome" name="nome" placeholder="Nome" value="<?php echo htmlspecialchars($usuario['nome'] ?? ''); ?>">
            <input type="text" id="sobrenome" name="sobrenome" placeholder="Sobrenome" value="<?php echo htmlspecialchars($usuario['sobrenome'] ?? ''); ?>">
        </div>

      <div class="linha">
        <input type="email" id="email" name="email" placeholder="E-mail" value="<?php echo htmlspecialchars($usuario['email'] ?? ''); ?>">
        <input type="password" id="senha" name="senha" placeholder="Nova senha (opcional)">
      </div>

    <div class="linha">
        <?php if ($tipo === 'aluno' || $tipo === 'bolsista'): ?>
            <?php
            // pega valor cru do BD
            $cursoSalvoRaw = $usuario['curso'] ?? '';

            // função utilitária: remove acentos e normaliza para comparação
            function remover_acentos_e_normalizar($str) {
                // tenta Normalizer se disponível
                if (function_exists('transliterator_transliterate')) {
                    $r = transliterator_transliterate('Any-Latin; Latin-ASCII;', $str);
                } else {
                    // fallback para iconv
                    $r = @iconv('UTF-8', 'ASCII//TRANSLIT', $str);
                    if ($r === false) $r = $str;
                }
                $r = strtolower(trim($r));
                // remove caracteres que não sejam letras/números/espaço
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
                    // adicione aqui outras variações esperadas
                ];

                $key = remover_acentos_e_normalizar($nomeRaw);
                // remover espaços para chaves alternativas
                $keySemEspaco = str_replace(' ', '', $key);

                if (isset($mapa[$key])) return $mapa[$key];
                if (isset($mapa[$keySemEspaco])) return $mapa[$keySemEspaco];
                // fallback: capitaliza primeira letra de cada palavra
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
    <textarea id="descricao" maxlength="1000" placeholder="Escreva algo sobre você..."></textarea>
    <small id="contador">0/1000</small>
  </div>

  <!-- Botões -->
  <div class="botoes">
    <button type="submit" class="btn-salvar">Salvar Alterações</button>
    <button type="button" class="btn-excluir">Excluir Conta</button>
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
                <img src="../assets/photos/icones/logo-acesso-informacao.png" alt="Logo Acesso à Informação">
            </a>
        </div>
    </footer>
    <script src="../assets/js/global.js"></script>
    <script src="../assets/js/conta.js"></script>
    <script>
        (function() {
            const inputFotoInline = document.getElementById('inputFoto') || document.getElementById('input-foto') || document.getElementById('inputFotoPerfil') || null;
            const previewEl = document.getElementById('previewFoto') || document.getElementById('fotoPreview') || null;
            const mensagemFotoEl = document.getElementById('mensagemFoto') || null;

            if (inputFotoInline && typeof inputFotoInline.addEventListener === 'function') {
            inputFotoInline.addEventListener('change', function(e) {
                const file = e.target.files && e.target.files[0];
                if (file && previewEl) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    previewEl.src = event.target.result;
                };
                reader.readAsDataURL(file);
                // enviar via fetch se desejar (chamar processar_foto.php)
                if (typeof enviarFoto === 'function') {
                    enviarFoto(file);
                } else {
                    // fallback: enviar manualmente
                    const formData = new FormData();
                    formData.append('foto', file);
                    fetch('processar_foto.php', { method: 'POST', body: formData })
                    .then(r => r.json()).then(data => {
                        if (mensagemFotoEl) {
                        mensagemFotoEl.innerHTML = data.sucesso ? '<span style="color:green">Foto enviada com sucesso!</span>' : '<span style="color:red">Erro: '+(data.erro||'')+'</span>';
                        setTimeout(()=> mensagemFotoEl.innerHTML = '', 3000);
                        }
                    }).catch(err => {
                        console.error('Erro enviar foto (inline):', err);
                        if (mensagemFotoEl) mensagemFotoEl.innerHTML = '<span style="color:red">Erro ao enviar foto</span>';
                    });
                }
                }
            });
            }
        })();

        (function(){
            const descEl = document.getElementById('descricao') || document.getElementById('descricao-perfil');
            const contadorAtual = document.getElementById('contadorAtual') || document.getElementById('contador') || null;
            if (descEl && contadorAtual) {
                descEl.addEventListener('input', function() {
                    contadorAtual.textContent = this.value.length;
                });
            }
        })();
    </script>
</body>
</html>
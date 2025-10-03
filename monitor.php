<?php
session_start();
$nome = $_SESSION['nome'] ?? '';
$tipo = $_SESSION['tipo'] ?? '';

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

$id = $_GET['id'] ?? null;
$monitoria = null;
if ($id) {
    $stmt = $conn->prepare("SELECT m.*, p.nome as monitor_nome, p.email as monitor_email FROM monitoria m LEFT JOIN pessoa p ON m.idPessoa = p.idPessoa WHERE m.idMonitoria = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $monitoria = $result->fetch_assoc();
    $stmt->close();
}

$monitor = null;
if ($id) {
    $stmt = $conn->prepare("SELECT * FROM monitores WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $monitor = $result->fetch_assoc();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/tema-global.css">
    <link rel="stylesheet" href="../assets/css/monitor.css">
    <title>Monitorias</title>
</head>

<body>
    <script>
        sessionStorage.setItem('usuarioLogado', '<?php echo $nome; ?>');
        sessionStorage.setItem('tipoUsuario', '<?php echo $tipo; ?>');
    </script>
    <div class="container">
        <header>
            <div class="logo">
                <div class="grid-icon">
                    <img src="/assets/photos/ifc-logo-preto.png" id="icone-ifc">
                    
                </div>
                Monitores do Campus Ibirama
            </div>
            <div class="navegador">
                <div class="projetos-nav">Projetos</div>
                <div class="monitoria-nav">Monitoria</div>
                <div class="login-nav"> <?php include 'menuUsuario.php'; ?> </div>
            </div>
        </header>
        
<div class="monitoria-container">
  <div class="monitoria-capa">
    <img src="<?php echo $monitor['capa_img']; ?>" alt="<?php echo $monitor['area']; ?>">
    <div class="foto-monitor">
      <img src="<?php echo $monitor['foto']; ?>" alt="Monitor <?php echo $monitor['nome']; ?>">
    </div>
  </div>
  <div class="monitoria-conteudo">
    <h1>Monitor <?php echo $monitor['nome']; ?></h1>
    <h3><?php echo $monitor['area']; ?></h3>
    <section class="sobre">
      <h2>Sobre</h2>
      <p><?php echo $monitor['sobre']; ?></p>
    </section>
    <section class="horarios">
      <h2>Horários</h2>
      <p><?php echo $monitor['horarios']; ?></p>
    </section>
    <section class="contato">
      <h2>Contato</h2>
      <a href="mailto:<?php echo $monitor['email']; ?>"><?php echo $monitor['email']; ?></a>
    </section>
  </div>
</div>
<footer>
    <div class="container footer-container">
        <div class="Aluno">
            <h2>Recursos</h2>
            <ul id="menu-aluno">
                <li><a href="https://ibirama.ifc.edu.br/">Site IF Ibirama</a></li>
                <li><a href="https://ensino.ifc.edu.br/calendarios-academicos/">Calendários Acadêmicos</a></li>
                <li><a href="https://ifc.edu.br/portal-do-estudante/">Políticas e Programas Estudantis</a></li>
                <li><a href="https://ingresso.ifc.edu.br/">Portal de Ingresso IFC</a></li>
                <li><a href="https://estudante.ifc.edu.br/2017/03/21/regulamento-de-conduta-discente/">Regulamento da Conduta Discente</a></li>
                <li><a href="http://sig.ifc.edu.br/sigaa">SIGAA</a></li>
            </ul>
        </div>
        <div class="Sobre">
            <h2>Sobre este site</h2>
            <p> 
                O Campus Ibirama, inaugurado em 2010, com dezenas de profissionais, proporciona uma educação de 
                qualidade e oferece cursos de Tecnologia da Informação, Administração e Vestuário, 
                que são importantes para inovações e negócios.  

                <b id="gab">Gabriella</b> e <b id="gui">Guilherme</b> criaram um site para facilitar o acesso a informações sobre projetos e monitorias,
                que antes eram pouco divulgados. O site reúne dados sobre inscrições, horários de monitorias e contatos 
                dos responsáveis pelos projetos, mostrando a aplicação de conhecimentos do curso de Tecnologia da Informação.
            </p>
            <span id="License"><i>Licença M.I.T.2025</i></span>
        </div>
    </div>
</footer>
    <script src="../assets/js/global.js"></script>
</body>
</html>

<?php
    $conn->close();
?>
const header = document.querySelector("header");

const loginBtn = document.querySelector(".login-nav");
if (loginBtn) {
  loginBtn.addEventListener("click", function () {
    window.location.href = "../telaLogin/login.html";
  });
}

document.querySelector("#projetos-nav").addEventListener("click", function() {
  window.location.href = "../telaPrincipal/principal.php";
});

document.querySelector("#monitoria-nav").addEventListener("click", function() {
  window.location.href = "../telaMonitorias/telaMonitorias.php";
});

document.querySelector("#sobre-nav").addEventListener("click", function() {
  window.location.href = "../telaSobre/sobre.php";
});
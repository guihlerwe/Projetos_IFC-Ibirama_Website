// Seleciona todos os cards
document.querySelectorAll(".project-card").forEach(card => {
  card.addEventListener("click", () => {
    const label = card.querySelector(".project-label").innerText.trim();

    switch (label) {
      case "Administração":
        window.location.href = "monitor.php";
        break;
      case "Informática":
        window.location.href = "monitor.php";
        break;
      case "Vestuário":
        window.location.href = "monitor.php";
        break;
      case "Moda":
        window.location.href = "monitor.php";
        break;
      case "Ciências Humanas":
        window.location.href = "monitor.php";
        break;
      case "Ciências da Natureza":
        window.location.href = "monitor.php";
        break;
      case "Linguagens":
        window.location.href = "monitor.php";
        break;
      case "Matemática":
        window.location.href = "monitor.php";
        break;
      default:
        alert("Monitoria ainda não cadastrada!");
    }
  });
});
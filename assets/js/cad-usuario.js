// --- Função para selects customizados ---
function setupCustomSelect(id, inputId, callback = null) {
    const selectBox = document.getElementById(id);
    if(!selectBox) return;
    const selected = selectBox.querySelector(".select-selected");
    const optionsContainer = selectBox.querySelector(".select-items");
    const options = optionsContainer.querySelectorAll("div");
    const hiddenInput = document.getElementById(inputId);

    selected.addEventListener("click", () => {
        selectBox.classList.toggle("open");
    });

    options.forEach(option => {
        option.addEventListener("click", () => {
            hiddenInput.value = option.getAttribute("data-value");
            selected.textContent = option.textContent;
            selectBox.classList.remove("open");
            if(callback) callback(option.getAttribute("data-value"));
        });
    });

    document.addEventListener("click", (e) => {
        if (!selectBox.contains(e.target)) {
            selectBox.classList.remove("open");
        }
    });
}

// --- Lógica para radio buttons do tipo de usuário ---
document.addEventListener("DOMContentLoaded", function() {
    const radios = document.querySelectorAll('input[name="usuario"]');
    const hiddenTipo = document.getElementById("inputTipo");
    const camposAluno = document.getElementById("camposAluno");
    const camposCoordenador = document.getElementById("camposCoordenador");

    function atualizarCampos(value) {
        hiddenTipo.value = value;
        camposAluno.style.display = (value === "aluno") ? "block" : "none";
        camposCoordenador.style.display = (value === "coordenador") ? "block" : "none";
    }

    radios.forEach(r => {
        if (r.checked) atualizarCampos(r.value);
        r.addEventListener("change", (e) => {
            atualizarCampos(e.target.value);
        });
    });

    // Inicializa selects customizados restantes
    setupCustomSelect("curso-aluno", "inputCurso");
    setupCustomSelect("area-coordenador", "inputArea");
});

function mostrarToast(mensagem, tipo = "sucesso") {
  const container = document.getElementById("toast-container");
  const toast = document.createElement("div");
  toast.className = `toast ${tipo}`;
  toast.textContent = mensagem;
  container.appendChild(toast);

  setTimeout(() => {
    toast.remove();
  }, 4000);
}

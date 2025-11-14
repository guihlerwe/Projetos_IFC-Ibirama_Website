// ================================
//  CONTA.JS - Corrigido 2025-11
// ================================

document.addEventListener("DOMContentLoaded", () => {
  inicializarSelectsPersonalizados();
  configurarBotoesConta();
});

let modalSenha;
let inputSenhaConfirmacao;
let btnConfirmarSenhaModal;
let btnCancelarSenhaModal;

// ================================
// 1. Salvar alterações da conta
// ================================
function salvarAlteracoes(senhaConfirmada) {
  const form = document.getElementById("formConta");
  const descricao = document.getElementById("descricao");
  
  if (!form) {
    console.error("Formulário não encontrado!");
    return;
  }

  // Validar campos obrigatórios
  const nome = document.getElementById("nome").value.trim();
  const sobrenome = document.getElementById("sobrenome").value.trim();
  const email = document.getElementById("email").value.trim();

  if (!nome || !sobrenome || !email) {
    alert("⚠️ Por favor, preencha todos os campos obrigatórios.");
    return;
  }

  // Validar email
  if (!validarEmail(email)) {
    alert("⚠️ Por favor, insira um e-mail válido.");
    return;
  }

  if (!senhaConfirmada) {
    alert("⚠️ É necessário informar sua senha para salvar as alterações.");
    return;
  }

  const formData = new FormData(form);
  
  // Adicionar descrição ao FormData
  if (descricao) {
    formData.append("descricao", descricao.value);
  }
  
  formData.append("acao", "atualizar_perfil");
  formData.append("senha_confirmacao", senhaConfirmada);

  // Se o input de foto estiver fora do <form> (como em menuConta.php),
  // o FormData(form) não o incluirá automaticamente. Tenta adicionar
  // o arquivo manualmente a partir do input ou da variável global usada
  // no HTML (`arquivoFotoSelecionado`).
  try {
    const inputFoto = document.getElementById('inputFotoPerfil');
    if (inputFoto && inputFoto.files && inputFoto.files[0]) {
      formData.append('foto', inputFoto.files[0]);
    } else if (typeof arquivoFotoSelecionado !== 'undefined' && arquivoFotoSelecionado) {
      formData.append('foto', arquivoFotoSelecionado);
    }
  } catch (e) {
    // não fatal — apenas log para debug
    console.debug('Não foi possível anexar foto ao FormData:', e);
  }

  // Mostrar loading
  const btnSalvar = document.getElementById("btnSalvar");
  const textoOriginal = btnSalvar.textContent;
  btnSalvar.disabled = true;
  btnSalvar.textContent = "Salvando...";

  fetch("contaBD.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.sucesso) {
        alert("✅ Alterações salvas com sucesso!");
        location.reload();
      } else {
        alert("⚠️ Erro ao salvar: " + (data.erro || "Tente novamente."));
      }
    })
    .catch((error) => {
      console.error("Erro na requisição:", error);
      alert("❌ Erro de comunicação com o servidor.");
    })
    .finally(() => {
      btnSalvar.disabled = false;
      btnSalvar.textContent = textoOriginal;
    });
}

// ================================
// 2. Excluir conta
// ================================
function excluirConta() {
  if (!confirm("⚠️ Tem certeza de que deseja excluir sua conta permanentemente?\n\nEsta ação NÃO pode ser desfeita!")) {
    return;
  }

  // Segunda confirmação
  if (!confirm("Esta é sua última chance! Confirma a exclusão da conta?")) {
    return;
  }

  const formData = new FormData();
  formData.append("acao", "excluir_conta");

  const btnExcluir = document.getElementById("btnExcluir");
  const textoOriginal = btnExcluir.textContent;
  btnExcluir.disabled = true;
  btnExcluir.textContent = "Excluindo...";

  fetch("contaBD.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.sucesso) {
        alert("🗑️ Conta excluída com sucesso!");
        window.location.href = "../login.php";
      } else {
        alert("⚠️ Erro ao excluir: " + (data.erro || "Tente novamente."));
        btnExcluir.disabled = false;
        btnExcluir.textContent = textoOriginal;
      }
    })
    .catch((error) => {
      console.error("Erro na exclusão:", error);
      alert("❌ Erro de comunicação com o servidor.");
      btnExcluir.disabled = false;
      btnExcluir.textContent = textoOriginal;
    });
}

// ================================
// 3. Configurar botões
// ================================
function configurarBotoesConta() {
  const btnSalvar = document.getElementById("btnSalvar");
  const btnExcluir = document.getElementById("btnExcluir");
  const btnResetSenha = document.getElementById("btnResetSenha");

  modalSenha = document.getElementById("modalConfirmarSenha");
  inputSenhaConfirmacao = document.getElementById("inputSenhaConfirmacao");
  btnConfirmarSenhaModal = document.getElementById("btnConfirmarSenha");
  btnCancelarSenhaModal = document.getElementById("btnCancelarSenha");

  if (btnSalvar) {
    btnSalvar.addEventListener("click", abrirModalSenhaConfirmacao);
  } else {
    console.error("Botão 'Salvar' não encontrado!");
  }

  if (btnExcluir) {
    btnExcluir.addEventListener("click", excluirConta);
  } else {
    console.error("Botão 'Excluir' não encontrado!");
  }

  if (btnResetSenha) {
    btnResetSenha.addEventListener("click", solicitarResetSenha);
  }

  if (btnConfirmarSenhaModal) {
    btnConfirmarSenhaModal.addEventListener("click", confirmarSenhaModal);
  }

  if (btnCancelarSenhaModal) {
    btnCancelarSenhaModal.addEventListener("click", fecharModalSenha);
  }

  if (inputSenhaConfirmacao) {
    inputSenhaConfirmacao.addEventListener("keydown", (event) => {
      if (event.key === "Enter") {
        event.preventDefault();
        confirmarSenhaModal();
      }
    });
  }

  if (modalSenha) {
    modalSenha.addEventListener("click", (event) => {
      if (event.target === modalSenha) {
        fecharModalSenha();
      }
    });
  }
}

function abrirModalSenhaConfirmacao() {
  if (!modalSenha) {
    salvarAlteracoes(prompt("Digite sua senha para continuar:") || "");
    return;
  }

  inputSenhaConfirmacao.value = "";
  modalSenha.classList.add("ativo");
  modalSenha.setAttribute("aria-hidden", "false");
  setTimeout(() => inputSenhaConfirmacao?.focus(), 50);
}

function fecharModalSenha() {
  if (!modalSenha) return;
  modalSenha.classList.remove("ativo");
  modalSenha.setAttribute("aria-hidden", "true");
}

function confirmarSenhaModal() {
  const senha = inputSenhaConfirmacao?.value.trim();
  if (!senha) {
    inputSenhaConfirmacao?.focus();
    return;
  }
  fecharModalSenha();
  salvarAlteracoes(senha);
}

function solicitarResetSenha() {
  const btnReset = document.getElementById("btnResetSenha");
  const mensagem = document.getElementById("mensagemResetSenha");

  if (!confirm("Um link de redefinição será enviado para o seu e-mail. Deseja continuar?")) {
    return;
  }

  const formData = new FormData();
  formData.append("acao", "solicitar_reset");

  if (btnReset) {
    btnReset.disabled = true;
    btnReset.textContent = "Enviando...";
  }

  fetch("contaBD.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (mensagem) {
        mensagem.textContent = data.sucesso || data.erro || "";
        mensagem.classList.toggle("sucesso", Boolean(data.sucesso));
        mensagem.classList.toggle("erro", Boolean(data.erro));
      }
      if (!data.sucesso && !data.erro) {
        alert("Não foi possível enviar o e-mail de redefinição. Tente novamente em instantes.");
      }
    })
    .catch((error) => {
      console.error("Erro ao solicitar redefinição:", error);
      if (mensagem) {
        mensagem.textContent = "Erro ao enviar e-mail. Tente novamente.";
        mensagem.classList.add("erro");
      }
    })
    .finally(() => {
      if (btnReset) {
        btnReset.disabled = false;
        btnReset.textContent = "Enviar link de redefinição";
      }
    });
}

// ================================
// 4. Selects personalizados
// ================================
function inicializarSelectsPersonalizados() {
  const selects = document.querySelectorAll('.custom-select');
  
  selects.forEach(select => {
    const selected = select.querySelector('.select-selected');
    const items = select.querySelector('.select-items');
    let hiddenInput = select.querySelector('input[type="hidden"]');
    
    if (!hiddenInput) {
      hiddenInput = document.getElementById('inputCursoPerfil') || document.getElementById('inputAreaPerfil') || null;
    }
    
    if (!selected || !items || !hiddenInput) return;

    const options = items.querySelectorAll('div[data-value]');
    const valorSalvo = (hiddenInput.value || '').toString().trim().toLowerCase();
    
    if (valorSalvo) {
      const found = Array.from(options).find(o => 
        (o.dataset.value || '').toString().trim().toLowerCase() === valorSalvo
      );
      
      if (found) {
        selected.textContent = found.textContent;
        selected.setAttribute('data-value', found.dataset.value);
      } else {
        selected.textContent = hiddenInput.value;
        selected.setAttribute('data-value', hiddenInput.value);
      }
    }

    // Clonar para remover eventos anteriores
    const newSelected = selected.cloneNode(true);
    selected.replaceWith(newSelected);

    // Abrir/fechar dropdown
    newSelected.addEventListener('click', (e) => {
      e.stopPropagation();
      fecharTodosSelects();
      select.classList.toggle('open');
    });

    // Selecionar opção
    options.forEach(option => {
      option.addEventListener('click', (e) => {
        e.stopPropagation();
        hiddenInput.value = option.dataset.value;
        newSelected.textContent = option.textContent;
        newSelected.setAttribute('data-value', option.dataset.value);
        select.classList.remove('open');
      });
    });
  });

  // Fechar ao clicar fora
  if (!window.__customSelectGlobalClickAdded) {
    window.addEventListener('click', fecharTodosSelects);
    window.addEventListener('keydown', (e) => { 
      if (e.key === 'Escape') fecharTodosSelects(); 
    });
    window.__customSelectGlobalClickAdded = true;
  }
}

function fecharTodosSelects() {
  document.querySelectorAll('.custom-select.open').forEach(s => s.classList.remove('open'));
}

// ================================
// 5. Validação de email
// ================================
function validarEmail(email) {
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return re.test(String(email).toLowerCase());
}
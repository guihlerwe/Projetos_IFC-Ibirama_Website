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
let acaoAtual = null; // 'salvar' ou 'excluir'

// ================================
// 1. Salvar alterações da conta
// ================================
function salvarAlteracoes(senhaConfirmada) {
  const form = document.getElementById("formConta");
  const descricao = document.getElementById("descricao");
  
  if (!form) {
    console.error("Formulário não encontrado!");
    alert("❌ Erro: Formulário não encontrado.");
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

  // Adicionar foto se houver
  try {
    const inputFoto = document.getElementById('inputFotoPerfil');
    if (inputFoto && inputFoto.files && inputFoto.files[0]) {
      formData.append('foto', inputFoto.files[0]);
    } else if (typeof arquivoFotoSelecionado !== 'undefined' && arquivoFotoSelecionado) {
      formData.append('foto', arquivoFotoSelecionado);
    }
  } catch (e) {
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
    .then((response) => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      return response.json();
    })
    .then((data) => {
      if (data.sucesso) {
        alert("✅ " + data.sucesso);
        location.reload();
      } else if (data.erro) {
        alert("⚠️ " + data.erro);
        btnSalvar.disabled = false;
        btnSalvar.textContent = textoOriginal;
      } else {
        alert("⚠️ Resposta inesperada do servidor.");
        btnSalvar.disabled = false;
        btnSalvar.textContent = textoOriginal;
      }
    })
    .catch((error) => {
      console.error("Erro na requisição:", error);
      alert("❌ Erro de comunicação com o servidor.");
      btnSalvar.disabled = false;
      btnSalvar.textContent = textoOriginal;
    });
}

// ================================
// 2. Excluir conta
// ================================
function excluirConta(senhaConfirmada) {
  if (!senhaConfirmada) {
    alert("⚠️ É necessário informar sua senha para excluir a conta.");
    return;
  }

  // Confirmação adicional
  if (!confirm("⚠️ ATENÇÃO: Tem certeza de que deseja excluir sua conta permanentemente?\n\nEsta ação NÃO pode ser desfeita!\n\nTodos os seus dados serão apagados.")) {
    return;
  }

  // Segunda confirmação
  if (!confirm("⚠️ ÚLTIMA CHANCE!\n\nDigite OK no próximo passo para confirmar a exclusão definitiva da sua conta.")) {
    return;
  }

  const confirmacaoFinal = prompt("Digite OK em MAIÚSCULAS para confirmar a exclusão:");
  if (confirmacaoFinal !== "OK") {
    alert("Exclusão cancelada.");
    return;
  }

  const formData = new FormData();
  formData.append("acao", "excluir_conta");
  formData.append("senha_confirmacao", senhaConfirmada);

  const btnExcluir = document.getElementById("btnExcluir");
  const textoOriginal = btnExcluir ? btnExcluir.textContent : '';
  
  if (btnExcluir) {
    btnExcluir.disabled = true;
    btnExcluir.textContent = "Excluindo...";
  }

  fetch("contaBD.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      return response.json();
    })
    .then((data) => {
      if (data.sucesso) {
        alert("🗑️ " + data.sucesso + "\n\nVocê será redirecionado para a página de login.");
        window.location.href = "login.php";
      } else if (data.erro) {
        alert("⚠️ " + data.erro);
        if (btnExcluir) {
          btnExcluir.disabled = false;
          btnExcluir.textContent = textoOriginal;
        }
      } else {
        alert("⚠️ Resposta inesperada do servidor.");
        if (btnExcluir) {
          btnExcluir.disabled = false;
          btnExcluir.textContent = textoOriginal;
        }
      }
    })
    .catch((error) => {
      console.error("Erro na exclusão:", error);
      alert("❌ Erro de comunicação com o servidor.");
      if (btnExcluir) {
        btnExcluir.disabled = false;
        btnExcluir.textContent = textoOriginal;
      }
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
    btnSalvar.addEventListener("click", (e) => {
      e.preventDefault();
      acaoAtual = 'salvar';
      abrirModalSenhaConfirmacao('salvar');
    });
  } else {
    console.error("Botão 'Salvar' não encontrado!");
  }

  if (btnExcluir) {
    btnExcluir.addEventListener("click", (e) => {
      e.preventDefault();
      acaoAtual = 'excluir';
      abrirModalSenhaConfirmacao('excluir');
    });
  } else {
    console.error("Botão 'Excluir' não encontrado!");
  }

  if (btnResetSenha) {
    btnResetSenha.addEventListener("click", solicitarResetSenha);
  }

  if (btnConfirmarSenhaModal) {
    btnConfirmarSenhaModal.addEventListener("click", (e) => {
      e.preventDefault();
      confirmarSenhaModal();
    });
  }

  if (btnCancelarSenhaModal) {
    btnCancelarSenhaModal.addEventListener("click", (e) => {
      e.preventDefault();
      fecharModalSenha();
    });
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

function abrirModalSenhaConfirmacao(acao) {
  acaoAtual = acao;
  
  if (!modalSenha) {
    const senha = prompt("Digite sua senha para continuar:") || "";
    if (senha.trim() === "") {
      alert("⚠️ Senha não informada.");
      return;
    }
    
    if (acao === 'salvar') {
      salvarAlteracoes(senha);
    } else if (acao === 'excluir') {
      excluirConta(senha);
    }
    return;
  }

  // Atualizar texto do modal conforme a ação
  const modalTitulo = modalSenha.querySelector('h3');
  const modalTexto = modalSenha.querySelector('p');
  
  if (acao === 'excluir') {
    if (modalTitulo) modalTitulo.textContent = 'Confirme sua senha para excluir';
    if (modalTexto) modalTexto.textContent = 'Por segurança, digite sua senha atual para excluir sua conta permanentemente.';
  } else {
    if (modalTitulo) modalTitulo.textContent = 'Confirme sua senha';
    if (modalTexto) modalTexto.textContent = 'Por segurança, digite sua senha atual para salvar as alterações da conta.';
  }

  inputSenhaConfirmacao.value = "";
  modalSenha.classList.add("ativo");
  modalSenha.setAttribute("aria-hidden", "false");
  setTimeout(() => inputSenhaConfirmacao?.focus(), 100);
}

function fecharModalSenha() {
  if (!modalSenha) return;
  modalSenha.classList.remove("ativo");
  modalSenha.setAttribute("aria-hidden", "true");
  if (inputSenhaConfirmacao) {
    inputSenhaConfirmacao.value = "";
  }
  acaoAtual = null;
}

function confirmarSenhaModal() {
  if (!inputSenhaConfirmacao) {
    alert("⚠️ Campo de senha não encontrado.");
    return;
  }

  const senha = inputSenhaConfirmacao.value.trim();
  
  if (senha === "") {
    alert("⚠️ Por favor, digite sua senha.");
    inputSenhaConfirmacao.focus();
    return;
  }
  
  console.log("Ação atual:", acaoAtual); // Debug
  console.log("Senha fornecida:", senha ? "***" : "vazia"); // Debug
  
  // SALVAR A AÇÃO ANTES DE FECHAR O MODAL
  const acaoParaExecutar = acaoAtual;
  
  fecharModalSenha();
  
  if (acaoParaExecutar === 'salvar') {
    console.log("Executando salvarAlteracoes"); // Debug
    salvarAlteracoes(senha);
  } else if (acaoParaExecutar === 'excluir') {
    console.log("Executando excluirConta"); // Debug
    excluirConta(senha);
  } else {
    console.error("Ação não definida. Valor:", acaoParaExecutar);
    alert("⚠️ Erro: Ação não definida.");
  }
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
      } else {
        if (data.sucesso) {
          alert("✅ " + data.sucesso);
        } else if (data.erro) {
          alert("⚠️ " + data.erro);
        }
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
      } else {
        alert("❌ Erro ao enviar e-mail. Tente novamente.");
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
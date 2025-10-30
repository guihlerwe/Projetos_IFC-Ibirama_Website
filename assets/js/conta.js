// conta.js — versão integrada, robusta e pronta para menuConta.php

// Variáveis globais
let modoEdicao = false;
let dadosOriginais = {};

// Referências serão inicializadas no DOMContentLoaded
let formulario = null;
let inputFoto = null;
let fotoPerfil = null;
let placeholderFoto = null;
let botaoAlterarFoto = null;
let botaoRemoverFoto = null;
let botaoEditar = null;
let botaoSalvar = null;
let botaoCancelar = null;
let botaoExcluirConta = null;
let modal = null;
let descricaoTextarea = null;
let contadorChars = null;

// UTIL: pega o primeiro elemento existente de uma lista de ids/seletores
function getFirst(selectorList) {
  for (const sel of selectorList) {
    try {
      const el = document.getElementById(sel) || document.querySelector(sel);
      if (el) return el;
    } catch (e) { /* ignorar seletores inválidos */ }
  }
  return null;
}

// Inicialização
document.addEventListener('DOMContentLoaded', () => {
  // inicializa refs (com fallbacks)
  formulario = getFirst(['formConta', 'formulario-perfil', 'formulario']);
  inputFoto = getFirst(['inputFoto', 'input-foto', 'inputFotoPerfil', 'inputFotoPerfil']);
  fotoPerfil = getFirst(['fotoPreview', 'foto-perfil', 'fotoPreview']);
  placeholderFoto = getFirst(['placeholder-foto', '.placeholder-foto']);
  botaoAlterarFoto = getFirst(['#btnAlterarFoto', 'btnAlterarFoto', 'botao-alterar-foto']);
  botaoRemoverFoto = getFirst(['botao-remover-foto', 'btnRemoverFoto']);
  botaoEditar = getFirst(['botao-editar', '#botao-editar']);
  botaoSalvar = getFirst(['.btn-salvar', '#botao-salvar']);
  botaoCancelar = getFirst(['botao-cancelar', '#botao-cancelar']);
  botaoExcluirConta = getFirst(['.btn-excluir', '#botao-excluir-conta', 'botao-excluir-conta']);
  modal = getFirst(['modal-confirmacao', '#modal-confirmacao']);
  descricaoTextarea = getFirst(['#descricao', '#descricao-perfil', 'descricao']);
  contadorChars = getFirst(['#contador', '#contadorAtual', '.contador-caracteres']);

  if (descricaoTextarea && !descricaoTextarea.getAttribute('maxlength')) {
    descricaoTextarea.setAttribute('maxlength', '1000');
  }

  // iniciar fluxo
  carregarDadosUsuario();
  inicializarEventListeners();
  atualizarContadorCaracteres();
  inicializarSelectsPersonalizados();
});

/* ===========================
   CARREGAR DADOS (fetch ou fallback DOM)
   =========================== */
async function carregarDadosUsuario() {
  // Tenta buscar em contaBD.php (compatibilidade retro)
  try {
    mostrarLoading(true);

    // tenta o fetch — se der problema (404 ou HTML), capturamos e fazemos fallback
    const resp = await fetch('contaBD.php', { method: 'GET' });

    if (resp.ok) {
      // verifica cabeçalho content-type
      const ctype = resp.headers.get('content-type') || '';
      if (ctype.includes('application/json')) {
        const dados = await resp.json();
        preencherCamposComDados(dados);
        return;
      } else {
        // resposta não é JSON (provavelmente HTML) -> fallback
        console.warn('contaBD.php retornou conteúdo não JSON — usando valores já no DOM');
      }
    } else {
      console.warn('contaBD.php não encontrada (HTTP ' + resp.status + ') — usando valores no DOM');
    }
  } catch (err) {
    // fetch falhou (CORS/404/network) -> fallback
    console.warn('Erro ao buscar contaBD.php:', err);
  } finally {
    // sempre tenta preencher com DOM se fetch não funcionou / não retornou JSON
    try {
      preencherCamposAPartirDoDOM();
    } finally {
      mostrarLoading(false);
    }
  }
}

// Preenche campos com objeto JSON (quando disponível)
function preencherCamposComDados(dados) {
  if (!dados) return;
  const nomeEl = getFirst(['nome-perfil', 'nome']);
  const sobrenomeEl = getFirst(['sobrenome-perfil', 'sobrenome']);
  const emailEl = getFirst(['email-perfil', 'email']);

  if (nomeEl) nomeEl.value = dados.nome || '';
  if (sobrenomeEl) sobrenomeEl.value = dados.sobrenome || '';
  if (emailEl) emailEl.value = dados.email || '';
  if (descricaoTextarea) descricaoTextarea.value = dados.descricao || '';

  // foto
  if (dados.foto_perfil && fotoPerfil) mostrarFoto(dados.foto_perfil);

  // hidden inputs curso/area
  const inCurso = document.getElementById('inputCursoPerfil');
  const inArea  = document.getElementById('inputAreaPerfil');
  if (inCurso && dados.curso) inCurso.value = String(dados.curso).trim().toLowerCase();
  if (inArea && dados.area) inArea.value = String(dados.area).trim().toLowerCase();

  atualizarContadorCaracteres();
}

// Fallback: lê valores já renderizados no HTML/PHP (menuConta.php)
function preencherCamposAPartirDoDOM() {
  // alguns campos (nome, sobrenome, email) já podem estar no HTML (value=... vindo do PHP)
  const nomeEl = getFirst(['nome', 'nome-perfil']);
  const sobrenomeEl = getFirst(['sobrenome', 'sobrenome-perfil']);
  const emailEl = getFirst(['email', 'email-perfil']);
  const descricaoEl = descricaoTextarea;

  // foto: há <img id="fotoPreview"> no menuConta.php — tentamos ler seu src
  if (fotoPerfil && fotoPerfil.getAttribute) {
    const src = fotoPerfil.getAttribute('src') || '';
    // se src for "caminho/para/foto.jpg" ou vazio, não assume que seja válida — se for caminho real, exibe
    if (src && !src.includes('caminho/para/foto.jpg')) {
      // exibe
      mostrarFoto(src);
    } else {
      // caso padrão - podemos exibir imagem padrão se houver (arquivo sem_foto_perfil.png)
      // não forçamos aqui porque template PHP pode gerenciar
      console.info('Imagem de perfil é placeholder no HTML ou não apontada.');
    }
  }

  // atualiza contador e mantém os valores já presentes (não sobrescreve)
  if (nomeEl && nomeEl.value) { /* ok */ }
  if (sobrenomeEl && sobrenomeEl.value) { /* ok */ }
  if (emailEl && emailEl.value) { /* ok */ }
  if (descricaoEl) atualizarContadorCaracteres();
}

/* ===========================
   FUNÇÕES DE FOTO
   =========================== */
function mostrarFoto(src) {
  if (!fotoPerfil) return;
  fotoPerfil.src = src;
  fotoPerfil.style.display = 'block';
  if (placeholderFoto) placeholderFoto.style.display = 'none';
  if (botaoRemoverFoto) botaoRemoverFoto.style.display = 'inline-block';
}
function esconderFoto() {
  if (!fotoPerfil) return;
  fotoPerfil.style.display = 'none';
  if (placeholderFoto) placeholderFoto.style.display = 'flex';
  if (botaoRemoverFoto) botaoRemoverFoto.style.display = 'none';
  fotoPerfil.src = '';
}

/* ===========================
   CONTADOR
   =========================== */
function atualizarContadorCaracteres() {
  if (!descricaoTextarea) return;
  const texto = descricaoTextarea.value || '';
  const len = texto.length;
  if (contadorChars) {
    if (contadorChars.tagName && contadorChars.tagName.toLowerCase() === 'small') {
      contadorChars.textContent = `${len}/1000`;
    } else {
      contadorChars.textContent = `${len}`;
    }
  }
  const div = document.querySelector('.contador-caracteres');
  if (div) {
    div.classList.remove('limite-proximo', 'limite-excedido');
    if (len > 1000) div.classList.add('limite-excedido');
    else if (len > 800) div.classList.add('limite-proximo');
  }
}

/* ===========================
   SALVAR / EXCLUIR (com confirmação + "Salvando...")
   =========================== */

async function salvarAlteracoes() {
  // confirmação
  if (!confirm('Deseja salvar as alterações do perfil?')) return;

  // pega botão e altera estado
  if (botaoSalvar) {
    botaoSalvar.disabled = true;
    const originalText = botaoSalvar.textContent;
    botaoSalvar.textContent = 'Salvando...';

    try {
      // validações (usando campos existentes)
      const nomeEl = getFirst(['nome-perfil', 'nome']);
      const sobrenomeEl = getFirst(['sobrenome-perfil', 'sobrenome']);
      const emailEl = getFirst(['email-perfil', 'email']);

      const nome = (nomeEl && nomeEl.value || '').trim();
      const sobrenome = (sobrenomeEl && sobrenomeEl.value || '').trim();
      const email = (emailEl && emailEl.value || '').trim();
      const descricao = (descricaoTextarea && descricaoTextarea.value || '').trim();

      if (!nome || !sobrenome || !email) {
        mostrarMensagem('Todos os campos obrigatórios devem ser preenchidos', 'erro');
        return;
      }
      if (!validarEmail(email)) {
        mostrarMensagem('Email inválido', 'erro');
        return;
      }
      if (descricao.length > 1000) {
        mostrarMensagem('A descrição não pode ter mais de 1000 caracteres', 'erro');
        return;
      }

      mostrarLoading(true);

      // Prepara FormData — envia para o endpoint atual. Se você quer que form envie para menuConta.php,
      // altere o URL abaixo para o endpoint correto (ex: menuConta.php ou processa_perfil.php).
      const formData = new FormData();
      formData.append('acao', 'atualizar_perfil');
      formData.append('nome', nome);
      formData.append('sobrenome', sobrenome);
      formData.append('email', email);
      formData.append('descricao', descricao);

      const inputCurso = document.getElementById('inputCursoPerfil');
      const inputArea = document.getElementById('inputAreaPerfil');
      if (inputCurso) formData.append('curso', inputCurso.value);
      if (inputArea) formData.append('area', inputArea.value);

      // Tenta enviar para contaBD.php por compatibilidade, se não existir o backend, o servidor retornará 404
      const resp = await fetch('contaBD.php', { method: 'POST', body: formData });
      if (!resp.ok) {
        // tenta enviar para menuConta.php (se o backend processa lá)
        console.warn('contaBD.php retornou HTTP ' + resp.status + '. Tentando menuConta.php como fallback.');
        const resp2 = await fetch('menuConta.php', { method: 'POST', body: formData });
        if (!resp2.ok) throw new Error('Erro ao salvar (nenhum endpoint respondeu OK).');
        // idealmente o servidor responde JSON — tentamos parse
        try {
          const j = await resp2.json();
          if (j.sucesso) {
            mostrarMensagem(j.sucesso, 'sucesso');
            alternarModoEdicao(false);
          } else {
            mostrarMensagem(j.erro || 'Alterações salvas, mas sem confirmação do servidor.', 'sucesso');
            alternarModoEdicao(false);
          }
        } catch (e) {
          // servidor não retornou JSON — assumimos sucesso parcial
          mostrarMensagem('Alterações enviadas (Resposta do servidor não estava em JSON).', 'sucesso');
          alternarModoEdicao(false);
        }
      } else {
        // resp.ok — tenta parse JSON
        try {
          const json = await resp.json();
          if (json.sucesso) {
            mostrarMensagem(json.sucesso, 'sucesso');
            alternarModoEdicao(false);
          } else {
            mostrarMensagem(json.erro || 'Alterações enviadas, servidor não confirmou.', 'sucesso');
          }
        } catch (e) {
          mostrarMensagem('Alterações enviadas (resposta não-JSON).', 'sucesso');
        }
      }
    } catch (err) {
      console.error('Erro ao salvar:', err);
      mostrarMensagem('Erro ao salvar alterações', 'erro');
    } finally {
      mostrarLoading(false);
      if (botaoSalvar) {
        botaoSalvar.disabled = false;
        botaoSalvar.textContent = originalText || 'Salvar Alterações';
      }
    }
  } else {
    // sem botão salvo: apenas executa lógica mínima de validação e mensagem
    mostrarMensagem('Botão de salvar não encontrado na página.', 'erro');
  }
}

// Excluir conta (com confirmação robusta)
async function excluirConta() {
  if (!confirm('Tem certeza que deseja excluir sua conta? Esta ação é irreversível. Deseja continuar?')) return;

  try {
    mostrarLoading(true);

    const formData = new FormData();
    formData.append('acao', 'excluir_conta');

    const resp = await fetch('contaBD.php', { method: 'POST', body: formData });
    if (!resp.ok) {
      // tenta menuConta.php como fallback
      const resp2 = await fetch('menuConta.php', { method: 'POST', body: formData });
      if (!resp2.ok) throw new Error('Erro ao excluir conta (nenhum endpoint respondeu OK).');
      try {
        const j = await resp2.json();
        if (j.sucesso) {
          mostrarMensagem(j.sucesso, 'sucesso');
          setTimeout(() => window.location.href = '../telaPrincipal/principal.php', 1500);
        } else {
          mostrarMensagem(j.erro || 'Conta excluída (resposta sem JSON).', 'sucesso');
        }
      } catch {
        mostrarMensagem('Conta excluída (resposta sem JSON).', 'sucesso');
        setTimeout(() => window.location.href = '../telaPrincipal/principal.php', 1500);
      }
    } else {
      try {
        const j = await resp.json();
        if (j.sucesso) {
          mostrarMensagem(j.sucesso, 'sucesso');
          setTimeout(() => window.location.href = '../telaPrincipal/principal.php', 1500);
        } else {
          mostrarMensagem(j.erro || 'Erro ao excluir conta.', 'erro');
        }
      } catch {
        mostrarMensagem('Conta excluída (resposta não-JSON).', 'sucesso');
        setTimeout(() => window.location.href = '../telaPrincipal/principal.php', 1500);
      }
    }
  } catch (err) {
    console.error('Erro ao excluir conta:', err);
    mostrarMensagem('Erro ao excluir conta', 'erro');
  } finally {
    mostrarLoading(false);
  }
}

/* ===========================
   UPLOAD / REMOVER FOTO (mantidos)
   =========================== */
async function uploadFoto(arquivo) {
  try {
    mostrarLoading(true);
    const formData = new FormData();
    formData.append('acao', 'upload_foto');
    formData.append('foto', arquivo);

    const resp = await fetch('contaBD.php', { method: 'POST', body: formData });
    if (!resp.ok) {
      // tenta menuConta.php
      const resp2 = await fetch('menuConta.php', { method: 'POST', body: formData });
      if (!resp2.ok) throw new Error('Erro no upload (endpoints offline).');
      try {
        const j = await resp2.json();
        if (j.caminho_foto) mostrarFoto(j.caminho_foto);
        mostrarMensagem(j.sucesso || 'Upload realizado (fallback).', 'sucesso');
      } catch {
        mostrarMensagem('Upload realizado (resposta sem JSON).', 'sucesso');
      }
    } else {
      const j = await resp.json();
      if (j.sucesso) {
        if (j.caminho_foto) mostrarFoto(j.caminho_foto);
        mostrarMensagem(j.sucesso, 'sucesso');
      } else {
        mostrarMensagem(j.erro || 'Erro no upload.', 'erro');
      }
    }
  } catch (err) {
    console.error('Erro upload:', err);
    mostrarMensagem('Erro ao fazer upload da foto', 'erro');
  } finally {
    mostrarLoading(false);
  }
}

async function removerFoto() {
  try {
    mostrarLoading(true);
    const formData = new FormData();
    formData.append('acao', 'remover_foto');

    const resp = await fetch('contaBD.php', { method: 'POST', body: formData });
    if (!resp.ok) {
      const resp2 = await fetch('menuConta.php', { method: 'POST', body: formData });
      if (!resp2.ok) throw new Error('Erro ao remover foto (endpoints falharam).');
      mostrarMensagem('Foto removida (fallback).', 'sucesso');
      esconderFoto();
    } else {
      const j = await resp.json();
      if (j.sucesso) {
        mostrarMensagem(j.sucesso, 'sucesso');
        esconderFoto();
      } else {
        mostrarMensagem(j.erro || 'Erro ao remover foto', 'erro');
      }
    }
  } catch (err) {
    console.error('Erro ao remover foto:', err);
    mostrarMensagem('Erro ao remover foto', 'erro');
  } finally {
    mostrarLoading(false);
  }
}

/* ===========================
   LAYOUT / UTILITÁRIOS
   =========================== */
function mostrarLoading(mostrar) {
  let container = document.querySelector('.container-perfil') || document.querySelector('.container-conta');
  if (!container) return;
  if (mostrar) container.classList.add('loading');
  else container.classList.remove('loading');
}

function mostrarMensagem(mensagem, tipo) {
  const existentes = document.querySelectorAll('.mensagem-sucesso, .mensagem-erro');
  existentes.forEach(e => e.remove());

  const div = document.createElement('div');
  div.className = tipo === 'sucesso' ? 'mensagem-sucesso' : 'mensagem-erro';
  div.textContent = mensagem;
  div.style.cssText = `
    padding: 12px 20px; margin-bottom: 20px; border-radius: 8px; font-weight: bold; text-align: center;
    ${tipo === 'sucesso' ? 'background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb;' :
     'background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;'}
  `;
  const container = document.querySelector('.conteudo-conta') || document.querySelector('.container-conta') || document.body;
  container.insertBefore(div, container.firstChild);
  setTimeout(() => div.remove(), 5000);
}

/* ===========================
   VALIDAÇÕES E EVENT LISTENERS
   =========================== */
function validarEmail(email) {
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return re.test(email);
}

function validarArquivoImagem(arquivo) {
  const tipos = ['image/jpeg','image/png','image/gif','image/webp'];
  const max = 5 * 1024 * 1024;
  if (!tipos.includes(arquivo.type)) { mostrarMensagem('Tipo de arquivo não permitido.', 'erro'); return false; }
  if (arquivo.size > max) { mostrarMensagem('Arquivo muito grande. Máx 5MB', 'erro'); return false; }
  return true;
}

function inicializarEventListeners() {
  // contador de caracteres
  if (descricaoTextarea) {
    const contador = contadorChars || document.querySelector('#contador') || document.querySelector('#contadorAtual');
    descricaoTextarea.addEventListener('input', () => {
      if (contador) contador.textContent = `${descricaoTextarea.value.length} / ${descricaoTextarea.getAttribute('maxlength') || 1000}`;
      atualizarContadorCaracteres();
    });
  }

  // alterar foto
  if (botaoAlterarFoto && inputFoto) {
    botaoAlterarFoto.addEventListener('click', () => inputFoto.click());
  }

  if (inputFoto) {
    inputFoto.addEventListener('change', (e) => {
      const arquivo = e.target.files && e.target.files[0];
      if (arquivo && validarArquivoImagem(arquivo)) uploadFoto(arquivo);
    });
  }

  if (botaoRemoverFoto) {
    botaoRemoverFoto.addEventListener('click', () => {
      if (confirm('Remover foto de perfil?')) removerFoto();
    });
  }

  if (botaoEditar) botaoEditar.addEventListener('click', () => alternarModoEdicao(true));
  if (botaoSalvar) botaoSalvar.addEventListener('click', () => salvarAlteracoes());
  if (botaoCancelar) botaoCancelar.addEventListener('click', () => { restaurarDadosOriginais(); alternarModoEdicao(false); });

  if (botaoExcluirConta) {
    botaoExcluirConta.addEventListener('click', () => {
      // se há modal, abre; se não, chama confirmar
      if (modal) modal.style.display = 'flex';
      else excluirConta();
    });
  }

  // confirm modal buttons (se existirem no HTML)
  const btnConfirmar = document.getElementById('confirmar-exclusao');
  if (btnConfirmar) btnConfirmar.addEventListener('click', () => { if (modal) modal.style.display = 'none'; excluirConta(); });

  const btnCancelar = document.getElementById('cancelar-exclusao');
  if (btnCancelar) btnCancelar.addEventListener('click', () => { if (modal) modal.style.display = 'none'; });

  if (modal) {
    modal.addEventListener('click', e => { if (e.target === modal) modal.style.display = 'none'; });
  }

  // beforeunload
  window.addEventListener('beforeunload', (e) => {
    if (modoEdicao) {
      e.preventDefault();
      e.returnValue = 'Você tem alterações não salvas. Tem certeza que deseja sair?';
      return e.returnValue;
    }
  });

  // atalhos
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && modoEdicao) {
      e.preventDefault();
      restaurarDadosOriginais();
      alternarModoEdicao(false);
    }
    if (e.ctrlKey && e.key === 's' && modoEdicao) {
      e.preventDefault();
      salvarAlteracoes();
    }
    if (e.key === 'Escape' && modal && modal.style.display === 'flex') {
      e.preventDefault();
      modal.style.display = 'none';
    }
  });
}

/* ===========================
   SELECTS CUSTOMIZADOS (curso / área)
   =========================== */
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

    // valor salvo
    const valorSalvo = (hiddenInput.value || '').toString().trim().toLowerCase();
    if (valorSalvo) {
      const found = Array.from(options).find(o => (o.dataset.value || '').toString().trim().toLowerCase() === valorSalvo);
      if (found) {
        selected.textContent = found.textContent;
        selected.setAttribute('data-value', found.dataset.value);
      } else {
        selected.textContent = hiddenInput.value;
        selected.setAttribute('data-value', hiddenInput.value);
      }
    }

    // remove listeners antigos ao clonar
    const newSelected = selected.cloneNode(true);
    selected.replaceWith(newSelected);

    newSelected.addEventListener('click', (e) => {
      e.stopPropagation();
      fecharTodosSelects();
      select.classList.toggle('open');
    });

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

  if (!window.__customSelectGlobalClickAdded) {
    window.addEventListener('click', fecharTodosSelects);
    window.addEventListener('keydown', (e) => { if (e.key === 'Escape') fecharTodosSelects(); });
    window.__customSelectGlobalClickAdded = true;
  }
}

function fecharTodosSelects() {
  document.querySelectorAll('.custom-select.open').forEach(s => s.classList.remove('open'));
}

/* ===========================
   Utilitários / debug
   =========================== */
function salvarDadosOriginais() {
  dadosOriginais = {
    nome: (getFirst(['nome-perfil', 'nome']) || { value: '' }).value,
    sobrenome: (getFirst(['sobrenome-perfil', 'sobrenome']) || { value: '' }).value,
    email: (getFirst(['email-perfil', 'email']) || { value: '' }).value,
    descricao: (descricaoTextarea || { value: '' }).value
  };
}
function restaurarDadosOriginais() {
  const n = getFirst(['nome-perfil', 'nome']);
  const s = getFirst(['sobrenome-perfil', 'sobrenome']);
  const e = getFirst(['email-perfil', 'email']);
  if (n) n.value = dadosOriginais.nome || '';
  if (s) s.value = dadosOriginais.sobrenome || '';
  if (e) e.value = dadosOriginais.email || '';
  if (descricaoTextarea) descricaoTextarea.value = dadosOriginais.descricao || '';
  atualizarContadorCaracteres();
}

window.debugPerfil = {
  carregarDados: carregarDadosUsuario,
  alternarEdicao: () => alternarModoEdicao(true),
  salvar: salvarAlteracoes,
  mostrarMensagem: mostrarMensagem
};

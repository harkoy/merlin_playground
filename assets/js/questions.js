document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('add-question-form');
  if (form) {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const input = form.querySelector('input[name="new_question"]');
      const texto = input.value.trim();
      if (!texto) return;
      const resp = await fetch('questions_api.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'create', texto})
      });
      const data = await resp.json();
      if (data.id) {
        const table = document.getElementById('questions-table');
        const tbody = table.querySelector('tbody');
        const tr = document.createElement('tr');
        tr.dataset.id = data.id;
        tr.innerHTML = `
          <td>${data.id}</td>
          <td><input type="text" class="form-input question-text" value="${texto}"></td>
          <td>
            <div style="display: flex; gap: 0.5rem;">
              <button type="button" class="btn btn-success btn-save" data-id="${data.id}"><i class="fas fa-save"></i> Guardar</button>
              <button type="button" class="btn btn-danger btn-del" data-id="${data.id}"><i class="fas fa-trash"></i></button>
            </div>
          </td>
        `;
        tbody.appendChild(tr);
        input.value = '';
        const empty = document.getElementById('questions-empty');
        if (empty) empty.remove();
        table.style.display = '';
      }
    });
  }

  document.addEventListener('click', async (e) => {
    const saveBtn = e.target.closest('.btn-save');
    if (saveBtn) {
      const id = saveBtn.dataset.id;
      const input = saveBtn.closest('tr').querySelector('input');
      const texto = input.value;
      await fetch('questions_api.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'update', id, texto})
      });
    }

    const delBtn = e.target.closest('.btn-del');
    if (delBtn) {
      if (!confirm('¿Eliminar esta pregunta?')) return;
      const id = delBtn.dataset.id;
      await fetch('questions_api.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'delete', id})
      });
      const row = delBtn.closest('tr');
      const table = document.getElementById('questions-table');
      row.remove();
      if (!table.querySelector('tbody').children.length) {
        table.style.display = 'none';
        const empty = document.createElement('div');
        empty.id = 'questions-empty';
        empty.className = 'empty-state';
        empty.innerHTML = `
          <i class="fas fa-question-circle"></i>
          <h3>No hay preguntas configuradas</h3>
          <p>Agrega preguntas para que aparezcan en el sistema.</p>
        `;
        table.parentNode.appendChild(empty);
      }
    }
  });
});

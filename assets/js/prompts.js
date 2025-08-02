document.addEventListener('DOMContentLoaded', () => {
  const setForm = document.getElementById('add-set-form');
  if (setForm) {
    setForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const input = setForm.querySelector('input[name="new_set_name"]');
      const nombre = input.value.trim();
      if (!nombre) return;
      const resp = await fetch('prompts_api.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'create_set', nombre})
      });
      const data = await resp.json();
      if (data.id) {
        let table = document.getElementById('sets-table');
        if (!table) {
          table = document.createElement('table');
          table.id = 'sets-table';
          table.className = 'data-table';
          table.innerHTML = `
            <thead>
              <tr>
                <th>ID</th>
                <th>Nombre del Conjunto</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody></tbody>
          `;
          setForm.insertAdjacentElement('afterend', table);
        }
        const tbody = table.querySelector('tbody');
        const tr = document.createElement('tr');
        tr.dataset.id = data.id;
        tr.innerHTML = `
          <form method="post" action="?prompt_set=${data.id}">
            <td>${data.id}</td>
            <td>
              <input type="hidden" name="set_id" value="${data.id}">
              <input type="text" name="set_name" value="${nombre}" class="form-input">
            </td>
            <td>
              <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                <button type="submit" name="rename_set" class="btn btn-success btn-save-set" data-id="${data.id}"><i class="fas fa-save"></i></button>
                <a href="?prompt_set=${data.id}" class="btn"><i class="fas fa-eye"></i> Ver</a>
                <button type="button" class="btn btn-danger btn-del-set" data-id="${data.id}"><i class="fas fa-trash"></i></button>
              </div>
            </td>
          </form>
        `;
        tbody.appendChild(tr);
        input.value = '';
        const empty = document.getElementById('sets-empty');
        if (empty) empty.remove();
      }
    });
  }

  document.addEventListener('submit', async (e) => {
    const form = e.target;
    if (form.closest('#sets-table')) {
      e.preventDefault();
      const id = form.querySelector('input[name="set_id"]').value;
      const nombre = form.querySelector('input[name="set_name"]').value;
      await fetch('prompts_api.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'rename_set', id, nombre})
      });
    } else if (form.closest('#lines-table')) {
      e.preventDefault();
      const id = form.querySelector('input[name="line_id"]').value;
      const orden = form.querySelector('input[name="line_order"]').value;
      const role = form.querySelector('select[name="line_role"]').value;
      const content = form.querySelector('textarea[name="line_content"]').value;
      await fetch('prompts_api.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'update_line', id, orden, role, content})
      });
    }
  });

  document.addEventListener('click', async (e) => {
    const delSet = e.target.closest('.btn-del-set');
    if (delSet) {
      if (!confirm('¿Eliminar este conjunto?')) return;
      const id = delSet.dataset.id;
      await fetch('prompts_api.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'delete_set', id})
      });
      const tr = delSet.closest('tr');
      const table = document.getElementById('sets-table');
      tr.remove();
      if (!table.querySelector('tbody tr')) {
        table.insertAdjacentHTML('afterend', `
          <div id="sets-empty" class="empty-state">
            <i class="fas fa-cogs"></i>
            <h3>No hay conjuntos de prompts</h3>
            <p>Crea conjuntos de prompts para configurar el comportamiento del chat.</p>
          </div>
        `);
        table.remove();
      }
    }

    const delLine = e.target.closest('.btn-del-line');
    if (delLine) {
      if (!confirm('¿Eliminar este mensaje?')) return;
      const id = delLine.dataset.id;
      await fetch('prompts_api.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'delete_line', id})
      });
      const row = delLine.closest('tr');
      const table = document.getElementById('lines-table');
      row.remove();
      if (table && !table.querySelector('tbody tr')) {
        table.insertAdjacentHTML('afterend', `
          <div id="lines-empty" class="empty-state">
            <i class="fas fa-list"></i>
            <h3>No hay mensajes en este conjunto</h3>
            <p>Agrega mensajes para configurar el comportamiento del chat.</p>
          </div>
        `);
        table.remove();
      }
    }
  });

  const lineForm = document.getElementById('add-line-form');
  if (lineForm) {
    lineForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const set_id = lineForm.dataset.setId;
      const orden = lineForm.querySelector('input[name="line_order"]').value;
      const role = lineForm.querySelector('select[name="line_role"]').value;
      const content = lineForm.querySelector('input[name="line_content"]').value;
      const resp = await fetch('prompts_api.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'create_line', set_id, orden, role, content})
      });
      const data = await resp.json();
      if (data.id) {
        let table = document.getElementById('lines-table');
        if (!table) {
          table = document.createElement('table');
          table.id = 'lines-table';
          table.className = 'data-table';
          table.innerHTML = `
            <thead>
              <tr>
                <th>Orden</th>
                <th>Rol</th>
                <th>Contenido</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody></tbody>
          `;
          lineForm.insertAdjacentElement('afterend', table);
        }
        const tbody = table.querySelector('tbody');
        const tr = document.createElement('tr');
        tr.dataset.id = data.id;
        tr.innerHTML = `
          <form method="post" action="?prompt_set=${set_id}">
            <td><input type="hidden" name="line_id" value="${data.id}"><input type="number" name="line_order" value="${orden}" class="form-input" style="max-width: 60px;" min="1"></td>
            <td>
              <select name="line_role" class="form-select">
                <option value="system"${role==='system'?' selected':''}>System</option>
                <option value="assistant"${role==='assistant'?' selected':''}>Assistant</option>
                <option value="user"${role==='user'?' selected':''}>User</option>
              </select>
              <span class="role-tag role-${role}" style="margin-left: 0.5rem;">${role.charAt(0).toUpperCase()+role.slice(1)}</span>
            </td>
            <td><textarea name="line_content" rows="3" class="form-textarea">${content}</textarea></td>
            <td>
              <div style="display: flex; gap: 0.5rem; flex-direction: column;">
                <button type="submit" name="edit_line" class="btn btn-success btn-save-line" data-id="${data.id}"><i class="fas fa-save"></i> Guardar</button>
                <button type="button" class="btn btn-danger btn-del-line" data-id="${data.id}"><i class="fas fa-trash"></i></button>
              </div>
            </td>
          </form>
        `;
        tbody.appendChild(tr);
        lineForm.querySelector('input[name="line_content"]').value = '';
        lineForm.querySelector('input[name="line_order"]').value = parseInt(orden) + 1;
        const empty = document.getElementById('lines-empty');
        if (empty) empty.remove();
        table.style.display = '';
      }
    });
  }
});

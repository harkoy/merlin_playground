<?php
session_start();
require 'db.php';

if (!isset($_SESSION['usuario_id']) || empty($_SESSION['es_admin'])) {
    header('Location: login.php');
    exit;
}

// Handle user deletion
if (isset($_GET['delete_user'])) {
    $stmt = $pdo->prepare('DELETE FROM usuarios WHERE id = ?');
    $stmt->execute([$_GET['delete_user']]);
}

// Handle question add/update/delete
$selectedSet = isset($_GET['prompt_set']) ? (int)$_GET['prompt_set'] : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // questions
    if (isset($_POST['new_question'])) {
        $stmt = $pdo->prepare('INSERT INTO preguntas_admin (texto_pregunta) VALUES (?)');
        $stmt->execute([$_POST['new_question']]);
    }
    if (isset($_POST['edit_id']) && isset($_POST['edit_text'])) {
        $stmt = $pdo->prepare('UPDATE preguntas_admin SET texto_pregunta = ? WHERE id = ?');
        $stmt->execute([$_POST['edit_text'], $_POST['edit_id']]);
    }
    if (isset($_POST['prompt_update'])) {
        $stmt = $pdo->prepare('UPDATE usuarios SET prompt_set_id = ? WHERE id = ?');
        $stmt->execute([$_POST['prompt_set_id'], $_POST['user_id']]);
    }

    // prompt set operations
    if (isset($_POST['add_set']) || isset($_POST['new_set_name'])) {
        $stmt = $pdo->prepare('INSERT INTO prompt_sets (nombre) VALUES (?)');
        $stmt->execute([$_POST['new_set_name']]);
        $newId = $pdo->lastInsertId();
        header('Location: admin.php?prompt_set=' . $newId . '&success=1');
        exit;
    }
    if (isset($_POST['rename_set'])) {
        $stmt = $pdo->prepare('UPDATE prompt_sets SET nombre = ? WHERE id = ?');
        $stmt->execute([$_POST['set_name'], $_POST['set_id']]);
    }

    if (isset($_POST['line_content']) && !isset($_POST['line_id'])) {
        $setId = isset($_POST['set_id']) ? (int)$_POST['set_id'] : $selectedSet;
        $stmt = $pdo->prepare('INSERT INTO prompt_lines (set_id, role, content, orden) VALUES (?,?,?,?)');
        $stmt->execute([
            $setId,
            $_POST['line_role'],
            $_POST['line_content'],
            (int)$_POST['line_order']
        ]);
    }
    if (isset($_POST['line_id']) && isset($_POST['line_content'])) {
        $setId = isset($_POST['set_id']) ? (int)$_POST['set_id'] : $selectedSet;
        $stmt = $pdo->prepare('UPDATE prompt_lines SET role = ?, content = ?, orden = ? WHERE id = ? AND set_id = ?');
        $stmt->execute([
            $_POST['line_role'],
            $_POST['line_content'],
            (int)$_POST['line_order'],
            $_POST['line_id'],
            $setId
        ]);
    }
}
if (isset($_GET['del_question'])) {
    $stmt = $pdo->prepare('DELETE FROM preguntas_admin WHERE id = ?');
    $stmt->execute([$_GET['del_question']]);
}
if (isset($_GET['del_set'])) {
    $stmt = $pdo->prepare('DELETE FROM prompt_sets WHERE id = ?');
    $stmt->execute([$_GET['del_set']]);
    $selectedSet = null;
}
if (isset($_GET['del_line']) && $selectedSet) {
    $stmt = $pdo->prepare('DELETE FROM prompt_lines WHERE id = ? AND set_id = ?');
    $stmt->execute([$_GET['del_line'], $selectedSet]);
}

$users = $pdo->query('SELECT id, nombre, apellido, email, telefono, es_admin, prompt_set_id FROM usuarios')->fetchAll();
$promptSets = $pdo->query('SELECT id, nombre FROM prompt_sets ORDER BY id')->fetchAll();
$questions = $pdo->query('SELECT id, texto_pregunta FROM preguntas_admin ORDER BY id')->fetchAll();
$promptLines = [];
if ($selectedSet) {
    $stmt = $pdo->prepare('SELECT id, role, content, orden FROM prompt_lines WHERE set_id = ? ORDER BY orden');
    $stmt->execute([$selectedSet]);
    $promptLines = $stmt->fetchAll();
}

// Get selected set name for display
$selectedSetName = '';
if ($selectedSet) {
    $stmt = $pdo->prepare('SELECT nombre FROM prompt_sets WHERE id = ?');
    $stmt->execute([$selectedSet]);
    $result = $stmt->fetch();
    $selectedSetName = $result ? $result['nombre'] : '';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>⚡ Panel de Administración - Celestial Chat</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>

<!-- Header -->
<header class="admin-header">
    <div class="header-content">
        <h1 class="admin-title">
            <i class="fas fa-shield-alt"></i>
            Panel de Administración
        </h1>
        <div class="admin-actions">
            <a href="chat.php" class="btn">
                <i class="fas fa-comments"></i>
                Ir al Chat
            </a>
            <a href="logout.php" class="btn btn-danger">
                <i class="fas fa-sign-out-alt"></i>
                Cerrar Sesión
            </a>
        </div>
    </div>
</header>

<!-- Main Container -->
<div class="admin-container">
    
    <!-- Stats Overview -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?php echo count($users); ?></div>
            <div class="stat-label">
                <i class="fas fa-users"></i> Usuarios Totales
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo count(array_filter($users, fn($u) => $u['es_admin'])); ?></div>
            <div class="stat-label">
                <i class="fas fa-user-shield"></i> Administradores
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo count($questions); ?></div>
            <div class="stat-label">
                <i class="fas fa-question-circle"></i> Preguntas
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo count($promptSets); ?></div>
            <div class="stat-label">
                <i class="fas fa-cogs"></i> Sets de Prompts
            </div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="tab-navigation">
        <button class="tab-btn active" onclick="showTab('users')">
            <i class="fas fa-users"></i> Usuarios
        </button>
        <button class="tab-btn" onclick="showTab('questions')">
            <i class="fas fa-question-circle"></i> Preguntas
        </button>
        <button class="tab-btn" onclick="showTab('prompts')">
            <i class="fas fa-cogs"></i> Prompts
        </button>
    </div>

    <!-- Users Tab -->
    <div id="users-tab" class="tab-content active">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-users"></i>
                    Gestión de Usuarios
                </h2>
            </div>
            
            <?php if (!empty($users)): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuario</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Rol</th>
                            <th>Prompt Set</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?php echo $u['id']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($u['nombre'].' '.$u['apellido']); ?></strong>
                            </td>
                            <td><?php echo htmlspecialchars($u['email']); ?></td>
                            <td><?php echo htmlspecialchars($u['telefono'] ?: 'No especificado'); ?></td>
                            <td>
                                <?php if ($u['es_admin']): ?>
                                    <span class="user-badge">
                                        <i class="fas fa-crown"></i> Admin
                                    </span>
                                <?php else: ?>
                                    <span class="user-badge" style="background: rgba(52, 73, 94, 0.3); color: var(--text-muted);">
                                        <i class="fas fa-user"></i> Usuario
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="post" class="form-inline">
                                    <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                    <select name="prompt_set_id" class="form-select">
                                        <?php foreach ($promptSets as $set): ?>
                                            <option value="<?php echo $set['id']; ?>" <?php if($set['id']==$u['prompt_set_id']) echo 'selected'; ?>>
                                                <?php echo htmlspecialchars($set['nombre']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" name="prompt_update" class="btn btn-success">
                                        <i class="fas fa-save"></i>
                                    </button>
                                </form>
                            </td>
                            <td>
                                <a href="?delete_user=<?php echo $u['id']; ?>" 
                                   class="btn btn-danger" 
                                   onclick="return confirm('¿Estás seguro de eliminar este usuario?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-users"></i>
                    <h3>No hay usuarios registrados</h3>
                    <p>Los usuarios aparecerán aquí cuando se registren en el sistema.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Questions Tab -->
    <div id="questions-tab" class="tab-content">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-question-circle"></i>
                    Gestión de Preguntas
                </h2>
            </div>
            
            <!-- Add New Question -->
            <form method="post" class="form-inline" style="margin-bottom: 1.5rem;">
                <input type="text" name="new_question" class="form-input" placeholder="Escribe una nueva pregunta..." required>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Agregar
                </button>
            </form>
            
            <?php if (!empty($questions)): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Pregunta</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($questions as $q): ?>
                        <tr>
                            <form method="post">
                                <td><?php echo $q['id']; ?></td>
                                <td>
                                    <input type="hidden" name="edit_id" value="<?php echo $q['id']; ?>">
                                    <input type="text" name="edit_text" value="<?php echo htmlspecialchars($q['texto_pregunta']); ?>" class="form-input">
                                </td>
                                <td>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-save"></i> Guardar
                                        </button>
                                        <a href="?del_question=<?php echo $q['id']; ?>" 
                                           class="btn btn-danger" 
                                           onclick="return confirm('¿Eliminar esta pregunta?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </form>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-question-circle"></i>
                    <h3>No hay preguntas configuradas</h3>
                    <p>Agrega preguntas para que aparezcan en el sistema.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Prompts Tab -->
    <div id="prompts-tab" class="tab-content">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-cogs"></i>
                    Gestión de Prompts
                </h2>
            </div>
            
            <!-- Add New Set -->
            <form method="post" class="form-inline" style="margin-bottom: 1.5rem;">
                <input type="text" name="new_set_name" class="form-input" placeholder="Nombre del nuevo conjunto..." required>
                <button type="submit" name="add_set" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Crear Set
                </button>
            </form>
            
            <?php if (!empty($promptSets)): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre del Conjunto</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($promptSets as $pset): ?>
                        <tr>
                            <form method="post" action="?prompt_set=<?php echo $pset['id']; ?>">
                                <td><?php echo $pset['id']; ?></td>
                                <td>
                                    <input type="hidden" name="set_id" value="<?php echo $pset['id']; ?>">
                                    <input type="text" name="set_name" value="<?php echo htmlspecialchars($pset['nombre']); ?>" class="form-input">
                                </td>
                                <td>
                                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                        <button type="submit" name="rename_set" class="btn btn-success">
                                            <i class="fas fa-save"></i>
                                        </button>
                                        <a href="?prompt_set=<?php echo $pset['id']; ?>" class="btn">
                                            <i class="fas fa-eye"></i> Ver
                                        </a>
                                        <a href="?del_set=<?php echo $pset['id']; ?>" 
                                           class="btn btn-danger" 
                                           onclick="return confirm('¿Eliminar este conjunto?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </form>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-cogs"></i>
                    <h3>No hay conjuntos de prompts</h3>
                    <p>Crea conjuntos de prompts para configurar el comportamiento del chat.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Prompt Lines for Selected Set -->
        <?php if ($selectedSet): ?>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list"></i>
                    Mensajes del conjunto: <?php echo htmlspecialchars($selectedSetName); ?>
                </h3>
                <a href="admin.php" class="btn">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
            
            <!-- Add New Line -->
            <form method="post" action="?prompt_set=<?php echo $selectedSet; ?>" class="form-inline" style="margin-bottom: 1.5rem;">
                <input type="hidden" name="set_id" value="<?php echo $selectedSet; ?>">
                <input type="number" name="line_order" value="<?php echo count($promptLines)+1; ?>" class="form-input" style="max-width: 80px;" min="1">
                <select name="line_role" class="form-select" style="max-width: 120px;">
                    <option value="system">System</option>
                    <option value="assistant">Assistant</option>
                    <option value="user">User</option>
                </select>
                <input type="text" name="line_content" class="form-input" placeholder="Contenido del mensaje..." required>
                <button type="submit" name="add_line" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Agregar
                </button>
            </form>
            
            <?php if (!empty($promptLines)): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Orden</th>
                            <th>Rol</th>
                            <th>Contenido</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($promptLines as $line): ?>
                        <tr>
                            <form method="post" action="?prompt_set=<?php echo $selectedSet; ?>">
                                <td>
                                    <input type="hidden" name="set_id" value="<?php echo $selectedSet; ?>">
                                    <input type="hidden" name="line_id" value="<?php echo $line['id']; ?>">
                                    <input type="number" name="line_order" value="<?php echo $line['orden']; ?>" class="form-input" style="max-width: 60px;" min="1">
                                </td>
                                <td>
                                    <select name="line_role" class="form-select">
                                        <option value="system" <?php if($line['role']=='system') echo 'selected'; ?>>System</option>
                                        <option value="assistant" <?php if($line['role']=='assistant') echo 'selected'; ?>>Assistant</option>
                                        <option value="user" <?php if($line['role']=='user') echo 'selected'; ?>>User</option>
                                    </select>
                                    <span class="role-tag role-<?php echo $line['role']; ?>" style="margin-left: 0.5rem;">
                                        <?php echo ucfirst($line['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <textarea name="line_content" rows="3" class="form-textarea"><?php echo htmlspecialchars($line['content']); ?></textarea>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 0.5rem; flex-direction: column;">
                                        <button type="submit" name="edit_line" class="btn btn-success">
                                            <i class="fas fa-save"></i> Guardar
                                        </button>
                                        <a href="?prompt_set=<?php echo $selectedSet; ?>&del_line=<?php echo $line['id']; ?>" 
                                           class="btn btn-danger" 
                                           onclick="return confirm('¿Eliminar este mensaje?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </form>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-list"></i>
                    <h3>No hay mensajes en este conjunto</h3>
                    <p>Agrega mensajes para configurar el comportamiento del chat.</p>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Tab functionality
function showTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
    });
    
    // Remove active class from all tab buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show selected tab content
    document.getElementById(tabName + '-tab').classList.add('active');
    
    // Add active class to clicked button
    event.target.classList.add('active');
}

// Auto-save functionality for forms
document.addEventListener('DOMContentLoaded', function() {
    // Add loading states to forms
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
                submitBtn.disabled = true;
                
                // Re-enable after 3 seconds if still on page
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 3000);
            }
        });
    });
    
    // Auto-resize textareas
    document.querySelectorAll('textarea').forEach(textarea => {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
        
        // Initial resize
        textarea.style.height = textarea.scrollHeight + 'px';
    });
    
    // Confirm deletion dialogs with more context
    document.querySelectorAll('a[onclick*="confirm"]').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            let message = 'Esta acción no se puede deshacer.';
            if (this.href.includes('delete_user')) {
                message = '¿Estás seguro de eliminar este usuario?\n\nSe eliminarán todos sus datos y conversaciones.';
            } else if (this.href.includes('del_question')) {
                message = '¿Estás seguro de eliminar esta pregunta?\n\nYa no estará disponible en el sistema.';
            } else if (this.href.includes('del_set')) {
                message = '¿Estás seguro de eliminar este conjunto?\n\nSe eliminarán todos los mensajes asociados.';
            } else if (this.href.includes('del_line')) {
                message = '¿Estás seguro de eliminar este mensaje?\n\nEsto puede afectar el comportamiento del chat.';
            }
            
            if (confirm(message)) {
                // Show loading state
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Eliminando...';
                this.style.pointerEvents = 'none';
                window.location.href = this.href;
            }
        });
    });
    
    // Success/Error notifications
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('success')) {
        showNotification('Operación completada exitosamente', 'success');
    }
    if (urlParams.has('error')) {
        showNotification('Ocurrió un error al procesar la solicitud', 'error');
    }
    
    // Auto-focus on inputs when tabs change
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            setTimeout(() => {
                const activeTab = document.querySelector('.tab-content.active');
                const firstInput = activeTab.querySelector('input[type="text"], textarea');
                if (firstInput) {
                    firstInput.focus();
                }
            }, 100);
        });
    });
    
    // Enhanced form validation
    document.querySelectorAll('input[required], textarea[required]').forEach(field => {
        field.addEventListener('invalid', function() {
            this.style.borderColor = 'var(--error-red)';
            this.style.boxShadow = '0 0 10px rgba(231, 76, 60, 0.3)';
        });
        
        field.addEventListener('input', function() {
            if (this.validity.valid) {
                this.style.borderColor = 'var(--success-green)';
                this.style.boxShadow = '0 0 10px rgba(39, 174, 96, 0.3)';
            }
        });
    });
    
    // Smart table sorting
    document.querySelectorAll('.data-table th').forEach(header => {
        header.style.cursor = 'pointer';
        header.addEventListener('click', function() {
            sortTable(this);
        });
    });
});

// Notification system
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
        ${message}
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Table sorting function
function sortTable(header) {
    const table = header.closest('table');
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    const columnIndex = Array.from(header.parentNode.children).indexOf(header);
    
    // Determine sort direction
    const isAscending = !header.classList.contains('sort-asc');
    
    // Clear previous sort indicators
    header.parentNode.querySelectorAll('th').forEach(th => {
        th.classList.remove('sort-asc', 'sort-desc');
    });
    
    // Add sort indicator
    header.classList.add(isAscending ? 'sort-asc' : 'sort-desc');
    
    // Sort rows
    rows.sort((a, b) => {
        const aText = a.children[columnIndex].textContent.trim();
        const bText = b.children[columnIndex].textContent.trim();
        
        // Check if values are numbers
        const aNum = parseFloat(aText);
        const bNum = parseFloat(bText);
        
        if (!isNaN(aNum) && !isNaN(bNum)) {
            return isAscending ? aNum - bNum : bNum - aNum;
        }
        
        // String comparison
        return isAscending ? aText.localeCompare(bText) : bText.localeCompare(aText);
    });
    
    // Re-append sorted rows
    rows.forEach(row => tbody.appendChild(row));
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + S to save current form
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault();
        const activeTab = document.querySelector('.tab-content.active');
        const form = activeTab.querySelector('form');
        if (form) {
            form.submit();
        }
    }
    
    // Escape to close modals or reset forms
    if (e.key === 'Escape') {
        document.querySelectorAll('input[type="text"], textarea').forEach(field => {
            if (document.activeElement === field) {
                field.blur();
            }
        });
    }
});

// Auto-save drafts for textareas (simple localStorage implementation)
document.querySelectorAll('textarea').forEach(textarea => {
    const key = `draft_${textarea.name}_${window.location.pathname}`;
    
    // Load saved draft
    const savedDraft = localStorage.getItem(key);
    if (savedDraft && !textarea.value.trim()) {
        textarea.value = savedDraft;
        textarea.style.borderColor = 'var(--warning-orange)';
        textarea.title = 'Borrador guardado automáticamente';
    }
    
    // Save draft on input
    textarea.addEventListener('input', function() {
        localStorage.setItem(key, this.value);
        this.style.borderColor = 'var(--warning-orange)';
        this.title = 'Borrador guardado automáticamente';
    });
    
    // Clear draft on successful submit
    textarea.closest('form').addEventListener('submit', function() {
        localStorage.removeItem(key);
    });
});

// Enhanced user experience features
document.addEventListener('DOMContentLoaded', function() {
    // Add tooltips to action buttons
    document.querySelectorAll('.btn').forEach(btn => {
        if (!btn.title && btn.querySelector('i')) {
            const icon = btn.querySelector('i').className;
            if (icon.includes('fa-save')) btn.title = 'Guardar cambios';
            if (icon.includes('fa-trash')) btn.title = 'Eliminar elemento';
            if (icon.includes('fa-eye')) btn.title = 'Ver detalles';
            if (icon.includes('fa-plus')) btn.title = 'Agregar nuevo';
            if (icon.includes('fa-arrow-left')) btn.title = 'Volver';
        }
    });
    
    // Highlight unsaved changes
    document.querySelectorAll('input, textarea, select').forEach(field => {
        const originalValue = field.value;
        field.addEventListener('input', function() {
            if (this.value !== originalValue) {
                this.style.backgroundColor = 'rgba(241, 196, 15, 0.1)';
                this.style.borderColor = 'var(--warning-orange)';
            } else {
                this.style.backgroundColor = '';
                this.style.borderColor = '';
            }
        });
    });
});
</script>

</body>
</html>
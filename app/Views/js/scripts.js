// Simulación de datos (reemplazar con llamadas a la API real)
const mockTasks = [
    { ID_task: 1, subject: "Preparar presentación", description: "Crear slides para reunión", priority: 3, stat: 1, expiration_date: "2025-04-25", reminder_date: null, color: "#ff0000", owner: 1, archived: 0 },
    { ID_task: 2, subject: "Revisar informe", description: "Corregir errores en informe financiero", priority: 2, stat: 2, expiration_date: "2025-04-22", reminder_date: "2025-04-20", color: "#00ff00", owner: 2, archived: 0 }
];

const mockSubtasks = [
    { ID_subtask: 1, description: "Redactar introducción", stat: 1, priority: null, expiration_date: null, cmt: null, task: 1, assignee: 1 },
    { ID_subtask: 2, description: "Diseñar diapositivas", stat: 2, priority: 2, expiration_date: "2025-04-24", cmt: "Usar plantilla corporativa", task: 1, assignee: 2 }
];

const mockInvitations = [
    { recipient: 1, task: 3, stat: 1 }
];

const mockUsers = [
    { ID_user: 1, name: "Juan Pérez", email: "juan@example.com" },
    { ID_user: 2, name: "María Gómez", email: "maria@example.com" }
];

const mockUser = { ID_user: 1, name: "Juan Pérez", email: "juan@example.com" };

// Funciones de autenticación
function login(event) {
    event.preventDefault();
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;

    // Validar email
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        alert('Por favor, ingrese un correo electrónico válido.');
        return;
    }

    // Enviar solicitud al backend
    fetch('/api/login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, password })
    })
    .then(response => response.json())
    .then(data => {
        if (data.token) {
            localStorage.setItem('token', data.token);
            window.location.href = 'index.html';
        } else {
            alert('Credenciales incorrectas.');
        }
    })
    .catch(error => alert('Error al iniciar sesión: ' + error));
}

function register(event) {
    event.preventDefault();
    const name = document.getElementById('name').value;
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;

    // Validaciones
    if (name.trim() === '') {
        alert('El nombre no puede estar vacío.');
        return;
    }
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        alert('Por favor, ingrese un correo electrónico válido.');
        return;
    }
    if (password.length < 6) {
        alert('La contraseña debe tener al menos 6 caracteres.');
        return;
    }

    // Enviar solicitud al backend
    fetch('/api/register', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name, email, password })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Registro exitoso. Por favor, inicia sesión.');
            window.location.href = 'login.html';
        } else {
            alert('Error al registrarse: ' + data.message);
        }
    })
    .catch(error => alert('Error al registrarse: ' + error));
}

function logout() {
    localStorage.removeItem('token');
    window.location.href = 'login.html';
}

// Funciones para Home
function loadTasks() {
    const sortBy = document.getElementById('sortTasks').value;
    // Obtener tareas desde el backend (simulado)
    const tasks = mockTasks.filter(task => task.owner === mockUser.ID_user || mockSubtasks.some(st => st.task === task.ID_task && st.assignee === mockUser.ID_user)).sort((a, b) => {
        if (sortBy === 'expiration_date') return new Date(a.expiration_date) - new Date(b.expiration_date);
        if (sortBy === 'priority') return b.priority - a.priority;
        return a.subject.localeCompare(b.subject);
    });

    const tasksList = document.getElementById('tasksList');
    tasksList.innerHTML = '';

    tasks.forEach(task => {
        const isOwner = task.owner === mockUser.ID_user;
        const card = document.createElement('div');
        card.className = `col-md-6 task-card ${task.priority === 3 ? 'priority-high' : ''}`;
        card.style.borderLeftColor = task.color;
        card.innerHTML = `
            <div class="card shadow">
                <div class="card-body">
                    <h5 class="card-title">${task.subject}</h5>
                    <p class="card-text">${task.description}</p>
                    <p><strong>Prioridad:</strong> ${['Baja', 'Normal', 'Alta'][task.priority - 1]}</p>
                    <p><strong>Estado:</strong> ${['Definido', 'En proceso', 'Completada'][task.stat - 1]}</p>
                    <p><strong>Vencimiento:</strong> ${task.expiration_date}</p>
                    ${task.reminder_date ? `<p><strong>Recordatorio:</strong> ${task.reminder_date}</p>` : ''}
                    <div class="btn-group">
                        <button class="btn btn-sm btn-primary" onclick="toggleSubtasks(${task.ID_task})">Subtareas</button>
                        ${isOwner ? `
                            <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#createSubtaskModal" onclick="prepareSubtaskForm(${task.ID_task})">Crear Subtarea</button>
                            <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#inviteCollaboratorModal" onclick="prepareInviteForm(${task.ID_task})">Invitar</button>
                            <button class="btn btn-sm btn-warning" onclick="editTask(${task.ID_task})">Editar</button>
                            <button class="btn btn-sm btn-danger" onclick="deleteTask(${task.ID_task})">Eliminar</button>
                            ${task.stat === 3 && !task.archived ? `<button class="btn btn-sm btn-secondary" onclick="archiveTask(${task.ID_task})">Archivar</button>` : ''}
                        ` : ''}
                    </div>
                </div>
                <div id="subtasks-${task.ID_task}" class="subtask-list collapse"></div>
            </div>
        `;
        tasksList.appendChild(card);
    });
}

function toggleSubtasks(taskId) {
    const subtasksDiv = document.getElementById(`subtasks-${taskId}`);
    const isShown = subtasksDiv.classList.contains('show');

    if (!isShown) {
        // Cargar subtareas (simulado)
        const subtasks = mockSubtasks.filter(st => st.task === taskId);
        subtasksDiv.innerHTML = '';

        subtasks.forEach(subtask => {
            const isAssignee = subtask.assignee === mockUser.ID_user;
            const isOwner = mockTasks.find(t => t.ID_task === taskId).owner === mockUser.ID_user;
            const subtaskItem = document.createElement('div');
            subtaskItem.className = 'subtask-item';
            subtaskItem.innerHTML = `
                <p><strong>Descripción:</strong> ${subtask.description}</p>
                <p><strong>Estado:</strong> ${['Definido', 'En proceso', 'Completada'][subtask.stat - 1]}</p>
                ${subtask.priority ? `<p><strong>Prioridad:</strong> ${['Baja', 'Normal', 'Alta'][subtask.priority - 1]}</p>` : ''}
                ${subtask.expiration_date ? `<p><strong>Vencimiento:</strong> ${subtask.expiration_date}</p>` : ''}
                ${subtask.cmt ? `<p><strong>Comentario:</strong> ${subtask.cmt}</p>` : ''}
                <p><strong>Asignado a:</strong> ${mockUsers.find(u => u.ID_user === subtask.assignee)?.name || 'Desconocido'}</p>
                ${(isAssignee || isOwner) ? `
                    <select class="form-select w-auto d-inline-block" onchange="updateSubtaskStatus(${subtask.ID_subtask}, this.value)">
                        <option value="1" ${subtask.stat === 1 ? 'selected' : ''}>Definido</option>
                        <option value="2" ${subtask.stat === 2 ? 'selected' : ''}>En proceso</option>
                        <option value="3" ${subtask.stat === 3 ? 'selected' : ''}>Completada</option>
                    </select>
                ` : ''}
                ${isOwner ? `
                    <button class="btn btn-sm btn-danger" onclick="deleteSubtask(${subtask.ID_subtask}, ${taskId})">Eliminar</button>
                ` : ''}
            `;
            subtasksDiv.appendChild(subtaskItem);
        });
    }

    subtasksDiv.classList.toggle('show');
}

function prepareSubtaskForm(taskId) {
    document.getElementById('subtaskTaskId').value = taskId;
    const assigneeSelect = document.getElementById('subtaskAssignee');
    assigneeSelect.innerHTML = mockUsers.map(user => `<option value="${user.ID_user}">${user.name}</option>`).join('');
}

function createSubtask(event) {
    event.preventDefault();
    const subtask = {
        task: parseInt(document.getElementById('subtaskTaskId').value),
        description: document.getElementById('subtaskDescription').value,
        priority: document.getElementById('subtaskPriority').value || null,
        expiration_date: document.getElementById('subtaskExpirationDate').value || null,
        cmt: document.getElementById('subtaskComment').value || null,
        assignee: parseInt(document.getElementById('subtaskAssignee').value),
        stat: 1
    };

    // Validaciones
    if (!subtask.description.trim()) {
        alert('La descripción es obligatoria.');
        return;
    }
    if (!subtask.assignee) {
        alert('Debe asignar la subtarea a un usuario.');
        return;
    }

    // Enviar al backend
    fetch('/api/subtasks', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + localStorage.getItem('token')
        },
        body: JSON.stringify(subtask)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Subtarea creada exitosamente.');
            bootstrap.Modal.getInstance(document.getElementById('createSubtaskModal')).hide();
            toggleSubtasks(subtask.task);
        } else {
            alert('Error al crear la subtarea.');
        }
    })
    .catch(error => alert('Error: ' + error));
}

function prepareInviteForm(taskId) {
    document.getElementById('inviteTaskId').value = taskId;
}

function inviteCollaborator(event) {
    event.preventDefault();
    const email = document.getElementById('inviteEmail').value;
    const taskId = parseInt(document.getElementById('inviteTaskId').value);

    // Validar email
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        alert('Por favor, ingrese un correo electrónico válido.');
        return;
    }

    // Verificar si el usuario ya fue invitado (simulado)
    if (mockInvitations.some(inv => inv.task === taskId && mockUsers.find(u => u.email === email && u.ID_user === inv.recipient))) {
        alert('Este usuario ya fue invitado a la tarea.');
        return;
    }

    // Enviar al backend
    fetch('/api/invitations', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + localStorage.getItem('token')
        },
        body: JSON.stringify({ email, task: taskId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Invitación enviada exitosamente.');
            bootstrap.Modal.getInstance(document.getElementById('inviteCollaboratorModal')).hide();
        } else {
            alert('Error al enviar la invitación.');
        }
    })
    .catch(error => alert('Error: ' + error));
}

function createTask(event) {
    event.preventDefault();
    const task = {
        subject: document.getElementById('taskSubject').value,
        description: document.getElementById('taskDescription').value,
        priority: parseInt(document.getElementById('taskPriority').value),
        expiration_date: document.getElementById('taskExpirationDate').value,
        reminder_date: document.getElementById('taskReminderDate').value || null,
        color: document.getElementById('taskColor').value,
        owner: mockUser.ID_user,
        stat: 1,
        archived: 0
    };

    // Validaciones
    if (!task.subject.trim() || !task.description.trim()) {
        alert('Asunto y descripción son obligatorios.');
        return;
    }
    if (!task.expiration_date) {
        alert('La fecha de vencimiento es obligatoria.');
        return;
    }

    // Enviar al backend
    fetch('/api/tasks', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + localStorage.getItem('token')
        },
        body: JSON.stringify(task)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Tarea creada exitosamente.');
            bootstrap.Modal.getInstance(document.getElementById('createTaskModal')).hide();
            loadTasks();
        } else {
            alert('Error al crear la tarea.');
        }
    })
    .catch(error => alert('Error: ' + error));
}

function editTask(taskId) {
    // Implementar lógica para editar tarea (modal similar a createTaskModal)
    alert('Funcionalidad de edición no implementada en este ejemplo.');
}

function deleteTask(taskId) {
    if (!confirm('¿Estás seguro de que quieres eliminar esta tarea?')) return;

    // Enviar al backend
    fetch(`/api/tasks/${taskId}`, {
        method: 'DELETE',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Tarea eliminada exitosamente.');
            loadTasks();
        } else {
            alert('Error al eliminar la tarea. Solo el dueño puede eliminarla.');
        }
    })
    .catch(error => alert('Error: ' + error));
}

function archiveTask(taskId) {
    fetch(`/api/tasks/${taskId}/archive`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + localStorage.getItem('token')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Tarea archivada exitosamente.');
            loadTasks();
        } else {
            alert('Error al archivar la tarea.');
        }
    })
    .catch(error => alert('Error: ' + error));
}

function deleteSubtask(subtaskId, taskId) {
    if (!confirm('¿Estás seguro de que quieres eliminar esta subtarea?')) return;

    // Enviar al backend
    fetch(`/api/subtasks/${subtaskId}`, {
        method: 'DELETE',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Subtarea eliminada exitosamente.');
            toggleSubtasks(taskId);
        } else {
            alert('Error al eliminar la subtarea. Solo el dueño puede eliminarla.');
        }
    })
    .catch(error => alert('Error: ' + error));
}

function updateSubtaskStatus(subtaskId, status) {
    // Enviar al backend
    fetch(`/api/subtasks/${subtaskId}/status`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + localStorage.getItem('token')
        },
        body: JSON.stringify({ stat: parseInt(status) })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Estado de la subtarea actualizado.');
            const taskId = mockSubtasks.find(st => st.ID_subtask === subtaskId).task;
            toggleSubtasks(taskId);
        } else {
            alert('Error al actualizar el estado. Solo el asignado puede modificarlo.');
        }
    })
    .catch(error => alert('Error: ' + error));
}

function loadInvitations() {
    // Obtener invitaciones desde el backend (simulado)
    const invitations = mockInvitations.filter(inv => inv.recipient === mockUser.ID_user && inv.stat === 1);
    const invitationsList = document.getElementById('invitationsList');
    invitationsList.innerHTML = '';

    invitations.forEach(inv => {
        const item = document.createElement('div');
        item.className = 'invitation-item list-group-item d-flex justify-content-between align-items-center';
        item.innerHTML = `
            Invitación para la tarea ${inv.task}
            <div>
                <button class="btn btn-sm btn-success" onclick="acceptInvitation(${inv.task})">Aceptar</button>
                <button class="btn btn-sm btn-danger" onclick="rejectInvitation(${inv.task})">Rechazar</button>
            </div>
        `;
        invitationsList.appendChild(item);
    });
}

function acceptInvitation(taskId) {
    fetch('/api/invitations/accept', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + localStorage.getItem('token')
        },
        body: JSON.stringify({ task: taskId, recipient: mockUser.ID_user })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Invitación aceptada.');
            loadInvitations();
            loadTasks();
        } else {
            alert('Error al aceptar la invitación.');
        }
    })
    .catch(error => alert('Error: ' + error));
}

function rejectInvitation(taskId) {
    fetch('/api/invitations/reject', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + localStorage.getItem('token')
        },
        body: JSON.stringify({ task: taskId, recipient: mockUser.ID_user })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Invitación rechazada.');
            loadInvitations();
        } else {
            alert('Error al rechazar la invitación.');
        }
    })
    .catch(error => alert('Error: ' + error));
}

// Funciones para Perfil
function loadProfile() {
    // Cargar datos del usuario (simulado)
    document.getElementById('name').value = mockUser.name;
    document.getElementById('email').value = mockUser.email;

    // Cargar resumen de tareas (simulado)
    const summary = document.getElementById('tasksSummary');
    summary.innerHTML = `
        <p><strong>Tareas Activas:</strong> ${mockTasks.filter(t => !t.archived).length}</p>
        <p><strong>Tareas Archivadas:</strong> ${mockTasks.filter(t => t.archived).length}</p>
    `;
}

function updateProfile(event) {
    event.preventDefault();
    const name = document.getElementById('name').value;
    const email = document.getElementById('email').value;

    // Validaciones
    if (name.trim() === '') {
        alert('El nombre no puede estar vacío.');
        return;
    }
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        alert('Por favor, ingrese un correo electrónico válido.');
        return;
    }

    // Enviar al backend
    fetch('/api/profile', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + localStorage.getItem('token')
        },
        body: JSON.stringify({ name, email })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Perfil actualizado exitosamente.');
        } else {
            alert('Error al actualizar el perfil.');
        }
    })
    .catch(error => alert('Error: ' + error));
}

function changePassword(event) {
    event.preventDefault();
    const currentPassword = document.getElementById('currentPassword').value;
    const newPassword = document.getElementById('newPassword').value;

    // Validaciones
    if (newPassword.length < 6) {
        alert('La nueva contraseña debe tener al menos 6 caracteres.');
        return;
    }

    // Enviar al backend
    fetch('/api/password', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + localStorage.getItem('token')
        },
        body: JSON.stringify({ currentPassword, newPassword })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Contraseña cambiada exitosamente.');
        } else {
            alert('Error al cambiar la contraseña.');
        }
    })
    .catch(error => alert('Error: ' + error));
}

// Inicialización
document.addEventListener('DOMContentLoaded', () => {
    if (window.location.pathname.includes('login.html')) {
        document.getElementById('loginForm').addEventListener('submit', login);
    } else if (window.location.pathname.includes('register.html')) {
        document.getElementById('registerForm').addEventListener('submit', register);
    } else if (window.location.pathname.includes('index.html')) {
        if (!localStorage.getItem('token')) {
            window.location.href = 'login.html';
        } else {
            loadTasks();
            loadInvitations();
            document.getElementById('createTaskForm').addEventListener('submit', createTask);
            document.getElementById('createSubtaskForm').addEventListener('submit', createSubtask);
            document.getElementById('inviteCollaboratorForm').addEventListener('submit', inviteCollaborator);
        }
    } else if (window.location.pathname.includes('profile.html')) {
        if (!localStorage.getItem('token')) {
            window.location.href = 'login.html';
        } else {
            loadProfile();
            document.getElementById('profileForm').addEventListener('submit', updateProfile);
            document.getElementById('changePasswordForm').addEventListener('submit', changePassword);
        }
    }
});
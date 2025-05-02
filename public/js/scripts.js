const URL_BASE = window.location.origin + '/ato';

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

function login(event) {
    event.preventDefault();
    const email = document.getElementById('email').value;
    const pass = document.getElementById('password').value;

    if (!/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(email)) {
        alert('Por favor, ingrese un correo electrónico válido.');
        return;
    }

    fetch(`${URL_BASE}/auth`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, pass })
    })
        .then(response => response.json())
        .then(response => {
            if (response.code === 200) {
                console.log(response.data);
                localStorage.setItem('user', JSON.stringify(response.data.user));
                localStorage.setItem('isLoggedIn', 'true');
                window.location.href = `${URL_BASE}/workspace`;
            } else {
                showToast('El correo o la contraseña son incorrectas', 4000, 'error');
            }
        })
        .catch(error => showToast('Ups!. Ocurrió un error, reintente nuevamente', 4000, 'error'));
}

function register(event) {
    event.preventDefault();
    const name = document.getElementById('name').value;
    const surname = document.getElementById('surname').value;
    const email = document.getElementById('email').value;
    const pass = document.getElementById('password').value;

    if (name.trim() === '') {
        alert('El nombre no puede estar vacío.');
        return;
    }
    if (surname.trim() === '') {
        alert('El apellido no puede estar vacío.');
        return;
    }
    if (!/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(email)) {
        alert('Por favor, ingrese un correo electrónico válido.');
        return;
    }
    if (pass.length < 6) {
        alert('La contraseña debe tener al menos 6 caracteres.');
        return;
    }

    fetch(`${URL_BASE}/users`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name, surname, email, pass })
    })
        .then(response => response.json())
        .then(response => {
            if (response.code === 201) {
                localStorage.setItem('user', JSON.stringify(response.data.user));
                localStorage.setItem('isLoggedIn', 'true');
                window.location.href = `${URL_BASE}/workspace`;
            } else {
                showToast(response?.message, 4000, 'warning')
            }
        })
        .catch(error => showToast('Ups!. Ocurrió un error, reintente nuevamente', 4000, 'error'));
}

function logout() {
    fetch(`${URL_BASE}/deauth`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' }
    })
        .then(response => response.json())
        .then(response => {
            localStorage.removeItem('isLoggedIn');
            window.location.href = `${URL_BASE}/`;
        })
        .catch(error => alert('Error al cerrar sesión: ' + error));
}

async function loadTasks() {
    const user_id = JSON.parse(localStorage.getItem('user')).id;

    const data = await fetch(`${URL_BASE}/tasks?owner=${user_id}`, {
        method: 'GET',
    })
        .then(response => response.json())
        .then(response => {
            if (response.code == 200) {
                return response.data;
            }
        })
        .catch(error => showToast('Ups!. Ocurrió un error al cargar las tareas', 4000, 'error'));


    if (data.length > 0) {
        const sortBy = document.getElementById('sortTasks').value;

        const tasks = data.sort((a, b) => {
            if (sortBy === 'expiration_date') return new Date(a.expiration_date) - new Date(b.expiration_date);
            if (sortBy === 'priority') return b.priority - a.priority;
            if (sortBy === 'subject') return a.subject.localeCompare(b.subject);
            if (sortBy === 'color') return a.color.localeCompare(b.color);
        });

        const tasksList = document.getElementById('tasksList');
        tasksList.innerHTML = '';

        tasks.forEach(task => {
            const isOwner = task.owner === user_id;
            const card = document.createElement('div');
            card.className = "col-md-6 task-card";
            card.style.border = "none";
            card.innerHTML = `
            <div class="card shadow">
                <div class="card-body">
                    <span class="badge" style="position: absolute; right: 20px; border-radius: 100px; width: 20px; height: 20px; background-color: ${task.color};"> </span>
                    <h5 class="card-title">${task.subject}</h5>
                    <p class="card-text">${task.description}</p>
                    <p><strong>Prioridad:</strong> <span style="border-radius: ${task.priority == 3 ? '10px' : ''}; padding: ${task.priority == 3 ? '2px 10px' : ''}; color: ${task.priority == 3 ? '#FFFFFF' : ''}; background-color: ${task.priority == 3 ? '#dc3545' : ''}; font-weight: ${task.priority == 3 ? 'bold' : 'normal'};">${['Baja', 'Normal', 'Alta'][task.priority - 1]}</span></p>
                    <p><strong>Estado:</strong> <span>${['Definido', 'En proceso', 'Completada'][task.stat - 1]}</span></p>
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
    const user_id = JSON.parse(localStorage.getItem('user')).id;

    const task = {
        subject: document.getElementById('taskSubject').value,
        description: document.getElementById('taskDescription').value,
        priority: parseInt(document.getElementById('taskPriority').value),
        expiration_date: document.getElementById('taskExpirationDate').value,
        color: document.getElementById('taskColor').value,
        owner: user_id
    };

    const reminder_date = document.getElementById('taskReminderDate').value || null;
    if (reminder_date) {
        task.reminder_date = reminder_date;
    }

    // Validaciones
    if (!task.subject.trim() || !task.description.trim()) {
        showToast('Asunto y descripción son obligatorios', 4000, 'warning');
        return;
    }
    if (!task.expiration_date) {
        showToast('La fecha de vencimiento es obligatoria', 4000, 'warning');
        return;
    }

    // Enviar al backend
    fetch(`${URL_BASE}/tasks`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(task)
    })
        .then(response => response.json())
        .then(response => {
            if (response.code == 201) {
                showToast(response.message, 4000, 'success');
                bootstrap.Modal.getInstance(document.getElementById('createTaskModal')).hide();
                loadTasks();
            } else {
                showToast(response.message, 4000, 'error');
            }
        })
        .catch(error => showToast('Ups!. Ocurrió un error, reintente nuevamente', 4000, 'error'));
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

function loadProfile() {
    const user_id = JSON.parse(localStorage.getItem('user')).id;

    // Enviar solicitud al backend
    fetch(`${URL_BASE}/users/${user_id}`, {
        method: 'GET',
    })
        .then(response => response.json())
        .then(response => {
            user = response?.data[0];

            document.getElementById('name').value = user.name;
            document.getElementById('surname').value = user.surname;
            document.getElementById('email').value = user.email;
        })
        .catch(error => alert('Error al iniciar sesión: ' + error));

    // Cargar resumen de tareas (simulado)
    const summary = document.getElementById('tasksSummary');
    summary.innerHTML = `
        <p><strong>Tareas Activas:</strong> ${mockTasks.filter(t => !t.archived).length}</p>
        <p><strong>Tareas Archivadas:</strong> ${mockTasks.filter(t => t.archived).length}</p>
    `;
}

function updateProfile(event) {
    event.preventDefault();
    const user_id = JSON.parse(localStorage.getItem('user')).id;
    const name = document.getElementById('name').value;
    const surname = document.getElementById('surname').value;
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
    fetch(`${URL_BASE}/users/${user_id}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ name, surname, email })
    })
        .then(response => response.json())
        .then(response => {
            if (response.code === 200) {
                showToast(response?.message, 5000, 'success');
            } else {
                showToast(response?.message, 5000, 'error');
            }
        })
        .catch(error => showToast('Ups!. Ocurrió un error, reintente nuevamente', 4000, 'error'));
}

function changePassword(event) {
    event.preventDefault();
    const user_id = JSON.parse(localStorage.getItem('user')).id;
    const pass = document.getElementById('pass').value;

    // Validaciones
    if (pass.length < 6) {
        alert('La nueva contraseña debe tener al menos 6 caracteres.');
        return;
    }

    // Enviar al backend
    fetch(`${URL_BASE}/users/${user_id}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ pass })
    })
        .then(response => response.json())
        .then(response => {
            if (response.code == 200) {
                showToast(response?.message, 5000, 'success');
            } else {
                showToast(response?.message, 5000, 'error');
            }
        })
        .catch(error => showToast('Ups!. Ocurrió un error, reintente nuevamente', 4000, 'error'));
}

document.addEventListener('DOMContentLoaded', () => {
    const pathname = window.location.pathname;
    const normalizedPath = pathname.replace(/^\/ato\//, '/');

    switch (normalizedPath) {
        case '/':
            document.getElementById('loginForm').addEventListener('submit', login);
            break;
        case '/register':
            document.getElementById('registerForm').addEventListener('submit', register);
            break;
        case '/workspace':
            loadTasks();
            loadInvitations();
            document.getElementById('createTaskForm').addEventListener('submit', createTask);
            document.getElementById('createSubtaskForm').addEventListener('submit', createSubtask);
            document.getElementById('inviteCollaboratorForm').addEventListener('submit', inviteCollaborator);
            break;
        case '/profile':
            loadProfile();
            document.getElementById('profileForm').addEventListener('submit', updateProfile);
            document.getElementById('changePasswordForm').addEventListener('submit', changePassword);
            break;
        default:
            break;
    }

    const value = localStorage.getItem('isLoggedIn');

    if (value !== 'true' && normalizedPath !== '/' && normalizedPath !== '/register') {
        window.location.href = `${URL_BASE}/`;
    }
});

class Toast {
    constructor(message, duration = 4000, type = 'success', options = {}) {
        this.message = message;
        this.duration = duration;
        this.type = type;
        this.options = options;
        this.element = null;
    }

    create() {
        this.element = document.createElement('div');
        this.element.className = `custom-toast ${this.type}`;
        this.element.setAttribute('role', 'alert');
        this.element.setAttribute('aria-live', 'polite');

        // Type-specific icons
        const icons = {
            success: `<svg class="custom-toast-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>`,
            error: `<svg class="custom-toast-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>`,
            warning: `<svg class="custom-toast-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`,
            custom: `<svg class="custom-toast-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`
        };

        // Apply custom icon background color if provided
        if (this.type === 'custom' && this.options && this.options.iconColor) {
            this.element.style.setProperty('--custom-icon-bg', this.options.iconColor);
            this.element.querySelector('.custom-toast-icon')?.style.setProperty('background-color', this.options.iconColor);
        }

        this.element.innerHTML = `
        ${icons[this.type] || icons.success}
        <span class="custom-toast-message">${this.message}</span>
        <button class="custom-toast-close" aria-label="Close notification">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      `;

        document.body.appendChild(this.element);

        // Close button event
        this.element.querySelector('.custom-toast-close').addEventListener('click', () => this.hide());

        // Auto-hide after duration
        setTimeout(() => this.hide(), this.duration);
    }

    hide() {
        this.element.classList.add('hidden');
        setTimeout(() => {
            if (this.element) {
                this.element.remove();
            }
        }, 300); // Wait for animation
    }
}

function showToast(message, duration, type, options) {
    const toast = new Toast(message, duration, type, options);
    toast.create();
}
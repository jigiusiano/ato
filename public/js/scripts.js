const URL_BASE = window.location.origin + '/ato';

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
            localStorage.removeItem('user');
            window.location.href = `${URL_BASE}/`;
        })
        .catch(error => alert('Error al cerrar sesión: ' + error));
}

async function loadTasks() {
    const user_id = JSON.parse(localStorage.getItem('user')).id;

    const data = await fetch(`${URL_BASE}/tasks?user=${user_id}&archived=false`, {
        method: 'GET',
    })
        .then(response => response.json())
        .then(response => {
            if (response.code == 200) {
                return response.data;
            }
        })
        .catch(error => showToast('Ups!. Ocurrió un error al cargar las tareas', 4000, 'error'));

    const tasksList = document.getElementById('tasksList');
    tasksList.innerHTML = '';

    if (data?.length > 0) {
        const sortBy = document.getElementById('sortTasks').value;

        const tasks = data.sort((a, b) => {
            if (sortBy === 'expiration_date') return new Date(a.expiration_date) - new Date(b.expiration_date);
            if (sortBy === 'priority') return b.priority - a.priority;
            if (sortBy === 'subject') return a.subject.localeCompare(b.subject);
            if (sortBy === 'color') return a.color.localeCompare(b.color);
        });

        tasks.forEach(task => {
            const isOwner = task.owner === user_id;
            const card = document.createElement('div');
            card.className = `col-md-6 task-card ${task.priority == 3 ? 'priority-high' : ''}`;
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
                        <button class="btn btn-sm btn-primary" onclick="toggleSubtasks(${task.ID_task}, ${task.owner})">Subtareas</button>
                        ${isOwner ? `
                            <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#createSubtaskModal" onclick="prepareSubtaskForm(${task.ID_task})">Crear Subtarea</button>
                            <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#inviteCollaboratorModal" onclick="prepareInviteForm(${task.ID_task})">Invitar</button>
                            <button class="btn btn-sm btn-warning" onclick="editTask(${task.ID_task})">Editar</button>
                            <button class="btn btn-sm btn-danger" onclick="deleteTask(${task.ID_task})">Eliminar</button>
                            ${Number(task.stat) === 3 && !Boolean(Number(task.archived)) ? `<button class="btn btn-sm btn-secondary" onclick="archiveTask(${task.ID_task})">Archivar</button>` : ''}
                        ` : ''}
                    </div>
                </div>
                <div id="subtasks-${task.ID_task}" class="subtask-list collapse"></div>
            </div>
        `;
            tasksList.appendChild(card);
        });
    } else {
        tasksList.innerHTML = '<p>No hay tareas activas.</p>';
    }
}

async function loadArchivedTasks() {
    const user_id = JSON.parse(localStorage.getItem('user')).id;

    const tasks = await fetch(`${URL_BASE}/tasks?user=${user_id}&archived=true`, {
        method: 'GET',
    })
        .then(response => response.json())
        .then(response => {
            if (response.code == 200) {
                return response.data;
            }
        })
        .catch(error => showToast('Ups!. Ocurrió un error al cargar las tareas archivadas', 4000, 'error'));

    const archivedTasksList = document.getElementById('archivedTasksList');
    archivedTasksList.innerHTML = '';

    if (tasks?.length > 0) {
        tasks.forEach(task => {
            const card = document.createElement('div');
            card.className = `col-md-6 task-card ${task.priority == 3 ? 'priority-high' : ''}`;
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
                        <button class="btn btn-sm btn-primary" onclick="toggleSubtasks(${task.ID_task}, ${task.owner}, true)">Subtareas</button>
                        ${task.owner === user_id ? `
                            <button class="btn btn-sm btn-warning" onclick="editTask(${task.ID_task})">Editar</button>
                            <button class="btn btn-sm btn-danger" onclick="deleteTask(${task.ID_task})">Eliminar</button>
                            <button class="btn btn-sm btn-secondary" onclick="unarchiveTask(${task.ID_task})">Desarchivar</button>
                        ` : ''}
                    </div>
                </div>
                <div id="subtasks-${task.ID_task}" class="subtask-list collapse"></div>
            </div>
        `;
            archivedTasksList.appendChild(card);
        });
    } else {
        archivedTasksList.innerHTML = '<p>No hay tareas archivadas.</p>';
    }
}

function unarchiveTask(taskId) {
    fetch(`${URL_BASE}/tasks/${taskId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ archived: false })
    })
        .then(response => response.json())
        .then(response => {
            if (response.code === 200) {
                showToast(response.message, 4000, 'success');
                loadTasks();
                loadArchivedTasks();
            } else {
                showToast(response.message, 4000, 'error');
            }
        })
        .catch(error => showToast('Ups!. Ocurrió un error, reintente nuevamente', 4000, 'error'));
}

async function toggleSubtasks(taskId, owner = false, archived = false) {
    const user_id = JSON.parse(localStorage.getItem('user')).id;
    const subtasksDiv = document.getElementById(`subtasks-${taskId}`);
    const isShown = subtasksDiv.classList.contains('show');

    const subtasks = await fetch(`${URL_BASE}/subtasks?task=${taskId}&user=${user_id}`, {
        method: 'GET',
    })
        .then(response => response.json())
        .then(response => {
            if (response.code == 200) {
                return response.data;
            }
        })
        .catch(error => showToast('Ups!. Ocurrió un error al cargar las tareas', 4000, 'error'));

    if (!isShown) {
        subtasksDiv.innerHTML = '';

        subtasks.forEach(subtask => {
            const isTaskOwner = owner == user_id;
            const isSubtaskAssignee = subtask.assignee.id == user_id;
            const canEditSubtask = archived ? isTaskOwner : true;
            const canChangeStatus = archived ? isTaskOwner : (isTaskOwner || isSubtaskAssignee);

            const subtaskItem = document.createElement('div');
            subtaskItem.className = 'subtask-item';
            subtaskItem.innerHTML = `
                <p><strong>Descripción:</strong> ${subtask.description}</p>
                <p><strong>Estado:</strong> ${['Definido', 'En proceso', 'Completada'][subtask.stat - 1]}</p>
                ${subtask.priority ? `<p><strong>Prioridad:</strong> ${['Baja', 'Normal', 'Alta'][subtask.priority - 1]}</p>` : ''}
                ${subtask.expiration_date ? `<p><strong>Vencimiento:</strong> ${subtask.expiration_date}</p>` : ''}
                ${subtask.cmt ? `<p><strong>Comentario:</strong> ${subtask.cmt}</p>` : ''}
                <p><strong>Asignado a:</strong> ${subtask.assignee.name || 'Desconocido'}</p>
                ${`
                    <select class="form-select w-auto d-inline-block" onchange="updateSubtaskStatus(${subtask.ID_subtask}, this.value)" ${canChangeStatus ? '' : isSubtaskAssignee ? archived ? 'disabled' : '' : ''}>
                        ${isTaskOwner ? `
                            <option value="1" ${subtask.stat === "1" ? 'selected' : ''}>Definido</option>
                            <option value="2" ${subtask.stat === "2" ? 'selected' : ''}>En proceso</option>
                            <option value="3" ${subtask.stat === "3" ? 'selected' : ''}>Completada</option>
                        ` : `
                                <option value="2" ${subtask.stat === "2" ? 'selected' : ''}>En proceso</option>
                                <option value="3" ${subtask.stat === "3" ? 'selected' : ''}>Completada</option>
                            `}
                    </select>
                `}
                ${canEditSubtask ? `
                    <button class="btn btn-sm btn-warning" onclick="prepareEditSubtaskForm(${subtask.ID_subtask}, ${taskId}, '${encodeURIComponent(JSON.stringify(subtask))}')">Editar</button>
                ` : ''}
                ${isTaskOwner ? `
                    <button class="btn btn-sm btn-danger" onclick="deleteSubtask(${subtask.ID_subtask})">Eliminar</button>
                ` : ''}
            `;
            subtasksDiv.appendChild(subtaskItem);
        });
    }

    subtasksDiv.classList.toggle('show');
}

function prepareSubtaskForm(taskId) {
    document.getElementById('subtaskTaskId').value = taskId;

    fetch(`${URL_BASE}/collaborators?ID_task=${taskId}`, {
        method: 'GET',
    })
        .then(response => response.json())
        .then(response => {
            if (response.code == 200) {
                const assigneeSelect = document.getElementById('subtaskAssignee');
                assigneeSelect.innerHTML = '';

                let collaborators = response?.data?.filter(user => Object.keys(user).includes("ID_user"));

                if (collaborators.length === 0) {
                    assigneeSelect.innerHTML = '<option value="">No hay usuarios disponibles</option>';
                } else {
                    assigneeSelect.innerHTML = collaborators.map(user => `<option value="${user.ID_user}">${user?.name} ${user?.surname}</option>`).join('');
                }
            } else {
                showToast(response?.message, 4000, 'error');
            }
        })
        .catch(error => showToast('Ups!. Ocurrió un error al cargar los colaboradores', 4000, 'error'));
}

function createSubtask(event) {
    event.preventDefault();
    const subtask = {
        task: parseInt(document.getElementById('subtaskTaskId').value),
        description: document.getElementById('subtaskDescription').value,
        priority: document.getElementById('subtaskPriority').value || undefined,
        expiration_date: document.getElementById('subtaskExpirationDate').value || undefined,
        cmt: document.getElementById('subtaskComment').value || undefined,
        assignee: parseInt(document.getElementById('subtaskAssignee').value)
    };

    if (!subtask.description.trim()) {
        alert('La descripción es obligatoria.');
        return;
    }
    if (!subtask.assignee) {
        alert('Debe asignar la subtarea a un usuario.');
        return;
    }

    fetch(`${URL_BASE}/subtasks`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(subtask)
    })
        .then(response => response.json())
        .then(response => {
            if (response.code == 201) {
                showToast(response?.message, 4000, 'success');
                bootstrap.Modal.getInstance(document.getElementById('createSubtaskModal')).hide();
                toggleSubtasks(subtask.task);
            } else {
                showToast(response?.message, 4000, 'error')
            }
        })
        .catch(error => console.log('EL ERROR: ', error?.message) ?? showToast('Ups!. Ocurrió un error al crear la subtarea', 4000, 'error'));
}

function prepareEditSubtaskForm(subtaskId, taskId, encodedSubtask) {
    const subtask = JSON.parse(decodeURIComponent(encodedSubtask));

    document.getElementById('editSubtaskId').value = subtask.ID_subtask;
    document.getElementById('editSubtaskTaskId').value = taskId;
    document.getElementById('editSubtaskDescription').value = subtask.description;
    document.getElementById('editSubtaskPriority').value = subtask.priority || '';
    document.getElementById('editSubtaskExpirationDate').value = subtask.expiration_date || '';
    document.getElementById('editSubtaskComment').value = subtask.cmt || '';

    const modal = new bootstrap.Modal(document.getElementById('editSubtaskModal'));
    modal.show();
}

function updateSubtask(event) {
    event.preventDefault();
    const subtaskId = parseInt(document.getElementById('editSubtaskId').value);
    const taskId = parseInt(document.getElementById('editSubtaskTaskId').value);
    const subtask = {
        description: document.getElementById('editSubtaskDescription').value,
        priority: document.getElementById('editSubtaskPriority').value || undefined,
        expiration_date: document.getElementById('editSubtaskExpirationDate').value || undefined,
        cmt: document.getElementById('editSubtaskComment').value ?? ''
    };

    if (!subtask.description.trim()) {
        showToast('La descripción es obligatoria.', 4000, 'warning');
        return;
    }

    fetch(`${URL_BASE}/subtasks/${subtaskId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + localStorage.getItem('token')
        },
        body: JSON.stringify(subtask)
    })
        .then(response => response.json())
        .then(response => {
            if (response.code === 200) {
                showToast(response.message, 4000, 'success');
                bootstrap.Modal.getInstance(document.getElementById('editSubtaskModal')).hide();
                toggleSubtasks(taskId);
            } else {
                showToast(response.message, 4000, 'error');
            }
        })
        .catch(error => showToast('Error al actualizar la subtarea.', 4000, 'error'));
}

function prepareInviteForm(taskId) {
    document.getElementById('inviteTaskId').value = taskId;
}

function inviteCollaborator(event) {
    event.preventDefault();
    const email = document.getElementById('inviteEmail').value;
    const taskId = parseInt(document.getElementById('inviteTaskId').value);

    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        alert('Por favor, ingrese un correo electrónico válido.');
        return;
    }

    fetch(`${URL_BASE}/invitations`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ recipient: email, task: taskId })
    })
        .then(response => response.json())
        .then(data => {
            if (data.code == 201) {
                showToast(data.message, 4000, 'success');
                bootstrap.Modal.getInstance(document.getElementById('inviteCollaboratorModal')).hide();
            } else {
                showToast(data.message, 4000, 'error');
            }
        })
        .catch(error => showToast('Ups!. Ocurrió un error al crear la invitación', 4000, 'error'));
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

    if (!task.subject.trim() || !task.description.trim()) {
        showToast('Asunto y descripción son obligatorios', 4000, 'warning');
        return;
    }
    if (!task.expiration_date) {
        showToast('La fecha de vencimiento es obligatoria', 4000, 'warning');
        return;
    }

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

function prepareEditTaskForm(taskId) {
    fetch(`${URL_BASE}/tasks/${taskId}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        }
    })
        .then(response => response.json())
        .then(response => {
            if (response.code === 200) {
                const task = response.data[0];

                document.getElementById('editTaskId').value = task.ID_task;
                document.getElementById('editTaskSubject').value = task.subject;
                document.getElementById('editTaskDescription').value = task.description;
                document.getElementById('editTaskPriority').value = task.priority;
                document.getElementById('editTaskExpirationDate').value = task.expiration_date.replace(' ', 'T');
                document.getElementById('editTaskReminderDate').value = task.reminder_date ? task.reminder_date.replace(' ', 'T') : '';
                document.getElementById('editTaskColor').value = task.color;

                const modal = new bootstrap.Modal(document.getElementById('editTaskModal'));
                modal.show();
            } else {
                showToast(response.message, 4000, 'error');
            }
        })
        .catch(error => showToast('Ups!. Ocurrió un error, reintente nuevamente', 4000, 'error'));
}

function editTask(taskId) {
    prepareEditTaskForm(taskId);
}

function updateTask(event) {
    event.preventDefault();
    const user_id = JSON.parse(localStorage.getItem('user')).id;
    const taskId = parseInt(document.getElementById('editTaskId').value);
    const task = {
        subject: document.getElementById('editTaskSubject').value,
        description: document.getElementById('editTaskDescription').value,
        priority: parseInt(document.getElementById('editTaskPriority').value),
        expiration_date: document.getElementById('editTaskExpirationDate').value,
        color: document.getElementById('editTaskColor').value,
    };

    const reminder_date = document.getElementById('editTaskReminderDate').value || null;
    if (reminder_date) {
        task.reminder_date = reminder_date;
    }

    if (!task.subject.trim() || !task.description.trim()) {
        showToast('Asunto y descripción son obligatorios', 4000, 'warning');
        return;
    }
    if (!task.expiration_date) {
        showToast('La fecha de vencimiento es obligatoria', 4000, 'warning');
        return;
    }

    fetch(`${URL_BASE}/tasks/${taskId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(task)
    })
        .then(response => response.json())
        .then(response => {
            if (response.code === 200) {
                showToast(response.message, 4000, 'success');
                bootstrap.Modal.getInstance(document.getElementById('editTaskModal')).hide();
                loadTasks();
                loadArchivedTasks();
            } else {
                showToast(response.message, 4000, 'error');
            }
        })
        .catch(error => showToast('Ups!. Ocurrió un error, reintente nuevamente', 4000, 'error'));
}

function deleteTask(taskId) {
    document.getElementById('deleteTaskId').value = taskId;
    const modal = new bootstrap.Modal(document.getElementById('deleteTaskModal'));
    modal.show();
}

function confirmDeleteTask() {
    const taskId = document.getElementById('deleteTaskId').value;

    fetch(`${URL_BASE}/tasks/${taskId}`, {
        method: 'DELETE'
    })
        .then(response => response.json())
        .then(data => {
            if (data.code == 200) {
                showToast(data.message, 4000, 'success');
                loadTasks();
            } else {
                showToast(data.message, 4000, 'error');
            }
        })
        .catch(error => showToast('Ups!. Ocurrió un error, reintente nuevamente', 4000, 'error'));

    bootstrap.Modal.getInstance(document.getElementById('deleteTaskModal')).hide();
}

function archiveTask(taskId) {
    fetch(`${URL_BASE}/tasks/${taskId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ archived: true })
    })
        .then(response => response.json())
        .then(response => {
            if (response.code === 200) {
                showToast(response.message, 4000, 'success');
                loadTasks();
            } else {
                showToast(response.message, 4000, 'error');
            }
        })
        .catch(error => showToast('Ups!. Ocurrió un error, reintente nuevamente', 4000, 'error'));
}

function deleteSubtask(subtaskId) {
    document.getElementById('deleteSubtaskId').value = subtaskId;
    const modal = new bootstrap.Modal(document.getElementById('deleteSubtaskModal'));
    modal.show();
}

function confirmDeleteSubtask() {
    const subtaskId = document.getElementById('deleteSubtaskId').value;

    fetch(`${URL_BASE}/subtasks/${subtaskId}`, {
        method: 'DELETE'
    })
        .then(response => response.json())
        .then(data => {
            if (data.code == 200) {
                showToast(data.message, 4000, 'success');
                loadTasks();
            } else {
                showToast(data.message, 4000, 'error');
            }
        })
        .catch(error => showToast('Ups!. Ocurrió un error, reintente nuevamente', 4000, 'error'));

    bootstrap.Modal.getInstance(document.getElementById('deleteSubtaskModal')).hide();
}

async function updateSubtaskStatus(subtaskId, status) {
    await fetch(`${URL_BASE}/subtasks/${subtaskId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ stat: parseInt(status) })
    })
        .then(response => response.json())
        .then(response => {
            if (response.code === 200) {
                showToast(response.message, 4000, 'success');
                loadTasks();
            } else {
                showToast(response.message, 4000, 'error');
            }
        })
        .catch(error => showToast('Ups!. Ocurrió un error, reintente nuevamente', 4000, 'error'));
}

function loadProfile() {
    const user_id = JSON.parse(localStorage.getItem('user')).id;

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
}

function updateProfile(event) {
    event.preventDefault();
    const user_id = JSON.parse(localStorage.getItem('user')).id;
    const name = document.getElementById('name').value;
    const surname = document.getElementById('surname').value;
    const email = document.getElementById('email').value;

    if (name.trim() === '') {
        alert('El nombre no puede estar vacío.');
        return;
    }
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        alert('Por favor, ingrese un correo electrónico válido.');
        return;
    }

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

    if (pass.length < 6) {
        alert('La nueva contraseña debe tener al menos 6 caracteres.');
        return;
    }

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

async function fetchAndDisplayReminders() {
    const user_id = JSON.parse(localStorage.getItem('user')).id;

    try {
        const response = await fetch(`${URL_BASE}/tasks?user=${user_id}&archived=false&reminders=true`, {
            method: 'GET',
            headers: { 'Content-Type': 'application/json' }
        })
            .then(response => response.json())
            .then(response => {
                if (response.code == 200) {
                    return response;
                } else {
                    showToast(response?.message, 5000, 'error');
                }
            })
            .catch(error => showToast('Ups!. Ocurrió un error, reintente nuevamente', 4000, 'error'));

        const container = document.getElementById('reminders-container');
        container.innerHTML = '';

        if (response?.data?.length > 0) {
            response.data.forEach(task => {
                const reminderItem = document.createElement('div');
                reminderItem.className = 'list-group-item';
                const expirationDate = new Date(task.expiration_date);
                reminderItem.innerHTML = `
                    <div>
                        La tarea <strong>${task.subject}</strong> está próxima a vencer.
                    </div>
                    <span class="badge text-bg-warning">
                        Vence el ${expirationDate.toLocaleString().split(',')[0] + ' a las ' + expirationDate.toLocaleString().split(',')[1].split(' ')[1]}
                    </span>
                `;

                container.appendChild(reminderItem);
            });
        } else {
            container.innerHTML = '<div class="list-group-item">No hay recordatorios activos.</div>';
        }
    } catch (error) {
        console.error('Error al cargar recordatorios:', error);
    }
}

function initializeTabs() {
    const activeTab = document.querySelector('#active-tasks-tab');
    const archivedTab = document.querySelector('#archived-tasks-tab');

    loadTasks();
    fetchAndDisplayReminders();
    setInterval(fetchAndDisplayReminders, 5000);

    activeTab.addEventListener('shown.bs.tab', () => {
        loadTasks();
    });

    archivedTab.addEventListener('shown.bs.tab', () => {
        loadArchivedTasks();
    });
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
            initializeTabs();
            document.getElementById('createTaskForm').addEventListener('submit', createTask);
            document.getElementById('createSubtaskForm').addEventListener('submit', createSubtask);
            document.getElementById('inviteCollaboratorForm').addEventListener('submit', inviteCollaborator);
            document.getElementById('editTaskForm').addEventListener('submit', updateTask);
            // document.getElementById('inviteCollaboratorForm').addEventListener('submit', inviteCollaborator);
            document.getElementById('editSubtaskForm').addEventListener('submit', updateSubtask);
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

        const icons = {
            success: `<svg class="custom-toast-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>`,
            error: `<svg class="custom-toast-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>`,
            warning: `<svg class="custom-toast-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`,
            custom: `<svg class="custom-toast-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`
        };

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

        this.element.querySelector('.custom-toast-close').addEventListener('click', () => this.hide());

        setTimeout(() => this.hide(), this.duration);
    }

    hide() {
        this.element.classList.add('hidden');
        setTimeout(() => {
            if (this.element) {
                this.element.remove();
            }
        }, 300);
    }
}

function showToast(message, duration, type, options) {
    const toast = new Toast(message, duration, type, options);
    toast.create();
}

async function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}
const originalFetch = window.fetch;

window.fetch = async (...args) => {
    const response = await originalFetch(...args);

    if (response.status === 401) {
        showToast('Sesión caducada', 2000, 'warning');
        await sleep(4000);
        localStorage.removeItem('isLoggedIn');
        localStorage.removeItem('user');
        window.location.href = `${URL_BASE}/`;
    }

    return response;
};
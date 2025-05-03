<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workspace - Administrador de Tareas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="public/css/styles.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="workspace">Administrador de Tareas</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="workspace">Workspace</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profile">Perfil</a>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link btn btn-link" onclick="logout()">Cerrar Sesión</button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between mb-3">
            <h2>Mis Tareas</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTaskModal">Crear Tarea</button>
        </div>

        <div class="mb-4">
            <h4>Invitaciones Pendientes</h4>
            <div id="invitationsList" class="list-group"></div>
        </div>

        <div class="mb-3">
            <label for="sortTasks" class="form-label">Ordenar por:</label>
            <select id="sortTasks" class="form-select w-auto d-inline-block" onchange="loadTasks()">
                <option value="expiration_date">Fecha de Vencimiento</option>
                <option value="priority">Prioridad</option>
                <option value="subject">Asunto</option>
                <option value="color">Color</option>
            </select>
        </div>

        <div id="tasksList" class="row"></div>
    </div>

    <div class="modal fade" id="createTaskModal" tabindex="-1" aria-labelledby="createTaskModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createTaskModalLabel">Crear Nueva Tarea</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="createTaskForm">
                        <div class="mb-3">
                            <label for="taskSubject" class="form-label">Asunto</label>
                            <input type="text" class="form-control" id="taskSubject" required>
                        </div>
                        <div class="mb-3">
                            <label for="taskDescription" class="form-label">Descripción</label>
                            <textarea class="form-control" id="taskDescription" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="taskPriority" class="form-label">Prioridad</label>
                            <select class="form-select" id="taskPriority" required>
                                <option value="1">Baja</option>
                                <option value="2">Normal</option>
                                <option value="3">Alta</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="taskExpirationDate" class="form-label">Fecha de Vencimiento</label>
                            <input type="datetime-local" class="form-control" id="taskExpirationDate" required>
                        </div>
                        <div class="mb-3">
                            <label for="taskReminderDate" class="form-label">Fecha de Recordatorio (Opcional)</label>
                            <input type="datetime-local" class="form-control" id="taskReminderDate">
                        </div>
                        <div class="mb-3">
                            <label for="taskColor" class="form-label">Color</label>
                            <input type="color" class="form-control form-control-color" id="taskColor" value="#007bff">
                        </div>
                        <button type="submit" class="btn btn-primary">Crear Tarea</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="createSubtaskModal" tabindex="-1" aria-labelledby="createSubtaskModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createSubtaskModalLabel">Crear Subtarea</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="createSubtaskForm">
                        <input type="hidden" id="subtaskTaskId">
                        <div class="mb-3">
                            <label for="subtaskDescription" class="form-label">Descripción</label>
                            <textarea class="form-control" id="subtaskDescription" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="subtaskPriority" class="form-label">Prioridad (Opcional)</label>
                            <select class="form-select" id="subtaskPriority">
                                <option value="">Ninguna</option>
                                <option value="1">Baja</option>
                                <option value="2">Normal</option>
                                <option value="3">Alta</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="subtaskExpirationDate" class="form-label">Fecha de Vencimiento (Opcional)</label>
                            <input type="date" class="form-control" id="subtaskExpirationDate">
                        </div>
                        <div class="mb-3">
                            <label for="subtaskAssignee" class="form-label">Asignar a</label>
                            <select class="form-select" id="subtaskAssignee" required></select>
                        </div>
                        <div class="mb-3">
                            <label for="subtaskComment" class="form-label">Comentario (Opcional)</label>
                            <textarea class="form-control" id="subtaskComment"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Crear Subtarea</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="inviteCollaboratorModal" tabindex="-1" aria-labelledby="inviteCollaboratorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="inviteCollaboratorModalLabel">Invitar Colaborador</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="inviteCollaboratorForm">
                        <input type="hidden" id="inviteTaskId">
                        <div class="mb-3">
                            <label for="inviteEmail" class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" id="inviteEmail" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Enviar Invitación</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteTaskModal" tabindex="-1" aria-labelledby="deleteTaskModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteTaskModalLabel">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que quieres eliminar esta tarea?</p>
                    <input type="hidden" id="deleteTaskId">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" onclick="confirmDeleteTask()">Eliminar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editTaskModal" tabindex="-1" aria-labelledby="editTaskModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editTaskModalLabel">Editar Tarea</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editTaskForm">
                        <input type="hidden" id="editTaskId">
                        <div class="mb-3">
                            <label for="editTaskSubject" class="form-label">Asunto</label>
                            <input type="text" class="form-control" id="editTaskSubject" required>
                        </div>
                        <div class="mb-3">
                            <label for="editTaskDescription" class="form-label">Descripción</label>
                            <textarea class="form-control" id="editTaskDescription" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="editTaskPriority" class="form-label">Prioridad</label>
                            <select class="form-select" id="editTaskPriority" required>
                                <option value="1">Baja</option>
                                <option value="2">Normal</option>
                                <option value="3">Alta</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editTaskExpirationDate" class="form-label">Fecha de Vencimiento</label>
                            <input type="datetime-local" class="form-control" id="editTaskExpirationDate" required>
                        </div>
                        <div class="mb-3">
                            <label for="editTaskReminderDate" class="form-label">Fecha de Recordatorio (Opcional)</label>
                            <input type="datetime-local" class="form-control" id="editTaskReminderDate">
                        </div>
                        <div class="mb-3">
                            <label for="editTaskColor" class="form-label">Color</label>
                            <input type="color" class="form-control form-control-color" id="editTaskColor" value="#007bff">
                        </div>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/scripts.js"></script>
</body>

</html>
document.addEventListener('DOMContentLoaded', () => {
    // === DOM ELEMENTS ===
    // Navigation
    const navItems = document.querySelectorAll('.nav-item');
    const views = document.querySelectorAll('.view');

    // Agenda View
    const form = document.getElementById('agenda-form');
    const tasksContainer = document.getElementById('tasks-container');
    const filterBtns = document.querySelectorAll('.filter-btn');
    const currentDateDisplay = document.getElementById('current-date-display');
    const tituloInput = document.getElementById('titulo');

    // Calendar view
    const calendarGrid = document.getElementById('calendar-grid');
    const calendarMonthYear = document.getElementById('calendar-month-year');
    const prevMonthBtn = document.getElementById('prev-month');
    const nextMonthBtn = document.getElementById('next-month');

    // Inbox View
    const inboxListContainer = document.getElementById('inbox-list');
    const inboxBadge = document.getElementById('inbox-count');

    // === APP STATE ===
    let tasks = JSON.parse(localStorage.getItem('agenda_tasks')) || [];
    let inboxMails = JSON.parse(localStorage.getItem('agenda_inbox')) || [];
    let currentFilter = 'all';

    // Calendar state
    let currentDate = new Date();
    let displayMonth = currentDate.getMonth();
    let displayYear = currentDate.getFullYear();

    // === INITIALIZATION ===
    init();

    function init() {
        // Generate mock emails if empty initially to show functionality
        if (inboxMails.length === 0) {
            inboxMails = generateMockEmails();
            saveInbox();
        }

        updateDateDisplay();
        renderTasks();
        renderCalendar();
        renderInbox();

        // Event Listeners for Nav
        navItems.forEach(item => {
            item.addEventListener('click', (e) => {
                const targetViewId = e.currentTarget.getAttribute('data-view');
                switchView(targetViewId);
            });
        });

        // Event Listeners for Agenda
        form.addEventListener('submit', handleFormSubmit);

        filterBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                filterBtns.forEach(b => b.classList.remove('active'));
                e.target.classList.add('active');
                currentFilter = e.target.getAttribute('data-filter');
                renderTasks();
            });
        });

        const today = new Date().toISOString().split('T')[0];
        document.getElementById('fecha').setAttribute('min', today);

        // Calendar Listeners
        prevMonthBtn.addEventListener('click', () => {
            displayMonth--;
            if (displayMonth < 0) { displayMonth = 11; displayYear--; }
            renderCalendar();
        });
        nextMonthBtn.addEventListener('click', () => {
            displayMonth++;
            if (displayMonth > 11) { displayMonth = 0; displayYear++; }
            renderCalendar();
        });
    }

    // === ROUTING LOGIC ===
    function switchView(viewName) {
        // change active nav class
        navItems.forEach(i => i.classList.remove('active'));
        document.querySelector(`[data-view="${viewName}"]`).classList.add('active');

        // change active view display
        views.forEach(v => v.classList.remove('active-view'));
        document.getElementById(`view-${viewName}`).classList.add('active-view');

        if (viewName === 'agenda') {
            renderCalendar(); // refresh calendar markings just in case
        }
    }

    // === AGENDA LOGIC ===
    function updateDateDisplay() {
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        const todayStr = new Date().toLocaleDateString('es-ES', options);
        currentDateDisplay.textContent = todayStr.charAt(0).toUpperCase() + todayStr.slice(1);
    }

    function handleFormSubmit(e) {
        e.preventDefault();

        const titleInput = tituloInput.value.trim();
        const dateInput = document.getElementById('fecha').value;
        const timeInput = document.getElementById('hora').value;
        const catInput = document.getElementById('categoria').value;
        const descInput = document.getElementById('descripcion').value.trim();

        if (!titleInput || !dateInput || !timeInput) return;

        const dateObj = new Date(`${dateInput}T${timeInput}`);
        const day = dateObj.getDate().toString().padStart(2, '0');
        const month = (dateObj.getMonth() + 1).toString().padStart(2, '0');
        const year = dateObj.getFullYear();
        const monthName = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'][dateObj.getMonth()];

        const newTask = {
            id: Date.now().toString(),
            title: titleInput,
            dateRaw: dateInput,
            timeRaw: timeInput,
            formattedDate: `${day}/${month}/${year}`,
            displayDate: `${day} ${monthName} ${year}`,
            categoria: catInput,
            descripcion: descInput,
            completed: false,
            createdAt: new Date().toISOString()
        };

        tasks.push(newTask);
        saveTasks();

        form.reset();

        if (currentFilter === 'completed') document.querySelector('[data-filter="all"]').click();
        else renderTasks();

        renderCalendar(); // re-render calendar to place the dot
    }

    function toggleTask(id) {
        tasks = tasks.map(task => task.id === id ? { ...task, completed: !task.completed } : task);
        saveTasks(); renderTasks(); renderCalendar();
    }

    function deleteTask(id) {
        tasks = tasks.filter(task => task.id !== id);
        saveTasks(); renderTasks(); renderCalendar();
    }

    function saveTasks() {
        tasks.sort((a, b) => new Date(`${a.dateRaw}T${a.timeRaw}`) - new Date(`${b.dateRaw}T${b.timeRaw}`));
        localStorage.setItem('agenda_tasks', JSON.stringify(tasks));
    }

    function renderTasks() {
        tasksContainer.innerHTML = '';
        let filteredTasks = tasks;
        if (currentFilter === 'pending') filteredTasks = tasks.filter(t => !t.completed);

        if (filteredTasks.length === 0) {
            tasksContainer.innerHTML = `<div class="empty-state"><i class="fa-regular fa-calendar-check"></i><p>No tienes citas en esta vista.</p></div>`;
            return;
        }

        filteredTasks.forEach(task => {
            const card = document.createElement('div');
            card.className = `task-card ${task.completed ? 'completed' : ''}`;
            card.innerHTML = `
                <div class="task-checkbox" data-id="${task.id}"><i class="fa-solid fa-check"></i></div>
                <div class="task-info">
                    <div class="task-header-info">
                        <span class="task-title">${escapeHTML(task.title)}</span>
                        <span class="task-badge badge-${task.categoria}">${task.categoria}</span>
                    </div>
                    <div class="task-meta">
                        <span><i class="fa-regular fa-calendar"></i><span>${task.displayDate}</span></span>
                        <span><i class="fa-regular fa-clock"></i><span>${task.timeRaw}</span></span>
                    </div>
                </div>
                <div class="task-actions">
                    <button class="btn-icon delete-btn" data-id="${task.id}"><i class="fa-solid fa-trash-can"></i></button>
                </div>
            `;
            tasksContainer.appendChild(card);
        });

        document.querySelectorAll('.task-checkbox').forEach(btn => btn.addEventListener('click', (e) => toggleTask(e.currentTarget.getAttribute('data-id'))));
        document.querySelectorAll('.delete-btn').forEach(btn => btn.addEventListener('click', (e) => deleteTask(e.currentTarget.getAttribute('data-id'))));
    }

    // === CALENDAR LOGIC ===
    function renderCalendar() {
        calendarGrid.innerHTML = '';

        const months = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        calendarMonthYear.textContent = `${months[displayMonth]} ${displayYear}`;

        const firstDay = new Date(displayYear, displayMonth, 1).getDay(); // 0 is Sunday
        const daysInMonth = new Date(displayYear, displayMonth + 1, 0).getDate();

        // Convert JS day (Sunday=0, Mon=1) to standard calendar (Mon=0)
        let blankDays = firstDay === 0 ? 6 : firstDay - 1;

        // Build array of dates that have tasks
        // dateRaw format: YYYY-MM-DD
        const taskDates = tasks.map(t => t.dateRaw);

        for (let i = 0; i < blankDays; i++) {
            const emptyDiv = document.createElement('div');
            emptyDiv.className = 'calendar-day empty';
            calendarGrid.appendChild(emptyDiv);
        }

        for (let day = 1; day <= daysInMonth; day++) {
            const dayDiv = document.createElement('div');
            dayDiv.className = 'calendar-day';
            dayDiv.textContent = day;

            // Check if it's today
            const isToday = (day === currentDate.getDate() && displayMonth === currentDate.getMonth() && displayYear === currentDate.getFullYear());
            if (isToday) dayDiv.classList.add('today');

            // Format current iteration date to YYYY-MM-DD to check against tasks
            const fMonth = (displayMonth + 1).toString().padStart(2, '0');
            const fDay = day.toString().padStart(2, '0');
            const checkDate = `${displayYear}-${fMonth}-${fDay}`;

            if (taskDates.includes(checkDate)) {
                dayDiv.classList.add('has-task');
            }

            calendarGrid.appendChild(dayDiv);
        }
    }

    // === INBOX EMAILS LOGIC ===
    function generateMockEmails() {
        return [
            { id: 'm1', sender: "Carlos Ruiz", email: "cruiz@empresa.com", subject: "Reunión Estrategia General", body: "Hola, me gustaría agendar una reunión la próxima semana para revisar los nuevos diseños.", date: "Hace 2 horas" },
            { id: 'm2', sender: "Laura Gómez", email: "laura.gomez@mail.com", subject: "Entrevista Candidato", body: "Tenemos pendiente entrevistar al nuevo desarrollador. Mándame tu disponibilidad o agendalo pronto.", date: "Ayer" },
            { id: 'm3', sender: "Admin Sistema", email: "admin@sistema.net", subject: "Mantenimiento del Servidor", body: "Programemos un hueco en tu agenda la semana que viene para que podamos hacer la migración.", date: "Hace 2 días" }
        ];
    }

    function saveInbox() {
        localStorage.setItem('agenda_inbox', JSON.stringify(inboxMails));
        inboxBadge.textContent = inboxMails.length;
        inboxBadge.style.display = inboxMails.length ? 'block' : 'none';
    }

    function renderInbox() {
        inboxListContainer.innerHTML = '';
        inboxBadge.textContent = inboxMails.length;
        inboxBadge.style.display = inboxMails.length ? 'block' : 'none';

        if (inboxMails.length === 0) {
            inboxListContainer.innerHTML = `<div class="empty-state"><i class="fa-solid fa-inbox"></i><p>Nada por aquí. Bandeja de entrada limpia.</p></div>`;
            return;
        }

        inboxMails.forEach(mail => {
            const card = document.createElement('div');
            card.className = 'email-card';
            card.innerHTML = `
                <div class="email-header">
                    <div class="email-sender">
                        <div class="sender-avatar">${mail.sender.charAt(0)}</div>
                        <div class="sender-info">
                            <h4>${mail.sender}</h4>
                            <span>${mail.email}</span>
                        </div>
                    </div>
                    <span class="email-date">${mail.date}</span>
                </div>
                <div class="email-body">
                    <h3>${mail.subject}</h3>
                    <p>${mail.body}</p>
                </div>
                <div class="email-actions">
                    <button class="btn-action btn-dismiss" data-id="${mail.id}">Declinar / Borrar</button>
                    <button class="btn-action btn-schedule" data-id="${mail.id}" data-subject="${mail.subject}">Agendar Cita</button>
                </div>
            `;
            inboxListContainer.appendChild(card);
        });

        // Inbox Listeners
        document.querySelectorAll('.btn-dismiss').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = e.currentTarget.getAttribute('data-id');
                inboxMails = inboxMails.filter(m => m.id !== id);
                saveInbox(); renderInbox();
            });
        });

        document.querySelectorAll('.btn-schedule').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = e.currentTarget.getAttribute('data-id');
                const subject = e.currentTarget.getAttribute('data-subject');

                // Switch back to Agenda
                switchView('agenda');

                // Pre-fill form
                tituloInput.value = subject;
                tituloInput.focus();

                // Remove from Inbox
                inboxMails = inboxMails.filter(m => m.id !== id);
                saveInbox(); renderInbox();
            });
        });
    }

    function escapeHTML(str) { return str.replace(/[&<>'"]/g, t => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' }[t] || t)); }
});
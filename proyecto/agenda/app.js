document.addEventListener('DOMContentLoaded', () => {
    // DOM Elements
    const form = document.getElementById('agenda-form');
    const tasksContainer = document.getElementById('tasks-container');
    const filterBtns = document.querySelectorAll('.filter-btn');
    const currentDateDisplay = document.getElementById('current-date-display');

    // App State
    let tasks = JSON.parse(localStorage.getItem('agenda_tasks')) || [];
    let currentFilter = 'all';

    // Initialize App
    init();

    function init() {
        updateDateDisplay();
        renderTasks();

        // Event Listeners
        form.addEventListener('submit', handleFormSubmit);
        
        filterBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                // Update active class
                filterBtns.forEach(b => b.classList.remove('active'));
                e.target.classList.add('active');
                
                // Update filter and re-render
                currentFilter = e.target.getAttribute('data-filter');
                renderTasks();
            });
        });

        // Set default minimum date to today for the date input
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('fecha').setAttribute('min', today);
    }

    // Helper: Display today's date elegantly
    function updateDateDisplay() {
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        const todayStr = new Date().toLocaleDateString('es-ES', options);
        // Capitalize first letter
        currentDateDisplay.textContent = todayStr.charAt(0).toUpperCase() + todayStr.slice(1);
    }

    // Handle new task submission
    function handleFormSubmit(e) {
        e.preventDefault();

        const titleInput = document.getElementById('titulo').value.trim();
        const dateInput = document.getElementById('fecha').value;
        const timeInput = document.getElementById('hora').value;
        const catInput = document.getElementById('categoria').value;
        const descInput = document.getElementById('descripcion').value.trim();

        if (!titleInput || !dateInput || !timeInput) return;

        // Parse date for fancy display
        const dateObj = new Date(`${dateInput}T${timeInput}`);
        const day = dateObj.getDate().toString().padStart(2, '0');
        const month = (dateObj.getMonth() + 1).toString().padStart(2, '0');
        const year = dateObj.getFullYear();
        
        const monthsNames = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        const monthName = monthsNames[dateObj.getMonth()];

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
        
        // Reset form
        form.reset();
        
        // Return to 'all' filter if adding a new task to see it
        if (currentFilter === 'completed') {
            document.querySelector('[data-filter="all"]').click();
        } else {
            renderTasks();
        }
    }

    // Toggle Task completion
    function toggleTask(id) {
        tasks = tasks.map(task => {
            if (task.id === id) {
                return { ...task, completed: !task.completed };
            }
            return task;
        });
        saveTasks();
        renderTasks();
    }

    // Delete Task
    function deleteTask(id) {
        tasks = tasks.filter(task => task.id !== id);
        saveTasks();
        renderTasks();
    }

    // Save to localStorage
    function saveTasks() {
        // Sort tasks: pending first, then by date/time
        tasks.sort((a, b) => {
            const dateA = new Date(`${a.dateRaw}T${a.timeRaw}`);
            const dateB = new Date(`${b.dateRaw}T${b.timeRaw}`);
            return dateA - dateB;
        });
        localStorage.setItem('agenda_tasks', JSON.stringify(tasks));
    }

    // Render Tasks to DOM
    function renderTasks() {
        tasksContainer.innerHTML = '';

        let filteredTasks = tasks;
        if (currentFilter === 'pending') {
            filteredTasks = tasks.filter(t => !t.completed);
        } else if (currentFilter === 'completed') {
            filteredTasks = tasks.filter(t => t.completed);
        }

        if (filteredTasks.length === 0) {
            tasksContainer.innerHTML = `
                <div class="empty-state">
                    <i class="fa-regular ${currentFilter === 'completed' ? 'fa-face-frown' : 'fa-calendar-check'}"></i>
                    <p>${currentFilter === 'completed' ? 'No hay eventos completados aún.' : 'No tienes eventos en esta vista.'}</p>
                </div>
            `;
            return;
        }

        filteredTasks.forEach(task => {
            const card = document.createElement('div');
            card.className = `task-card ${task.completed ? 'completed' : ''}`;
            
            // Generate HTML
            card.innerHTML = `
                <div class="task-checkbox" data-id="${task.id}" title="${task.completed ? 'Marcar como pendiente' : 'Marcar como completado'}">
                    <i class="fa-solid fa-check"></i>
                </div>
                
                <div class="task-info">
                    <div class="task-header-info">
                        <span class="task-title">${escapeHTML(task.title)}</span>
                        <span class="task-badge badge-${task.categoria}">${task.categoria}</span>
                    </div>
                    
                    <div class="task-meta">
                        <span><i class="fa-regular fa-calendar"></i><span class="date-highlight">${task.displayDate}</span></span>
                        <span><i class="fa-regular fa-clock"></i><span class="date-highlight">${task.timeRaw}</span></span>
                    </div>
                    
                    ${task.descripcion ? `<p class="task-desc">${escapeHTML(task.descripcion)}</p>` : ''}
                </div>
                
                <div class="task-actions">
                    <button class="btn-icon delete-btn" data-id="${task.id}" title="Eliminar evento">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </div>
            `;

            tasksContainer.appendChild(card);
        });

        // Add event listeners to generated elements
        document.querySelectorAll('.task-checkbox').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = e.currentTarget.getAttribute('data-id');
                toggleTask(id);
            });
        });

        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = e.currentTarget.getAttribute('data-id');
                // Simple animation before delete
                const card = e.currentTarget.closest('.task-card');
                card.style.transform = 'scale(0.95)';
                card.style.opacity = '0';
                setTimeout(() => {
                    deleteTask(id);
                }, 250);
            });
        });
    }

    // Helper: Escape HTML to prevent XSS
    function escapeHTML(str) {
        return str.replace(/[&<>'"]/g, 
            tag => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                "'": '&#39;',
                '"': '&quot;'
            }[tag] || tag)
        );
    }
});

// ============= CONSTANTS =============
const API_BASE = 'api.php';
let currentFilter = {
    category: '',
    level: '',
    search: ''
};

// ============= DOM ELEMENTS =============
const projectsList = document.getElementById('projects-list');
const projectForm = document.getElementById('projectForm');
const projectModal = document.getElementById('projectModal');
const modalBody = document.getElementById('modalBody');
const formMessage = document.getElementById('formMessage');
const searchInput = document.getElementById('searchInput');

// ============= INITIALIZATION =============
document.addEventListener('DOMContentLoaded', () => {
    console.log('E-Learning system loaded successfully');
    loadProjects();
    setupEventListeners();
});

// ============= EVENT LISTENERS =============
function setupEventListeners() {
    // Project form submission
    if (projectForm) {
        projectForm.addEventListener('submit', (e) => {
            e.preventDefault();
            createProject();
        });
    }

    // Search input - search on enter
    if (searchInput) {
        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                searchProjects();
            }
        });
    }
}

// ============= SECTION MANAGEMENT =============
function showSection(sectionName) {
    // Hide all sections
    document.querySelectorAll('.section').forEach(section => {
        section.classList.remove('active');
    });

    // Show selected section
    const sectionId = sectionName === 'projects' ? 'projects-section' : 
                      sectionName === 'add-project' ? 'add-project-section' :
                      'about-section';
    
    const section = document.getElementById(sectionId);
    if (section) {
        section.classList.add('active');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}

// ============= LOAD PROJECTS =============
async function loadProjects() {
    try {
        projectsList.innerHTML = '<p><span class="loading"></span> Loading projects...</p>';
        
        const response = await fetch(`${API_BASE}?action=list`);
        const result = await response.json();

        if (result.success && result.data) {
            displayProjects(result.data);
        } else {
            projectsList.innerHTML = '<p style="color: #e74c3c;">Error loading projects. Please try again.</p>';
            console.error('Failed to load projects:', result.message);
        }
    } catch (error) {
        projectsList.innerHTML = '<p style="color: #e74c3c;">Error: ' + error.message + '</p>';
        console.error('Error loading projects:', error);
    }
}

// ============= DISPLAY PROJECTS =============
function displayProjects(projects) {
    if (projects.length === 0) {
        projectsList.innerHTML = '<p style="grid-column: 1/-1;">No projects found.</p>';
        return;
    }

    projectsList.innerHTML = projects.map(project => `
        <div class="project-card" onclick="viewProjectDetails(${project.id})">
            ${project.image_url ? `<img src="${project.image_url}" alt="${project.title}" onerror="this.src='https://via.placeholder.com/300x200?text=No+Image'">` : ''}
            <h3>${escapeHtml(project.title)}</h3>
            <div class="meta">
                ${project.category ? `<span><strong>Category:</strong> ${escapeHtml(project.category)}</span>` : ''}
                ${project.level ? `<span class="badge level-${project.level.toLowerCase()}">${escapeHtml(project.level)}</span>` : ''}
            </div>
            <p>${escapeHtml(project.description.substring(0, 100))}...</p>
            <div class="meta">
                ${project.instructor ? `<span><strong>Instructor:</strong> ${escapeHtml(project.instructor)}</span>` : ''}
                ${project.duration ? `<span><strong>Duration:</strong> ${escapeHtml(project.duration)}</span>` : ''}
            </div>
            <div class="meta">
                <span class="badge" style="background: #e8f4f8; color: #3498db;">
                    ${project.status || 'Active'}
                </span>
            </div>
        </div>
    `).join('');
}

// ============= VIEW PROJECT DETAILS =============
async function viewProjectDetails(projectId) {
    try {
        const response = await fetch(`${API_BASE}?action=get&id=${projectId}`);
        const result = await response.json();

        if (result.success && result.data) {
            const project = result.data;
            const created = new Date(project.created_at).toLocaleDateString();
            
            modalBody.innerHTML = `
                <div class="project-details">
                    ${project.image_url ? `<img src="${project.image_url}" alt="${project.title}" onerror="this.src='https://via.placeholder.com/600x300?text=No+Image'">` : ''}
                    
                    <h2>${escapeHtml(project.title)}</h2>
                    
                    <div class="meta">
                        <div class="meta-item">
                            <strong>Instructor:</strong><br>
                            ${escapeHtml(project.instructor || 'TBD')}
                        </div>
                        <div class="meta-item">
                            <strong>Duration:</strong><br>
                            ${escapeHtml(project.duration || 'Self-paced')}
                        </div>
                        <div class="meta-item">
                            <strong>Level:</strong><br>
                            <span class="badge level-${project.level.toLowerCase()}">${escapeHtml(project.level)}</span>
                        </div>
                        <div class="meta-item">
                            <strong>Status:</strong><br>
                            ${project.status}
                        </div>
                    </div>

                    <h3>Description</h3>
                    <p>${escapeHtml(project.description)}</p>

                    <div class="enrollment-form">
                        <h3>Enroll in This Course</h3>
                        <form onsubmit="enrollStudent(event, ${projectId})">
                            <div class="form-group">
                                <label for="studentName">Full Name *</label>
                                <input type="text" id="studentName" name="studentName" required>
                            </div>
                            <div class="form-group">
                                <label for="studentEmail">Email Address *</label>
                                <input type="email" id="studentEmail" name="studentEmail" required>
                            </div>
                            <button type="submit" class="btn-primary" style="width: 100%;">
                                Enroll Now
                            </button>
                        </form>
                    </div>

                    <div style="margin-top: 20px;">
                        <button class="btn-danger btn-small" onclick="deleteProject(${projectId})">Delete Project</button>
                        <button class="btn-secondary btn-small" onclick="editProject(${projectId})">Edit Project</button>
                    </div>
                </div>
            `;

            projectModal.classList.remove('hidden');
        } else {
            alert('Error loading project details');
        }
    } catch (error) {
        alert('Error: ' + error.message);
        console.error('Error loading project details:', error);
    }
}

// ============= SEARCH PROJECTS =============
async function searchProjects() {
    const query = document.getElementById('searchInput').value.trim();
    
    if (!query) {
        loadProjects();
        return;
    }

    try {
        projectsList.innerHTML = '<p><span class="loading"></span> Searching...</p>';
        
        const response = await fetch(`${API_BASE}?action=search&q=${encodeURIComponent(query)}`);
        const result = await response.json();

        if (result.success && result.data) {
            displayProjects(result.data);
            if (result.count === 0) {
                projectsList.innerHTML = `<p style="grid-column: 1/-1; text-align: center;">No results found for "${escapeHtml(query)}"</p>`;
            }
        } else {
            projectsList.innerHTML = '<p style="color: #e74c3c;">Error searching projects.</p>';
        }
    } catch (error) {
        projectsList.innerHTML = '<p style="color: #e74c3c;">Error: ' + error.message + '</p>';
        console.error('Error searching:', error);
    }
}

// ============= FILTER PROJECTS =============
function filterProjects() {
    const category = document.getElementById('categoryFilter').value;
    const level = document.getElementById('levelFilter').value;

    currentFilter.category = category;
    currentFilter.level = level;

    // Get all cards and filter them
    const cards = document.querySelectorAll('.project-card');
    let visibleCount = 0;

    cards.forEach(card => {
        let show = true;

        // Check category filter
        if (category && !card.textContent.includes(category)) {
            show = false;
        }

        // Check level filter
        if (level && !card.textContent.includes(level)) {
            show = false;
        }

        card.style.display = show ? 'block' : 'none';
        if (show) visibleCount++;
    });

    // Show message if no results
    if (visibleCount === 0) {
        projectsList.innerHTML += '<p style="grid-column: 1/-1; text-align: center; padding: 20px;">No projects match the selected filters.</p>';
    }
}

// ============= RESET FILTERS =============
function resetFilters() {
    document.getElementById('categoryFilter').value = '';
    document.getElementById('levelFilter').value = '';
    document.getElementById('searchInput').value = '';
    currentFilter = { category: '', level: '', search: '' };
    loadProjects();
}

// ============= CREATE PROJECT =============
async function createProject() {
    const formData = {
        title: document.getElementById('title').value,
        description: document.getElementById('description').value,
        category: document.getElementById('category').value,
        instructor: document.getElementById('instructor').value,
        duration: document.getElementById('duration').value,
        level: document.getElementById('level').value,
        status: document.getElementById('status').value,
        image_url: document.getElementById('image_url').value
    };

    // Validation
    if (!formData.title.trim() || !formData.description.trim()) {
        showMessage('Please fill in all required fields', 'error');
        return;
    }

    try {
        showMessage('Creating project...', 'info');
        
        const response = await fetch(`${API_BASE}?action=create`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });

        const result = await response.json();

        if (result.success) {
            showMessage('Project created successfully!', 'success');
            projectForm.reset();
            
            // Reload projects and switch to projects section
            setTimeout(() => {
                loadProjects();
                showSection('projects');
            }, 1500);
        } else {
            showMessage(result.message || 'Error creating project', 'error');
        }
    } catch (error) {
        showMessage('Error: ' + error.message, 'error');
        console.error('Error creating project:', error);
    }
}

// ============= EDIT PROJECT =============
async function editProject(projectId) {
    try {
        const response = await fetch(`${API_BASE}?action=get&id=${projectId}`);
        const result = await response.json();

        if (result.success) {
            const project = result.data;
            
            // Populate form with project data
            document.getElementById('title').value = project.title;
            document.getElementById('description').value = project.description;
            document.getElementById('category').value = project.category;
            document.getElementById('instructor').value = project.instructor;
            document.getElementById('duration').value = project.duration;
            document.getElementById('level').value = project.level;
            document.getElementById('status').value = project.status;
            document.getElementById('image_url').value = project.image_url;

            // Close modal and switch to edit section
            closeModal();
            showSection('add-project');

            // Change form heading
            document.querySelector('#add-project-section h1').textContent = 'Edit Project';

            // Change submit handler
            projectForm.onsubmit = async (e) => {
                e.preventDefault();
                await updateProject(projectId);
            };
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
}

// ============= UPDATE PROJECT =============
async function updateProject(projectId) {
    const formData = {
        title: document.getElementById('title').value,
        description: document.getElementById('description').value,
        category: document.getElementById('category').value,
        instructor: document.getElementById('instructor').value,
        duration: document.getElementById('duration').value,
        level: document.getElementById('level').value,
        status: document.getElementById('status').value,
        image_url: document.getElementById('image_url').value
    };

    try {
        showMessage('Updating project...', 'info');
        
        const response = await fetch(`${API_BASE}?action=update&id=${projectId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });

        const result = await response.json();

        if (result.success) {
            showMessage('Project updated successfully!', 'success');
            projectForm.reset();
            document.querySelector('#add-project-section h1').textContent = 'Add New Project';
            
            // Reset form handler
            projectForm.onsubmit = (e) => {
                e.preventDefault();
                createProject();
            };

            setTimeout(() => {
                loadProjects();
                showSection('projects');
            }, 1500);
        } else {
            showMessage(result.message || 'Error updating project', 'error');
        }
    } catch (error) {
        showMessage('Error: ' + error.message, 'error');
        console.error('Error updating project:', error);
    }
}

// ============= DELETE PROJECT =============
async function deleteProject(projectId) {
    if (!confirm('Are you sure you want to delete this project? This action cannot be undone.')) {
        return;
    }

    try {
        const response = await fetch(`${API_BASE}?action=delete&id=${projectId}`, {
            method: 'DELETE'
        });

        const result = await response.json();

        if (result.success) {
            alert('Project deleted successfully!');
            closeModal();
            loadProjects();
        } else {
            alert(result.message || 'Error deleting project');
        }
    } catch (error) {
        alert('Error: ' + error.message);
        console.error('Error deleting project:', error);
    }
}

// ============= ENROLL STUDENT =============
async function enrollStudent(event, projectId) {
    event.preventDefault();

    const studentName = document.getElementById('studentName').value.trim();
    const studentEmail = document.getElementById('studentEmail').value.trim();

    if (!studentName || !studentEmail) {
        alert('Please fill in all fields');
        return;
    }

    try {
        const response = await fetch(`${API_BASE}?action=enroll`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                project_id: projectId,
                student_name: studentName,
                email: studentEmail
            })
        });

        const result = await response.json();

        if (result.success) {
            alert('Enrollment successful! Welcome to the course.');
            closeModal();
        } else {
            alert(result.message || 'Error enrolling in course');
        }
    } catch (error) {
        alert('Error: ' + error.message);
        console.error('Error enrolling:', error);
    }
}

// ============= MODAL FUNCTIONS =============
function closeModal() {
    projectModal.classList.add('hidden');
    modalBody.innerHTML = '';
}

// Close modal when clicking outside
projectModal.addEventListener('click', (e) => {
    if (e.target === projectModal) {
        closeModal();
    }
});

// ============= MESSAGE DISPLAY =============
function showMessage(text, type) {
    formMessage.textContent = text;
    formMessage.className = `message ${type}`;
    formMessage.classList.remove('hidden');

    if (type !== 'error') {
        setTimeout(() => {
            formMessage.classList.add('hidden');
        }, 3000);
    }
}

// ============= UTILITY FUNCTIONS =============
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

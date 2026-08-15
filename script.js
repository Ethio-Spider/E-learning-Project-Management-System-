/**
 * E-Learning Management System - Frontend
 * 
 * Modern, accessible UI for course management and enrollment
 */

const state = {
    editingId: null,
    courses: [],
    selectedCourse: null,
    isLoading: false,
};

// DOM selectors
const $ = (selector) => document.querySelector(selector);
const $$ = (selector) => document.querySelectorAll(selector);

// Cache elements
const elements = {
    year: $('#year'),
    projectsList: $('#projectsList'),
    courseForm: $('#courseForm'),
    formTitle: $('#formTitle'),
    formMessage: $('#formMessage'),
    globalMessage: $('#globalMessage'),
    modal: $('#projectModal'),
    modalBody: $('#modalBody'),
    searchInput: $('#searchInput'),
    searchButton: $('#searchButton'),
    categoryFilter: $('#categoryFilter'),
    levelFilter: $('#levelFilter'),
    resetButton: $('#resetButton'),
    cancelEditButton: $('#cancelEditButton'),
    courseId: $('#courseId'),
    title: $('#title'),
    description: $('#description'),
    category: $('#category'),
    instructor: $('#instructor'),
    duration: $('#duration'),
    level: $('#level'),
    status: $('#status'),
    imageUrl: $('#imageUrl'),
    saveButton: $('#saveButton'),
    closeModalButton: $('#closeModalButton'),
};

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    initializeApp();
});

/**
 * Initialize application
 */
function initializeApp() {
    elements.year.textContent = new Date().getFullYear();
    attachEventListeners();
    loadCourses();
}

/**
 * Attach event listeners
 */
function attachEventListeners() {
    // Navigation
    $$('[data-section]').forEach((button) => {
        button.addEventListener('click', () => showSection(button.dataset.section));
    });
    
    // Search and filters
    elements.searchButton.addEventListener('click', loadCourses);
    elements.searchInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') loadCourses();
    });
    elements.categoryFilter.addEventListener('change', loadCourses);
    elements.levelFilter.addEventListener('change', loadCourses);
    elements.resetButton.addEventListener('click', resetFilters);
    
    // Form
    elements.courseForm.addEventListener('submit', saveCourse);
    elements.cancelEditButton.addEventListener('click', resetForm);
    
    // Modal
    elements.closeModalButton.addEventListener('click', closeModal);
    elements.modal.addEventListener('click', (e) => {
        if (e.target === elements.modal) closeModal();
    });
    
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeModal();
    });
}

/**
 * Make API call
 */
async function api(url, options = {}) {
    const startTime = performance.now();
    
    try {
        const response = await fetch(url, {
            ...options,
            headers: {
                ...(options.body ? { 'Content-Type': 'application/json' } : {}),
                ...(options.headers || {})
            }
        });
        
        const responseTime = Math.round(performance.now() - startTime);
        let result;
        
        try {
            result = await response.json();
        } catch {
            throw new Error(`Server returned an invalid response (${response.status})`);
        }
        
        if (!response.ok || !result.success) {
            throw new Error(result.message || `Request failed (${response.status})`);
        }
        
        console.log(`API: ${options.method || 'GET'} ${url} - ${responseTime}ms`);
        return result;
    } catch (error) {
        console.error('API Error:', error);
        throw error;
    }
}

/**
 * Load courses with filters
 */
async function loadCourses() {
    const query = elements.searchInput.value.trim();
    const category = elements.categoryFilter.value;
    const level = elements.levelFilter.value;
    
    setListLoading();
    
    try {
        let url = 'api.php?';
        const params = new URLSearchParams();
        
        if (query) {
            params.append('action', 'search');
            params.append('q', query);
        } else {
            params.append('action', '');
            if (category) params.append('category', category);
            if (level) params.append('level', level);
        }
        
        const result = await api(url + params.toString());
        state.courses = result.data.courses || [];
        
        // Filter by level if searching
        let filtered = state.courses;
        if (!query && level) {
            filtered = filtered.filter(c => c.level === level);
        }
        
        displayCourses(filtered);
        populateCategories(state.courses);
    } catch (error) {
        elements.projectsList.innerHTML = `<div class="empty-state error-state">${escapeHtml(error.message)}</div>`;
    }
}

/**
 * Display courses in grid
 */
function displayCourses(courses) {
    if (!courses.length) {
        elements.projectsList.innerHTML = '<div class="empty-state">No courses match your search or filters.</div>';
        return;
    }
    
    elements.projectsList.innerHTML = courses.map((course) => {
        const image = safeImageUrl(course.image_url);
        const badge = `<span class="badge">${escapeHtml(course.category || 'General')}</span>`;
        const levelBadge = `<span class="badge level-${escapeHtml((course.level || '').toLowerCase())}">${escapeHtml(course.level || 'Beginner')}</span>`;
        
        return `
            <article class="project-card" data-id="${course.id}">
                ${image
                    ? `<img class="course-image" src="${escapeAttribute(image)}" alt="${escapeAttribute(course.title)}" loading="lazy">`
                    : '<div class="course-placeholder" aria-hidden="true">E-Learning</div>'}
                <div class="card-body">
                    <div class="card-topline">
                        ${badge}
                        ${levelBadge}
                    </div>
                    <h3>${escapeHtml(course.title)}</h3>
                    <p>${escapeHtml(truncate(course.description || '', 150))}</p>
                    <div class="course-meta">
                        <span title="Instructor">👨‍🏫 ${escapeHtml(course.instructor || 'TBD')}</span>
                        <span title="Duration">⏱ ${escapeHtml(course.duration || 'Self-paced')}</span>
                        ${course.rating ? `<span title="Rating">⭐ ${parseFloat(course.rating).toFixed(1)}</span>` : ''}
                    </div>
                    <div class="card-actions">
                        <button class="btn btn-primary btn-small" type="button" data-action="view" data-id="${course.id}" title="View course details">View</button>
                        <button class="btn btn-secondary btn-small" type="button" data-action="edit" data-id="${course.id}" title="Edit course">Edit</button>
                        <button class="btn btn-danger btn-small" type="button" data-action="delete" data-id="${course.id}" title="Delete course">Delete</button>
                    </div>
                </div>
            </article>
        `;
    }).join('');
    
    // Attach event listeners to action buttons
    $$('[data-action]').forEach((button) => {
        button.addEventListener('click', () => {
            const id = Number(button.dataset.id);
            const action = button.dataset.action;
            
            if (action === 'view') viewCourse(id);
            else if (action === 'edit') editCourse(id);
            else if (action === 'delete') deleteCourse(id);
        });
    });
}

/**
 * Populate category filter
 */
function populateCategories(courses) {
    const categories = [...new Set(courses.map(c => c.category).filter(Boolean))].sort();
    const current = elements.categoryFilter.value;
    
    elements.categoryFilter.innerHTML = '<option value="">All categories</option>' +
        categories.map(cat => 
            `<option value="${escapeAttribute(cat)}">${escapeHtml(cat)}</option>`
        ).join('');
    
    if (current) elements.categoryFilter.value = current;
}

/**
 * View course details
 */
async function viewCourse(id) {
    try {
        const result = await api(`api.php?action=get&id=${id}`);
        const course = result.data;
        
        if (!course) {
            showMessage(elements.globalMessage, 'Course not found', 'error');
            return;
        }
        
        const image = safeImageUrl(course.image_url);
        const categoryBadge = `<span class="badge">${escapeHtml(course.category || 'General')}</span>`;
        const levelBadge = `<span class="badge level-${escapeHtml((course.level || '').toLowerCase())}">${escapeHtml(course.level || 'Beginner')}</span>`;
        
        elements.modalBody.innerHTML = `
            ${image ? `<img class="modal-image" src="${escapeAttribute(image)}" alt="${escapeAttribute(course.title)}" loading="lazy">` : ''}
            <div class="card-topline">
                ${categoryBadge}
                ${levelBadge}
                ${course.price ? `<span class="badge price">$${parseFloat(course.price).toFixed(2)}</span>` : ''}
            </div>
            <h2 id="modalTitle">${escapeHtml(course.title)}</h2>
            <div class="detail-grid">
                <div><strong>Instructor</strong><span>${escapeHtml(course.instructor || 'TBD')}</span></div>
                <div><strong>Duration</strong><span>${escapeHtml(course.duration || 'Self-paced')}</span></div>
                <div><strong>Status</strong><span class="status-${escapeHtml((course.status || '').toLowerCase())}">${escapeHtml(course.status || 'Active')}</span></div>
                <div><strong>Created</strong><span>${formatDate(course.created_at)}</span></div>
            </div>
            
            <h3>Description</h3>
            <p class="description">${escapeHtml(course.description)}</p>
            
            ${course.resources && course.resources.length > 0 ? `
                <h3>Course Resources</h3>
                <ul class="resources-list">
                    ${course.resources.map(r => `
                        <li>
                            <span class="resource-type">${escapeHtml(r.type)}</span>
                            <a href="${escapeAttribute(r.file_url)}" target="_blank" rel="noopener">${escapeHtml(r.title)}</a>
                            ${r.description ? `<p>${escapeHtml(r.description)}</p>` : ''}
                        </li>
                    `).join('')}
                </ul>
            ` : ''}
            
            <form id="enrollmentForm" class="enrollment-form">
                <h3>Enroll in this course</h3>
                <div class="form-group">
                    <label for="studentName">Full name *</label>
                    <input id="studentName" name="student_name" type="text" maxlength="255" required>
                </div>
                <div class="form-group">
                    <label for="studentEmail">Email *</label>
                    <input id="studentEmail" name="email" type="email" maxlength="255" required>
                </div>
                <button class="btn btn-success" type="submit">Enroll Now</button>
            </form>
            <div id="enrollmentMessage" class="message hidden" role="status" aria-live="polite"></div>
        `;
        
        elements.modal.classList.remove('hidden');
        state.selectedCourse = course;
        
        $('#enrollmentForm').addEventListener('submit', (e) => enrollStudent(e, id));
    } catch (error) {
        showMessage(elements.globalMessage, error.message, 'error');
    }
}

/**
 * Enroll student in course
 */
async function enrollStudent(event, projectId) {
    event.preventDefault();
    
    const message = $('#enrollmentMessage');
    const form = event.currentTarget;
    const data = Object.fromEntries(new FormData(form).entries());
    data.project_id = projectId;
    
    try {
        const result = await api('api.php?action=enroll', {
            method: 'POST',
            body: JSON.stringify(data)
        });
        
        showMessage(message, result.message || 'Enrollment successful!', 'success');
        form.reset();
        setTimeout(() => closeModal(), 2000);
    } catch (error) {
        showMessage(message, error.message, 'error');
    }
}

/**
 * Show section
 */
function showSection(section) {
    $$('.section').forEach(s => s.classList.remove('active'));
    
    const sectionMap = {
        'courses': 'courses-section',
        'add-course': 'add-course-section',
        'about': 'about-section'
    };
    
    const target = $(`#${sectionMap[section]}`);
    if (target) target.classList.add('active');
    
    $$('.nav-link').forEach(link => {
        link.classList.toggle('active', link.dataset.section === section);
    });
    
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

/**
 * Save course (create or update)
 */
async function saveCourse(event) {
    event.preventDefault();
    
    const data = Object.fromEntries(new FormData(elements.courseForm).entries());
    const id = state.editingId;
    
    try {
        const result = await api(id ? `api.php?action=update&id=${id}` : 'api.php?action=create', {
            method: id ? 'PUT' : 'POST',
            body: JSON.stringify(data)
        });
        
        showMessage(elements.formMessage, result.message || 'Course saved successfully!', 'success');
        resetForm(false);
        await loadCourses();
        showSection('courses');
    } catch (error) {
        showMessage(elements.formMessage, error.message, 'error');
    }
}

/**
 * Edit course
 */
async function editCourse(id) {
    try {
        const result = await api(`api.php?action=get&id=${id}`);
        const course = result.data;
        
        if (!course) {
            showMessage(elements.globalMessage, 'Course not found', 'error');
            return;
        }
        
        state.editingId = id;
        elements.courseId.value = id;
        elements.title.value = course.title || '';
        elements.description.value = course.description || '';
        elements.category.value = course.category || '';
        elements.instructor.value = course.instructor || '';
        elements.duration.value = course.duration || '';
        elements.level.value = course.level || 'Beginner';
        elements.status.value = course.status || 'Active';
        elements.imageUrl.value = course.image_url || '';
        
        elements.formTitle.textContent = 'Edit Course';
        elements.saveButton.textContent = 'Update Course';
        elements.cancelEditButton.classList.remove('hidden');
        showSection('add-course');
    } catch (error) {
        showMessage(elements.globalMessage, error.message, 'error');
    }
}

/**
 * Delete course
 */
async function deleteCourse(id) {
    const course = state.courses.find(c => Number(c.id) === id);
    const name = course?.title || 'this course';
    
    if (!confirm(`Delete "${name}"? This also removes its enrollments and resources. This action cannot be undone.`)) {
        return;
    }
    
    try {
        const result = await api(`api.php?action=delete&id=${id}`, { method: 'DELETE' });
        showMessage(elements.globalMessage, result.message || 'Course deleted successfully!', 'success');
        await loadCourses();
    } catch (error) {
        showMessage(elements.globalMessage, error.message, 'error');
    }
}

/**
 * Reset form
 */
function resetForm(clearMessage = true) {
    state.editingId = null;
    elements.courseForm.reset();
    elements.courseId.value = '';
    elements.level.value = 'Beginner';
    elements.status.value = 'Active';
    elements.formTitle.textContent = 'Add New Course';
    elements.saveButton.textContent = 'Save Course';
    elements.cancelEditButton.classList.add('hidden');
    
    if (clearMessage) elements.formMessage.classList.add('hidden');
}

/**
 * Reset filters
 */
function resetFilters() {
    elements.searchInput.value = '';
    elements.categoryFilter.value = '';
    elements.levelFilter.value = '';
    loadCourses();
}

/**
 * Close modal
 */
function closeModal() {
    elements.modal.classList.add('hidden');
    elements.modalBody.innerHTML = '';
    state.selectedCourse = null;
}

/**
 * Set list loading state
 */
function setListLoading() {
    elements.projectsList.innerHTML = '<div class="empty-state"><span class="loading"></span> Loading courses...</div>';
}

/**
 * Show message
 */
function showMessage(element, text, type) {
    element.textContent = text;
    element.className = `message ${type}`;
    element.classList.remove('hidden');
    
    if (type !== 'error') {
        setTimeout(() => element.classList.add('hidden'), 3500);
    }
}

/**
 * Truncate text
 */
function truncate(text, maxLength) {
    return text.length > maxLength ? `${text.slice(0, maxLength).trim()}…` : text;
}

/**
 * Format date
 */
function formatDate(value) {
    const date = new Date(value);
    return Number.isNaN(date.getTime()) ? 'Unknown' : date.toLocaleDateString();
}

/**
 * Safe image URL (validate protocol)
 */
function safeImageUrl(value) {
    if (!value) return '';
    try {
        const url = new URL(value, window.location.href);
        return ['http:', 'https:'].includes(url.protocol) ? url.href : '';
    } catch {
        return '';
    }
}

/**
 * Escape HTML entities
 */
function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, (char) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    })[char]);
}

/**
 * Escape attribute values
 */
function escapeAttribute(value) {
    return escapeHtml(value);
}


    projectsList.innerHTML = courses.map((course) => {
        const image = safeImageUrl(course.image_url);
        return `
            <article class="project-card">
                ${image
                    ? `<img class="course-image" src="${escapeAttribute(image)}" alt="${escapeAttribute(course.title)}">`
                    : '<div class="course-placeholder" aria-hidden="true">E-Learning</div>'}
                <div class="card-body">
                    <div class="card-topline">
                        <span class="badge">${escapeHtml(course.category || 'General')}</span>
                        <span class="badge level-${escapeHtml((course.level || '').toLowerCase())}">${escapeHtml(course.level || 'Beginner')}</span>
                    </div>
                    <h3>${escapeHtml(course.title)}</h3>
                    <p>${escapeHtml(truncate(course.description || '', 150))}</p>
                    <div class="course-meta">
                        <span>👨‍🏫 ${escapeHtml(course.instructor || 'TBD')}</span>
                        <span>⏱ ${escapeHtml(course.duration || 'Self-paced')}</span>
                    </div>
                    <div class="card-actions">
                        <button class="btn btn-primary btn-small" type="button" data-action="view" data-id="${Number(course.id)}">View</button>
                        <button class="btn btn-secondary btn-small" type="button" data-action="edit" data-id="${Number(course.id)}">Edit</button>
                        <button class="btn btn-danger btn-small" type="button" data-action="delete" data-id="${Number(course.id)}">Delete</button>
                    </div>
                </div>
            </article>
        `;
    }).join('');

    projectsList.querySelectorAll('[data-action]').forEach((button) => {
        button.addEventListener('click', () => {
            const id = Number(button.dataset.id);
            if (button.dataset.action === 'view') viewCourse(id);
            if (button.dataset.action === 'edit') editCourse(id);
            if (button.dataset.action === 'delete') deleteCourse(id);
        });
    });
}

function populateCategories(courses) {
    const select = $('#categoryFilter');
    const current = select.value;
    const categories = [...new Set(courses.map((course) => course.category).filter(Boolean))].sort();

    select.innerHTML = '<option value="">All categories</option>' +
        categories.map((category) =>
            `<option value="${escapeAttribute(category)}">${escapeHtml(category)}</option>`
        ).join('');

    if (categories.includes(current)) select.value = current;
}

async function viewCourse(id) {
    try {
        const result = await api(`api.php?action=get&id=${id}`);
        const course = result.data;
        const image = safeImageUrl(course.image_url);

        modalBody.innerHTML = `
            ${image ? `<img class="modal-image" src="${escapeAttribute(image)}" alt="${escapeAttribute(course.title)}">` : ''}
            <div class="card-topline">
                <span class="badge">${escapeHtml(course.category || 'General')}</span>
                <span class="badge level-${escapeHtml((course.level || '').toLowerCase())}">${escapeHtml(course.level || 'Beginner')}</span>
            </div>
            <h2 id="modalTitle">${escapeHtml(course.title)}</h2>
            <div class="detail-grid">
                <div><strong>Instructor</strong><span>${escapeHtml(course.instructor || 'TBD')}</span></div>
                <div><strong>Duration</strong><span>${escapeHtml(course.duration || 'Self-paced')}</span></div>
                <div><strong>Status</strong><span>${escapeHtml(course.status || 'Active')}</span></div>
                <div><strong>Created</strong><span>${formatDate(course.created_at)}</span></div>
            </div>
            <h3>Description</h3>
            <p class="description">${escapeHtml(course.description)}</p>

            <form id="enrollmentForm" class="enrollment-form">
                <h3>Enroll in this course</h3>
                <div class="form-group">
                    <label for="studentName">Full name *</label>
                    <input id="studentName" name="student_name" type="text" maxlength="255" required>
                </div>
                <div class="form-group">
                    <label for="studentEmail">Email *</label>
                    <input id="studentEmail" name="email" type="email" maxlength="255" required>
                </div>
                <button class="btn btn-success" type="submit">Enroll Now</button>
            </form>
            <div id="enrollmentMessage" class="message hidden"></div>
        `;

        modal.classList.remove('hidden');

        $('#enrollmentForm').addEventListener('submit', (event) => enrollStudent(event, id));
    } catch (error) {
        showMessage(globalMessage, error.message, 'error');
    }
}

async function enrollStudent(event, projectId) {
    event.preventDefault();

    const message = $('#enrollmentMessage');
    const form = event.currentTarget;
    const data = Object.fromEntries(new FormData(form).entries());
    data.project_id = projectId;

    try {
        const result = await api('api.php?action=enroll', {
            method: 'POST',
            body: JSON.stringify(data)
        });
        showMessage(message, result.message, 'success');
        form.reset();
    } catch (error) {
        showMessage(message, error.message, 'error');
    }
}

function showSection(section) {
    document.querySelectorAll('.section').forEach((item) => item.classList.remove('active'));
    const target = $(`#${section === 'courses' ? 'courses-section' : section === 'add-course' ? 'add-course-section' : 'about-section'}`);
    if (target) target.classList.add('active');

    document.querySelectorAll('.nav-link').forEach((link) => {
        link.classList.toggle('active', link.dataset.section === section);
    });

    window.scrollTo({ top: 0, behavior: 'smooth' });
}

async function saveCourse(event) {
    event.preventDefault();

    const data = Object.fromEntries(new FormData(courseForm).entries());
    const id = state.editingId;

    try {
        const result = await api(id ? `api.php?action=update&id=${id}` : 'api.php?action=create', {
            method: id ? 'PUT' : 'POST',
            body: JSON.stringify(data)
        });

        showMessage(formMessage, result.message, 'success');
        resetForm(false);
        await loadCourses();
        showSection('courses');
    } catch (error) {
        showMessage(formMessage, error.message, 'error');
    }
}

async function editCourse(id) {
    try {
        const result = await api(`api.php?action=get&id=${id}`);
        const course = result.data;

        state.editingId = id;
        $('#courseId').value = id;
        $('#title').value = course.title || '';
        $('#description').value = course.description || '';
        $('#category').value = course.category || '';
        $('#instructor').value = course.instructor || '';
        $('#duration').value = course.duration || '';
        $('#level').value = course.level || 'Beginner';
        $('#status').value = course.status || 'Active';
        $('#imageUrl').value = course.image_url || '';

        formTitle.textContent = 'Edit Course';
        $('#saveButton').textContent = 'Update Course';
        $('#cancelEditButton').classList.remove('hidden');
        showSection('add-course');
    } catch (error) {
        showMessage(globalMessage, error.message, 'error');
    }
}

async function deleteCourse(id) {
    const course = state.courses.find((item) => Number(item.id) === id);
    const name = course?.title || 'this course';

    if (!window.confirm(`Delete "${name}"? This also removes its enrollments and resources.`)) {
        return;
    }

    try {
        const result = await api(`api.php?action=delete&id=${id}`, { method: 'DELETE' });
        showMessage(globalMessage, result.message, 'success');
        await loadCourses();
    } catch (error) {
        showMessage(globalMessage, error.message, 'error');
    }
}

function resetForm(clearMessage = true) {
    state.editingId = null;
    courseForm.reset();
    $('#courseId').value = '';
    $('#level').value = 'Beginner';
    $('#status').value = 'Active';
    formTitle.textContent = 'Add New Course';
    $('#saveButton').textContent = 'Save Course';
    $('#cancelEditButton').classList.add('hidden');

    if (clearMessage) formMessage.classList.add('hidden');
}

function resetFilters() {
    $('#searchInput').value = '';
    $('#categoryFilter').value = '';
    $('#levelFilter').value = '';
    loadCourses();
}

function closeModal() {
    modal.classList.add('hidden');
    modalBody.innerHTML = '';
}

function setListLoading() {
    projectsList.innerHTML = '<div class="empty-state"><span class="loading"></span> Loading courses...</div>';
}

function showMessage(element, text, type) {
    element.textContent = text;
    element.className = `message ${type}`;
    if (type !== 'error') {
        window.setTimeout(() => element.classList.add('hidden'), 3500);
    }
}

function truncate(text, maxLength) {
    return text.length > maxLength ? `${text.slice(0, maxLength).trim()}…` : text;
}

function formatDate(value) {
    const date = new Date(value);
    return Number.isNaN(date.getTime()) ? 'Unknown' : date.toLocaleDateString();
}

function safeImageUrl(value) {
    if (!value) return '';
    try {
        const url = new URL(value, window.location.href);
        return ['http:', 'https:'].includes(url.protocol) ? url.href : '';
    } catch {
        return '';
    }
}

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, (character) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    })[character]);
}

function escapeAttribute(value) {
    return escapeHtml(value);
}

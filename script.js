const state = {
    role: 'student',
    dashboard: null,
    isAuthenticated: false,
    user: null,
    currentView: 'overview',
    courses: [],
    enrollments: new Set(),
};

const $ = (selector) => document.querySelector(selector);
const $$ = (selector) => document.querySelectorAll(selector);

const elements = {
    headerTitle: $('#headerTitle'),
    statsGrid: $('#statsGrid'),
    coursesList: $('#coursesList'),
    coursesListOverview: $('#coursesListOverview'),
    assignmentsList: $('#assignmentsList'),
    assignmentsListOverview: $('#assignmentsListOverview'),
    notificationsList: $('#notificationsList'),
    scheduleList: $('#scheduleList'),
    analyticsPanel: $('#analyticsPanel'),
    forumList: $('#forumList'),
    certificatesList: $('#certificatesList'),
    courseReviewList: $('#courseReviewList'),
    badgeGrid: $('#badgeGrid'),
    lmsFeatureGrid: $('#lmsFeatureGrid'),
    certificateCodeInput: $('#certificateCodeInput'),
    verifyCertificateBtn: $('#verifyCertificateBtn'),
    paymentsList: $('#paymentsList'),
    aiQuestion: $('#aiQuestion'),
    aiAnswer: $('#aiAnswer'),
    askAi: $('#askAi'),
    refreshData: $('#refreshData'),
    logoutBtn: $('#logoutBtn'),
    roleButtons: $$('.role-btn'),
    navItems: $$('.nav-item'),
    loginModal: $('#loginModal'),
    loginForm: $('#loginForm'),
    loginEmail: $('#loginEmail'),
    loginPassword: $('#loginPassword'),
    loginRole: $('#loginRole'),
    closeLoginModal: $('#closeLoginModal'),
    overviewPanel: $('#overviewPanel'),
    coursesPanel: $('#coursesPanel'),
    courseSearch: $('#courseSearch'),
    levelFilter: $('#levelFilter'),
    courseModal: $('#courseModal'),
    closeCourseModal: $('#closeCourseModal'),
    courseModalBody: $('#courseModalBody'),
    assignmentsPanel: $('#assignmentsPanel'),
    submissionModal: $('#submissionModal'),
    closeSubmissionModal: $('#closeSubmissionModal'),
    submissionForm: $('#submissionForm'),
    submissionTitle: $('#submissionTitle'),
    submissionText: $('#submissionText'),
    manageCoursesList: $('#manageCoursesList'),
    managePanel: $('#managePanel'),
    submissionsList: $('#submissionsList'),
    gradingPanel: $('#gradingPanel'),
    gradingCourseFilter: $('#gradingCourseFilter'),
    gradingAssignmentFilter: $('#gradingAssignmentFilter'),
    gradingModal: $('#gradingModal'),
    closeGradingModal: $('#closeGradingModal'),
    gradingModalBody: $('#gradingModalBody'),
    instructorOnlyItems: $$('.instructor-only'),
};

async function getCsrfToken() {
    try {
        const raw = await fetch('api.php?action=csrf-token');
        const result = await raw.json();
        if (result && result.data && result.data.csrf_token) {
            localStorage.setItem('learnflow-csrf-token', result.data.csrf_token);
            return result.data.csrf_token;
        }
    } catch (error) {
        console.warn('Unable to fetch CSRF token:', error);
    }

    return localStorage.getItem('learnflow-csrf-token') || '';
}

async function api(url, options = {}) {
    const method = (options.method || 'GET').toUpperCase();
    const csrfToken = method !== 'GET' ? await getCsrfToken() : '';

    const response = await fetch(url, {
        ...options,
        headers: {
            'Content-Type': 'application/json',
            ...(method !== 'GET' && csrfToken ? { 'X-CSRF-Token': csrfToken } : {}),
            ...(options.headers || {}),
        },
    });

    const raw = await response.text();
    let result;

    try {
        result = raw ? JSON.parse(raw) : {};
    } catch (parseError) {
        console.error('Non-JSON server response:', raw);
        throw new Error(`Server returned an invalid response (HTTP ${response.status}). Check the PHP terminal/logs.`);
    }

    if (!response.ok || !result.success) {
        throw new Error(result.message || `Request failed (HTTP ${response.status})`);
    }

    return result.data;
}

function setRole(role, shouldLoad = true) {
    state.role = role;
    syncRoleButtons();
    elements.headerTitle.textContent = `${role.charAt(0).toUpperCase() + role.slice(1)} dashboard`;
    if (shouldLoad) {
        loadDashboard();
    }
}

async function loadDashboard() {
    if (!state.isAuthenticated) {
        showLoginModal();
        return;
    }

    try {
        const data = await api(`api.php?action=dashboard&role=${state.role}`);
        state.dashboard = data;
        renderDashboard(data);
    } catch (error) {
        console.error('Dashboard load failed:', error);
        if (error.message.includes('Authentication required') || error.message.includes('No active session')) {
            state.isAuthenticated = false;
            showLoginModal();
            return;
        }

        elements.statsGrid.innerHTML = `
            <div class="empty-box">
                <strong>Dashboard could not be loaded.</strong><br>
                ${escapeHtml(error.message)}
                <br><small>Check the PHP server terminal and database connection.</small>
            </div>
        `;
    }
}

async function initSession() {
    try {
        const result = await api('api.php?action=me');
        state.isAuthenticated = true;
        state.user = result.user;
        state.role = result.role || state.role;
        syncRoleButtons();
        setRole(state.role, false);
        hideLoginModal();
        
        // Show instructor-only nav items
        if (state.user.role === 'instructor' || state.user.role === 'admin') {
            elements.instructorOnlyItems.forEach(item => item.classList.remove('hidden'));
        }
    } catch (error) {
        state.isAuthenticated = false;
        state.user = null;

        if (error.message && !error.message.includes('Authentication required') && !error.message.includes('No active session')) {
            console.error('Session check failed:', error);
            const loginMessage = document.querySelector('#loginForm')?.parentElement?.querySelector('.login-error');
            if (loginMessage) {
                loginMessage.textContent = error.message;
                loginMessage.classList.remove('hidden');
            }
        }

        showLoginModal();
    }
}

function syncRoleButtons() {
    elements.roleButtons.forEach((button) => {
        const isActive = button.dataset.role === state.role;
        button.classList.toggle('active', isActive);
    });
}

function renderDashboard(data) {
    renderStats(data.stats || []);

    const courses = data.courses || [];
    const assignments = data.assignments || [];
    const notifications = data.notifications || [];
    const schedule = data.schedule || [];
    const reviews = data.reviews || [
        { title: 'PHP Foundations', rating: 5, summary: 'Clear structure and great practice labs.' },
        { title: 'Mobile-first Design', rating: 4, summary: 'Helpful examples and better learner flow.' },
    ];
    const badges = data.badges || [
        { name: 'Frontend Builder', icon: '🏅', color: 'gold' },
        { name: 'Project Leader', icon: '🚀', color: 'cyan' },
        { name: 'Problem Solver', icon: '🧠', color: 'purple' },
    ];

    renderList(elements.coursesList, courses, 'course');
    renderList(elements.coursesListOverview, courses.slice(0, 3), 'course');
    renderList(elements.assignmentsList, assignments, 'assignment');
    renderList(elements.assignmentsListOverview, assignments.slice(0, 3), 'assignment');
    renderList(elements.notificationsList, notifications, 'notification');
    renderList(elements.scheduleList, schedule, 'schedule');

    renderForum(data.forum || []);
    renderModernFeatureCards(data);
    renderCourseReviews(reviews);
    renderBadges(badges);
    renderCertificates(data.certificates || []);
    renderPayments(data.payments || []);
    renderAnalytics(data.analytics || {});
}

function renderStats(stats) {
    elements.statsGrid.innerHTML = stats.map((stat) => `
        <div class="stat-card">
            <p>${escapeHtml(stat.label)}</p>
            <div class="stat-value">${escapeHtml(stat.value)}</div>
            <span class="trend">${escapeHtml(stat.trend)}</span>
        </div>
    `).join('');
}

function renderList(container, items, type) {
    if (!items.length) {
        container.innerHTML = '<div class="empty-box">No items available.</div>';
        return;
    }

    container.innerHTML = items.map((item) => {
        if (type === 'course') {
            return `
                <div class="list-item course-item">
                    <div>
                        <strong>${escapeHtml(item.title)}</strong>
                        <small>${escapeHtml(item.category)} • ${escapeHtml(item.level)}</small>
                    </div>
                    <div class="pill-row">
                        <span class="pill">${escapeHtml(item.progress || 0)}%</span>
                        <span class="meta-text">${escapeHtml(item.nextLesson || 'On schedule')}</span>
                    </div>
                </div>
            `;
        }

        if (type === 'assignment') {
            return `
                <div class="list-item">
                    <div>
                        <strong>${escapeHtml(item.title)}</strong>
                        <small>${escapeHtml(item.course)} • due ${escapeHtml(item.due || item.dueDate || 'Soon')}</small>
                    </div>
                    <span class="pill ${item.status === 'Pending' ? 'warning' : ''}">${escapeHtml(item.status || 'Pending')}</span>
                </div>
            `;
        }

        if (type === 'notification') {
            return `
                <div class="list-item">
                    <div>
                        <strong>${escapeHtml(item.text)}</strong>
                        <small>${escapeHtml(item.time)}</small>
                    </div>
                </div>
            `;
        }

        return `
            <div class="list-item schedule-item">
                <div>
                    <strong>${escapeHtml(item.title)}</strong>
                    <small>${escapeHtml(item.type)}</small>
                </div>
                <div class="schedule-meta">
                    <span>${escapeHtml(item.day)}</span>
                    <span>${escapeHtml(item.time)}</span>
                </div>
            </div>
        `;
    }).join('');
}

function renderForum(threads) {
    const safeThreads = threads.length ? threads : [
        { topic: 'Career-ready front-end workflow', author: 'Aisha • 2h ago', replies: 18 },
        { topic: 'Project review: API patterns', author: 'Mateo • Today', replies: 9 },
        { topic: 'Study group for PHP fundamentals', author: 'Lina • 1d ago', replies: 27 },
    ];

    elements.forumList.innerHTML = safeThreads.map((thread) => `
        <div class="list-item">
            <div>
                <strong>${escapeHtml(thread.topic)}</strong>
                <small>${escapeHtml(thread.author)}</small>
            </div>
            <span class="pill">${escapeHtml(thread.replies)} replies</span>
        </div>
    `).join('');
}

function renderModernFeatureCards(data = {}) {
    const features = (data.features || [
        { icon: '🎥', title: 'Video lessons', description: 'Short, mobile-friendly lecture videos with key checkpoints.', metric: '12 lessons' },
        { icon: '📝', title: 'Quizzes', description: 'Adaptive assessments with instant feedback and mastery bands.', metric: '4 quizzes' },
        { icon: '⏱️', title: 'Completion tracking', description: 'Progress milestones, streaks, and learning velocity alerts.', metric: '86% complete' },
        { icon: '📊', title: 'Better analytics', description: 'Actionable progress, engagement, and focus area insights.', metric: 'Live insights' },
        { icon: '🔔', title: 'Real notifications', description: 'Deadline reminders, feedback, and support updates.', metric: '3 alerts' },
        { icon: '💬', title: 'Discussion forum', description: 'Peer dialogue, instructor feedback, and Q&A threads.', metric: '24 threads' },
        { icon: '⭐', title: 'Course reviews', description: 'Authentic learner feedback and course quality ratings.', metric: '4.8/5' },
        { icon: '🏆', title: 'Badges', description: 'Achievements, unlocks, and gamified progress rewards.', metric: '6 badges' },
        { icon: '📜', title: 'Certificate verification', description: 'Secure credential verification with public code lookup.', metric: 'Ready' },
    ]);

    elements.lmsFeatureGrid.innerHTML = features.map((feature) => `
        <article class="feature-tile">
            <div class="feature-tile__icon">${escapeHtml(feature.icon)}</div>
            <div class="feature-tile__content">
                <h4>${escapeHtml(feature.title)}</h4>
                <p>${escapeHtml(feature.description)}</p>
                <span>${escapeHtml(feature.metric)}</span>
            </div>
        </article>
    `).join('');
}

function renderCourseReviews(reviews) {
    const safeReviews = reviews.length ? reviews : [
        { title: 'PHP Foundations', rating: 5, summary: 'Clear coding walkthroughs and practical labs.' },
        { title: 'UX Design Sprint', rating: 4, summary: 'Great pacing and real-world handoff examples.' },
    ];

    elements.courseReviewList.innerHTML = safeReviews.map((review) => `
        <div class="list-item review-item">
            <div>
                <strong>${escapeHtml(review.title)}</strong>
                <small>${escapeHtml(review.summary)}</small>
            </div>
            <span class="pill success">${'★'.repeat(Math.max(1, Number(review.rating) || 5))}</span>
        </div>
    `).join('');
}

function renderBadges(items) {
    const safeItems = items.length ? items : [
        { name: 'Frontend Builder', icon: '🏅', color: 'gold' },
        { name: 'Problem Solver', icon: '🧠', color: 'purple' },
        { name: 'Project Leader', icon: '🚀', color: 'cyan' },
    ];

    elements.badgeGrid.innerHTML = safeItems.map((item) => `
        <div class="badge-item ${escapeHtml(item.color || 'gold')}">
            <span>${escapeHtml(item.icon || '🏆')}</span>
            <strong>${escapeHtml(item.name)}</strong>
        </div>
    `).join('');
}

function renderCertificates(certificates) {
    elements.certificatesList.innerHTML = certificates.map((item) => `
        <div class="list-item">
            <div>
                <strong>${escapeHtml(item.name)}</strong>
                <small>${escapeHtml(item.status)}</small>
            </div>
            <span class="pill ${item.status === 'Issued' ? 'success' : 'warning'}">${escapeHtml(item.status)}</span>
        </div>
    `).join('');
}

function renderPayments(items) {
    elements.paymentsList.innerHTML = items.map((item) => `
        <div class="list-item">
            <div>
                <strong>${escapeHtml(item.plan)}</strong>
                <small>${escapeHtml(item.status)}</small>
            </div>
            <span class="pill">${escapeHtml(item.amount)}</span>
        </div>
    `).join('');
}

function renderAnalytics(data) {
    const summary = data.summary || {};
    const cards = [
        ['Completion', `${summary.completionRate ?? 0}%`],
        ['Study time', `${summary.weeklyStudyHours ?? summary.weeklyMinutes ?? 0}h`],
        ['Engagement', `${summary.engagement ?? 0}%`],
        ['Retention', `${summary.retention ?? 0}%`],
        ['Weekly streak', `${summary.streak ?? 5} days`],
        ['Avg score', `${summary.avgScore ?? 92}%`],
    ];

    const focus = (data.focusAreas || ['Progress', 'Assignments', 'Milestones', 'Revision']).map((item) => `<span class="pill neutral">${escapeHtml(item)}</span>`).join('');

    elements.analyticsPanel.innerHTML = `
        <div class="analytics-grid">
            ${cards.map(([label, value]) => `
                <div class="mini-stat">
                    <span>${escapeHtml(label)}</span>
                    <strong>${escapeHtml(value)}</strong>
                </div>
            `).join('')}
        </div>
        <div class="focus-tags">${focus}</div>
    `;
}

async function askAi() {
    const question = elements.aiQuestion.value.trim();
    if (!question) {
        elements.aiAnswer.textContent = 'Ask a question to get guidance from the learning assistant.';
        elements.aiAnswer.classList.remove('hidden');
        return;
    }

    try {
        const response = await api('api.php?action=ai-chat', {
            method: 'POST',
            body: JSON.stringify({ question }),
        });

        elements.aiAnswer.textContent = `${response.answer}\n\nSuggested action: ${response.suggestedAction}`;
        elements.aiAnswer.classList.remove('hidden');
    } catch (error) {
        elements.aiAnswer.textContent = error.message;
        elements.aiAnswer.classList.remove('hidden');
    }
}

async function handleLogin(event) {
    event.preventDefault();

    const email = elements.loginEmail.value.trim();
    const password = elements.loginPassword.value.trim();
    const role = elements.loginRole.value;

    try {
        const result = await api('api.php?action=login&role=' + encodeURIComponent(role), {
            method: 'POST',
            body: JSON.stringify({ email, password }),
        });

        state.isAuthenticated = true;
        state.user = result.user;
        state.role = result.role || role;
        syncRoleButtons();
        setRole(state.role, false);
        hideLoginModal();
        await loadDashboard();
    } catch (error) {
        alert(error.message || 'Login failed');
    }
}

async function handleLogout() {
    try {
        await api('api.php?action=logout', { method: 'POST' });
    } catch (error) {
        console.warn(error);
    } finally {
        state.isAuthenticated = false;
        state.user = null;
        showLoginModal();
    }
}

function showLoginModal() {
    elements.loginModal.classList.remove('hidden');
}

function hideLoginModal() {
    elements.loginModal.classList.add('hidden');
}

async function loadCourses(level = '') {
    if (!state.isAuthenticated) {
        showLoginModal();
        return;
    }

    try {
        const url = new URL('api.php?action=courses', window.location.origin);
        const activeLevel = level || elements.levelFilter.value || '';
        const searchText = elements.courseSearch?.value.trim() || '';

        if (activeLevel) {
            url.searchParams.append('level', activeLevel);
        }
        if (searchText) {
            url.searchParams.append('search', searchText);
        }

        const data = await api(url.toString());
        state.courses = data.courses || [];
        renderCourses(state.courses);

        const enrollmentsData = await api('api.php?action=my-courses');
        state.enrollments = new Set((enrollmentsData.enrollments || []).map(e => e.project_id));
    } catch (error) {
        console.error(error);
        elements.coursesList.innerHTML = `<div class="empty-box">Failed to load courses: ${escapeHtml(error.message)}</div>`;
    }
}

function renderCourses(courses) {
    if (!courses.length) {
        elements.coursesList.innerHTML = '<div class="empty-box">No courses available.</div>';
        return;
    }

    elements.coursesList.innerHTML = courses.map((course) => `
        <div class="course-card">
            <div class="course-header">
                <h3>${escapeHtml(course.title)}</h3>
                <span class="pill">${escapeHtml(course.level)}</span>
            </div>
            <p class="course-meta">${escapeHtml(course.category)} • ${escapeHtml(course.duration)}</p>
            <p class="course-desc">${escapeHtml(course.description ? course.description.substring(0, 120) + '...' : 'No description')}</p>
            <div class="course-footer">
                <span>${escapeHtml(course.instructor)}</span>
                <button class="btn btn-primary small" data-course-id="${course.id}" data-course-title="${escapeHtml(course.title)}">
                    ${state.enrollments.has(course.id) ? 'Enrolled' : 'Enroll'}
                </button>
            </div>
        </div>
    `).join('');

    // Add event listeners to enrollment buttons
    document.querySelectorAll('[data-course-id]').forEach((button) => {
        button.addEventListener('click', () => {
            const courseId = parseInt(button.dataset.courseId, 10);
            if (!state.enrollments.has(courseId)) {
                enrollCourse(courseId, button.dataset.courseTitle);
            }
        });
    });
}

async function enrollCourse(courseId, courseTitle) {
    try {
        await api('api.php?action=enroll', {
            method: 'POST',
            body: JSON.stringify({ course_id: courseId }),
        });
        state.enrollments.add(courseId);
        alert(`Successfully enrolled in ${courseTitle}!`);
        loadCourses();
    } catch (error) {
        alert(error.message || 'Enrollment failed');
    }
}

function switchView(view) {
    state.currentView = view;
    
    // Hide all panels
    elements.overviewPanel?.classList.add('hidden');
    elements.coursesPanel?.classList.add('hidden');
    elements.assignmentsPanel?.classList.add('hidden');
    elements.managePanel?.classList.add('hidden');
    elements.gradingPanel?.classList.add('hidden');

    // Show selected panel
    if (view === 'overview') {
        elements.overviewPanel?.classList.remove('hidden');
    } else if (view === 'courses') {
        elements.coursesPanel?.classList.remove('hidden');
        loadCourses();
    } else if (view === 'assignments') {
        elements.assignmentsPanel?.classList.remove('hidden');
        loadAssignments();
    } else if (view === 'manage') {
        elements.managePanel?.classList.remove('hidden');
        loadInstructorCourses();
    } else if (view === 'grading') {
        elements.gradingPanel?.classList.remove('hidden');
        loadGradingSubmissions();
    } else {
        const overviewTargets = {
            schedule: elements.scheduleList,
            analytics: elements.analyticsPanel,
            forum: elements.forumList,
            payments: elements.paymentsList,
        };
        const target = overviewTargets[view];
        if (target) {
            elements.overviewPanel?.classList.remove('hidden');
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }
}

async function loadAssignments() {
    if (!state.isAuthenticated) {
        showLoginModal();
        return;
    }

    try {
        // Get user's courses first
        const enrollmentsData = await api('api.php?action=my-courses');
        const enrollments = enrollmentsData.enrollments || [];
        
        if (enrollments.length === 0) {
            elements.assignmentsList.innerHTML = '<div class="empty-box">You are not enrolled in any courses yet.</div>';
            return;
        }

        // Load assignments for all enrolled courses
        let allAssignments = [];
        for (const enrollment of enrollments) {
            try {
                const data = await api(`api.php?action=assignments&course_id=${enrollment.project_id}`);
                const assignments = (data.assignments || []).map(a => ({
                    ...a,
                    course_id: enrollment.project_id,
                    enrollment_id: enrollment.id
                }));
                allAssignments.push(...assignments);
            } catch (error) {
                console.error(error);
            }
        }

        renderAssignments(allAssignments);
    } catch (error) {
        console.error(error);
        elements.assignmentsList.innerHTML = `<div class="empty-box">Failed to load assignments: ${escapeHtml(error.message)}</div>`;
    }
}

function renderAssignments(assignments) {
    if (!assignments.length) {
        elements.assignmentsList.innerHTML = '<div class="empty-box">No assignments available.</div>';
        return;
    }

    elements.assignmentsList.innerHTML = assignments.map((assignment) => {
        const dueDate = new Date(assignment.due_date);
        const now = new Date();
        const isOverdue = dueDate < now;
        const daysRemaining = Math.ceil((dueDate - now) / (1000 * 60 * 60 * 24));

        return `
            <div class="assignment-card ${isOverdue ? 'overdue' : ''}">
                <div class="assignment-header">
                    <div>
                        <h3>${escapeHtml(assignment.title)}</h3>
                        <p class="assignment-meta">Due: ${dueDate.toLocaleDateString()} ${isOverdue ? '(Overdue)' : `(${daysRemaining} days)`}</p>
                    </div>
                    <span class="pill ${isOverdue ? 'danger' : 'warning'}">${escapeHtml(assignment.status)}</span>
                </div>
                <p class="assignment-desc">${escapeHtml(assignment.description ? assignment.description.substring(0, 100) + '...' : 'No description')}</p>
                <div class="assignment-footer">
                    <span>Max score: ${assignment.max_score} pts</span>
                    <button class="btn btn-primary small" data-assignment-id="${assignment.id}" data-assignment-title="${escapeHtml(assignment.title)}">
                        Submit
                    </button>
                </div>
            </div>
        `;
    }).join('');

    // Add event listeners to submit buttons
    document.querySelectorAll('[data-assignment-id]').forEach((button) => {
        button.addEventListener('click', () => {
            const assignmentId = parseInt(button.dataset.assignmentId, 10);
            const assignmentTitle = button.dataset.assignmentTitle;
            openSubmissionModal(assignmentId, assignmentTitle);
        });
    });
}

function openSubmissionModal(assignmentId, assignmentTitle) {
    elements.submissionTitle.value = assignmentTitle;
    elements.submissionText.value = '';
    elements.submissionForm.dataset.assignmentId = assignmentId;
    elements.submissionModal.classList.remove('hidden');
}

function closeSubmissionModal() {
    elements.submissionModal.classList.add('hidden');
}

async function handleSubmissionForm(event) {
    event.preventDefault();
    
    const assignmentId = parseInt(elements.submissionForm.dataset.assignmentId, 10);
    const submissionText = elements.submissionText.value.trim();

    if (!submissionText) {
        alert('Please enter your submission.');
        return;
    }

    try {
        await api('api.php?action=submit-assignment', {
            method: 'POST',
            body: JSON.stringify({ assignment_id: assignmentId, submission_text: submissionText }),
        });
        alert('Assignment submitted successfully!');
        closeSubmissionModal();
        loadAssignments();
    } catch (error) {
        alert(error.message || 'Submission failed');
    }
}

// Instructor/Admin Functions
async function loadInstructorCourses() {
    if (!state.isAuthenticated || (state.user.role !== 'instructor' && state.user.role !== 'admin')) {
        return;
    }

    try {
        const data = await api('api.php?action=instructor-courses');
        const courses = data.courses || [];
        
        // Render manage courses
        elements.manageCoursesList.innerHTML = courses.map((course) => `
            <div class="course-card">
                <div class="course-header">
                    <h3>${escapeHtml(course.title)}</h3>
                    <span class="pill">${escapeHtml(course.level)}</span>
                </div>
                <p class="course-meta">${escapeHtml(course.category)} • ${escapeHtml(course.duration)}</p>
                <div class="course-footer">
                    <span>${escapeHtml(course.instructor)}</span>
                    <button class="btn btn-primary small" data-course-id="${course.id}">View Class</button>
                </div>
            </div>
        `).join('');

        // Add click listeners
        document.querySelectorAll('[data-course-id]').forEach((button) => {
            button.addEventListener('click', () => {
                const courseId = parseInt(button.dataset.courseId, 10);
                loadCourseAnalytics(courseId);
            });
        });

        // Populate assignment filter
        elements.gradingCourseFilter.innerHTML = '<option value="">All courses</option>' + 
            courses.map(c => `<option value="${c.id}">${escapeHtml(c.title)}</option>`).join('');
    } catch (error) {
        console.error(error);
    }
}

async function loadCourseAnalytics(courseId) {
    try {
        const data = await api(`api.php?action=course-analytics&course_id=${courseId}`);
        const analytics = data.analytics || {};
        const enrollments = data.enrollments || [];

        // Build analytics display
        const analyticsHtml = `
            <div class="analytics-grid">
                <div class="mini-stat">
                    <span>Total Students</span>
                    <strong>${analytics.total_students || 0}</strong>
                </div>
                <div class="mini-stat">
                    <span>Active</span>
                    <strong>${analytics.active_count || 0}</strong>
                </div>
                <div class="mini-stat">
                    <span>Completed</span>
                    <strong>${analytics.completed_count || 0}</strong>
                </div>
                <div class="mini-stat">
                    <span>Avg Completion</span>
                    <strong>${Math.round(analytics.avg_completion || 0)}%</strong>
                </div>
            </div>
            <div class="enrollments-table">
                <table>
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Progress</th>
                            <th>Avg Score</th>
                            <th>Submitted</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${enrollments.map(e => `
                            <tr>
                                <td>${escapeHtml(e.student_name)}</td>
                                <td>${Math.round(e.progress || 0)}%</td>
                                <td>${e.avg_score ? Math.round(e.avg_score) + '%' : 'N/A'}</td>
                                <td>${e.submitted_count || 0}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;

        alert(`Course: ${courseId}\nStudents: ${analytics.total_students}\nCompletion: ${Math.round(analytics.avg_completion)}%`);
    } catch (error) {
        alert('Failed to load analytics: ' + error.message);
    }
}

async function loadGradingSubmissions() {
    if (!state.isAuthenticated || (state.user.role !== 'instructor' && state.user.role !== 'admin')) {
        return;
    }

    try {
        const courseId = parseInt(elements.gradingCourseFilter.value, 10) || null;
        
        // For now, show a simple list of pending work
        elements.submissionsList.innerHTML = `
            <div class="empty-box">
                <p>Grading interface ready.</p>
                <p>Select a course to view student submissions.</p>
            </div>
        `;
    } catch (error) {
        console.error(error);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    elements.roleButtons.forEach((button) => {
        button.addEventListener('click', () => {
            if (!state.isAuthenticated) {
                showLoginModal();
                return;
            }
            setRole(button.dataset.role);
        });
    });

    elements.navItems.forEach((item) => {
        item.addEventListener('click', () => {
            elements.navItems.forEach((button) => button.classList.toggle('active', button === item));
            const view = item.dataset.view || 'overview';
            switchView(view);
        });
    });

    elements.askAi.addEventListener('click', askAi);
    elements.refreshData.addEventListener('click', loadDashboard);
    elements.logoutBtn.addEventListener('click', handleLogout);
    elements.loginForm.addEventListener('submit', handleLogin);
    elements.closeLoginModal.addEventListener('click', () => {
        if (!state.isAuthenticated) {
            showLoginModal();
        } else {
            hideLoginModal();
        }
    });
    elements.closeCourseModal.addEventListener('click', () => {
        elements.courseModal.classList.add('hidden');
    });
    elements.closeSubmissionModal.addEventListener('click', closeSubmissionModal);
    elements.submissionForm.addEventListener('submit', handleSubmissionForm);
    elements.courseSearch.addEventListener('input', () => {
        loadCourses();
    });
    elements.levelFilter.addEventListener('change', () => {
        loadCourses(elements.levelFilter.value);
    });
    elements.verifyCertificateBtn.addEventListener('click', () => {
        const code = elements.certificateCodeInput.value.trim();
        if (!code) {
            alert('Enter a certificate code to verify.');
            return;
        }

        const certificate = (state.dashboard?.certificates || [{ name: 'Frontend Certificate', status: 'Verified', id: 'SKG-2026-1001' }]).find((item) => String(item.id || item.certificate_id || item.name || '').toLowerCase().includes(code.toLowerCase()));
        if (certificate) {
            alert(`${certificate.name || 'Certificate'} verified successfully. Status: ${certificate.status || 'Active'}`);
            return;
        }

        alert('Certificate code not found. Please check the code and try again.');
    });
    elements.gradingCourseFilter.addEventListener('change', loadGradingSubmissions);
    elements.gradingAssignmentFilter.addEventListener('change', loadGradingSubmissions);
    elements.closeGradingModal.addEventListener('click', () => {
        elements.gradingModal.classList.add('hidden');
    });

    initSession();
});

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, (char) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    })[char]);
}

<?php
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Modern e-learning and project management system dashboard">
    <meta name="theme-color" content="#4f46e5">
    <title>LearnFlow Pro</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="app-shell">
        <aside class="sidebar">
            <div class="brand-block">
                <div class="brand-mark">LF</div>
                <div>
                    <p class="eyebrow muted">Academy OS</p>
                    <h1>LearnFlow</h1>
                </div>
            </div>

            <nav class="side-nav">
                <button class="nav-item active" data-view="overview">Overview</button>
                <button class="nav-item" data-view="courses">Explore</button>
                <button class="nav-item" data-view="assignments">Assignments</button>
                <button class="nav-item instructor-only hidden" data-view="manage">Manage</button>
                <button class="nav-item instructor-only hidden" data-view="grading">Grading</button>
                <button class="nav-item" data-view="schedule">Schedule</button>
                <button class="nav-item" data-view="analytics">Analytics</button>
                <button class="nav-item" data-view="forum">Forum</button>
                <button class="nav-item" data-view="payments">Payments</button>
            </nav>

            <div class="mini-panel">
                <p class="eyebrow muted">AI assistant</p>
                <h3>Learning coach</h3>
                <textarea id="aiQuestion" rows="4" placeholder="Ask for a study plan or feedback..."></textarea>
                <button id="askAi" class="btn btn-primary full">Ask AI</button>
                <div id="aiAnswer" class="ai-answer hidden"></div>
            </div>
        </aside>

        <main class="main-panel">
            <header class="topbar">
                <div>
                    <p class="eyebrow">Learning platform</p>
                    <h2 id="headerTitle">Student dashboard</h2>
                </div>

                <div class="topbar-actions">
                    <div class="role-switcher" aria-label="Choose user role">
                        <button class="role-btn active" data-role="student">Student</button>
                        <button class="role-btn" data-role="instructor">Instructor</button>
                        <button class="role-btn" data-role="admin">Admin</button>
                    </div>
                    <button class="btn btn-secondary" id="refreshData">Refresh</button>
                    <button class="btn btn-secondary" id="logoutBtn">Log out</button>
                </div>
            </header>

            <section class="overview-panel" id="overviewPanel">
                <div class="hero-card">
                    <div>
                        <p class="eyebrow">This week</p>
                        <h3>Keep your momentum moving.</h3>
                        <p>Complete your active tasks, review your next milestone, and stay on track with your learning roadmap.</p>
                    </div>
                    <button class="btn btn-primary">View roadmap</button>
                </div>

                <div id="statsGrid" class="stats-grid"></div>
            </section>

            <section class="overview-panel hidden" id="coursesPanel">
                <div class="hero-card">
                    <div>
                        <p class="eyebrow">Explore</p>
                        <h3>Discover new learning paths.</h3>
                        <p>Browse from dozens of curated courses across web development, data science, design, and more.</p>
                    </div>
                </div>

                <div class="course-filters">
                    <input type="text" id="courseSearch" placeholder="Search courses..." class="search-input">
                    <select id="levelFilter" class="filter-select">
                        <option value="">All Levels</option>
                        <option value="Beginner">Beginner</option>
                        <option value="Intermediate">Intermediate</option>
                        <option value="Advanced">Advanced</option>
                    </select>
                </div>

                <div id="coursesList" class="courses-grid"></div>
            </section>

            <section class="overview-panel hidden" id="assignmentsPanel">
                <div class="hero-card">
                    <div>
                        <p class="eyebrow">Learning</p>
                        <h3>Your active assignments.</h3>
                        <p>Submit your work, track your progress, and get feedback from instructors.</p>
                    </div>
                </div>

                <div id="assignmentsList" class="assignments-list"></div>
            </section>

            <section class="overview-panel hidden instructor-only" id="managePanel">
                <div class="hero-card">
                    <div>
                        <p class="eyebrow">Instructor</p>
                        <h3>Manage your courses.</h3>
                        <p>View enrollments, track student progress, and monitor course analytics.</p>
                    </div>
                </div>

                <div id="manageCoursesList" class="courses-grid"></div>
            </section>

            <section class="overview-panel hidden instructor-only" id="gradingPanel">
                <div class="hero-card">
                    <div>
                        <p class="eyebrow">Instructor</p>
                        <h3>Review and grade submissions.</h3>
                        <p>View pending assignments and provide feedback to students.</p>
                    </div>
                </div>

                <div class="grading-toolbar">
                    <select id="gradingCourseFilter" class="filter-select">
                        <option value="">All courses</option>
                    </select>
                    <select id="gradingAssignmentFilter" class="filter-select">
                        <option value="">All assignments</option>
                    </select>
                </div>

                <div id="submissionsList" class="submissions-list"></div>
            </section>
                <div class="panel panel-wide">
                    <div class="panel-header">
                        <h3>Courses</h3>
                        <button class="mini-link" type="button">Manage</button>
                    </div>
                    <div id="coursesList" class="stack-list"></div>
                </div>

                <div class="panel">
                    <div class="panel-header">
                        <h3>Assignments</h3>
                        <button class="mini-link" type="button">Open tasks</button>
                    </div>
                    <div id="assignmentsList" class="stack-list compact"></div>
                </div>

                <div class="panel">
                    <div class="panel-header">
                        <h3>Notifications</h3>
                        <button class="mini-link" type="button">Inbox</button>
                    </div>
                    <div id="notificationsList" class="stack-list compact"></div>
                </div>

                <div class="panel">
                    <div class="panel-header">
                        <h3>Schedule</h3>
                        <button class="mini-link" type="button">Calendar</button>
                    </div>
                    <div id="scheduleList" class="stack-list compact"></div>
                </div>

                <div class="panel panel-wide">
                    <div class="panel-header">
                        <h3>Learning analysis</h3>
                        <button class="mini-link" type="button">Insights</button>
                    </div>
                    <div id="analyticsPanel" class="analytics-panel"></div>
                </div>

                <div class="panel">
                    <div class="panel-header">
                        <h3>Discussions</h3>
                        <button class="mini-link" type="button">Forum</button>
                    </div>
                    <div id="forumList" class="stack-list compact"></div>
                </div>

                <div class="panel">
                    <div class="panel-header">
                        <h3>Certificates</h3>
                        <button class="mini-link" type="button">Download</button>
                    </div>
                    <div id="certificatesList" class="stack-list compact"></div>
                </div>

                <div class="panel">
                    <div class="panel-header">
                        <h3>Payments</h3>
                        <button class="mini-link" type="button">Manage</button>
                    </div>
                    <div id="paymentsList" class="stack-list compact"></div>
                </div>
            </section>
        </main>
    </div>

    <div id="loginModal" class="modal" aria-live="polite">
        <div class="modal-content">
            <button class="close" type="button" id="closeLoginModal" aria-label="Close">&times;</button>
            <div class="brand-block" style="margin-bottom: 18px;">
                <div class="brand-mark">LF</div>
                <div>
                    <p class="eyebrow muted">Welcome back</p>
                    <h1 style="color: #111827;">LearnFlow Pro</h1>
                </div>
            </div>

            <form id="loginForm" class="login-form">
                <div class="field-group">
                    <label for="loginEmail">Email</label>
                    <input id="loginEmail" type="email" value="student@learnflow.app" required>
                </div>
                <div class="field-group">
                    <label for="loginPassword">Password</label>
                    <input id="loginPassword" type="password" value="student123" required>
                </div>
                <div class="field-group">
                    <label for="loginRole">Role</label>
                    <select id="loginRole">
                        <option value="student">Student</option>
                        <option value="instructor">Instructor</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary full">Sign in</button>
            </form>
            <p class="demo-credentials" style="margin-top: 12px; color: var(--muted); font-size: 0.8rem;">
                Demo credentials: student@learnflow.app / student123, instructor@learnflow.app / instructor123, admin@learnflow.app / admin123
            </p>
        </div>
    </div>

    <div id="courseModal" class="modal hidden" aria-live="polite">
        <div class="modal-content">
            <button class="close" type="button" id="closeCourseModal" aria-label="Close">&times;</button>
            <div id="courseModalBody"></div>
        </div>
    </div>

    <div id="submissionModal" class="modal hidden" aria-live="polite">
        <div class="modal-content">
            <button class="close" type="button" id="closeSubmissionModal" aria-label="Close">&times;</button>
            <h2>Submit Assignment</h2>
            <form id="submissionForm" class="login-form">
                <div class="field-group">
                    <label for="submissionTitle">Assignment</label>
                    <input id="submissionTitle" type="text" disabled>
                </div>
                <div class="field-group">
                    <label for="submissionText">Your Response</label>
                    <textarea id="submissionText" rows="8" placeholder="Enter your assignment response here..." required></textarea>
                </div>
                <button type="submit" class="btn btn-primary full">Submit Assignment</button>
            </form>
        </div>
    </div>

    <div id="gradingModal" class="modal hidden" aria-live="polite">
        <div class="modal-content">
            <button class="close" type="button" id="closeGradingModal" aria-label="Close">&times;</button>
            <div id="gradingModalBody"></div>
        </div>
    </div>

    <script src="script.js" defer></script>
</body>
</html>

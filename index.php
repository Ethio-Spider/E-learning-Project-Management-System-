<?php
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SkillGrid OS is a modern learning platform architecture overview for a connected education system.">
    <meta name="theme-color" content="#050b17">
    <title>SkillGrid OS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="page-shell">
        <header class="top-header">
            <div class="brand-wrap">
                <div class="brand-mark">SG</div>
                <div>
                    <span class="eyebrow">Learning ecosystem</span>
                    <h1>SkillGrid OS</h1>
                </div>
            </div>

            <nav class="header-nav" aria-label="Main navigation">
                <a href="#overview">Overview</a>
                <a href="#architecture">Architecture</a>
                <a href="#modules">Modules</a>
                <a href="#features">Features</a>
                <a href="#dashboard">Dashboard</a>
            </nav>

            <button class="primary-btn" type="button" onclick="document.getElementById('dashboard').scrollIntoView({behavior:'smooth'})">Launch platform</button>
        </header>

        <main>
            <section class="hero" id="overview">
                <div class="hero-copy">
                    <span class="badge">Enterprise e-learning platform</span>
                    <h2>Build smarter learning systems for every learner.</h2>
                    <p>
                        SkillGrid OS unifies web, mobile, and API experiences into one secure learning ecosystem.
                        It connects identity, course delivery, data services, and learner workflows to create a
                        more adaptive and measurable education experience.
                    </p>

                    <div class="cta-row">
                        <button class="primary-btn" type="button" onclick="document.getElementById('dashboard').scrollIntoView({behavior:'smooth'})">Explore system</button>
                        <button class="ghost-btn" type="button" onclick="document.getElementById('architecture').scrollIntoView({behavior:'smooth'})">View modules</button>
                    </div>

                    <ul class="feature-list">
                        <li>Multi-channel access</li>
                        <li>REST API foundation</li>
                        <li>Secure user orchestration</li>
                    </ul>
                </div>

                <div class="hero-panel">
                    <div class="metric-head">
                        <span>Platform health</span>
                        <strong>99.9%</strong>
                    </div>
                    <div class="mini-metrics">
                        <div>
                            <span>Active learners</span>
                            <strong>12.4K</strong>
                        </div>
                        <div>
                            <span>Courses</span>
                            <strong>148</strong>
                        </div>
                        <div>
                            <span>Assignments</span>
                            <strong>3.2K</strong>
                        </div>
                    </div>
                    <div class="signal-card">
                        <div>
                            <span class="dot green"></span>
                            <small>API responsive</small>
                        </div>
                        <div>
                            <span class="dot blue"></span>
                            <small>Syncing data</small>
                        </div>
                    </div>
                </div>
            </section>

            <section class="architecture-section" id="architecture">
                <div class="section-heading">
                    <span class="eyebrow">Platform architecture</span>
                    <h3>Connected layers powering every learner journey</h3>
                </div>

                <div class="architecture-diagram" aria-label="LearnFlow Pro system architecture">
                    <div class="layer layer-top">
                        <div class="node node-web">Web</div>
                        <div class="node node-mobile">Mobile</div>
                        <div class="node node-api">API</div>
                    </div>

                    <div class="layer layer-bridge">
                        <div class="node node-bridge node-bridge--wide">Flutter</div>
                        <div class="node node-bridge">REST API</div>
                    </div>

                    <div class="layer layer-central">
                        <div class="node node-auth">Authentication</div>
                    </div>

                    <div class="layer layer-split">
                        <div class="node node-services">Services</div>
                        <div class="node node-repos">Repositories</div>
                    </div>

                    <div class="layer layer-db">
                        <div class="node node-db">MariaDB</div>
                    </div>

                    <div class="layer layer-domain">
                        <div class="node node-domain">Users</div>
                        <div class="node node-domain">Courses</div>
                        <div class="node node-domain node-learning">Learning</div>
                    </div>

                    <div class="layer layer-submodules">
                        <div class="node node-sub">Assignments</div>
                        <div class="node node-sub">Quizzes</div>
                        <div class="node node-sub">Progress</div>
                        <div class="node node-sub">Certificates</div>
                        <div class="node node-sub">Analytics</div>
                        <div class="node node-sub">Notifications</div>
                    </div>
                </div>
            </section>

            <section class="module-section" id="modules">
                <div class="section-heading">
                    <span class="eyebrow">Core domains</span>
                    <h3>Every part of the learning lifecycle</h3>
                </div>

                <div class="module-grid">
                    <article class="module-card accent-blue">
                        <span class="module-tag">Users</span>
                        <h4>Identity and role access</h4>
                        <p>Profile management, secure sign-in, enrollment flow, and role-specific access for every user.</p>
                    </article>

                    <article class="module-card accent-purple">
                        <span class="module-tag">Courses</span>
                        <h4>Learning pathways</h4>
                        <p>Course planning, curriculum sequencing, structured milestones, and guided engagement across programs.</p>
                    </article>

                    <article class="module-card accent-green">
                        <span class="module-tag">Learning</span>
                        <h4>Assessment and momentum</h4>
                        <p>Assignments, feedback loops, progress checkpoints, and learner analytics that keep each path moving.</p>
                    </article>
                </div>
            </section>

            <section class="feature-panel" id="features">
                <div class="feature-card">
                    <div class="feature-icon">01</div>
                    <div>
                        <h4>Responsive learning access</h4>
                        <p>Support for desktop, tablet, and mobile learners through one unified interface.</p>
                    </div>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">02</div>
                    <div>
                        <h4>Smart workflow automation</h4>
                        <p>Automated notifications, milestone reminders, and guided activity flows throughout learning journeys.</p>
                    </div>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">03</div>
                    <div>
                        <h4>Secure platform foundation</h4>
                        <p>Protected APIs, identity checks, and repository-backed services that keep every workflow reliable.</p>
                    </div>
                </div>
            </section>

            <section class="dashboard-shell" id="dashboard">
                <aside class="dashboard-sidebar">
                    <div class="brand-wrap">
                        <div class="brand-mark">SG</div>
                        <div>
                            <span class="eyebrow">Academy OS</span>
                            <h1>SkillGrid</h1>
                        </div>
                    </div>

                    <nav class="side-nav" aria-label="Dashboard navigation">
                        <button class="nav-item active" data-view="overview">Overview</button>
                        <button class="nav-item" data-view="courses" data-roles="student">Explore</button>
                        <button class="nav-item" data-view="assignments" data-roles="student">Assignments</button>
                        <button class="nav-item hidden" data-view="manage" data-roles="instructor">Manage courses</button>
                        <button class="nav-item hidden" data-view="grading" data-roles="instructor,admin">Grading</button>
                        <button class="nav-item" data-view="schedule" data-roles="student,instructor,admin">Schedule</button>
                        <button class="nav-item" data-view="analytics" data-roles="student,instructor,admin">Analytics</button>
                        <button class="nav-item" data-view="forum" data-roles="student,instructor,admin">Forum</button>
                        <button class="nav-item" data-view="payments" data-roles="student,admin">Payments</button>
                        <button class="nav-item hidden" data-view="admin-users" data-roles="admin">User management</button>
                    </nav>

                    <div class="mini-panel">
                        <p class="eyebrow muted">AI assistant</p>
                        <h3>Learning coach</h3>
                        <textarea id="aiQuestion" rows="4" placeholder="Ask for a study plan or feedback..."></textarea>
                        <button id="askAi" class="btn btn-primary full">Ask AI</button>
                        <div id="aiAnswer" class="ai-answer hidden"></div>
                    </div>
                </aside>

                <main class="dashboard-main">
                    <header class="topbar">
                        <div>
                            <p class="eyebrow">Learning platform</p>
                            <h2 id="headerTitle">Student dashboard</h2>
                        </div>

                        <div class="topbar-actions">
                            <div class="role-switcher" aria-label="Current user role">
                                <button class="role-btn active" data-role="student">Student</button>
                                <button class="role-btn" data-role="instructor">Instructor</button>
                                <button class="role-btn" data-role="admin">Admin</button>
                            </div>
                            <button class="btn btn-secondary" id="refreshData">Refresh</button>
                            <button class="btn btn-secondary" id="logoutBtn">Log out</button>
                        </div>
                    </header>

                    <section class="overview-panel" id="overviewPanel">
                        <div class="hero-card dashboard-hero">
                            <div>
                                <p class="eyebrow">This week</p>
                                <h3>Keep your momentum moving.</h3>
                                <p>Complete your active tasks, review your next milestone, and stay on track with your learning roadmap.</p>
                            </div>
                                <button class="btn btn-primary" type="button" data-view="assignments">View roadmap</button>
                        </div>

                        <div id="statsGrid" class="stats-grid"></div>

                        <div class="showcase-panel">
                            <div class="panel-header">
                                <h3>Modern LMS capabilities</h3>
                                <button class="mini-link" type="button">Everything included</button>
                            </div>
                            <div id="lmsFeatureGrid" class="lms-feature-grid"></div>
                        </div>
                    </section>

                    <section class="overview-panel hidden" id="coursesPanel">
                        <div class="hero-card dashboard-hero">
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
                        <div class="hero-card dashboard-hero">
                            <div>
                                <p class="eyebrow">Learning</p>
                                <h3>Your active assignments.</h3>
                                <p>Submit your work, track your progress, and get feedback from instructors.</p>
                            </div>
                        </div>

                        <div id="assignmentsList" class="assignments-list"></div>
                    </section>

                    <section class="overview-panel hidden" id="managePanel" data-roles="instructor">
                        <div class="hero-card dashboard-hero">
                            <div>
                                <p class="eyebrow">Instructor</p>
                                <h3>Manage your courses.</h3>
                                <p>View enrollments, track student progress, and monitor course analytics.</p>
                            </div>
                        </div>

                        <div id="manageCoursesList" class="courses-grid"></div>
                    </section>

                    <section class="overview-panel hidden" id="gradingPanel" data-roles="instructor,admin">
                        <div class="hero-card dashboard-hero">
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

                    <div class="content-grid">
                        <div class="panel panel-wide">
                            <div class="panel-header">
                                <h3>Courses</h3>
                                <button class="mini-link" type="button">Manage</button>
                            </div>
                            <div id="coursesListOverview" class="stack-list"></div>
                        </div>

                        <div class="panel">
                            <div class="panel-header">
                                <h3>Assignments</h3>
                                <button class="mini-link" type="button">Open tasks</button>
                            </div>
                            <div id="assignmentsListOverview" class="stack-list compact"></div>
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
                            <div class="verification-box">
                                <input id="certificateCodeInput" type="text" placeholder="Enter certificate code" aria-label="Certificate code" />
                                <button class="btn btn-secondary small" id="verifyCertificateBtn" type="button">Verify</button>
                            </div>
                        </div>

                        <div class="panel">
                            <div class="panel-header">
                                <h3>Course reviews</h3>
                                <button class="mini-link" type="button">Ratings</button>
                            </div>
                            <div id="courseReviewList" class="stack-list compact"></div>
                        </div>

                        <div class="panel">
                            <div class="panel-header">
                                <h3>Badges & rewards</h3>
                                <button class="mini-link" type="button">Progress</button>
                            </div>
                            <div id="badgeGrid" class="badge-grid"></div>
                        </div>

                        <div class="panel">
                            <div class="panel-header">
                                <h3>Payments</h3>
                                <button class="mini-link" id="managePaymentsBtn" type="button">Manage</button>
                            </div>
                            <div id="paymentsList" class="stack-list compact"></div>
                        </div>
                    </div>
                </main>
            </section>
        </main>
    </div>

    <div id="loginModal" class="modal hidden" aria-live="polite">
        <div class="modal-content">
            <button class="close" type="button" id="closeLoginModal" aria-label="Close">&times;</button>
            <div class="brand-wrap" style="margin-bottom: 18px;">
                <div class="brand-mark">SG</div>
                <div>
                    <p class="eyebrow muted">Welcome back</p>
                    <h1 style="color: #e5eefc;">SkillGrid OS</h1>
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

    <div id="paymentModal" class="modal hidden" aria-live="polite">
        <div class="modal-content payment-modal-content">
            <button class="close" type="button" id="closePaymentModal" aria-label="Close">&times;</button>
            <p class="eyebrow">Secure checkout</p>
            <h2 id="paymentCourseTitle">Complete payment</h2>
            <p class="payment-total">Total <strong id="paymentAmount">0 ETB</strong></p>
            <form id="paymentForm" class="login-form">
                <div class="field-group">
                    <label for="paymentMethod">Payment method</label>
                    <select id="paymentMethod" required>
                        <option value="telebirr">Telebirr</option>
                        <option value="visa">Visa card</option>
                        <option value="mastercard">Mastercard</option>
                        <option value="card">Other bank card</option>
                        <option value="paypal">PayPal</option>
                    </select>
                </div>
                <div id="telebirrFields" class="payment-fields">
                    <div class="field-group">
                        <label for="telebirrPhone">Telebirr phone number</label>
                        <input id="telebirrPhone" type="tel" placeholder="09XXXXXXXX" inputmode="tel">
                    </div>
                </div>
                <div id="cardFields" class="payment-fields hidden">
                    <div class="field-group">
                        <label for="cardholderName">Cardholder name</label>
                        <input id="cardholderName" type="text" autocomplete="cc-name" placeholder="Name on card">
                    </div>
                    <div class="field-group">
                        <label for="cardNumber">Card number</label>
                        <input id="cardNumber" type="tel" inputmode="numeric" autocomplete="cc-number" placeholder="1234 5678 9012 3456" maxlength="19">
                    </div>
                    <div class="payment-card-row">
                        <div class="field-group">
                            <label for="cardExpiry">Expiry</label>
                            <input id="cardExpiry" type="text" inputmode="numeric" autocomplete="cc-exp" placeholder="MM/YY" maxlength="5">
                        </div>
                        <div class="field-group">
                            <label for="cardCvv">CVV</label>
                            <input id="cardCvv" type="password" inputmode="numeric" autocomplete="cc-csc" placeholder="•••" maxlength="4">
                        </div>
                    </div>
                </div>
                <p id="cardError" class="payment-error" role="alert"></p>
                <p id="paymentProviderNote" class="payment-note">You will receive payment instructions after checkout.</p>
                <button type="submit" class="btn btn-primary full">Continue to payment</button>
                <small class="payment-security">Your card details are used only for this checkout demo and are never saved by this application.</small>
            </form>
        </div>
    </div>

    <script src="script.js" defer></script>
</body>
</html>

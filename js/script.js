document.addEventListener('DOMContentLoaded', () => {
    lucide.createIcons();
    loadSection('dashboard');
    setupNav();
});

function setupNav() {
    document.querySelectorAll('.nav-links li').forEach(link => {
        link.addEventListener('click', () => {
            if(link.hasAttribute('data-section')) {
                document.querySelector('.nav-links li.active').classList.remove('active');
                link.classList.add('active');
                loadSection(link.getAttribute('data-section'));
            }
        });
    });
}

function loadSection(section) {
    const container = document.getElementById('dynamic-area');
    document.getElementById('section-title').innerText = section.charAt(0).toUpperCase() + section.slice(1);
    
    container.innerHTML = ""; // Clear current
    
    if (section === 'dashboard') {
        container.innerHTML = `
            <div class="stats-grid" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:20px;">
                <div class="stat-card" data-tilt><h3>${USER_ROLE === 'teacher' ? '24' : '4'}</h3><p>Active ${USER_ROLE === 'teacher' ? 'Students' : 'Courses'}</p></div>
                <div class="stat-card" data-tilt><h3>12</h3><p>Messages</p></div>
                <div class="stat-card" data-tilt><h3>85%</h3><p>Avg Score</p></div>
            </div>
            <h3 style="margin-top:2rem">Recent Activity</h3>
            <div class="course-grid" style="margin-top:1rem">
                <div class="course-card"><h4>Welcome to LearnFlow</h4><p>You are logged in as a <b>${USER_ROLE}</b>.</p></div>
            </div>
        `;
    } 

    else if (section === 'courses') {
        if (USER_ROLE === 'teacher') {
            container.innerHTML = `
                <div class="stat-card">
                    <h3>Create a Class</h3>
                    <input type="text" placeholder="Subject Name" class="admin-input" style="width:100%; margin:10px 0;">
                    <button class="btn-primary" style="width:auto">Generate Invite Code</button>
                </div>`;
        } else {
            container.innerHTML = `<button class="btn-primary" style="width:auto">+ Join New Class</button><div class="course-grid"></div>`;
        }
    }

    else if (section === 'quiz') {
        if (USER_ROLE === 'teacher') {
            container.innerHTML = `
                <div class="stat-card">
                    <h3>Quiz Builder</h3>
                    <p>Create questions for your students.</p>
                    <button class="btn-primary" style="margin-top:15px">+ Add Question</button>
                </div>`;
        } else {
            container.innerHTML = `<div class="stat-card"><h3>Active Quizzes</h3><p>No quizzes assigned currently.</p></div>`;
        }
    }

    else if (section === 'profile') {
        container.innerHTML = `
            <div class="stat-card" style="max-width: 500px; text-align:center;">
                <div class="avatar" style="width:100px; height:100px; margin: 0 auto 15px;"></div>
                <h2>${USER_NAME}</h2>
                <p style="color:var(--primary)">${USER_ROLE.toUpperCase()}</p>
                <hr style="margin:20px 0; border:0; border-top:1px solid var(--glass-border);">
                <h4>Quiz Leaderboard (Top 3)</h4>
                <div style="text-align:left; margin-top:10px;">
                    <p>1. Sarah J. - 950 pts</p>
                    <p>2. Mike R. - 820 pts</p>
                    <p>3. You - 790 pts</p>
                </div>
            </div>`;
    }

    lucide.createIcons();
    VanillaTilt.init(document.querySelectorAll("[data-tilt]"));
}
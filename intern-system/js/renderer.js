// renderer.js
// DOM rendering only — NO state mutation

// -----------------------------
// Central render function
// -----------------------------
function render() {
  const state = AppState.getState();

  renderLoader(state.ui.loading);
  renderErrors(state.errors);
  renderView(state.ui.currentView, state);
}

// -----------------------------
// Loader
// -----------------------------
function renderLoader(isLoading) {
  const loader = document.getElementById("app-loader");
  loader.hidden = !isLoading;
}

// -----------------------------
// Error Renderer
// -----------------------------
function renderErrors(errors) {
  const errorBox = document.getElementById("app-error");

  if (!errors || errors.length === 0) {
    errorBox.hidden = true;
    errorBox.innerHTML = "";
    return;
  }

  errorBox.hidden = false;
  errorBox.innerHTML = `
    <ul>
      ${errors.map((e) => `<li>${e.message}</li>`).join("")}
    </ul>
  `;
}

// -----------------------------
// View Router
// -----------------------------
function renderView(view, state) {
  const container = document.getElementById("app-content");
  container.innerHTML = "";

  switch (view) {
    case "INTERN_LIST":
      renderInternList(container, state);
      break;

    case "TASK_LIST":
      renderTaskList(container, state);
      break;
    // inside renderView switch
    case "INTERN_CREATE":
      renderInternCreateForm(container);
      break;
    // inside renderView switch
    case "TASK_CREATE":
      renderTaskCreateForm(container, state);
      break;

    default:
      container.innerHTML = "<p>View not found</p>";
  }
}

// -----------------------------
// Intern List View
// -----------------------------
// renderer.js (modify renderInternList)

// renderer.js

// renderer.js (modify renderInternList)

function renderInternList(container, state) {
  const { interns, tasks, assignments, ui } = state;

  let filteredInterns = interns;

  if (ui.filters.status !== "ALL") {
    filteredInterns = filteredInterns.filter(
      i => i.status === ui.filters.status
    );
  }

  if (ui.filters.skill !== "ALL") {
    filteredInterns = filteredInterns.filter(
      i => i.skills.includes(ui.filters.skill)
    );
  }

  const rows = filteredInterns.map(intern => {
    const taskCount = assignments.filter(
      a => a.internId === intern.id
    ).length;

    // ✅ derived hours (NOT stored)
    const totalHours = calculateTotalHours(
      intern.id,
      tasks,
      assignments
    );

    let actions = "";
    if (intern.status === "ONBOARDING") {
      actions = `<button data-action="ACTIVATE" data-id="${intern.id}">Activate</button>`;
    } else if (intern.status === "ACTIVE") {
      actions = `<button data-action="EXIT" data-id="${intern.id}">Exit</button>`;
    }

    return `
      <tr>
        <td>${intern.id}</td>
        <td>${intern.name}</td>
        <td>${intern.status}</td>
        <td>${intern.skills.join(", ")}</td>
        <td>${taskCount}</td>
        <td>${totalHours}</td>
        <td>${actions}</td>
      </tr>
    `;
  }).join("");

  container.innerHTML = `
    <h2>Interns</h2>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Status</th>
          <th>Skills</th>
          <th>Tasks</th>
          <th>Total Hours</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        ${rows || "<tr><td colspan='7'>No interns found</td></tr>"}
      </tbody>
    </table>
  `;
}


// -----------------------------
// Task List View
// -----------------------------
// renderer.js (modify renderTaskList)

// renderer.js (modify renderTaskList)

function renderTaskList(container, state) {
  const { tasks, interns } = state;

  const activeInternOptions = interns
    .filter(i => i.status === "ACTIVE")
    .map(i => `<option value="${i.id}">${i.name} (${i.id})</option>`)
    .join("");

  const rows = tasks.map(task => {
    let action = "";

    if (task.status === "AVAILABLE") {
      action = `
        <select data-assign-task="${task.id}">
          <option value="">Assign to...</option>
          ${activeInternOptions}
        </select>
      `;
    }

    return `
      <tr>
        <td>${task.id}</td>
        <td>${task.title}</td>
        <td>${task.status}</td>
        <td>${task.requiredSkills.join(", ")}</td>
        <td>${task.estimatedHours}</td>
        <td>${action}</td>
      </tr>
    `;
  }).join("");

  container.innerHTML = `
    <h2>Tasks</h2>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Title</th>
          <th>Status</th>
          <th>Skills</th>
          <th>Hours</th>
          <th>Assign</th>
        </tr>
      </thead>
      <tbody>
        ${rows || "<tr><td colspan='6'>No tasks found</td></tr>"}
      </tbody>
    </table>
  `;
}



// renderer.js (add below existing code)

function renderInternCreateForm(container) {
  container.innerHTML = `
    <h2>Create Intern</h2>

    <form id="intern-form">
      <div>
        <label>Name</label>
        <input type="text" id="intern-name" />
      </div>

      <div>
        <label>Email</label>
        <input type="email" id="intern-email" />
      </div>

      <div>
        <label>Skills (comma separated)</label>
        <input type="text" id="intern-skills" placeholder="HTML, CSS, JS" />
      </div>

      <button type="submit">Create Intern</button>
    </form>
  `;
}

// renderer.js

function renderTaskCreateForm(container, state) {
  const taskOptions = state.tasks
    .map((t) => `<option value="${t.id}">${t.title}</option>`)
    .join("");

  container.innerHTML = `
    <h2>Create Task</h2>

    <form id="task-form">
      <div>
        <label>Title</label>
        <input type="text" id="task-title" />
      </div>

      <div>
        <label>Required Skills (comma separated)</label>
        <input type="text" id="task-skills" placeholder="HTML, CSS, JS" />
      </div>

      <div>
        <label>Estimated Hours</label>
        <input type="number" id="task-hours" />
      </div>

      <div>
        <label>Dependencies</label>
        <select id="task-deps" multiple>
          ${taskOptions}
        </select>
      </div>

      <button type="submit">Create Task</button>
    </form>
  `;
}

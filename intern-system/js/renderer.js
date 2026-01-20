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
      ${errors.map(e => `<li>${e.message}</li>`).join("")}
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

    default:
      container.innerHTML = "<p>View not found</p>";
  }
}

// -----------------------------
// Intern List View
// -----------------------------
function renderInternList(container, state) {
  const rows = state.interns.map(intern => {
    const taskCount = state.assignments.filter(
      a => a.internId === intern.id
    ).length;

    return `
      <tr>
        <td>${intern.id}</td>
        <td>${intern.name}</td>
        <td>${intern.status}</td>
        <td>${intern.skills.join(", ")}</td>
        <td>${taskCount}</td>
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
        </tr>
      </thead>
      <tbody>
        ${rows || "<tr><td colspan='5'>No interns found</td></tr>"}
      </tbody>
    </table>
  `;
}

// -----------------------------
// Task List View
// -----------------------------
function renderTaskList(container, state) {
  const rows = state.tasks.map(task => `
    <tr>
      <td>${task.id}</td>
      <td>${task.title}</td>
      <td>${task.status}</td>
      <td>${task.requiredSkills.join(", ")}</td>
      <td>${task.estimatedHours}</td>
    </tr>
  `).join("");

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
        </tr>
      </thead>
      <tbody>
        ${rows || "<tr><td colspan='5'>No tasks found</td></tr>"}
      </tbody>
    </table>
  `;
}

// app.js
// App bootstrap + event wiring
// UI → Validators → FakeServer → Rules → State → Renderer

document.addEventListener("DOMContentLoaded", initApp);

// -----------------------------
// App Initialization
// -----------------------------
function initApp() {
  render();
  setupNavigation();
}

// -----------------------------
// Navigation (state-based)
// -----------------------------
function setupNavigation() {
  const nav = document.getElementById("app-nav");

  nav.innerHTML = `
    <button data-view="INTERN_LIST">Interns</button>
    <button data-view="INTERN_CREATE">Create Intern</button>
    <button data-view="TASK_LIST">Tasks</button>
    <button data-view="TASK_CREATE">Create Task</button>

  `;

  nav.addEventListener("click", (e) => {
    if (e.target.dataset.view) {
      AppState.updateState((state) => {
        state.ui.currentView = e.target.dataset.view;
      });
      render();
    }
  });
}

// -----------------------------
// Intern Creation Flow
// -----------------------------
async function createIntern(formData) {
  AppState.clearErrors();

  const state = AppState.getState();
  const validationErrors = validateInternForm(formData, state.interns);

  if (validationErrors.length > 0) {
    validationErrors.forEach((msg) => AppState.addError(msg));
    render();
    return;
  }

  try {
    setLoading(true);

    await FakeServer.checkEmailUnique(formData.email);

    const intern = {
      id: generateInternId(state.interns),
      name: formData.name,
      email: formData.email,
      skills: formData.skills,
      status: "ONBOARDING",
    };

    await FakeServer.saveIntern(intern);
  } catch (error) {
    AppState.addError(error.message);
  } finally {
    setLoading(false);
    render();
  }
}

// -----------------------------
// Task Creation Flow
// -----------------------------
async function createTask(formData) {
  AppState.clearErrors();

  const errors = validateTaskForm(formData);
  if (errors.length > 0) {
    errors.forEach((msg) => AppState.addError(msg));
    render();
    return;
  }

  try {
    setLoading(true);

    const task = {
      id: `TASK-${Date.now()}`,
      title: formData.title,
      requiredSkills: formData.requiredSkills,
      dependsOn: formData.dependsOn || [],
      estimatedHours: formData.estimatedHours,
      status: "BLOCKED",
    };

    AppState.updateState((state) => {
      resolveTaskStatuses(state.tasks.concat(task));
    });

    await FakeServer.saveTask(task);
  } catch (error) {
    AppState.addError(error.message);
  } finally {
    setLoading(false);
    render();
  }
}

// -----------------------------
// Task Assignment Flow
// -----------------------------
async function assignTaskToIntern(internId, taskId) {
  AppState.clearErrors();

  const state = AppState.getState();
  const intern = state.interns.find((i) => i.id === internId);
  const task = state.tasks.find((t) => t.id === taskId);

  const errors = validateTaskAssignment(intern, task, state.assignments);
  if (errors.length > 0) {
    errors.forEach((msg) => AppState.addError(msg));
    render();
    return;
  }

  if (!isEligibleForTask(intern, task)) {
    AppState.addError("Intern not eligible for this task");
    render();
    return;
  }

  try {
    setLoading(true);
    await FakeServer.assignTask(internId, taskId);
  } catch (error) {
    AppState.addError(error.message);
  } finally {
    setLoading(false);
    render();
  }
}

// -----------------------------
// Loading handler (central)
// -----------------------------
function setLoading(isLoading) {
  AppState.updateState((state) => {
    state.ui.loading = isLoading;
  });
}

// app.js (add below existing code)

document.addEventListener("submit", e => {
  if (e.target.id === "intern-form") {
    e.preventDefault();

    const name = document.getElementById("intern-name").value;
    const email = document.getElementById("intern-email").value;
    const skillsInput = document.getElementById("intern-skills").value;

    const skills = skillsInput
      .split(",")
      .map(s => s.trim())
      .filter(Boolean);

    createIntern({ name, email, skills });
  }
});
// app.js (add below existing code)

document.addEventListener("click", e => {
  const action = e.target.dataset.action;
  const internId = e.target.dataset.id;

  if (!action || !internId) return;

  if (action === "ACTIVATE") {
    changeInternStatus(internId, "ACTIVE");
  }

  if (action === "EXIT") {
    changeInternStatus(internId, "EXITED");
  }
});
// app.js (add below existing code)

function changeInternStatus(internId, nextStatus) {
  AppState.clearErrors();

  const state = AppState.getState();
  const intern = state.interns.find(i => i.id === internId);

  if (!intern) {
    AppState.addError("Intern not found");
    render();
    return;
  }

  const allowed = canChangeStatus(intern.status, nextStatus);

  if (!allowed) {
    AppState.addError(
      `Invalid status transition: ${intern.status} → ${nextStatus}`
    );
    render();
    return;
  }

  AppState.updateState(state => {
    const target = state.interns.find(i => i.id === internId);
    target.status = nextStatus;
  });

  AppState.addLog(`Intern ${internId} status changed to ${nextStatus}`);
  render();
}

// app.js

document.addEventListener("change", e => {
  if (e.target.id === "status-filter") {
    AppState.updateState(state => {
      state.ui.filters.status = e.target.value;
    });
    render();
  }

  if (e.target.id === "skill-filter") {
    AppState.updateState(state => {
      state.ui.filters.skill = e.target.value;
    });
    render();
  }
});
// app.js

document.addEventListener("submit", e => {
  if (e.target.id === "task-form") {
    e.preventDefault();

    const title = document.getElementById("task-title").value;
    const skillsInput = document.getElementById("task-skills").value;
    const hours = Number(document.getElementById("task-hours").value);

    const depsSelect = document.getElementById("task-deps");
    const dependsOn = [...depsSelect.selectedOptions].map(o => o.value);

    const requiredSkills = skillsInput
      .split(",")
      .map(s => s.trim())
      .filter(Boolean);

    createTask({
      title,
      requiredSkills,
      estimatedHours: hours,
      dependsOn
    });
  }
});
// app.js

async function createTask(formData) {
  AppState.clearErrors();

  const errors = validateTaskForm(formData);
  if (errors.length > 0) {
    errors.forEach(e => AppState.addError(e));
    render();
    return;
  }

  const state = AppState.getState();

  // ---- circular dependency check ----
  for (let dep of formData.dependsOn) {
    if (hasCircularDependency("TEMP", dep, state.tasks)) {
      AppState.addError("Circular dependency detected");
      render();
      return;
    }
  }

  try {
    setLoading(true);

    const task = {
      id: `TASK-${Date.now()}`,
      title: formData.title,
      requiredSkills: formData.requiredSkills,
      estimatedHours: formData.estimatedHours,
      dependsOn: formData.dependsOn,
      status: "BLOCKED"
    };

    await FakeServer.saveTask(task);

    // ---- auto status resolution ----
    AppState.updateState(state => {
      resolveTaskStatuses(state.tasks);
      state.ui.currentView = "TASK_LIST";
    });

  } catch (err) {
    AppState.addError(err.message);
  } finally {
    setLoading(false);
    render();
  }
}
// app.js

function markTaskAsDone(taskId) {
  AppState.clearErrors();

  const state = AppState.getState();
  const task = state.tasks.find(t => t.id === taskId);

  if (!task) {
    AppState.addError("Task not found");
    render();
    return;
  }

  // ---- dependency enforcement ----
  if (!canMarkTaskDone(task, state.tasks)) {
    AppState.addError("Cannot complete task. Dependencies not completed.");
    render();
    return;
  }

  AppState.updateState(state => {
    const target = state.tasks.find(t => t.id === taskId);
    target.status = "DONE";

    // ---- auto status update for all tasks ----
    resolveTaskStatuses(state.tasks);
  });

  AppState.addLog(`Task ${taskId} marked as DONE`);
  render();
}

// app.js

document.addEventListener("click", e => {
  if (e.target.dataset.taskDone) {
    markTaskAsDone(e.target.dataset.taskDone);
  }
});
// app.js

document.addEventListener("change", e => {
  const taskId = e.target.dataset.assignTask;
  const internId = e.target.value;

  if (!taskId || !internId) return;

  assignTaskToIntern(internId, taskId);
});

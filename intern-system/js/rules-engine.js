// rules-engine.js
// All business rules only (NO DOM, NO async)

// -----------------------------
// Intern ID generation
// -----------------------------
function generateInternId(interns) {
  const year = new Date().getFullYear();
  const sameYearInterns = interns.filter(i => i.id.includes(year));
  const seq = String(sameYearInterns.length + 1).padStart(3, "0");
  return `INT-${year}-${seq}`;
}

// -----------------------------
// Intern lifecycle validation
// -----------------------------
function canChangeStatus(currentStatus, nextStatus) {
  if (currentStatus === "ONBOARDING" && nextStatus === "ACTIVE") return true;
  if (currentStatus === "ACTIVE" && nextStatus === "EXITED") return true;
  return false; // EXITED → ACTIVE or any invalid transition
}

// -----------------------------
// Skill-based task eligibility
// -----------------------------
function isEligibleForTask(intern, task) {
  if (intern.status !== "ACTIVE") return false;

  return task.requiredSkills.every(skill =>
    intern.skills.includes(skill)
  );
}

// -----------------------------
// Task dependency completion check
// -----------------------------
function areDependenciesCompleted(task, allTasks) {
  return task.dependsOn.every(depId => {
    const depTask = allTasks.find(t => t.id === depId);
    return depTask && depTask.status === "DONE";
  });
}

// -----------------------------
// Circular dependency detection
// -----------------------------
function hasCircularDependency(taskId, dependencyId, tasks, visited = new Set()) {
  if (taskId === dependencyId) return true;

  if (visited.has(dependencyId)) return false;
  visited.add(dependencyId);

  const depTask = tasks.find(t => t.id === dependencyId);
  if (!depTask) return false;

  return depTask.dependsOn.some(dep =>
    hasCircularDependency(taskId, dep, tasks, visited)
  );
}

// -----------------------------
// Auto task status resolution
// -----------------------------
function resolveTaskStatuses(tasks) {
  tasks.forEach(task => {
    if (task.dependsOn.length === 0) {
      if (task.status !== "DONE") task.status = "AVAILABLE";
    } else {
      task.status = areDependenciesCompleted(task, tasks)
        ? "AVAILABLE"
        : "BLOCKED";
    }
  });
}

// -----------------------------
// Dynamic total hours calculation
// -----------------------------
function calculateTotalHours(internId, tasks, assignments) {
  const assignedTaskIds = assignments
    .filter(a => a.internId === internId)
    .map(a => a.taskId);

  return tasks
    .filter(t => assignedTaskIds.includes(t.id))
    .reduce((total, task) => total + task.estimatedHours, 0);
}
// rules-engine.js

function canMarkTaskDone(task, allTasks) {
  return areDependenciesCompleted(task, allTasks);
}

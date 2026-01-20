// validators.js
// Validation only — NO DOM, NO state mutation

// -----------------------------
// Helper validators
// -----------------------------
function isEmpty(value) {
  return !value || value.trim() === "";
}

function isValidEmail(email) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
}

// -----------------------------
// Intern form validation
// -----------------------------
function validateInternForm(data, existingInterns) {
  const errors = [];

  if (isEmpty(data.name)) {
    errors.push("Name is required");
  }

  if (isEmpty(data.email)) {
    errors.push("Email is required");
  } else if (!isValidEmail(data.email)) {
    errors.push("Invalid email format");
  }

  if (!Array.isArray(data.skills) || data.skills.length === 0) {
    errors.push("At least one skill is required");
  }

  const duplicate = existingInterns.some(
    intern => intern.email.toLowerCase() === data.email.toLowerCase()
  );
  if (duplicate) {
    errors.push("Email already exists");
  }

  return errors;
}

// -----------------------------
// Task form validation
// -----------------------------
function validateTaskForm(data) {
  const errors = [];

  if (isEmpty(data.title)) {
    errors.push("Task title is required");
  }

  if (!Array.isArray(data.requiredSkills) || data.requiredSkills.length === 0) {
    errors.push("At least one required skill is needed");
  }

  if (typeof data.estimatedHours !== "number" || data.estimatedHours <= 0) {
    errors.push("Estimated hours must be a positive number");
  }

  return errors;
}

// -----------------------------
// Task assignment validation
// -----------------------------
function validateTaskAssignment(intern, task, assignments) {
  const errors = [];

  if (intern.status !== "ACTIVE") {
    errors.push("Only ACTIVE interns can receive tasks");
  }

  const alreadyAssigned = assignments.some(
    a => a.internId === intern.id && a.taskId === task.id
  );
  if (alreadyAssigned) {
    errors.push("Task already assigned to this intern");
  }

  return errors;
}

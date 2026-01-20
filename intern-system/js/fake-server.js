// fake-server.js
// Simulates backend APIs using Promise + setTimeout

const FakeServer = (() => {
  const DELAY = 600; // simulate network latency

  // Generic async wrapper
  function asyncWrapper(actionFn) {
    return new Promise((resolve, reject) => {
      setTimeout(() => {
        try {
          const result = actionFn();
          resolve(result);
        } catch (error) {
          reject(error);
        }
      }, DELAY);
    });
  }

  // -----------------------------
  // Simulated backend operations
  // -----------------------------

  // 1. Async email uniqueness check
  function checkEmailUnique(email) {
    return asyncWrapper(() => {
      const { interns } = AppState.getState();
      const exists = interns.some(
        intern => intern.email.toLowerCase() === email.toLowerCase()
      );
      if (exists) {
        throw new Error("Email already exists");
      }
      return true;
    });
  }

  // 2. Save intern
  function saveIntern(intern) {
    return asyncWrapper(() => {
      AppState.updateState(state => {
        state.interns.push(intern);
      });
      AppState.addLog(`Intern created: ${intern.id}`);
      return intern;
    });
  }

  // 3. Save task
  function saveTask(task) {
    return asyncWrapper(() => {
      AppState.updateState(state => {
        state.tasks.push(task);
      });
      AppState.addLog(`Task created: ${task.id}`);
      return task;
    });
  }

  // 4. Assign task to intern
  function assignTask(internId, taskId) {
    return asyncWrapper(() => {
      AppState.updateState(state => {
        const alreadyAssigned = state.assignments.some(
          a => a.internId === internId && a.taskId === taskId
        );

        if (alreadyAssigned) {
          throw new Error("Task already assigned to this intern");
        }

        state.assignments.push({
          internId,
          taskId,
          assignedAt: new Date().toISOString()
        });
      });

      AppState.addLog(`Task ${taskId} assigned to intern ${internId}`);
      return true;
    });
  }

  return {
    checkEmailUnique,
    saveIntern,
    saveTask,
    assignTask
  };
})();

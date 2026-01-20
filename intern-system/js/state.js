// state.js
// Single Source of Truth for the entire application

const AppState = (() => {
  // 🔒 Private internal state
  const _state = {
    interns: [], // all interns
    tasks: [], // all tasks
    assignments: [], // intern-task mapping

    ui: {
      currentView: "INTERN_LIST", // navigation state
      loading: false,
      filters: {
        status: "ALL",
        skill: "ALL",
      },
    },

    errors: [], // centralized error store
    logs: [], // optional audit logs
  };

  // 🔒 Prevent direct mutation
  function getState() {
    return JSON.parse(JSON.stringify(_state));
  }

  // ✅ Controlled state update
  function updateState(updaterFn) {
    if (typeof updaterFn !== "function") {
      throw new Error("State update must be a function");
    }
    updaterFn(_state);
  }

  // ✅ Error handling
  function addError(message) {
    updateState((state) => {
      state.errors.push({
        message,
        timestamp: new Date().toISOString(),
      });
    });
  }

  function clearErrors() {
    updateState((state) => {
      state.errors = [];
    });
  }

  // ✅ Logging (optional)
  function addLog(action) {
    updateState((state) => {
      state.logs.push({
        action,
        timestamp: new Date().toISOString(),
      });
    });
  }

  return {
    getState,
    updateState,
    addError,
    clearErrors,
    addLog,
  };
})();

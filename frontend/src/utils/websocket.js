let pollingInterval = null;
let isPolling = false;
let projectPollingInterval = null;
let isProjectPolling = false;

function startPollingSalespersonUpdates(callback, store) {
  if (!store?.state?.game?.id) {
    console.error('No game ID available for polling');
    return;
  }

  const gameId = store.state.game.id;

  // Clear any existing interval
  if (pollingInterval) {
    clearInterval(pollingInterval);
  }

  isPolling = true;

  // Start polling every 2 seconds
  pollingInterval = setInterval(async () => {
    if (!isPolling) return;

    try {
      const response = await fetch(`/api/salespeople?game_id=${gameId}`);
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      const salespeople = await response.json();
      
      // Find the salesperson that has been updated
      const updatedSalesperson = salespeople.find(sp => {
        const existingSalesperson = store.state.salespeople.find(esp => esp.id === sp.id);
        return existingSalesperson && (
          existingSalesperson.progress !== sp.progress ||
          existingSalesperson.busy !== sp.busy
        );
      });

      if (updatedSalesperson) {
        callback(updatedSalesperson);
      }
    } catch (error) {
      console.error('Error polling salesperson updates:', error);
      // Don't stop polling on error, just log it
    }
  }, 2000);
}

function startPollingProjectUpdates(callback, store) {
  if (!store?.state?.game?.id) {
    console.error('No game ID available for project polling');
    return;
  }

  const gameId = store.state.game.id;

  // Clear any existing interval
  if (projectPollingInterval) {
    clearInterval(projectPollingInterval);
  }

  isProjectPolling = true;

  // Start polling every 2 seconds
  projectPollingInterval = setInterval(async () => {
    if (!isProjectPolling) return;

    try {
      const response = await fetch(`/api/projects?game_id=${gameId}`);
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      const projects = await response.json();
      
      // Find the project that has been updated
      const updatedProject = projects.find(p => {
        const existingProject = store.state.projects.find(ep => ep.id === p.id);
        return existingProject && (
          existingProject.progress !== p.progress ||
          existingProject.completed !== p.completed
        );
      });

      if (updatedProject) {
        callback(updatedProject);
      }
    } catch (error) {
      console.error('Error polling project updates:', error);
      // Don't stop polling on error, just log it
    }
  }, 2000);
}

function stopPollingSalespersonUpdates() {
  isPolling = false;
  if (pollingInterval) {
    clearInterval(pollingInterval);
    pollingInterval = null;
  }
}

function stopPollingProjectUpdates() {
  isProjectPolling = false;
  if (projectPollingInterval) {
    clearInterval(projectPollingInterval);
    projectPollingInterval = null;
  }
}

export { 
  startPollingSalespersonUpdates, 
  stopPollingSalespersonUpdates,
  startPollingProjectUpdates,
  stopPollingProjectUpdates
}; 
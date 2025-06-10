<template>
  <div class="max-w-6xl mx-auto p-4">
    <StatsBar :stats="[
      { label: 'Money', value: money },
      { label: 'Developers', value: developers.length },
      { label: 'Projects', value: pendingProjects.length }
    ]" />

    <div class="mb-8">
      <h2 class="mb-4 text-text border-b border-gray-200 pb-2">Available Developers</h2>
      <div v-if="availableDevelopers.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <Card v-for="developer in availableDevelopers" :key="developer.id">
          <h3 class="mb-2 text-text">{{ developer.name }}</h3>
          <p class="my-1 text-text-dark">Seniority: {{ developer.seniority }}</p>
          <p class="my-1">Status: <span class="text-success font-bold">Available</span></p>
        </Card>
      </div>
      <EmptyState v-else message="No available developers." />
    </div>

    <div class="mb-8">
      <h2 class="mb-4 text-text border-b border-gray-200 pb-2">Busy Developers</h2>
      <div v-if="busyDevelopers.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <Card v-for="developer in busyDevelopers" :key="developer.id">
          <h3 class="mb-2 text-text">{{ developer.name }}</h3>
          <p class="my-1 text-text-dark">Seniority: {{ developer.seniority }}</p>
          <p class="my-1">Status: <span class="text-danger font-bold">Working on project</span></p>
          <template v-if="developer.project">
            <p class="my-1 text-text-dark">Project: {{ developer.project.name }}</p>
            <p class="my-1 text-text-dark">Completion: {{ Math.round(developer.project.progress * 100) }}%</p>
            <ProgressBar :percentage="developer.project.progress * 100" />
          </template>
        </Card>
      </div>
      <EmptyState v-else message="No busy developers." />
    </div>

    <div class="mb-8">
      <h2 class="mb-4 text-text border-b border-gray-200 pb-2">Pending Projects</h2>
      <div v-if="pendingProjects.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <Card v-for="project in pendingProjects" :key="project.id">
          <h3 class="mb-2 text-text">{{ project.name }}</h3>
          <p class="my-1 text-text-dark">Complexity: {{ project.complexity }}</p>
          <p class="my-1 text-text-dark">Value: €{{ project.value.toLocaleString() }}</p>
          <template #actions>
            <div class="flex flex-col gap-2">
              <select v-model="selectedDevelopers[project.id]" class="p-2 border border-gray-300 rounded text-sm">
                <option value="">Assign Developer</option>
                <option v-for="dev in availableDevelopers" :key="dev.id" :value="dev.id">
                  {{ dev.name }} (Seniority: {{ dev.seniority }})
                </option>
              </select>
              <button 
                class="btn btn-primary" 
                :disabled="!selectedDevelopers[project.id]" 
                @click="assignDeveloper(project.id, selectedDevelopers[project.id])"
              >
                Assign
              </button>
            </div>
          </template>
        </Card>
      </div>
      <EmptyState v-else message="No pending projects." />
    </div>
  </div>
</template>

<script>
import Card from '@/components/Card.vue'
import EmptyState from '@/components/EmptyState.vue'
import ProgressBar from '@/components/ProgressBar.vue'
import StatsBar from '@/components/StatsBar.vue'
import { startPollingProjectUpdates, stopPollingProjectUpdates } from '@/utils/websocket'

export default {
  props: {
    id: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      selectedDevelopers: {},
      loading: false
    }
  },
  created() {
    // Check if there's a game in the store, if not redirect to home
    if (!this.$store.state.game) {
      this.$router.push('/')
      return
    }
    this.loadGameData()

    // Start polling for project updates
    this.startPollingForProjectUpdates()
  },
  beforeUnmount() {
    // Stop polling when component is destroyed
    stopPollingProjectUpdates()
  },
  computed: {
    money() {
      return this.$store.state.money
    },
    developers() {
      return this.$store.state.developers
    },
    availableDevelopers() {
      return this.developers.filter(dev => !dev.busy)
    },
    busyDevelopers() {
      return this.developers.filter(dev => dev.busy)
    },
    pendingProjects() {
      return this.$store.state.projects.filter(project => !project.assigned)
    }
  },
  methods: {
    async loadGameData() {
      try {
        this.loading = true
        await this.$store.dispatch('loadGame', this.id)
      } catch (error) {
        console.error('Error loading game data:', error)
        // Redirect to home if game can't be loaded
        this.$router.push('/')
      } finally {
        this.loading = false
      }
    },

    startPollingForProjectUpdates() {
      if (!this.$store.state.game?.id) {
        console.error('Cannot start polling: No game ID available')
        return
      }

      startPollingProjectUpdates((project) => {
        this.$store.dispatch('updateProjectFromWebSocket', project)
      }, this.$store)
    },

    async assignDeveloper(projectId, developerId) {
      try {
        const response = await fetch(`/api/projects/${projectId}/assign`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            developer_id: developerId
          })
        });

        if (!response.ok) {
          const errorData = await response.json();
          throw new Error(errorData.error || 'Failed to assign developer');
        }

        const data = await response.json();
        
        // Update the project in the store
        this.$store.commit('updateProject', data.project);
        
        // Update the developer in the store
        const developerIndex = this.developers.findIndex(d => d.id === developerId);
        if (developerIndex !== -1) {
          this.$store.state.developers.splice(developerIndex, 1, data.developer);
        }

        // Clear the selection
        this.selectedDevelopers[projectId] = '';
      } catch (error) {
        console.error('Error assigning developer:', error);
        alert(error.message);
      }
    }
  }
}
</script>

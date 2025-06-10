<template>
  <div class="max-w-6xl mx-auto p-4">
    <StatsBar :stats="[
      { label: 'Money', value: money },
      { label: 'Salespeople', value: salespeople.length },
      { label: 'Projects', value: projects.length }
    ]" />

    <div class="mb-8">
      <h2 class="mb-4 text-text border-b border-gray-200 pb-2">Available Salespeople</h2>
      <div v-if="availableSalespeople.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <Card v-for="salesperson in availableSalespeople" :key="salesperson.id">
          <h3 class="mb-2 text-text">{{ salesperson.name }}</h3>
          <p class="my-1 text-text-dark">Experience: {{ salesperson.experience }}</p>
          <p class="my-1">Status: <span class="text-success font-bold">Available</span></p>
          <template #actions>
            <button class="btn btn-primary" @click="generateProject(salesperson.id)">
              Generate Project
            </button>
          </template>
        </Card>
      </div>
      <EmptyState v-else message="No available salespeople." />
    </div>

    <div class="mb-8">
      <h2 class="mb-4 text-text border-b border-gray-200 pb-2">Busy Salespeople</h2>
      <div v-if="busySalespeople.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <Card v-for="salesperson in busySalespeople" :key="salesperson.id">
          <h3 class="mb-2 text-text">{{ salesperson.name }}</h3>
          <p class="my-1 text-text-dark">Experience: {{ salesperson.experience }}</p>
          <p class="my-1">Status: <span class="text-danger font-bold">Generating project</span></p>
          <p class="my-1 text-text-dark">Progress: {{ Math.round(salesperson.progress * 100) }}%</p>
          <ProgressBar :percentage="salesperson.progress * 100" />
        </Card>
      </div>
      <EmptyState v-else message="No busy salespeople." />
    </div>

    <div class="mb-8">
      <h2 class="mb-4 text-text border-b border-gray-200 pb-2">Generated Projects</h2>
      <div v-if="projects.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <Card v-for="project in projects" :key="project.id">
          <h3 class="mb-2 text-text">{{ project.name }}</h3>
          <p class="my-1 text-text-dark">Complexity: {{ project.complexity }}</p>
          <p class="my-1 text-text-dark">Value: €{{ project.value.toLocaleString() }}</p>
          <p class="my-1">Status: 
            <span :class="project.assigned ? 'text-danger font-bold' : 'text-success font-bold'">
              {{ project.assigned ? 'In Development' : 'Pending' }}
            </span>
          </p>
        </Card>
      </div>
      <EmptyState v-else message="No projects generated yet." />
    </div>
  </div>
</template>

<script>
import { apiPut, apiPost, apiGet } from '@/utils/api'
import { startPollingSalespersonUpdates, stopPollingSalespersonUpdates } from '../utils/websocket.js'
import Card from '@/components/Card.vue'
import EmptyState from '@/components/EmptyState.vue'
import ProgressBar from '@/components/ProgressBar.vue'
import StatsBar from '@/components/StatsBar.vue'

export default {
  props: {
    id: {
      type: String,
      required: true
    }
  },
  data() {
    return {
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

    // Start polling for salesperson updates
    this.startPollingForSalespersonUpdates()
  },

  beforeUnmount() {
    // Stop polling when component is destroyed
    stopPollingSalespersonUpdates()
  },
  computed: {
    money() {
      return this.$store.state.money
    },
    salespeople() {
      return this.$store.state.salespeople
    },
    availableSalespeople() {
      return this.salespeople.filter(sp => !sp.busy)
    },
    busySalespeople() {
      return this.salespeople.filter(sp => sp.busy)
    },
    projects() {
      return this.$store.state.projects
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
    startPollingForSalespersonUpdates() {
      if (!this.$store.state.game?.id) {
        console.error('Cannot start polling: No game ID available')
        return
      }

      startPollingSalespersonUpdates((salesperson) => {
        this.$store.dispatch('updateSalespersonFromWebSocket', salesperson)
      }, this.$store)
    },

    async generateProject(salespersonId) {
      try {
        const salesperson = this.salespeople.find(sp => sp.id === salespersonId)

        if (salesperson) {
          // Update salesperson status to busy
          await apiPut(`salespeople/${salespersonId}`, {
            busy: true,
            progress: 0
          });
        }
      } catch (error) {
        console.error('Error generating project:', error);
        alert(error.message || 'An error occurred while generating the project');
      }
    }
  }
}
</script>

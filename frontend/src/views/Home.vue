<template>
  <div class="max-w-3xl mx-auto p-8">
    <h2 class="mb-4 text-text">Welcome to Tech Company Management Game</h2>
    <p class="mb-8">Build your tech company from the ground up. Manage developers, salespeople, and projects to grow your business and avoid bankruptcy.</p>

    <div class="flex flex-col gap-8">
      <button class="btn btn-primary" @click="startNewGame" :disabled="loading">
        {{ loading ? 'Loading...' : 'Start New Game' }}
      </button>

      <div v-if="savedGames && savedGames.length > 0" class="border border-gray-300 rounded p-4">
        <h3 class="mb-4 text-text">Your Saved Games</h3>
        <ul class="list-none">
          <li v-for="game in savedGames" :key="game.id" class="flex justify-between items-center py-3 border-b border-gray-200 last:border-b-0">
            <div class="flex flex-col cursor-pointer" @click="loadGame(game.id)">
              <span class="font-bold">{{ game.name }}</span>
              <span class="text-sm text-text-light">{{ formatDate(game.updated_at) }}</span>
            </div>
            <div class="flex gap-2">
              <button class="btn btn-secondary" @click="loadGame(game.id)" :disabled="loading">Load</button>
              <button class="btn btn-danger" @click="deleteGame(game.id, $event)" :disabled="loading">Delete</button>
            </div>
          </li>
        </ul>
      </div>
      <div v-else-if="loading" class="text-center text-text-light p-4 bg-light rounded">
        <p>Loading games...</p>
      </div>
      <div v-else class="text-center text-text-light p-4 bg-light rounded">
        <p>No saved games found.</p>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      savedGames: [],
      loading: false
    }
  },
  mounted() {
    this.fetchSavedGames()
  },
  methods: {
    async fetchSavedGames() {
      try {
        this.loading = true
        const games = await this.$store.dispatch('fetchSessionGames')
        this.savedGames = games || []
      } catch (error) {
        console.error('Error fetching saved games:', error)
        this.savedGames = []
      } finally {
        this.loading = false
      }
    },
    formatDate(dateString) {
      if (!dateString) return ''
      const date = new Date(dateString)
      return date.toLocaleDateString() + ' ' + date.toLocaleTimeString()
    },
    async startNewGame() {
      try {
        this.loading = true
        const game = await this.$store.dispatch('startNewGame')
        this.$router.push(`/game/${game.id}/production`)
      } catch (error) {
        console.error('Error starting new game:', error)
      } finally {
        this.loading = false
      }
    },
    async loadGame(gameId) {
      try {
        this.loading = true
        await this.$store.dispatch('loadGame', gameId)
        this.$router.push(`/game/${gameId}/production`)
      } catch (error) {
        console.error('Error loading game:', error)
      } finally {
        this.loading = false
      }
    },
    async deleteGame(gameId, event) {
      event.stopPropagation() // Prevent triggering loadGame

      if (!confirm('Are you sure you want to delete this game?')) return

      try {
        this.loading = true
        await this.$store.dispatch('deleteGame', gameId)
        await this.fetchSavedGames() // Refresh the list
      } catch (error) {
        console.error('Error deleting game:', error)
      } finally {
        this.loading = false
      }
    }
  }
}
</script>

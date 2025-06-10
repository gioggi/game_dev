import { createApp } from 'vue'
import { createStore } from 'vuex'
import { createRouter, createWebHistory } from 'vue-router'
import App from './App.vue'
import Home from './views/Home.vue'
import Production from './views/Production.vue'
import Sales from './views/Sales.vue'
import HR from './views/HR.vue'
import { apiGet, apiPost, apiPut, apiDelete } from './utils/api'
import './assets/css/main.css'

// Import components
import StatsBar from './components/StatsBar.vue'
import ProgressBar from './components/ProgressBar.vue'
import Card from './components/Card.vue'
import EmptyState from './components/EmptyState.vue'

// Create Vue app
const app = createApp(App)

// Register components globally
app.component('StatsBar', StatsBar)
app.component('ProgressBar', ProgressBar)
app.component('Card', Card)
app.component('EmptyState', EmptyState)

// Create router
const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', component: Home },
    { path: '/game/:id', redirect: to => `/game/${to.params.id}/production` },
    { path: '/game/:id/production', component: Production, props: true },
    { path: '/game/:id/sales', component: Sales, props: true },
    { path: '/game/:id/hr', component: HR, props: true },
    { 
      path: '/production', 
      redirect: to => {
        const app = router.app;
        if (app && app.$store && app.$store.state.game) {
          return `/game/${app.$store.state.game.id}/production`;
        }
        return '/';
      }
    },
    { 
      path: '/sales', 
      redirect: to => {
        const app = router.app;
        if (app && app.$store && app.$store.state.game) {
          return `/game/${app.$store.state.game.id}/sales`;
        }
        return '/';
      }
    },
    { 
      path: '/hr', 
      redirect: to => {
        const app = router.app;
        if (app && app.$store && app.$store.state.game) {
          return `/game/${app.$store.state.game.id}/hr`;
        }
        return '/';
      }
    },
    { path: '/:pathMatch(.*)*', redirect: '/' }
  ]
})

// Create store
const store = createStore({
  state() {
    return {
      game: null,
      developers: [],
      salespeople: [],
      projects: [],
      money: 5000,
      sessionId: localStorage.getItem('sessionId') || null
    }
  },
  mutations: {
    setGame(state, game) {
      state.game = game
    },
    setDevelopers(state, developers) {
      state.developers = developers
    },
    setSalespeople(state, salespeople) {
      state.salespeople = salespeople
    },
    setProjects(state, projects) {
      state.projects = projects
    },
    setMoney(state, money) {
      state.money = money
    },
    setSessionId(state, sessionId) {
      state.sessionId = sessionId
      localStorage.setItem('sessionId', sessionId)
    },
    updateProject(state, updatedProject) {
      const index = state.projects.findIndex(p => p.id === updatedProject.id)
      if (index !== -1) {
        // Replace the project with the updated one
        state.projects.splice(index, 1, updatedProject)
      }
    },
    updateSalesperson(state, updatedSalesperson) {
      const index = state.salespeople.findIndex(s => s.id === updatedSalesperson.id)
      if (index !== -1) {
        // Replace the salesperson with the updated one
        state.salespeople.splice(index, 1, updatedSalesperson)
      }
    }
  },
  actions: {
    updateProjectFromWebSocket({ commit, state }, project) {
      // Only update if the project belongs to the current game
      if (state.game && project.game_id === state.game.id) {
        commit('updateProject', project)

        // Find the developer working on this project
        const developer = state.developers.find(d => d.project && d.project.id === project.id)
        if (developer) {
          // Update the developer's project reference
          developer.project = { ...project }
          
          // If the project is completed, update the developer's status
          if (project.completed) {
            developer.busy = false
            developer.project = null
          }
        }
      }
    },
    updateSalespersonFromWebSocket({ commit, state }, salesperson) {
      // Only update if the salesperson belongs to the current game
      if (state.game && salesperson.game_id === state.game.id) {
        commit('updateSalesperson', salesperson)
      }
    },
    async loadGame({ commit }, gameId) {
      try {
        const game = await apiGet(`games/${gameId}`)
        commit('setGame', game)
        commit('setDevelopers', game.developers || [])
        commit('setSalespeople', game.salespeople || [])
        commit('setProjects', game.projects || [])
        commit('setMoney', game.money)

        return game
      } catch (error) {
        console.error('Error loading game:', error)
        throw error
      }
    },
    async startNewGame({ commit, state }) {
      try {
        // Generate a session ID if one doesn't exist
        if (!state.sessionId) {
          const sessionId = 'session_' + Math.random().toString(36).substring(2, 15)
          commit('setSessionId', sessionId)
        }

        const game = await apiPost('games', {
          name: 'My Tech Company',
          session_id: state.sessionId
        })

        commit('setGame', game)
        commit('setDevelopers', game.developers || [])
        commit('setSalespeople', game.salespeople || [])
        commit('setProjects', game.projects || [])
        commit('setMoney', game.money)

        return game
      } catch (error) {
        console.error('Error starting new game:', error)
        throw error
      }
    },
    async fetchSessionGames({ state }) {
      try {
        if (!state.sessionId) {
          return []
        }
        const games = await apiGet(`games?session_id=${state.sessionId}`)
        return games
      } catch (error) {
        console.error('Error fetching session games:', error)
        return []
      }
    },
    async deleteGame({ state }, gameId) {
      try {
        await apiDelete(`games/${gameId}`)
        // If the deleted game was the current game, clear the game state
        if (state.game && state.game.id === gameId) {
          commit('setGame', null)
          commit('setDevelopers', [])
          commit('setSalespeople', [])
          commit('setProjects', [])
          commit('setMoney', 5000)
        }
      } catch (error) {
        console.error('Error deleting game:', error)
        throw error
      }
    }
  }
})

// Use router and store
app.use(router)
app.use(store)
app.mount('#app')

// Make app available globally for polling
window.app = app

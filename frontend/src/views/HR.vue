<template>
  <div class="max-w-6xl mx-auto p-4">
    <StatsBar :stats="[
      { label: 'Money', value: money },
      { label: 'Developers', value: developers.length },
      { label: 'Salespeople', value: salespeople.length }
    ]" />

    <div class="mb-8">
      <h2 class="mb-4 text-text border-b border-gray-200 pb-2">Developers for Hire</h2>
      <div v-if="availableDevelopersForHire.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <Card v-for="developer in availableDevelopersForHire" :key="developer.id">
          <h3 class="mb-2 text-text">{{ developer.name }}</h3>
          <p class="my-1 text-text-dark">Seniority: {{ developer.seniority }}</p>
          <p class="my-1 text-text-dark">Hiring Cost: €{{ developer.cost.toLocaleString() }}</p>
          <template #actions>
            <button 
              class="btn btn-primary" 
              :disabled="money < developer.cost"
              @click="hireDeveloper(developer.id)"
            >
              Hire (€{{ developer.cost.toLocaleString() }})
            </button>
          </template>
        </Card>
      </div>
      <EmptyState v-else message="No developers available for hire." />
    </div>

    <div class="mb-8">
      <h2 class="mb-4 text-text border-b border-gray-200 pb-2">Salespeople for Hire</h2>
      <div v-if="availableSalespeopleForHire.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <Card v-for="salesperson in availableSalespeopleForHire" :key="salesperson.id">
          <h3 class="mb-2 text-text">{{ salesperson.name }}</h3>
          <p class="my-1 text-text-dark">Experience: {{ salesperson.experience }}</p>
          <p class="my-1 text-text-dark">Hiring Cost: €{{ salesperson.cost.toLocaleString() }}</p>
          <template #actions>
            <button 
              class="btn btn-primary" 
              :disabled="money < salesperson.cost"
              @click="hireSalesperson(salesperson.id)"
            >
              Hire (€{{ salesperson.cost.toLocaleString() }})
            </button>
          </template>
        </Card>
      </div>
      <EmptyState v-else message="No salespeople available for hire." />
    </div>

    <div class="mb-8">
      <h2 class="mb-4 text-text border-b border-gray-200 pb-2">Your Team</h2>
      <div class="bg-light p-4 rounded grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="flex flex-col items-center">
          <span class="text-sm text-text-light">Total Developers:</span>
          <span class="text-lg font-bold text-text">{{ developers.length }}</span>
        </div>
        <div class="flex flex-col items-center">
          <span class="text-sm text-text-light">Total Salespeople:</span>
          <span class="text-lg font-bold text-text">{{ salespeople.length }}</span>
        </div>
        <div class="flex flex-col items-center">
          <span class="text-sm text-text-light">Monthly Costs:</span>
          <span class="text-lg font-bold text-text">€{{ monthlyCosts.toLocaleString() }}</span>
        </div>
      </div>
    </div>

    <div class="mb-8">
      <h2 class="mb-4 text-text border-b border-gray-200 pb-2">Your Salespeople</h2>
      <div v-if="salespeople.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <Card v-for="salesperson in salespeople" :key="salesperson.id">
          <h3 class="mb-2 text-text">{{ salesperson.name }}</h3>
          <p class="my-1 text-text-dark">Experience: {{ salesperson.experience }}</p>
          <p class="my-1">Status: 
            <span :class="salesperson.busy ? 'text-danger font-bold' : 'text-success font-bold'">
              {{ salesperson.busy ? 'Generating project' : 'Available' }}
            </span>
          </p>
          <template v-if="salesperson.busy">
            <p class="my-1 text-text-dark">Progress: {{ Math.round(salesperson.progress * 100) }}%</p>
            <ProgressBar :percentage="salesperson.progress * 100" />
          </template>
        </Card>
      </div>
      <EmptyState v-else message="No salespeople hired yet." />
    </div>
  </div>
</template>

<script>
import { startPollingSalespersonUpdates, stopPollingSalespersonUpdates } from '../utils/websocket.js';

export default {
  props: {
    id: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      loading: false,
      availableDevelopersForHire: [
        {
          id: 'dev1',
          name: 'John Developer',
          seniority: 3,
          cost: 3000
        },
        {
          id: 'dev2',
          name: 'Jane Coder',
          seniority: 5,
          cost: 5000
        },
        {
          id: 'dev3',
          name: 'Bob Programmer',
          seniority: 1,
          cost: 1500
        }
      ],
      availableSalespeopleForHire: [
        {
          id: 'sales1',
          name: 'Alice Sales',
          experience: 3,
          cost: 2500
        },
        {
          id: 'sales2',
          name: 'Charlie Marketing',
          experience: 4,
          cost: 3500
        },
        {
          id: 'sales3',
          name: 'David Business',
          experience: 2,
          cost: 2000
        }
      ]
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
    developers() {
      return this.$store.state.developers
    },
    salespeople() {
      return this.$store.state.salespeople
    },
    monthlyCosts() {
      // Calculate monthly costs based on team size
      const developerCost = this.developers.length * 1000
      const salespeopleCost = this.salespeople.length * 800
      return developerCost + salespeopleCost
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
    async hireDeveloper(developerId) {
      try {
        const developerToHire = this.availableDevelopersForHire.find(d => d.id === developerId);
        if (!developerToHire) {
          throw new Error('Developer not found');
        }

        const response = await fetch('/api/developers', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            game_id: this.id,
            name: developerToHire.name,
            seniority: developerToHire.seniority,
            busy: false,
            cost: developerToHire.cost
          })
        });

        if (!response.ok) {
          const errorData = await response.json();
          throw new Error(errorData.error || 'Failed to hire developer');
        }

        const data = await response.json();
        this.$store.state.developers.push(data.developer);
        this.$store.commit('setMoney', data.remaining_money);

        // Remove the hired developer from available list
        this.availableDevelopersForHire = this.availableDevelopersForHire.filter(d => d.id !== developerId);
      } catch (error) {
        console.error('Error hiring developer:', error);
        alert(error.message);
      }
    },
    async hireSalesperson(salespersonId) {
      try {
        const salespersonToHire = this.availableSalespeopleForHire.find(s => s.id === salespersonId);
        if (!salespersonToHire) {
          throw new Error('Salesperson not found');
        }

        const response = await fetch('/api/salespeople', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            game_id: this.id,
            name: salespersonToHire.name,
            experience: salespersonToHire.experience,
            busy: false,
            progress: 0,
            cost: salespersonToHire.cost
          })
        });

        if (!response.ok) {
          const errorData = await response.json();
          throw new Error(errorData.error || 'Failed to hire salesperson');
        }

        const data = await response.json();
        this.$store.state.salespeople.push(data.salesperson);
        this.$store.commit('setMoney', data.remaining_money);

        // Remove the hired salesperson from available list
        this.availableSalespeopleForHire = this.availableSalespeopleForHire.filter(s => s.id !== salespersonId);
      } catch (error) {
        console.error('Error hiring salesperson:', error);
        alert(error.message);
      }
    },
    generateNewDeveloperForHire() {
      const id = 'dev' + Date.now()
      const seniority = Math.floor(Math.random() * 5) + 1
      const cost = seniority * 1000 + Math.floor(Math.random() * 500)

      const names = ['Alex', 'Sam', 'Taylor', 'Jordan', 'Casey', 'Riley', 'Morgan', 'Avery']
      const surnames = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Miller', 'Davis', 'Garcia']

      const name = names[Math.floor(Math.random() * names.length)] + ' ' + 
                  surnames[Math.floor(Math.random() * surnames.length)]

      this.availableDevelopersForHire.push({
        id,
        name,
        seniority,
        cost
      })
    },
    generateNewSalespersonForHire() {
      const id = 'sales' + Date.now()
      const experience = Math.floor(Math.random() * 5) + 1
      const cost = experience * 800 + Math.floor(Math.random() * 400)

      const names = ['Alex', 'Sam', 'Taylor', 'Jordan', 'Casey', 'Riley', 'Morgan', 'Avery']
      const surnames = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Miller', 'Davis', 'Garcia']

      const name = names[Math.floor(Math.random() * names.length)] + ' ' + 
                  surnames[Math.floor(Math.random() * surnames.length)]

      this.availableSalespeopleForHire.push({
        id,
        name,
        experience,
        cost
      })
    },
    startPollingForSalespersonUpdates() {
      if (!this.$store.state.game?.id) {
        console.error('Cannot start polling: No game ID available')
        return
      }

      startPollingSalespersonUpdates((salesperson) => {
        this.$store.dispatch('updateSalespersonFromWebSocket', salesperson)
      })
    }
  }
}
</script>

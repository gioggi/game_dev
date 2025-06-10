<template>
  <div class="flex flex-col min-h-screen">
    <header class="flex justify-between items-center p-4 bg-dark text-white">
      <h1>Tech Company Management Game</h1>
      <div class="flex flex-col justify-between w-[30px] h-5 cursor-pointer" @click="toggleSidebar">
        <span class="h-[3px] w-full bg-white"></span>
        <span class="h-[3px] w-full bg-white"></span>
        <span class="h-[3px] w-full bg-white"></span>
      </div>
    </header>

    <div class="flex flex-1 relative">
      <aside class="absolute top-0 w-[250px] h-full bg-dark-light transition-all duration-300 ease-in-out z-10" 
             :class="{ 'left-0': sidebarOpen, '-left-[250px]': !sidebarOpen }">
        <nav>
          <ul class="list-none p-4">
            <li class="mb-4"><router-link to="/" @click="closeSidebar" class="text-white no-underline text-lg">Home</router-link></li>
            <li class="mb-4"><router-link :to="gameId ? `/game/${gameId}/production` : '/production'" @click="closeSidebar" class="text-white no-underline text-lg">Production</router-link></li>
            <li class="mb-4"><router-link :to="gameId ? `/game/${gameId}/sales` : '/sales'" @click="closeSidebar" class="text-white no-underline text-lg">Sales</router-link></li>
            <li class="mb-4"><router-link :to="gameId ? `/game/${gameId}/hr` : '/hr'" @click="closeSidebar" class="text-white no-underline text-lg">HR</router-link></li>
          </ul>
        </nav>
      </aside>

      <main class="flex-1 p-4">
        <router-view></router-view>
      </main>
    </div>

    <footer class="bg-dark py-2">
      <nav class="flex justify-around">
        <router-link :to="gameId ? `/game/${gameId}/production` : '/production'" class="flex flex-col items-center text-white no-underline">
          <span class="text-2xl">🏗️</span>
          <span>Production</span>
        </router-link>
        <router-link :to="gameId ? `/game/${gameId}/sales` : '/sales'" class="flex flex-col items-center text-white no-underline">
          <span class="text-2xl">💼</span>
          <span>Sales</span>
        </router-link>
        <router-link :to="gameId ? `/game/${gameId}/hr` : '/hr'" class="flex flex-col items-center text-white no-underline">
          <span class="text-2xl">🧑‍💼</span>
          <span>HR</span>
        </router-link>
      </nav>
    </footer>
  </div>
</template>

<script>
export default {
  data() {
    return {
      sidebarOpen: false
    }
  },
  computed: {
    gameId() {
      return this.$store.state.game ? this.$store.state.game.id : null
    }
  },
  methods: {
    toggleSidebar() {
      this.sidebarOpen = !this.sidebarOpen
    },
    closeSidebar() {
      this.sidebarOpen = false
    }
  }
}
</script>

<style>
/* All styles now handled by Tailwind CSS */
</style>

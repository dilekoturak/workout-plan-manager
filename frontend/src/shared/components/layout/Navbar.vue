<script setup lang="ts">
import { RouterLink, useRoute } from 'vue-router'
import { Dumbbell, Users } from 'lucide-vue-next'

const route = useRoute()

const links = [
  { to: '/users', label: 'Users', icon: Users },
  { to: '/workout-plans', label: 'Workout Plans', icon: Dumbbell },
]
</script>

<template>
  <header class="border-b bg-background">
    <nav class="mx-auto flex h-16 max-w-7xl items-center justify-between px-6">
      <RouterLink to="/" class="flex items-center gap-2 font-semibold text-lg">
        <Dumbbell class="h-5 w-5" />
        <span>Workout Plan Manager</span>
      </RouterLink>

      <ul class="flex items-center gap-1">
        <li v-for="link in links" :key="link.to">
          <RouterLink
            :to="link.to"
            class="flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground"
            :class="route.path.startsWith(link.to)
              ? 'bg-accent text-accent-foreground'
              : 'text-muted-foreground'"
          >
            <component :is="link.icon" class="h-4 w-4" />
            {{ link.label }}
          </RouterLink>
        </li>
      </ul>
    </nav>
  </header>
</template>

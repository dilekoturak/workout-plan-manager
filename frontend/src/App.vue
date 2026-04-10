<script setup lang="ts">
import { RouterView } from 'vue-router'
import AppLayout from '@/shared/components/layout/AppLayout.vue'
import { Toaster } from '@/components/ui/sonner'
</script>

<template>
  <AppLayout>
    <RouterView />
  </AppLayout>
  <Toaster />
</template>

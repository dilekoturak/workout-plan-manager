<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query'
import { toast } from 'vue-sonner'
import { ArrowLeft, Pencil, UserPlus, UserMinus, Dumbbell, CalendarDays } from 'lucide-vue-next'

import { workoutPlanApi } from './workoutPlanApi'
import { userApi } from '@/users/userApi'
import PlanSheet from './PlanSheet.vue'

import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Separator } from '@/components/ui/separator'
import {
  Accordion,
  AccordionContent,
  AccordionItem,
  AccordionTrigger,
} from '@/components/ui/accordion'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'

const route = useRoute()
const router = useRouter()
const queryClient = useQueryClient()

const planId = computed(() => route.params.id as string)

// ── Sheet state ────────────────────────────────────────────
const sheetOpen = ref(false)

// ── Queries ────────────────────────────────────────────────
const { data: plan, isLoading: planLoading, isError: planError } = useQuery({
  queryKey: computed(() => ['workout-plans', planId.value]),
  queryFn: () => workoutPlanApi.getOne(planId.value),
})

const { data: assignedUsers, isLoading: usersLoading } = useQuery({
  queryKey: computed(() => ['workout-plans', planId.value, 'users']),
  queryFn: () => workoutPlanApi.getAssignedUsers(planId.value),
})

const { data: allUsers } = useQuery({
  queryKey: ['users'],
  queryFn: userApi.getAll,
})

// Users not yet assigned to this plan
const availableUsers = computed(() => {
  const assigned = new Set((assignedUsers.value ?? []).map(u => u.userId))
  return (allUsers.value ?? []).filter(u => !assigned.has(u.id))
})

// ── Assign / Unassign ──────────────────────────────────────
const selectedUserId = ref<string>('')

const { mutate: assignUser, isPending: isAssigning } = useMutation({
  mutationFn: (userId: string) => workoutPlanApi.assignUser(planId.value, userId),
  onSuccess: () => {
    queryClient.invalidateQueries({ queryKey: ['workout-plans', planId.value, 'users'] })
    toast.success('User assigned.')
    selectedUserId.value = ''
  },
  onError: () => toast.error('Failed to assign user.'),
})

const { mutate: unassignUser, isPending: isUnassigning } = useMutation({
  mutationFn: (userId: string) => workoutPlanApi.unassignUser(planId.value, userId),
  onSuccess: () => {
    queryClient.invalidateQueries({ queryKey: ['workout-plans', planId.value, 'users'] })
    toast.success('User removed.')
  },
  onError: () => toast.error('Failed to remove user.'),
})

function handleAssign() {
  if (selectedUserId.value) assignUser(selectedUserId.value)
}

// ── Total exercises helper ────────────────────────────────
function totalExercises() {
  return plan.value?.workoutDays.reduce((s, d) => s + d.exercises.length, 0) ?? 0
}
</script>

<template>
  <div v-if="planLoading" class="text-muted-foreground text-sm">Loading...</div>
  <div v-else-if="planError" class="text-destructive text-sm">Failed to load plan.</div>

  <div v-else-if="plan" class="flex flex-col gap-6">

    <!-- Header -->
    <div class="flex items-center gap-4">
      <Button variant="ghost" size="icon" @click="router.back()">
        <ArrowLeft class="h-4 w-4" />
      </Button>
      <div class="flex-1">
        <h1 class="text-2xl font-bold">{{ plan.name }}</h1>
        <p class="text-sm text-muted-foreground">
          Created {{ new Date(plan.createdAt).toLocaleDateString() }}
        </p>
      </div>
      <div class="flex items-center gap-2">
        <Badge variant="secondary" class="flex items-center gap-1">
          <CalendarDays class="h-3 w-3" />
          {{ plan.workoutDays.length }} {{ plan.workoutDays.length === 1 ? 'day' : 'days' }}
        </Badge>
        <Badge variant="secondary" class="flex items-center gap-1">
          <Dumbbell class="h-3 w-3" />
          {{ totalExercises() }} exercises
        </Badge>
        <Button size="sm" @click="sheetOpen = true">
          <Pencil class="h-4 w-4 mr-1" />
          Edit Plan
        </Button>
      </div>
    </div>

    <Separator />

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

      <!-- Workout Days -->
      <div class="lg:col-span-2 flex flex-col gap-3">
        <h2 class="font-semibold text-base">Workout Days</h2>

        <Accordion type="multiple" class="flex flex-col gap-2">
          <AccordionItem
            v-for="(day, i) in plan.workoutDays"
            :key="day.id ?? i"
            :value="day.id ?? String(i)"
            class="border rounded-lg px-4"
          >
            <AccordionTrigger class="hover:no-underline">
              <span class="font-medium">{{ day.name }}</span>
              <Badge variant="outline" class="ml-auto mr-4 text-xs">
                {{ day.exercises.length }} {{ day.exercises.length === 1 ? 'exercise' : 'exercises' }}
              </Badge>
            </AccordionTrigger>
            <AccordionContent>
              <div class="flex flex-col gap-2 pb-2">
                <div
                  v-for="(ex, j) in day.exercises"
                  :key="ex.id ?? j"
                  class="flex items-center justify-between rounded-md bg-muted/50 px-3 py-2 text-sm"
                >
                  <span class="font-medium">{{ ex.name }}</span>
                  <div class="flex items-center gap-3 text-muted-foreground text-xs">
                    <span v-if="ex.sets">{{ ex.sets }} sets</span>
                    <span v-if="ex.reps">× {{ ex.reps }} reps</span>
                    <span v-if="ex.notes" class="italic">{{ ex.notes }}</span>
                  </div>
                </div>
              </div>
            </AccordionContent>
          </AccordionItem>
        </Accordion>
      </div>

      <!-- Assigned Users -->
      <div class="flex flex-col gap-3">
        <h2 class="font-semibold text-base">Assigned Users</h2>

        <!-- Assign dropdown -->
        <div class="flex gap-2">
          <Select v-model="selectedUserId">
            <SelectTrigger class="flex-1">
              <SelectValue placeholder="Select a user..." />
            </SelectTrigger>
            <SelectContent>
              <SelectItem
                v-for="user in availableUsers"
                :key="user.id"
                :value="user.id"
              >
                {{ user.firstName }} {{ user.lastName }}
              </SelectItem>
              <div v-if="availableUsers.length === 0" class="px-3 py-2 text-sm text-muted-foreground">
                No users available
              </div>
            </SelectContent>
          </Select>
          <Button
            size="icon"
            :disabled="!selectedUserId || isAssigning"
            @click="handleAssign"
          >
            <UserPlus class="h-4 w-4" />
          </Button>
        </div>

        <!-- Assigned users list -->
        <div v-if="usersLoading" class="text-muted-foreground text-sm">Loading...</div>
        <div v-else-if="!assignedUsers?.length" class="text-muted-foreground text-sm">
          No users assigned yet.
        </div>
        <div v-else class="flex flex-col gap-2">
          <div
            v-for="user in assignedUsers"
            :key="user.userId"
            class="flex items-center justify-between rounded-lg border px-3 py-2"
          >
            <div>
              <p class="text-sm font-medium">{{ user.firstName }} {{ user.lastName }}</p>
              <p class="text-xs text-muted-foreground">{{ user.email }}</p>
            </div>
            <Button
              variant="ghost"
              size="icon"
              class="text-destructive hover:text-destructive shrink-0"
              :disabled="isUnassigning"
              @click="unassignUser(user.userId)"
            >
              <UserMinus class="h-4 w-4" />
            </Button>
          </div>
        </div>
      </div>

    </div>
  </div>

  <!-- Edit Sheet -->
  <PlanSheet
    v-if="plan"
    v-model:open="sheetOpen"
    :plan="plan"
    @update:open="(v) => { if (!v) queryClient.invalidateQueries({ queryKey: ['workout-plans', planId] }) }"
  />
</template>

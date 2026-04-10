<script setup lang="ts">
import { ref } from 'vue'
import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query'
import { useRouter } from 'vue-router'
import { toast } from 'vue-sonner'
import { Plus, Pencil, Trash2, Loader2, Dumbbell, CalendarDays, Zap } from 'lucide-vue-next'
import {
  Card,
  CardHeader,
  CardTitle,
  CardDescription,
  CardContent,
  CardFooter,
} from '@/components/ui/card'
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { workoutPlanApi } from './workoutPlanApi'
import PlanSheet from './PlanSheet.vue'
import type { WorkoutPlan } from './types'

const queryClient = useQueryClient()
const router = useRouter()

// ── Sheet state ────────────────────────────────────────────
const sheetOpen = ref(false)
const editingPlan = ref<WorkoutPlan | null>(null)

function openCreate() {
  editingPlan.value = null
  sheetOpen.value = true
}

function openEdit(plan: WorkoutPlan) {
  editingPlan.value = plan
  sheetOpen.value = true
}

// ── Delete dialog state ────────────────────────────────────
const deleteDialogOpen = ref(false)
const planToDelete = ref<WorkoutPlan | null>(null)

function openDelete(plan: WorkoutPlan) {
  planToDelete.value = plan
  deleteDialogOpen.value = true
}

// ── Helpers ────────────────────────────────────────────────
function totalExercises(plan: WorkoutPlan): number {
  return plan.workoutDays.reduce((sum, d) => sum + d.exercises.length, 0)
}

// ── Queries & Mutations ────────────────────────────────────
const { data: plans, isLoading, isError } = useQuery({
  queryKey: ['workout-plans'],
  queryFn: workoutPlanApi.getAll,
})

const { mutate: deletePlan, isPending: isDeleting } = useMutation({
  mutationFn: (id: string) => workoutPlanApi.delete(id),
  onSuccess: () => {
    queryClient.invalidateQueries({ queryKey: ['workout-plans'] })
    toast.success('Plan deleted.')
    deleteDialogOpen.value = false
  },
  onError: () => toast.error('Failed to delete plan.'),
})
</script>

<template>
  <div class="flex flex-col gap-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold">Workout Plans</h1>
        <p class="text-sm text-muted-foreground">Create and manage workout plans.</p>
      </div>
      <Button @click="openCreate">
        <Plus class="mr-2 h-4 w-4" />
        New Plan
      </Button>
    </div>

    <!-- Loading -->
    <div v-if="isLoading" class="flex items-center justify-center py-16">
      <Loader2 class="h-6 w-6 animate-spin text-muted-foreground" />
    </div>

    <!-- Error -->
    <div v-else-if="isError" class="rounded-md border border-destructive/40 bg-destructive/10 p-4 text-sm text-destructive">
      Failed to load workout plans. Is the backend running?
    </div>

    <!-- Empty -->
    <div v-else-if="!plans?.length" class="flex flex-col items-center justify-center gap-3 py-20 text-muted-foreground">
      <Dumbbell class="h-10 w-10 opacity-30" />
      <p class="text-sm">No plans yet. Click "New Plan" to create one.</p>
    </div>

    <!-- Card Grid -->
    <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
      <Card
        v-for="plan in plans"
        :key="plan.id"
        class="flex flex-col cursor-pointer transition-shadow hover:shadow-md"
        @click="router.push(`/workout-plans/${plan.id}`)"
      >
        <CardHeader class="pb-2">
          <CardTitle class="text-base">{{ plan.name }}</CardTitle>
          <CardDescription>
            {{ new Date(plan.createdAt).toLocaleDateString() }}
          </CardDescription>
        </CardHeader>

        <CardContent class="flex gap-3 pb-4">
          <Badge variant="secondary" class="flex items-center gap-1">
            <CalendarDays class="h-3 w-3" />
            {{ plan.workoutDays.length }} {{ plan.workoutDays.length === 1 ? 'day' : 'days' }}
          </Badge>
          <Badge variant="secondary" class="flex items-center gap-1">
            <Zap class="h-3 w-3" />
            {{ totalExercises(plan) }} exercises
          </Badge>
        </CardContent>

        <CardFooter class="mt-auto flex justify-end gap-1 pt-0" @click.stop>
          <Button variant="ghost" size="icon" @click="openEdit(plan)">
            <Pencil class="h-4 w-4" />
          </Button>
          <Button
            variant="ghost"
            size="icon"
            class="text-destructive hover:text-destructive"
            @click="openDelete(plan)"
          >
            <Trash2 class="h-4 w-4" />
          </Button>
        </CardFooter>
      </Card>
    </div>
  </div>

  <!-- Add / Edit Sheet -->
  <PlanSheet v-model:open="sheetOpen" :plan="editingPlan" />

  <!-- Delete Confirmation -->
  <AlertDialog :open="deleteDialogOpen" @update:open="deleteDialogOpen = $event">
    <AlertDialogContent>
      <AlertDialogHeader>
        <AlertDialogTitle class="text-foreground">Delete plan?</AlertDialogTitle>
        <AlertDialogDescription>
          This will permanently delete
          <span class="font-medium">{{ planToDelete?.name }}</span>
          including all its days, exercises, and user assignments.
        </AlertDialogDescription>
      </AlertDialogHeader>
      <AlertDialogFooter>
        <AlertDialogCancel>Cancel</AlertDialogCancel>
        <AlertDialogAction
          class="bg-primary text-primary-foreground hover:bg-primary/90"
          :disabled="isDeleting"
          @click="planToDelete && deletePlan(planToDelete.id)"
        >
          <Loader2 v-if="isDeleting" class="mr-2 h-4 w-4 animate-spin" />
          Delete
        </AlertDialogAction>
      </AlertDialogFooter>
    </AlertDialogContent>
  </AlertDialog>
</template>

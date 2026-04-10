<script setup lang="ts">
import { ref, watch } from 'vue'
import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { toast } from 'vue-sonner'
import { Plus, Trash2, Loader2 } from 'lucide-vue-next'
import {
  Sheet,
  SheetContent,
  SheetHeader,
  SheetTitle,
  SheetDescription,
  SheetFooter,
} from '@/components/ui/sheet'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Separator } from '@/components/ui/separator'
import { workoutPlanApi } from './workoutPlanApi'
import type { WorkoutPlan, CreateWorkoutPlanPayload } from './types'

const props = defineProps<{
  open: boolean
  plan?: WorkoutPlan | null
}>()

const emit = defineEmits<{
  'update:open': [value: boolean]
}>()

const queryClient = useQueryClient()

// ── Form state ──────────────────────────────────────────────
type ExerciseForm = { name: string; sets: string; reps: string; notes: string }
type DayForm = { name: string; exercises: ExerciseForm[] }

const planName = ref('')
const days = ref<DayForm[]>([])
const errors = ref<Record<string, string>>({})

function emptyExercise(): ExerciseForm {
  return { name: '', sets: '', reps: '', notes: '' }
}

function emptyDay(): DayForm {
  return { name: '', exercises: [emptyExercise()] }
}

watch(
  () => props.plan,
  (p) => {
    if (p) {
      planName.value = p.name
      days.value = p.workoutDays.map(d => ({
        name: d.name,
        exercises: d.exercises.map(e => ({
          name: e.name,
          sets: e.sets?.toString() ?? '',
          reps: e.reps?.toString() ?? '',
          notes: e.notes ?? '',
        })),
      }))
    } else {
      planName.value = ''
      days.value = [emptyDay()]
    }
    errors.value = {}
  },
  { immediate: true },
)

const isEditing = () => !!props.plan

// ── Day / Exercise helpers ──────────────────────────────────
function addDay() {
  days.value.push(emptyDay())
}

function removeDay(di: number) {
  days.value.splice(di, 1)
}

function addExercise(di: number) {
  days.value[di]?.exercises.push(emptyExercise())
}

function removeExercise(di: number, ei: number) {
  days.value[di]?.exercises.splice(ei, 1)
}

// ── Validation ──────────────────────────────────────────────
function validate(): boolean {
  const e: Record<string, string> = {}
  if (!planName.value.trim()) e.planName = 'Plan name is required.'
  if (days.value.length === 0) e.days = 'At least one day is required.'
  days.value.forEach((d, di) => {
    if (!d.name.trim()) e[`day_${di}_name`] = 'Day name is required.'
    if (d.exercises.length === 0) e[`day_${di}_exercises`] = 'At least one exercise is required.'
    d.exercises.forEach((ex, ei) => {
      if (!ex.name.trim()) e[`day_${di}_ex_${ei}_name`] = 'Exercise name is required.'
    })
  })
  errors.value = e
  return Object.keys(e).length === 0
}

function buildPayload(): CreateWorkoutPlanPayload {
  return {
    name: planName.value.trim(),
    days: days.value.map(d => ({
      name: d.name.trim(),
      exercises: d.exercises.map(ex => ({
        name: ex.name.trim(),
        sets: ex.sets ? parseInt(ex.sets) : null,
        reps: ex.reps ? parseInt(ex.reps) : null,
        notes: ex.notes.trim() || null,
      })),
    })),
  }
}

// ── Mutation ────────────────────────────────────────────────
const { mutate: save, isPending } = useMutation({
  mutationFn: (payload: CreateWorkoutPlanPayload) =>
    isEditing()
      ? workoutPlanApi.update(props.plan!.id, payload)
      : workoutPlanApi.create(payload),
  onSuccess: () => {
    queryClient.invalidateQueries({ queryKey: ['workout-plans'] })
    toast.success(isEditing() ? 'Plan updated.' : 'Plan created.')
    emit('update:open', false)
  },
  onError: () => toast.error('Something went wrong.'),
})

function submit() {
  if (!validate()) return
  save(buildPayload())
}
</script>

<template>
  <Sheet :open="open" @update:open="emit('update:open', $event)">
    <SheetContent class="w-full overflow-y-auto sm:max-w-lg">
      <SheetHeader>
        <SheetTitle>{{ isEditing() ? 'Edit Plan' : 'New Workout Plan' }}</SheetTitle>
        <SheetDescription>
          {{ isEditing() ? 'Update plan details, days and exercises.' : 'Create a plan with days and exercises.' }}
        </SheetDescription>
      </SheetHeader>

      <form class="flex flex-col gap-6 px-4 py-6" @submit.prevent="submit">

        <!-- Plan Name -->
        <div class="flex flex-col gap-1.5">
          <Label for="planName">Plan Name</Label>
          <Input id="planName" v-model="planName" placeholder="e.g. Push Pull Legs" />
          <p v-if="errors.planName" class="text-sm text-destructive">{{ errors.planName }}</p>
        </div>

        <Separator />

        <!-- Days -->
        <div class="flex flex-col gap-4">
          <div class="flex items-center justify-between">
            <span class="text-sm font-semibold">Days</span>
            <Button type="button" variant="outline" size="sm" @click="addDay">
              <Plus class="mr-1 h-3.5 w-3.5" /> Add Day
            </Button>
          </div>
          <p v-if="errors.days" class="text-sm text-destructive">{{ errors.days }}</p>

          <div
            v-for="(day, di) in days"
            :key="di"
            class="rounded-lg border p-4 flex flex-col gap-4"
          >
            <!-- Day header -->
            <div class="flex items-center gap-2">
              <div class="flex-1 flex flex-col gap-1">
                <Label :for="`day-${di}`">Day {{ di + 1 }} Name</Label>
                <Input :id="`day-${di}`" v-model="day.name" placeholder="e.g. Monday / Push Day" />
                <p v-if="errors[`day_${di}_name`]" class="text-sm text-destructive">{{ errors[`day_${di}_name`] }}</p>
              </div>
              <Button
                v-if="days.length > 1"
                type="button"
                variant="ghost"
                size="icon"
                class="mt-5 shrink-0 text-destructive hover:text-destructive"
                @click="removeDay(di)"
              >
                <Trash2 class="h-4 w-4" />
              </Button>
            </div>

            <!-- Exercises -->
            <div class="flex flex-col gap-3 pl-1">
              <p v-if="errors[`day_${di}_exercises`]" class="text-sm text-destructive">{{ errors[`day_${di}_exercises`] }}</p>

              <div
                v-for="(ex, ei) in day.exercises"
                :key="ei"
                class="flex flex-col gap-2 rounded-md bg-muted/40 p-3"
              >
                <div class="flex items-center justify-between">
                  <span class="text-xs font-medium text-muted-foreground">Exercise {{ ei + 1 }}</span>
                  <Button
                    v-if="day.exercises.length > 1"
                    type="button"
                    variant="ghost"
                    size="icon"
                    class="h-6 w-6 text-destructive hover:text-destructive"
                    @click="removeExercise(di, ei)"
                  >
                    <Trash2 class="h-3.5 w-3.5" />
                  </Button>
                </div>

                <div class="flex flex-col gap-1">
                  <Input v-model="ex.name" placeholder="Exercise name *" />
                  <p v-if="errors[`day_${di}_ex_${ei}_name`]" class="text-xs text-destructive">{{ errors[`day_${di}_ex_${ei}_name`] }}</p>
                </div>

                <div class="grid grid-cols-3 gap-2">
                  <Input v-model="ex.sets" type="number" placeholder="Sets" min="0" />
                  <Input v-model="ex.reps" type="number" placeholder="Reps" min="0" />
                  <Input v-model="ex.notes" placeholder="Notes" />
                </div>
              </div>

              <Button type="button" variant="ghost" size="sm" class="self-start" @click="addExercise(di)">
                <Plus class="mr-1 h-3.5 w-3.5" /> Add Exercise
              </Button>
            </div>
          </div>
        </div>

        <SheetFooter class="pt-2">
          <Button type="submit" :disabled="isPending" class="w-full">
            <Loader2 v-if="isPending" class="mr-2 h-4 w-4 animate-spin" />
            {{ isEditing() ? 'Save Changes' : 'Create Plan' }}
          </Button>
        </SheetFooter>
      </form>
    </SheetContent>
  </Sheet>
</template>

export interface Exercise {
  id?: string
  name: string
  sets: number | null
  reps: number | null
  notes: string | null
}

export interface WorkoutDay {
  id?: string
  name: string
  exercises: Exercise[]
}

export interface WorkoutPlan {
  id: string
  name: string
  days: WorkoutDay[]
  createdAt: string
  updatedAt: string
}

export interface AssignedUser {
  userId: string
  firstName: string
  lastName: string
  email: string
  assignedAt: string
}

export interface CreateWorkoutPlanPayload {
  name: string
  days: {
    name: string
    exercises: {
      name: string
      sets: number | null
      reps: number | null
      notes: string | null
    }[]
  }[]
}

export type UpdateWorkoutPlanPayload = CreateWorkoutPlanPayload

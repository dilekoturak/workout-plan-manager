import client from '@/shared/api/client'
import type {
  WorkoutPlan,
  AssignedUser,
  CreateWorkoutPlanPayload,
  UpdateWorkoutPlanPayload,
} from './types'

export const workoutPlanApi = {
  getAll(): Promise<WorkoutPlan[]> {
    return client.get<WorkoutPlan[]>('/api/workout-plans').then(r => r.data)
  },

  getOne(id: string): Promise<WorkoutPlan> {
    return client.get<WorkoutPlan>(`/api/workout-plans/${id}`).then(r => r.data)
  },

  create(payload: CreateWorkoutPlanPayload): Promise<WorkoutPlan> {
    return client.post<WorkoutPlan>('/api/workout-plans', payload).then(r => r.data)
  },

  update(id: string, payload: UpdateWorkoutPlanPayload): Promise<WorkoutPlan> {
    return client.put<WorkoutPlan>(`/api/workout-plans/${id}`, payload).then(r => r.data)
  },

  delete(id: string): Promise<void> {
    return client.delete(`/api/workout-plans/${id}`).then(() => undefined)
  },

  getAssignedUsers(planId: string): Promise<AssignedUser[]> {
    return client
      .get<AssignedUser[]>(`/api/workout-plans/${planId}/users`)
      .then(r => r.data)
  },

  assignUser(planId: string, userId: string): Promise<void> {
    return client
      .post(`/api/workout-plans/${planId}/assign/${userId}`)
      .then(() => undefined)
  },

  unassignUser(planId: string, userId: string): Promise<void> {
    return client
      .delete(`/api/workout-plans/${planId}/assign/${userId}`)
      .then(() => undefined)
  },
}

import client from '@/shared/api/client'
import type { User, CreateUserPayload, UpdateUserPayload } from './types'

export const userApi = {
  getAll(): Promise<User[]> {
    return client.get<User[]>('/api/users').then(r => r.data)
  },

  getOne(id: string): Promise<User> {
    return client.get<User>(`/api/users/${id}`).then(r => r.data)
  },

  create(payload: CreateUserPayload): Promise<User> {
    return client.post<User>('/api/users', payload).then(r => r.data)
  },

  update(id: string, payload: UpdateUserPayload): Promise<User> {
    return client.put<User>(`/api/users/${id}`, payload).then(r => r.data)
  },

  delete(id: string): Promise<void> {
    return client.delete(`/api/users/${id}`).then(() => undefined)
  },
}

export interface User {
  id: string
  firstName: string
  lastName: string
  email: string
  createdAt: string
  updatedAt: string
}

export interface CreateUserPayload {
  firstName: string
  lastName: string
  email: string
}

export interface UpdateUserPayload {
  firstName: string
  lastName: string
  email: string
}

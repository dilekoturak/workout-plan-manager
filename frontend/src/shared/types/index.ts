// Shared types used across domains

export interface ApiError {
  message: string
  errors?: Record<string, string[]>
}

import { User } from './user.draft'

export interface Project {
  id: number
  authorId?: string
  author?: User
  key: string
  name: string
  description?: string
  startDate?: string
  endDate?: string
  createdAt?: string
  updatedAt?: string
}

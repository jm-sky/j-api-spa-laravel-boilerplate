import { IUser, User } from './user.model.ts';

export interface IProject {
  id: number
  authorId?: string
  author?: IUser
  key: string
  name: string
  description?: string
  startDate?: string
  endDate?: string
  archived: number
  priority: string
  createdAt?: string
  updatedAt?: string
}

export class Project {
  declare id: number
  declare authorId?: string
  declare author?: User
  declare key: string
  declare name: string
  declare description?: string
  declare startDate?: string
  declare endDate?: string
  declare archived: number
  declare priority: string
  declare createdAt?: string
  declare updatedAt?: string

  constructor(payload: IProject) {
    this.id = payload.id
    this.authorId = payload.authorId
    this.key = payload.key
    this.name = payload.name
    this.description = payload.description
    this.startDate = payload.startDate
    this.endDate = payload.endDate
    this.archived = payload.archived
    this.priority = payload.priority
    this.createdAt = payload.createdAt
    this.updatedAt = payload.updatedAt
  }
}

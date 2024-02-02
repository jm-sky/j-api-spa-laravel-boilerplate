export interface User {
  id: string
  name: string
  email: string
  emailVerifiedAt?: string
  password: string
  twoFactorSecret?: string
  twoFactorRecoveryCodes?: string
  twoFactorConfirmedAt?: string
  rememberToken?: string
  createdAt?: string
  updatedAt?: string
  provider?: string
  providerId?: string
  providerToken?: string
  providerRefreshToken?: string
}

import { config } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { beforeEach, vi } from 'vitest'

// Mock de vue-router para todos los tests
config.global.stubs = {
  RouterLink: { template: '<a><slot /></a>' },
  RouterView: { template: '<div />' },
}

// Pinia fresca antes de cada test
beforeEach(() => {
  setActivePinia(createPinia())
})

// Mock global de axios/api
vi.mock('@/services/api', () => ({
  default: {
    get:    vi.fn(),
    post:   vi.fn(),
    put:    vi.fn(),
    delete: vi.fn(),
    interceptors: {
      response: { use: vi.fn() }
    }
  }
}))

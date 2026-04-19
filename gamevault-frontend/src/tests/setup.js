import { config } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { beforeEach, vi } from 'vitest'

config.global.stubs = {
  RouterLink: { template: '<a><slot /></a>' },
  RouterView: { template: '<div />' },
}

beforeEach(() => {
  setActivePinia(createPinia())
})

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

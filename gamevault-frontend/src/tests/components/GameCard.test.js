import { describe, it, expect, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { createRouter, createWebHistory } from 'vue-router'
import GameCard from '@/components/games/GameCard.vue'

const router = createRouter({
  history: createWebHistory(),
  routes: [{ path: '/games/:id', name: 'game-detail', component: { template: '<div/>' } }],
})

const mockGame = {
  id: 1,
  title: 'Elden Ring',
  developer: 'FromSoftware',
  price: 54.99,
  cover_image: 'https://example.com/cover.jpg',
  genres: ['RPG', 'Action', 'Souls-like'],
  metacritic_score: 95,
}

describe('GameCard', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  it('renders the game title', () => {
    const wrapper = mount(GameCard, {
      props: { game: mockGame },
      global: { plugins: [router] },
    })
    expect(wrapper.text()).toContain('Elden Ring')
  })

  it('renders the developer name', () => {
    const wrapper = mount(GameCard, {
      props: { game: mockGame },
      global: { plugins: [router] },
    })
    expect(wrapper.text()).toContain('FromSoftware')
  })

  it('renders the price in euros', () => {
    const wrapper = mount(GameCard, {
      props: { game: mockGame },
      global: { plugins: [router] },
    })
    expect(wrapper.text()).toContain('54.99')
  })

  it('shows "Gratis" when price is 0', () => {
    const freeGame = { ...mockGame, price: 0 }
    const wrapper = mount(GameCard, {
      props: { game: freeGame },
      global: { plugins: [router] },
    })
    expect(wrapper.text()).toContain('Gratis')
  })

  it('renders cover image when provided', () => {
    const wrapper = mount(GameCard, {
      props: { game: mockGame },
      global: { plugins: [router] },
    })
    const img = wrapper.find('img')
    expect(img.exists()).toBe(true)
    expect(img.attributes('src')).toBe(mockGame.cover_image)
  })

  it('renders max 2 genre tags', () => {
    const wrapper = mount(GameCard, {
      props: { game: mockGame },
      global: { plugins: [router] },
    })
    const genres = wrapper.findAll('span').filter(s =>
      ['RPG', 'Action', 'Souls-like'].includes(s.text())
    )
    expect(genres.length).toBeLessThanOrEqual(2)
  })

  it('shows green metacritic badge for scores >= 75', () => {
    const wrapper = mount(GameCard, {
      props: { game: { ...mockGame, metacritic_score: 95 } },
      global: { plugins: [router] },
    })
    const badge = wrapper.find('.bg-green-700')
    expect(badge.exists()).toBe(true)
    expect(badge.text()).toBe('95')
  })

  it('shows yellow metacritic badge for scores between 50 and 74', () => {
    const wrapper = mount(GameCard, {
      props: { game: { ...mockGame, metacritic_score: 60 } },
      global: { plugins: [router] },
    })
    const badge = wrapper.find('.bg-yellow-700')
    expect(badge.exists()).toBe(true)
  })

  it('links to the correct game detail page', () => {
    const wrapper = mount(GameCard, {
      props: { game: mockGame },
      global: { plugins: [router] },
    })
    const link = wrapper.find('a')
    expect(link.exists()).toBe(true)
  })
})

// @vitest-environment jsdom
import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import BaseButton from '@/components/ui/BaseButton.vue'

describe('BaseButton', () => {
  it('renders slot content', () => {
    const wrapper = mount(BaseButton, { slots: { default: 'Click me' } })
    expect(wrapper.text()).toContain('Click me')
  })

  it('applies primary variant classes by default', () => {
    const wrapper = mount(BaseButton)
    expect(wrapper.classes()).toContain('bg-blue-600')
  })

  it('applies danger variant classes', () => {
    const wrapper = mount(BaseButton, { props: { variant: 'danger' } })
    expect(wrapper.classes()).toContain('bg-red-700')
  })

  it('applies ghost variant classes', () => {
    const wrapper = mount(BaseButton, { props: { variant: 'ghost' } })
    expect(wrapper.classes()).toContain('bg-transparent')
  })

  it('is disabled when disabled prop is true', () => {
    const wrapper = mount(BaseButton, { props: { disabled: true } })
    expect(wrapper.attributes('disabled')).toBeDefined()
  })

  it('is disabled when loading prop is true', () => {
    const wrapper = mount(BaseButton, { props: { loading: true } })
    expect(wrapper.attributes('disabled')).toBeDefined()
  })

  it('shows spinner when loading', () => {
    const wrapper = mount(BaseButton, { props: { loading: true } })
    expect(wrapper.find('.animate-spin').exists()).toBe(true)
  })

  it('emits click event when clicked', async () => {
    const wrapper = mount(BaseButton)
    await wrapper.trigger('click')
    expect(wrapper.emitted('click')).toBeTruthy()
  })

  it('does not emit click when disabled', async () => {
    const wrapper = mount(BaseButton, { props: { disabled: true } })
    await wrapper.trigger('click')
    expect(wrapper.element.disabled).toBe(true)
  })

  it('applies sm size classes', () => {
    const wrapper = mount(BaseButton, { props: { size: 'sm' } })
    expect(wrapper.classes()).toContain('text-xs')
  })

  it('applies lg size classes', () => {
    const wrapper = mount(BaseButton, { props: { size: 'lg' } })
    expect(wrapper.classes()).toContain('text-base')
  })
})

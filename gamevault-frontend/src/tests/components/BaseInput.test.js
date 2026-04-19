// @vitest-environment jsdom
import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import BaseInput from '@/components/ui/BaseInput.vue'

describe('BaseInput', () => {
  it('renders a label when provided', () => {
    const wrapper = mount(BaseInput, {
      props: { label: 'Email', modelValue: '' },
    })
    expect(wrapper.find('label').text()).toBe('Email')
  })

  it('does not render label when not provided', () => {
    const wrapper = mount(BaseInput, {
      props: { modelValue: '' },
    })
    expect(wrapper.find('label').exists()).toBe(false)
  })

  it('renders the current value', () => {
    const wrapper = mount(BaseInput, {
      props: { modelValue: 'hello@test.com', label: 'Email' },
    })
    expect(wrapper.find('input').element.value).toBe('hello@test.com')
  })

  it('emits update:modelValue on input', async () => {
    const wrapper = mount(BaseInput, {
      props: { modelValue: '', label: 'Email' },
    })
    await wrapper.find('input').setValue('new@value.com')
    expect(wrapper.emitted('update:modelValue')?.[0]).toEqual(['new@value.com'])
  })

  it('displays error message when error prop is provided', () => {
    const wrapper = mount(BaseInput, {
      props: { modelValue: '', error: 'Campo obligatorio' },
    })
    expect(wrapper.find('p').text()).toBe('Campo obligatorio')
  })

  it('does not display error element when no error', () => {
    const wrapper = mount(BaseInput, {
      props: { modelValue: '' },
    })
    expect(wrapper.find('p').exists()).toBe(false)
  })
})

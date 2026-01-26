import { reactive } from 'vue'

const state = reactive({
  toasts: [],
})

export function useToast() {
  const show = (message, type = 'success') => {
    const id = Date.now()
    state.toasts.push({ id, message, type })

    setTimeout(() => remove(id), 3000)
  }

  const remove = (id) => {
    const index = state.toasts.findIndex(t => t.id === id)
    if (index !== -1) state.toasts.splice(index, 1)
  }

  return {
    toasts: state.toasts,
    show,
    remove,
  }
}

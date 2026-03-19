// toast.js

import { toast as toastify } from 'vue3-toastify'

export const toast = {
  success(msg) {
    toastify.success(msg)
  },
  error(msg) {
    toastify.error(msg)
  },
}

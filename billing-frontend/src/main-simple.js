import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'

import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap/dist/js/bootstrap.bundle.min.js'

window.addEventListener('error', (event) => {
  console.error('Global error:', event.error)
})

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(router)

setTimeout(() => {
  try {
    app.mount('#app')
    console.log('App mounted successfully')
  } catch (error) {
    console.error('App mount failed:', error)
    document.body.innerHTML = `
      <div style="padding: 20px; text-align: center;">
        <h1 style="color: red;">❌ Application Error</h1>
        <p style="color: #666;">${error.message}</p>
        <button onclick="location.reload()" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">
          Reload Application
        </button>
      </div>
    `
  }
}, 1000)
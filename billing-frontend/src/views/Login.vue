<template>
  <div class="login-wrapper">
    <div class="login-card">
      <!-- Left Side - Form -->
      <div class="form-section">
        <div class="brand-header">
          <div class="brand-logo">
            <i class="bi bi-receipt-cutoff"></i>
          </div>
          <h3 class="brand-title">Billing System</h3>
        </div>

        <div class="welcome-text">
          <h1>Welcome Back!</h1>
          <p>Please log in to your account.</p>
        </div>

        <div v-if="error" class="error-alert">
          <i class="bi bi-exclamation-triangle-fill"></i>
          {{ error }}
        </div>

        <form @submit.prevent="handleLogin">
          <div class="form-group">
            <label>Email Address</label>
            <input
              v-model="form.email"
              type="email"
              placeholder="name@example.com"
              :class="{ 'error': emailError, 'valid': emailTouched && !emailError }"
              @blur="validateEmail"
              @input="emailTouched && validateEmail()"
            />
            <span v-if="emailError" class="error-text">{{ emailError }}</span>
          </div>

          <div class="form-group">
            <label>Password</label>
            <div class="password-input">
              <input
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                placeholder="Enter your password"
                :class="{ 'error': passwordError, 'valid': passwordTouched && !passwordError }"
                @blur="validatePassword"
                @input="passwordTouched && validatePassword()"
              />
              <button type="button" @click="showPassword = !showPassword" class="toggle-btn">
                <i :class="showPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
              </button>
            </div>
            <span v-if="passwordError" class="error-text">{{ passwordError }}</span>
          </div>

          <div class="form-footer">
            <label class="checkbox-label">
              <input v-model="form.remember" type="checkbox" />
              <span>Remember me</span>
            </label>
            <a href="#" class="forgot-link">Forgot password?</a>
          </div>

          <button type="submit" class="btn-submit-outline" :disabled="loading">
            <span v-if="loading">
            <span class="spinner"></span>
            Logging in...
            </span>
            <span v-else class="btn-text">
            Login
            <span class="arrow">→</span>
            </span>
          </button>
        </form>
      </div>

      <!-- Right Side - Photo -->
      <div class="photo-section"></div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'

const form = reactive({
  email: '',
  password: '',
  remember: false,
})

const router = useRouter()
const showPassword = ref(false)
const loading = ref(false)
const error = ref('')

const emailTouched = ref(false)
const passwordTouched = ref(false)
const emailError = ref('')
const passwordError = ref('')

const validateEmail = () => {
  emailTouched.value = true
  
  if (!form.email) {
    emailError.value = 'Email is required'
    return false
  }
  
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  if (!emailRegex.test(form.email)) {
    emailError.value = 'Please enter a valid email'
    return false
  }
  
  emailError.value = ''
  return true
}

const validatePassword = () => {
  passwordTouched.value = true
  
  if (!form.password) {
    passwordError.value = 'Password is required'
    return false
  }
  
  if (form.password.length < 6) {
    passwordError.value = 'Password must be at least 6 characters'
    return false
  }
  
  passwordError.value = ''
  return true
}

const handleLogin = async () => {
  const isEmailValid = validateEmail()
  const isPasswordValid = validatePassword()

  if (!isEmailValid || !isPasswordValid) {
    error.value = 'Please correct the errors above'
    return
  }

  error.value = ''
  loading.value = true

  try {
    await new Promise(resolve => setTimeout(resolve, 1000))
    router.push('/dashboard')

  } catch (err) {
    error.value = 'Invalid email or password'
  } finally {
    loading.value = false
  }
}

</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
@import url('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css');

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: 'Inter', sans-serif;
}

.login-wrapper {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f5f5f5;
  padding: 2rem;
}

.login-card {
  display: flex;
  width: 100%;
  max-width: 1000px;
  background: white;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.12);
}

.form-section {
  flex: 1;
  padding: 3rem 2.5rem;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.brand-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 2.5rem;
}

.brand-logo {
  width: 44px;
  height: 44px;
  background: white;
  border: 2px solid #000;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.brand-logo i {
  font-size: 1.5rem;
  color: #000;
}

.brand-logo i {
  font-size: 1.5rem;
  color: #000;
}

.brand-title {
  font-size: 1.375rem;
  font-weight: 700;
  color: #111827;
  margin: 0;
}

.welcome-text {
  margin-bottom: 2rem;
}

.welcome-text h1 {
  font-size: 1.875rem;
  font-weight: 700;
  color: #111827;
  margin-bottom: 0.5rem;
}

.welcome-text p {
  font-size: 0.9375rem;
  color: #6b7280;
  margin: 0;
}

.error-alert {
  background: #fee2e2;
  color: #991b1b;
  padding: 0.875rem 1rem;
  border-radius: 10px;
  font-size: 0.875rem;
  margin-bottom: 1.5rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.form-group {
  margin-bottom: 1.25rem;
}

.form-group label {
  display: block;
  font-size: 0.875rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 0.5rem;
}

.form-group input {
  width: 100%;
  padding: 0.75rem 1rem;
  font-size: 0.9375rem;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  transition: all 0.2s ease;
}

.form-group input:focus {
  border-color: #667eea;
  outline: none;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-group input.error {
  border-color: #ef4444;
}

.form-group input.error:focus {
  box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

.form-group input.valid {
  border-color: #10b981;
}

.error-text {
  display: block;
  color: #ef4444;
  font-size: 0.8125rem;
  margin-top: 0.375rem;
}

.password-input {
  position: relative;
}

.toggle-btn {
  position: absolute;
  right: 1rem;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: #9ca3af;
  cursor: pointer;
  padding: 0.5rem;
  transition: color 0.2s ease;
}

.toggle-btn:hover {
  color: #667eea;
}

.form-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  font-size: 0.875rem;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  color: #6b7280;
  font-weight: 500;
}

.checkbox-label input {
  width: 1rem;
  height: 1rem;
  cursor: pointer;
  accent-color: #667eea;
}

.forgot-link {
  color: #667eea;
  text-decoration: none;
  font-weight: 600;
}

.forgot-link:hover {
  text-decoration: underline;
}

.btn-submit {
  width: 100%;
  padding: 0.875rem 1.5rem;
  font-size: 1rem;
  font-weight: 600;
  color: white;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-submit:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
}

.btn-submit:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.spinner {
  display: inline-block;
  width: 1rem;
  height: 1rem;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
  margin-right: 0.5rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.btn-submit-outline {
  width: 100%;
  padding: 0.875rem 1.5rem;
  font-size: 1rem;
  font-weight: 600;
  color: #000;
  background: #fff;
  border: 2px solid #000;
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.25s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-submit-outline:hover:not(:disabled) {
  background: #000;
  color: #fff;
}

.btn-submit-outline:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-text {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
}

.arrow {
  font-size: 1.25rem;
  transition: transform 0.25s ease;
}

.btn-submit-outline:hover .arrow {
  transform: translateX(4px);
}

.photo-section {
  flex: 1;
  background: url('/images/login-bg.avif');
  background-size: cover;
  background-position: center;
  min-height: 600px;
}

@media (max-width: 991px) {
  .login-card {
    flex-direction: column;
  }

  .photo-section {
    display: none;
  }

  .form-section {
    padding: 2.5rem 2rem;
  }
}

@media (max-width: 575px) {
  .login-wrapper {
    padding: 1rem;
  }

  .form-section {
    padding: 2rem 1.5rem;
  }

  .welcome-text h1 {
    font-size: 1.625rem;
  }
}
</style>
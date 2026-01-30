# Billing System

A modern multi-tenant billing system built with Laravel 12 (backend) and Vue 3 (frontend).

## Architecture Overview

### Backend (Laravel 12)

- **Framework**: Laravel 12 with PHP 8.2+
- **Authentication**: Laravel Sanctum for API token authentication
- **Database**: MySQL with multi-tenant architecture
- **Design Patterns**: Repository pattern, Service layer, Resource transformers
- **API**: RESTful API with proper HTTP status codes and error handling

### Frontend (Vue 3)

- **Framework**: Vue 3 with Composition API
- **Build Tool**: Vite
- **State Management**: Pinia stores
- **Routing**: Vue Router with role-based guards
- **UI Framework**: Bootstrap 5
- **HTTP Client**: Axios with interceptors

### Multi-Tenancy Architecture

The system implements organization-based multi-tenancy:

- **Data Isolation**: Each organization sees only its own data
- **User-Organization Mapping**: Users belong to specific organizations
- **Tenant Scoping**: Queries automatically filter by organization_id
- **Security**: Authentication and authorization prevent cross-tenant data access

## Quick Start

### Prerequisites

- Docker & Docker Compose
- Git
- Node.js 18+ (for local frontend development)
- PHP 8.2+ (for local backend development)

### Installation

1. **Clone the repository**

   ```bash
   git clone <repository-url>
   cd BillingSystem
   ```

2. **Start the services**

   ```bash
   docker-compose up -d --build
   ```

3. **Install backend dependencies**

   ```bash
   docker-compose exec app composer install
   ```

4. **Make the run script executable**

   ```bash
   cd billing-backend
   chmod +x run
   cd ..
   ```

5. **Generate application key**

   ```bash
   ./run artisan key:generate
   ```

6. **Run database migrations**

   ```bash
   ./run migrate:fresh
   ```

7. **Install frontend dependencies**

   ```bash
   cd billing-frontend
   npm install
   docker-compose up -d --build or npm run build
   cd ..
   ```

8. **Access the application**
   - Frontend: http://localhost:3000
   - Backend API: http://localhost:8000
   - API Documentation: http://localhost:8000/api/docs

## Configuration

### Environment Variables

Copy the backend environment file:

```bash
cd billing-backend
cp .env.example .env
```

Key environment variables:

```env
# Database
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=billing_system
DB_USERNAME=billing_user
DB_PASSWORD=billing_password

# Laravel Sanctum
SANCTUM_STATEFUL_DOMAINS=http://localhost:3000

# CORS
CORS_ALLOWED_ORIGINS=http://localhost:3000
```

### Frontend Configuration

Create frontend environment file:

```bash
cd billing-frontend
echo "VITE_API_BASE_URL=http://localhost:8000" > .env
```

## Testing

### Backend Tests

Run all backend tests:

```bash
./run test
```

Run specific test types:

```bash
# Unit tests
./run artisan test --testsuite=Unit

# Feature tests
./run artisan test --testsuite=Feature

# With coverage
./run artisan test --coverage
```

### Using the Run Script

The `./run` script provides convenient shortcuts for common Laravel commands:

```bash
# Artisan commands
./run artisan key:generate
./run artisan migrate --force
./run artisan cache:clear

# Create generators
./run make:model Product
./run make:controller ProductController
./run make:request StoreProductRequest
./run make:resource ProductResource
./run make:service ProductService
./run make:repository ProductRepository

# Database operations
./run migrate
./run migrate:fresh

# Testing
./run test
```

The script automatically runs commands inside the Docker container, so you don't need to type `docker-compose exec app php artisan` every time.

Run specific test types:

```bash
# Unit tests
./run artisan test --testsuite=Unit

# Feature tests
./run artisan test --testsuite=Feature

# With coverage
./run artisan test --coverage
```

### Frontend Tests

Install and run frontend tests:

```bash
cd billing-frontend
npm install
npm run test
```

### Manual Testing Steps

1. **Authentication Flow**

   - Navigate to http://localhost:3000/login
   - Login with seeded credentials:
     - Admin: `admin@doh.gov.ph` / `password`
     - Accountant: `accountant@doh.gov.ph` / `password`

2. **Multi-Tenant Verification**

   - Login as different organization users
   - Verify data isolation (users only see their organization's data)
   - Test cross-tenant access prevention

3. **Role-Based Access Control**

   - Admin can access all features including user management
   - Accountant has limited access (no user management)
   - Verify unauthorized routes redirect appropriately

4. **CRUD Operations**
   - Create, read, update, delete invoices
   - Create, read, update, delete vendors
   - Test form validation and error handling

## Architectural Decisions & Coding Principles

### 1. Repository Pattern Implementation

**Location**: `billing-backend/app/Repositories/`

**Principle**: Separation of data access logic from business logic

**Example**:

```php
// Interface
interface InvoiceRepositoryInterface
{
    public function getByOrganization(int $organizationId): Collection;
    public function getByStatus(string $status): Collection;
}

// Implementation
class InvoiceRepository extends BaseRepository implements InvoiceRepositoryInterface
{
    public function getByOrganization(int $organizationId): Collection
    {
        return $this->model->forTenant($organizationId)->get();
    }
}
```

### 2. Service Layer Architecture

**Location**: `billing-backend/app/Services/`

**Principle**: Encapsulation of business logic, reusable across controllers

**Example**:

```php
class AuthService
{
    public function authenticate(array $credentials): array
    {
        // Business logic for authentication
        // Token generation
        // User validation
    }
}
```

### 3. API Resource Transformation

**Location**: `billing-backend/app/Http/Resources/`

**Principle**: Consistent API responses, data shape control

**Example**:

```php
class InvoiceResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'amount' => $this->amount,
            'status' => $this->status,
            'vendor' => new VendorResource($this->whenLoaded('vendor')),
        ];
    }
}
```

### 4. Form Request Validation

**Location**: `billing-backend/app/Http/Requests/`

**Principle**: Separation of validation logic from controllers

**Example**:

```php
class StoreInvoiceRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'number' => 'required|string|max:50',
            'vendor_id' => 'required|exists:vendors,id',
            'amount' => 'required|numeric|min:0',
        ];
    }
}
```

### 5. Frontend Composition API

**Location**: `billing-frontend/src/views/`

**Principle**: Reactive, composable, and type-safe component logic

**Example**:

```javascript
// Dashboard.vue
<script setup>
import { computed, onMounted } from 'vue'
import { useAuthStore } from '../stores/auth'
import { useOrganizationStore } from '../stores/organization'

const authStore = useAuthStore()
const organizationStore = useOrganizationStore()

const quickActions = computed(() => {
  return authStore.isAdmin ? adminActions : accountantActions
})
</script>
```

### 6. Pinia State Management

**Location**: `billing-frontend/src/stores/`

**Principle**: Centralized, reactive state management with composition API

**Example**:

```javascript
export const useAuthStore = defineStore("auth", {
  state: () => ({
    user: null,
    token: null,
    isAuthenticated: false,
  }),

  actions: {
    async login(credentials) {
      // Authentication logic
    },
  },
});
```

### 7. Route Guards & Authorization

**Location**: `billing-frontend/src/router/index.js`

**Principle**: Declarative route protection and role-based access

**Example**:

```javascript
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore();

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return next("/login");
  }

  if (to.meta.roles && !to.meta.roles.includes(authStore.userRole)) {
    return next("/dashboard");
  }

  next();
});
```

### 8. Multi-Tenant Data Scoping

**Location**: `billing-backend/app/Models/`

**Principle**: Automatic data isolation by tenant

**Example**:

```php
class Invoice extends Model
{
    public function scopeForTenant($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }
}
```

## Security Features

1. **Authentication**: Laravel Sanctum tokens with expiration
2. **Authorization**: Role-based access control
3. **Data Isolation**: Multi-tenant query scoping
4. **CORS**: Proper cross-origin resource sharing configuration
5. **Input Validation**: Form request validation with sanitization
6. **SQL Injection Prevention**: Eloquent ORM with parameter binding

## 📊 API Endpoints

### Authentication

- `POST /api/login` - User login
- `POST /api/logout` - User logout
- `GET /api/user` - Get current user

### Invoices

- `GET /api/invoices` - List invoices (tenant-scoped)
- `POST /api/invoices` - Create invoice
- `GET /api/invoices/{id}` - Get invoice
- `PUT /api/invoices/{id}` - Update invoice
- `DELETE /api/invoices/{id}` - Delete invoice
- `GET /api/invoices/status/{status}` - Filter by status

### Vendors

- `GET /api/vendors` - List vendors (tenant-scoped)
- `POST /api/vendors` - Create vendor
- `GET /api/vendors/{id}` - Get vendor
- `PUT /api/vendors/{id}` - Update vendor
- `DELETE /api/vendors/{id}` - Delete vendor

### Organizations

- `GET /api/organizations` - List organizations
- `POST /api/organizations` - Create organization
- `GET /api/organizations/{id}` - Get organization
- `PUT /api/organizations/{id}` - Update organization
- `DELETE /api/organizations/{id}` - Delete organization
- `GET /api/organizations/{id}/users` - Get organization users

### Users

- `GET /api/users` - List users (admin only)
- `POST /api/users` - Create user (admin only)
- `GET /api/users/{id}` - Get user
- `PUT /api/users/{id}` - Update user
- `DELETE /api/users/{id}` - Delete user (admin only)

## Docker Services

### Backend Services

- **app**: Laravel PHP application
- **mysql**: MySQL 8.0 database
- **nginx**: Web server with PHP-FPM

### Frontend Services

- **frontend**: Vue.js development server

## Deployment

### Production Deployment

1. **Environment Setup**

   ```bash
   cp .env.example .env
   # Configure production variables
   ```

2. **Build Assets**

   ```bash
   cd billing-frontend
   npm run build
   cd ..
   ```

3. **Run Commands**
   ```bash
   docker-compose -f docker-compose.prod.yml up -d
   docker-compose exec app php artisan migrate --force
   docker-compose exec app php artisan config:cache
   docker-compose exec app php artisan route:cache
   ```

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## Development Guidelines

### Code Style

- Backend: Follow PSR-12 coding standards
- Frontend: Use Vue 3 Composition API conventions
- Use ESLint and Prettier for frontend formatting

### Git Commit Messages

Use conventional commits:

- `feat:` - New features
- `fix:` - Bug fixes
- `docs:` - Documentation updates
- `refactor:` - Code refactoring
- `test:` - Test additions/updates

### Testing Requirements

- All new features must include tests
- Maintain minimum 80% code coverage
- Write both unit and integration tests

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support and questions:

- Create an issue in the GitHub repository
- Check the API documentation at `/api/docs`
- Review the test files for usage examples

---

**Built with using Laravel 12 and Vue 3**

# Barber Shop — Sistema de Gestión

Sistema de gestión integral para barberías: citas, servicios, punto de venta, inventario, reportes y administración multi-sucursal.

## Stack tecnológico

| Capa | Tecnología | Versión |
|---|---|---|
| Backend | PHP / Laravel | 8.3+ / 13.x |
| Panel admin | Filament | 5.x |
| CSS | Tailwind CSS | 4.x |
| Bundler | Vite | 8.x |
| Base de datos | PostgreSQL | 16+ |

## Módulos

- **Agenda** — Citas con calendario tipo Google Calendar, drag & drop, filtro por barbero
- **Catálogo** — Servicios, categorías, combos y precios personalizados por empleado
- **Punto de venta (POS)** — Venta rápida de servicios y productos, integrado con citas
- **Inventario** — Productos, proveedores, movimientos y stock por sucursal
- **RR.HH.** — Empleados con datos personales/laborales, horarios y comisiones
- **Reportes** — Ventas, citas, comisiones e inventario con filtros avanzados
- **Multi-sucursal** — Dashboard y módulos filtrados por sucursal del usuario
- **Usuarios** — CRUD de cuentas con roles y control de acceso por sucursal

## Requisitos

- PHP 8.3+
- Composer
- Node.js 20+
- PostgreSQL 16+

## Instalación

```bash
# 1. Clonar el repositorio
git clone https://github.com/jeffs95/Barber-shop.git
cd Barber-shop

# 2. Instalar dependencias PHP y JS
composer install
npm install

# 3. Configurar entorno
cp .env.example .env
php artisan key:generate

# 4. Editar .env con los datos de la base de datos
# DB_CONNECTION=pgsql
# DB_HOST=127.0.0.1
# DB_PORT=5432
# DB_DATABASE=barber_shop
# DB_USERNAME=postgres
# DB_PASSWORD=

# 5. Migrar y sembrar datos de prueba
php artisan migrate --seed

# 6. Compilar assets
npm run build

# 7. Levantar servidor
php artisan serve
```

## Desarrollo

```bash
# Servidor completo (Laravel + Vite + Queue + Logs en paralelo)
composer run dev
```

## Acceso al panel

- **URL:** `http://localhost:8000/admin`
- **Credenciales por defecto (seeder):**
  - Dueño: `admin@barberia.com` / `password`
  - Barbero: `barbero@barberia.com` / `password`

## Roles

| Rol | Acceso |
|---|---|
| `dueño` | Acceso total a todas las sucursales |
| `admin_sucursal` | Acceso limitado a su sucursal asignada |
| `recepcionista` | Agenda y POS de su sucursal |

## Licencia

Uso privado — todos los derechos reservados.

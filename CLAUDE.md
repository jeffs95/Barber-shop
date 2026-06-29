# Barber Shop — Contexto del proyecto

## Descripción
Sistema de gestión para una **barbería**: citas, servicios, barberos, clientes y panel administrativo.

## Stack tecnológico
| Capa | Tecnología | Versión |
|---|---|---|
| Backend | PHP / Laravel | 8.3+ / 13.8 |
| Panel admin | Filament | 5.6 |
| CSS | Tailwind CSS | 4.x (vía `@tailwindcss/vite`) |
| Bundler | Vite | 8.x |
| Base de datos | PostgreSQL | local, DB: `barber_shop` |
| Fuente | Instrument Sans | Bunny Fonts |

## Comandos frecuentes
```bash
# Servidor de desarrollo completo (Laravel + Vite + Queue + Logs)
composer run dev

# Solo el servidor PHP
php artisan serve

# Migraciones
php artisan migrate
php artisan migrate:fresh --seed

# Crear un Filament Resource
php artisan make:filament-resource NombreModelo --generate

# Crear modelo + migración + factory + seeder
php artisan make:model NombreModelo -mfs

# Limpiar caché
php artisan optimize:clear
```

## Estructura relevante
```
app/
├── Filament/
│   ├── Resources/      # Recursos Filament (CRUD del panel)
│   ├── Pages/          # Páginas personalizadas
│   └── Widgets/        # Widgets del dashboard
├── Http/Controllers/   # Controladores web (fuera del panel)
├── Models/             # Modelos Eloquent
└── Providers/
    └── Filament/
        └── AdminPanelProvider.php  # Config del panel admin

database/
├── migrations/         # Migraciones de BD
├── seeders/            # Seeders
└── factories/          # Factories para tests/seeds
```

## Panel Filament
- URL: `http://localhost/admin`
- Color primario: Amber
- Auto-descubre resources en `app/Filament/Resources`
- Auto-descubre widgets en `app/Filament/Widgets`
- Auto-descubre páginas en `app/Filament/Pages`

## Convenciones del proyecto
- Modelos en español singular PascalCase: `Cita`, `Empleado`, `Servicio`, `Cliente`
- Tablas en español singular snake_case: `cita`, `empleado`, `servicio`, `cliente`
- Cada modelo debe declarar explícitamente `protected $table = 'nombre_tabla'` (Eloquent pluraliza en inglés por defecto)
- Columnas y relaciones en español snake_case: `fecha_hora`, `precio_base`, `es_activo`
- Booleans siempre con prefijo `es_`: `es_activo`, `es_visible`
- Migraciones siempre con `->comment()` en columnas no obvias
- Seeders para datos de prueba realistas (usar Faker en español: `es_GT`)

## Base de datos
- Motor: PostgreSQL
- Host: `localhost:5432`
- Base: `barber_shop`
- Usuario: `postgres`

## Tablas del dominio (19 tablas, español singular)
| Fase | Tablas |
|---|---|
| 1 — Fundamentos | `usuario`, `empleado`, `horario_empleado`, `cliente` |
| 2 — Catálogo | `categoria_servicio`, `servicio`, `precio_servicio_empleado`, `combo`, `combo_servicio` |
| 3 — Agenda | `cita`, `cita_servicio` |
| 4 — Inventario | `proveedor`, `producto`, `movimiento_inventario` |
| 5 — POS | `caja`, `venta`, `item_venta` |
| 7 — Extras | `membresia`, `cliente_membresia` |

## Estado actual
- [x] Fase 1: Fundamentos (usuario, empleado, horario_empleado, cliente — migraciones + modelos + auth)
- [x] Fase 2: Catálogo (categoria_servicio, servicio, precio_servicio_empleado, combo, combo_servicio — Resources Filament)
- [x] Fase 3: Agenda y citas (cita, cita_servicio — Resource + RelationManager + AgendaHoyWidget)
- [x] Fase 4: Inventario y productos (proveedor, producto, movimiento_inventario — Resources + lógica de stock automático)
- [x] Fase 5: Punto de venta (caja, venta, item_venta — CajaResource + VentaResource + POS custom page /admin/pos)
- [x] Multi-sucursal (sucursal + sucursal_id en empleado/cita/caja/movimiento_inventario — SucursalResource + SucursalesResumenWidget en dashboard)
- [x] Fase 6: Reportes y RR.HH. (ReporteVentas, ReporteCitas, ReporteComisiones, ReporteInventario + HorariosRelationManager en EmpleadoResource)
- [ ] Fase 7: Extras (membresías, puntos, comunicaciones)

### Mejoras (2026-06-28)
- [x] Validación de solapamiento de citas (`Cita::haySolapamiento()` — en formulario, drag&drop del calendario y reserva pública)
- [x] Una caja abierta por sucursal (`Caja::cajaAbiertaDe()` + índice único parcial; selector de sucursal en POS para el dueño)
- [x] Soft deletes en `cliente`, `empleado`, `producto` (+ `ClienteResource` nuevo con historial de citas)
- [x] Página pública de reservas `/reservar` (Livewire `ReservaPublica` + `DisponibilidadService` con slots automáticos; cita queda `pendiente`/`enlace`)
- [x] Landing page pública en `/` (`LandingController` + `landing.blade.php`): hero, servicios por categoría, equipo, sucursales, contacto y "trabaja con nosotros". El asistente de reservas va embebido (`@livewire('reserva-publica', ['embebido' => true])`). Tema oscuro forzado (`<html class="dark">`); textos de contacto/redes son placeholders editables en la vista.
- [x] Bandeja de solicitudes (`SolicitudCitaResource`, /admin/solicitudes): segundo resource sobre el modelo `Cita` que muestra solo `origen=enlace` + `estado=pendiente`, con acciones Aprobar (→confirmada) y Rechazar (→cancelada). `CitaResource` excluye esas mismas para no mezclarlas. Ambos reutilizan `FiltraPorSucursal::aplicarScopesSucursal()` en su `getEloquentQuery`.
- [x] Selector de tema claro/oscuro/sistema en la web pública (landing + /reservar). `app.css` usa `@custom-variant dark (&:where(.dark, .dark *))` → el modo oscuro es **por clase `.dark`** (no por `prefers-color-scheme`); un script anti-flash en el `<head>` aplica la clase según `localStorage['theme']`, y el toggle (`<x-tema-switcher/>` + función JS `themeToggle()`) la cambia. El panel admin NO se afecta (usa `theme.css` y el dark mode propio de Filament).
- [x] Login del panel rediseñado (split-screen + formulario en español): página `App\Filament\Auth\Login` extiende BaseLogin y sobreescribe heading/subheading + los componentes del formulario (`getEmailFormComponent`, `getPasswordFormComponent`, `getRememberFormComponent`, `getAuthenticateFormAction`) para etiquetas en español, íconos prefijo (heroicon-o-envelope / heroicon-o-lock-closed), placeholders y botón "Ingresar al panel". El panel de marca (columna izquierda) se inyecta con `->renderHook(PanelsRenderHook::SIMPLE_LAYOUT_START, ..., scopes: Login::class)` → vista `resources/views/filament/login/branding.blade.php`. El layout de 2 columnas lo arma CSS en `theme.css` (`.fi-simple-layout` → flex row en ≥1024px; el panel se oculta en móvil). La lógica de autenticación de Filament queda intacta.
- La cita hereda `sucursal_id` de su barbero vía hook `saving` en el modelo `Cita`

## Filament v5 — Cambios de API importantes
- `form()` recibe `Filament\Schemas\Schema`, NO `Filament\Forms\Form`
- `Section` es `Filament\Schemas\Components\Section` (no Forms)
- Actions (Edit, Delete, Create, BulkActionGroup) son `Filament\Actions\*`
- `$navigationIcon` type: `string|BackedEnum|null`
- `$navigationGroup` type: `string|UnitEnum|null`
- En páginas custom (`Page`), `$view` es **NO estático**: `protected string $view = '...'`
- **`Get`/`Set` en closures** (reglas, `afterStateUpdated`, etc.) son `Filament\Schemas\Components\Utilities\Get` / `...\Set`, **NO** `Filament\Forms\Get`. Tiparlos con el namespace viejo lanza un `TypeError` en runtime que el linter no detecta.

## Tema CSS de Filament v5 (Tailwind 4 + Vite)
El tema del panel está en `resources/css/filament/admin/theme.css`:
```css
@import 'tailwindcss' source(none);
@import '../../../../vendor/filament/filament/resources/css/index.css';
@source '../../../views/**/*.blade.php';
@source '../../../../app/Filament/**/*.php';
```
- **NO usar** `->viteTheme('resources/css/app.css')` — `app.css` no tiene fuentes de Filament y genera CSS incompleto
- Registrar con `->viteTheme('resources/css/filament/admin/theme.css')` en `AdminPanelProvider`
- Compilar con `npm run build` después de cambios
- `APP_URL=http://localhost:8000` en `.env` (incluir puerto — `asset()` lo usa para generar URLs)

## Notas técnicas
- `config/auth.php` actualizado para usar `App\Models\Usuario`
- Las tablas de infraestructura de Laravel (sessions, cache, jobs) se mantienen en inglés
  porque los drivers internos las referencian por nombre hardcodeado.
- Si se necesita limpiar el caché de config: `php artisan config:clear`

## Notas importantes
- `APP_NAME="Barber Shop"` y `APP_URL=http://localhost:8000` ya configurados en `.env`.
- `SESSION_DRIVER=database` y `QUEUE_CONNECTION=database` — correr migraciones antes de levantar.
- Correr `npm install` antes del primer `npm run dev` en un entorno nuevo.

# 🍽️ RestaurantChain — Sistema de Gestión de Productos

Sistema MVC completo construido con **Laravel 11** + **MySQL** + **Bootstrap 5** para la gestión de productos, categorías, inventario y sucursales de cadenas de restaurantes.

---

## 🚀 Instalación Rápida

### 1. Requisitos
- PHP 8.2+
- Composer
- MySQL 8+

### 2. Configurar base de datos

```sql
CREATE DATABASE restaurante_db;
```

### 3. Configurar `.env`

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=restaurante_db
DB_USERNAME=root
DB_PASSWORD=tu_password   ← CAMBIAR ESTO
```

### 4. Ejecutar migraciones y seeders

```bash
php artisan migrate
php artisan db:seed        # Carga datos de prueba
php artisan storage:link   # Ya ejecutado
```

### 5. Iniciar servidor

```bash
php artisan serve
```

Visita: **http://localhost:8000**

---

## 📦 Módulos

| Módulo | Ruta | Descripción |
|--------|------|-------------|
| Productos | `/products` | CRUD + toggle activo/inactivo + imagen |
| Categorías | `/categories` | CRUD con jerarquía padre/hijo |
| Inventario | `/inventory` | Stock consolidado por sucursal |
| Movimientos | `/inventory/movements` | Historial con filtros |
| Ajuste Stock | `/inventory/adjust` | Entrada/Salida/Ajuste con transacción DB |
| Stock Bajo | `/inventory/low-stock` | Alertas de productos bajo mínimo |
| Reporte | `/inventory/report` | Reporte por período |
| Sucursales | `/branches` | CRUD + geolocalización |
| Sucursal Productos | `/branches/{id}/products` | Asignación de productos y stock |

---

## 🗄️ Base de Datos

- **categories** — Categorías con auto-referencia padre/hijo
- **products** — Productos con SKU único, soft delete
- **branches** — Sucursales con lat/lng, soft delete  
- **branch_product** — Tabla pivot: stock y disponibilidad por sucursal
- **inventory_movements** — Historial de movimientos (in/out/adjust/transfer)

---

## ✅ Características

- SoftDeletes en Productos, Categorías y Sucursales
- Transacciones DB en ajustes de inventario
- Validaciones completas en todos los formularios
- Upload de imágenes para productos
- Paginación Bootstrap 5 en todos los listados
- Filtros combinables en cada módulo
- Sidebar responsivo con indicadores de ruta activa
- Alertas de sesión (success/error)

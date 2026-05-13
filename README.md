# ChatCenter CMS

Sistema de gestión de contenido (CMS) con API RESTful propia, diseñado para administrar páginas dinámicas, módulos y usuarios con distintos niveles de acceso.

---

## Tecnologías

**Backend**
- PHP 8
- MySQL
- PDO
- JWT (JSON Web Tokens)
- API RESTful propia

**Frontend**
- Bootstrap 5
- jQuery
- SweetAlert2
- Summernote (editor de texto)
- Chart.js
- Select2

---

## Estructura del proyecto

```
chatcenter/
├── api/              # API RESTful
│   ├── controllers/  # Lógica CRUD (GET, POST, PUT, DELETE)
│   ├── models/       # Conexión a base de datos y queries
│   └── routes/       # Definición de rutas y servicios
├── cms/              # Panel de administración
│   ├── controllers/  # Controladores del CMS
│   ├── views/        # Templates, assets (CSS, JS, plugins)
│   └── ajax/         # Peticiones asíncronas
└── .env.example      # Variables de entorno requeridas
```

---

## Instalación

### 1. Clonar el repositorio

```bash
git clone https://github.com/tu-usuario/chatcenter.git
cd chatcenter
```

### 2. Instalar dependencias

```bash
# Dependencias de la API
cd api
composer install

# Dependencias del CMS
cd ../cms/extensions
composer install
```

### 3. Configurar variables de entorno

```bash
cp .env.example .env
```

Edita el `.env` con tus datos:

```env
DB_DATABASE=chatcenter
DB_USER=tu_usuario
DB_PASS=tu_contraseña

API_URL=http://tu-dominio.com
API_KEY=tu_api_key

JWT_SECRET=tu_clave_secreta
```

### 4. Crear la base de datos

Crea una base de datos MySQL con el nombre que pusiste en `DB_DATABASE` y ejecuta el instalador desde el navegador.

### 5. Configurar el servidor web

Apunta el servidor web a la carpeta del proyecto. Ejemplo con Nginx:

```nginx
server {
    listen 80;
    server_name tu-dominio.com;
    root /ruta/chatcenter/cms;
    index index.php;
}
```

---

## Roles de usuario

| Rol | Permisos |
|---|---|
| `superadmin` | Acceso total, gestión de páginas y módulos |
| `admin` | Acceso a todas las páginas |
| `editor` | Acceso solo a páginas asignadas |

---

## Variables de entorno

| Variable | Descripción |
|---|---|
| `DB_DATABASE` | Nombre de la base de datos |
| `DB_USER` | Usuario de MySQL |
| `DB_PASS` | Contraseña de MySQL |
| `API_URL` | URL base de la API |
| `API_KEY` | Clave de autenticación de la API |
| `JWT_SECRET` | Clave secreta para firmar tokens JWT |

---

## Licencia

MIT

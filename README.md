# Sistema Administrativo de Gestión de Contenidos

Portal de Noticias con tecnología **PHP + MySQL**

## 📋 Descripción

Sistema profesional de gestión de contenidos (CMS) diseñado para portales de noticias. Incluye autenticación segura, gestión de usuarios con roles, categorías jerárquicas, workflow editorial, y más.

## ✨ Características Principales

### Módulos Implementados

1. **✅ Autenticación y Seguridad**
   - Login con usuario y contraseña
   - Password hash seguro (password_hash)
   - Gestión de sesiones
   - Bloqueo por intentos fallidos
   - Registro de accesos (logs)
   - Cierre de sesión seguro

2. **✅ Usuarios y Roles**
   - CRUD completo de usuarios
   - Sistema de roles y permisos
   - 6 roles predefinidos:
     - Super Administrador
     - Editor General
     - Editor de Sección
     - Redactor
     - Colaborador
     - Administrador Técnico
   - Activación/desactivación de cuentas
   - Historial de actividad

3. **✅ Categorías y Secciones**
   - Gestión de categorías
   - Soporte para subcategorías
   - Organización jerárquica
   - Control de visibilidad
   - Asignación de editores responsables
   - Conteo de noticias por categoría

4. **✅ Gestión de Noticias**
   - CRUD completo de noticias
   - Campos: título, subtítulo, contenido, autor, categoría, imagen
   - Estados del workflow: borrador, revisión, aprobado, publicado, rechazado, archivado
   - Contenido destacado
   - Sistema de slugs automáticos
   - Versionado de contenido
   - Contador de visitas

5. **✅ Dashboard Principal**
   - Estadísticas en tiempo real
   - Noticias recientes
   - Noticias más leídas
   - Acciones rápidas
   - Interfaz responsiva con Tailwind CSS

6. **✅ Logs y Auditoría**
   - Registro de accesos al sistema
   - Logs de acciones administrativas
   - Trazabilidad completa

### Módulos Base Implementados

7. **📦 Multimedia** (Estructura base)
8. **📦 Configuración** (Estructura base)
9. **📦 SEO y Metadatos** (Tablas creadas)
10. **📦 Comentarios** (Tablas creadas)
11. **📦 Banners** (Tablas creadas)

## 🛠️ Tecnologías

- **Backend:** PHP 7.4+ (sin framework)
- **Base de Datos:** MySQL 5.7+
- **Frontend:** HTML5, Tailwind CSS, JavaScript
- **Iconos:** Font Awesome 6
- **Arquitectura:** MVC (Model-View-Controller)

## 📦 Requisitos

- PHP 7.4 o superior
- MySQL 5.7 o superior
- Apache 2.4+ con mod_rewrite habilitado
- Extensiones PHP:
  - PDO
  - pdo_mysql
  - mbstring
  - session

## 🚀 Instalación

### Paso 1: Clonar o Descargar el Repositorio

```bash
git clone https://github.com/danjohn007/GestorContenidos.git
cd GestorContenidos
```

### Paso 2: Configurar el Servidor Apache

Coloca el proyecto en el directorio de tu servidor Apache:
- **XAMPP:** `C:\xampp\htdocs\GestorContenidos`
- **WAMP:** `C:\wamp64\www\GestorContenidos`
- **Linux:** `/var/www/html/GestorContenidos`

O configura un Virtual Host para un dominio personalizado.

### Paso 3: Crear la Base de Datos

1. Accede a phpMyAdmin o tu cliente MySQL preferido
2. Crea una nueva base de datos:

```sql
CREATE DATABASE gestor_contenidos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

3. Importa el archivo `database.sql`:
   - Desde phpMyAdmin: Importar → Seleccionar archivo `database.sql`
   - Desde línea de comandos:

```bash
mysql -u root -p gestor_contenidos < database.sql
```

### Paso 4: Configurar Credenciales de Base de Datos

Edita el archivo `config/config.php` con tus credenciales:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'gestor_contenidos');
define('DB_USER', 'root');
define('DB_PASS', ''); // Tu contraseña de MySQL
```

### Paso 5: Configurar Permisos

Asegúrate de que Apache tenga permisos de escritura en:

```bash
chmod -R 755 public/uploads
```

### Paso 6: Verificar la Instalación

Accede al archivo de test para verificar la configuración:

```
http://localhost/GestorContenidos/test.php
```

Este archivo verificará:
- ✅ Conexión a la base de datos
- ✅ URL base detectada correctamente
- ✅ Tablas creadas
- ✅ Extensiones PHP

### Paso 7: Acceder al Sistema

**URL de acceso:**
```
http://localhost/GestorContenidos/login.php
```

**Credenciales por defecto:**
- **Email:** admin@gestorcontenidos.mx
- **Contraseña:** admin123

> ⚠️ **IMPORTANTE:** Cambia la contraseña del administrador después del primer acceso.

## 📁 Estructura del Proyecto

```
GestorContenidos/
├── app/
│   ├── controllers/          # Controladores (MVC)
│   ├── models/              # Modelos de datos
│   │   ├── Usuario.php
│   │   ├── Noticia.php
│   │   └── Categoria.php
│   └── views/               # Vistas
│       ├── layouts/         # Plantillas base
│       ├── auth/            # Vistas de autenticación
│       ├── dashboard/       # Dashboard
│       ├── noticias/        # Gestión de noticias
│       ├── categorias/      # Gestión de categorías
│       └── usuarios/        # Gestión de usuarios
├── config/
│   ├── config.php           # Configuración general
│   ├── Database.php         # Clase de conexión
│   └── bootstrap.php        # Inicialización del sistema
├── public/
│   ├── css/                 # Estilos personalizados
│   ├── js/                  # JavaScript
│   ├── img/                 # Imágenes
│   └── uploads/             # Archivos subidos
├── .htaccess                # Configuración Apache
├── database.sql             # Script de base de datos
├── test.php                 # Test de conexión
├── login.php                # Página de login
├── logout.php               # Cierre de sesión
├── index.php                # Dashboard principal
├── noticias.php             # Listado de noticias
├── categorias.php           # Listado de categorías
├── usuarios.php             # Listado de usuarios
└── README.md                # Este archivo
```

## 🔐 Seguridad

- ✅ Contraseñas hasheadas con `password_hash()`
- ✅ Protección contra SQL Injection (PDO con prepared statements)
- ✅ Escape de HTML (función `e()`)
- ✅ Sesiones seguras con regeneración de ID
- ✅ Control de intentos de login fallidos
- ✅ Logs de acceso y auditoría
- ✅ Protección de archivos de configuración vía .htaccess

## 🎨 Personalización

### Cambiar Colores del Sistema

Edita las variables en `config/config.php` o en la base de datos tabla `configuracion`:

```php
'color_primario' => '#1e40af'    // Color principal
'color_secundario' => '#3b82f6'  // Color secundario
```

### Configurar Zona Horaria

En `config/config.php`:

```php
date_default_timezone_set('America/Mexico_City');
```

## 📊 Base de Datos

El sistema incluye datos de ejemplo para el estado de Querétaro:
- Categorías: Política, Economía, Seguridad, Cultura, Deportes, Turismo, Educación, Salud
- Subcategorías por municipio
- Usuario administrador predefinido
- Configuraciones iniciales

## 🔄 URL Amigables

El sistema incluye `.htaccess` configurado para URLs amigables. Asegúrate de que `mod_rewrite` esté habilitado en Apache.

## 🌐 Navegadores Compatibles

- ✅ Chrome/Edge (último)
- ✅ Firefox (último)
- ✅ Safari (último)
- ✅ Diseño responsivo para móviles

## 📝 Datos de Ejemplo

El sistema incluye:
- 1 usuario administrador
- 11 categorías del estado de Querétaro
- 1 noticia de bienvenida
- Configuraciones predeterminadas

## 🐛 Solución de Problemas

### Error de Conexión a Base de Datos
- Verifica que MySQL esté corriendo
- Revisa las credenciales en `config/config.php`
- Asegúrate de que la base de datos exista

### Error 500 - Internal Server Error
- Verifica que `mod_rewrite` esté habilitado
- Revisa los permisos de archivos y carpetas
- Verifica el log de errores de Apache

### Las URLs no funcionan (404)
- Verifica que `.htaccess` esté presente
- Asegúrate de que `AllowOverride All` esté configurado en Apache
- Habilita `mod_rewrite` en Apache

### Problemas con Sesiones
- Verifica permisos en el directorio temporal de PHP
- Revisa la configuración de sesiones en `php.ini`

## 🚧 Desarrollo Futuro

Módulos planeados para futuras versiones:
- Editor WYSIWYG completo
- Gestión de multimedia con galería
- Sistema de comentarios con moderación
- SEO avanzado y sitemap automático
- Estadísticas y analytics
- Sistema de notificaciones
- API REST
- Multi-idioma

## 📄 Licencia

Este proyecto es de código abierto y está disponible bajo licencia MIT.

## 👨‍💻 Soporte

Para reportar problemas o sugerencias:
- Crear un issue en GitHub
- Email: admin@gestorcontenidos.mx

## 📸 Capturas de Pantalla

### Login
![Login](docs/login.png)

### Dashboard
![Dashboard](docs/dashboard.png)

### Gestión de Noticias
![Noticias](docs/noticias.png)

---

**Desarrollado con ❤️ para la comunidad de Querétaro**

Sistema Administrativo de Gestión de Contenidos v1.0.0

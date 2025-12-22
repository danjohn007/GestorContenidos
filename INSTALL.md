# Guía Completa de Instalación

## Sistema de Gestión de Contenidos - Portal de Noticias

### 📋 Tabla de Contenidos
1. [Requisitos del Sistema](#requisitos-del-sistema)
2. [Instalación Paso a Paso](#instalación-paso-a-paso)
3. [Configuración](#configuración)
4. [Verificación](#verificación)
5. [Solución de Problemas](#solución-de-problemas)
6. [Primeros Pasos](#primeros-pasos)

---

## Requisitos del Sistema

### Software Necesario
- **PHP**: 7.4 o superior
- **MySQL**: 5.7 o superior (o MariaDB 10.2+)
- **Apache**: 2.4 o superior
- **Sistema Operativo**: Windows, Linux, o macOS

### Extensiones PHP Requeridas
- PDO
- pdo_mysql
- mbstring
- session
- json

### Verificar Extensiones PHP
```bash
php -m | grep -E 'PDO|pdo_mysql|mbstring|session|json'
```

---

## Instalación Paso a Paso

### Opción 1: Instalación Local (XAMPP/WAMP)

#### 1. Descargar e Instalar XAMPP
1. Descarga XAMPP desde [https://www.apachefriends.org](https://www.apachefriends.org)
2. Instala XAMPP en tu computadora
3. Inicia Apache y MySQL desde el panel de control de XAMPP

#### 2. Clonar el Proyecto
```bash
cd C:\xampp\htdocs
git clone https://github.com/danjohn007/GestorContenidos.git
```

O descarga el ZIP y extráelo en `C:\xampp\htdocs\GestorContenidos`

#### 3. Crear la Base de Datos
1. Abre tu navegador y ve a: `http://localhost/phpmyadmin`
2. Haz clic en "Nueva" para crear una base de datos
3. Nombre: `gestor_contenidos`
4. Cotejamiento: `utf8mb4_unicode_ci`
5. Haz clic en "Crear"

#### 4. Importar el Schema
1. Selecciona la base de datos `gestor_contenidos`
2. Haz clic en la pestaña "Importar"
3. Haz clic en "Elegir archivo"
4. Selecciona el archivo `database.sql` del proyecto
5. Haz clic en "Continuar"

#### 5. Configurar Credenciales
Edita el archivo `config/config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'gestor_contenidos');
define('DB_USER', 'root');
define('DB_PASS', ''); // Déjalo vacío para XAMPP por defecto
```

#### 6. Verificar Permisos
En Windows, XAMPP maneja los permisos automáticamente.
En Linux/Mac:
```bash
chmod -R 755 /opt/lampp/htdocs/GestorContenidos
chmod -R 777 /opt/lampp/htdocs/GestorContenidos/public/uploads
```

#### 7. Acceder al Sistema
Abre tu navegador y ve a:
```
http://localhost/GestorContenidos/test.php
```

Si todo está correcto, deberías ver la página de verificación con ✅ en verde.

---

### Opción 2: Instalación en Servidor Linux

#### 1. Instalar Dependencias
```bash
# Ubuntu/Debian
sudo apt update
sudo apt install apache2 mysql-server php php-mysql php-mbstring php-json

# CentOS/RHEL
sudo yum install httpd mariadb-server php php-mysqlnd php-mbstring php-json
```

#### 2. Configurar Apache
```bash
sudo nano /etc/apache2/sites-available/gestor.conf
```

Contenido:
```apache
<VirtualHost *:80>
    ServerName cms.tudominio.com
    DocumentRoot /var/www/html/GestorContenidos
    
    <Directory /var/www/html/GestorContenidos>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/gestor_error.log
    CustomLog ${APACHE_LOG_DIR}/gestor_access.log combined
</VirtualHost>
```

Habilitar el sitio:
```bash
sudo a2ensite gestor.conf
sudo a2enmod rewrite
sudo systemctl restart apache2
```

#### 3. Clonar el Proyecto
```bash
cd /var/www/html
sudo git clone https://github.com/danjohn007/GestorContenidos.git
sudo chown -R www-data:www-data GestorContenidos
```

#### 4. Configurar MySQL
```bash
sudo mysql -u root -p
```

```sql
CREATE DATABASE gestor_contenidos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'cms_user'@'localhost' IDENTIFIED BY 'tu_password_seguro';
GRANT ALL PRIVILEGES ON gestor_contenidos.* TO 'cms_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

#### 5. Importar Base de Datos
```bash
sudo mysql -u root -p gestor_contenidos < /var/www/html/GestorContenidos/database.sql
```

#### 6. Configurar Credenciales
```bash
sudo nano /var/www/html/GestorContenidos/config/config.php
```

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'gestor_contenidos');
define('DB_USER', 'cms_user');
define('DB_PASS', 'tu_password_seguro');
```

#### 7. Configurar Permisos
```bash
sudo chown -R www-data:www-data /var/www/html/GestorContenidos
sudo chmod -R 755 /var/www/html/GestorContenidos
sudo chmod -R 777 /var/www/html/GestorContenidos/public/uploads
```

---

## Configuración

### Archivo .htaccess
El archivo `.htaccess` ya está incluido. Verifica que contenga:

```apache
RewriteEngine On
RewriteBase /

# Proteger archivos de configuración
<FilesMatch "^(config\.php|\.env)$">
    Order allow,deny
    Deny from all
</FilesMatch>
```

### Configuración de PHP (Opcional)
Para mejor rendimiento, ajusta en `php.ini`:

```ini
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
memory_limit = 256M
```

### Zona Horaria
En `config/config.php`:
```php
date_default_timezone_set('America/Mexico_City');
```

Zonas horarias disponibles: [PHP Timezones](https://www.php.net/manual/en/timezones.php)

---

## Verificación

### 1. Test de Configuración
Accede a:
```
http://localhost/GestorContenidos/test.php
```

Verifica que aparezca:
- ✅ Conexión Exitosa
- ✅ Tablas Encontradas
- ✅ URL Base Detectada

### 2. Acceder al Login
```
http://localhost/GestorContenidos/login.php
```

### 3. Credenciales por Defecto
```
Email: admin@gestorcontenidos.mx
Contraseña: admin123
```

⚠️ **IMPORTANTE**: Cambia esta contraseña inmediatamente después del primer acceso.

### 4. Verificar Dashboard
Después de iniciar sesión, deberías ver:
- Estadísticas del sistema
- Noticias recientes
- Acciones rápidas

---

## Solución de Problemas

### Error: "No such file or directory"
**Problema**: No se puede conectar a MySQL

**Solución**:
```bash
# Verificar que MySQL esté corriendo
sudo systemctl status mysql

# Si no está corriendo, iniciarlo
sudo systemctl start mysql
```

### Error: "Access denied for user"
**Problema**: Credenciales de base de datos incorrectas

**Solución**:
1. Verifica el usuario y contraseña en `config/config.php`
2. Verifica los permisos en MySQL:
```sql
SHOW GRANTS FOR 'tu_usuario'@'localhost';
```

### Error 500 - Internal Server Error
**Problema**: Configuración de Apache o permisos

**Solución**:
```bash
# Verificar que mod_rewrite esté habilitado
sudo a2enmod rewrite

# Verificar permisos
sudo chown -R www-data:www-data /var/www/html/GestorContenidos

# Ver logs de error
sudo tail -f /var/log/apache2/error.log
```

### Error: "404 Not Found" en URLs
**Problema**: mod_rewrite no está habilitado

**Solución**:
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

Verificar `.htaccess` existe y AllowOverride está configurado.

### Error: "Call to undefined function password_hash"
**Problema**: Versión de PHP antigua

**Solución**:
Actualiza a PHP 7.4 o superior:
```bash
sudo apt install php7.4
```

### Páginas sin Estilos
**Problema**: Tailwind CSS no carga (CDN bloqueado)

**Solución**:
1. Verifica tu conexión a internet
2. Verifica que no haya bloqueadores de contenido
3. Para producción, considera descargar Tailwind localmente

---

## Primeros Pasos

### 1. Cambiar Contraseña de Administrador
1. Ve a `Usuarios` en el menú
2. Edita el usuario administrador
3. Ingresa una nueva contraseña segura
4. Guarda los cambios

### 2. Crear Categorías
1. Ve a `Categorías`
2. Haz clic en "Nueva Categoría"
3. Ingresa el nombre (ej: "Deportes")
4. Guarda

### 3. Crear Tu Primera Noticia
1. Ve a `Noticias`
2. Haz clic en "Nueva Noticia"
3. Completa el formulario:
   - Título
   - Contenido
   - Categoría
4. Selecciona el estado (Borrador/Publicado)
5. Guarda

### 4. Crear Usuarios Adicionales
1. Ve a `Usuarios`
2. Haz clic en "Nuevo Usuario"
3. Completa los datos
4. Asigna un rol apropiado
5. Guarda

### 5. Configurar el Sistema
1. Ve a `Configuración`
2. Ajusta:
   - Nombre del sitio
   - Email del sistema
   - Redes sociales
   - Otros parámetros

---

## Recursos Adicionales

### Documentación
- [README.md](README.md) - Documentación principal
- [SECURITY.md](SECURITY.md) - Guía de seguridad
- [PHP Manual](https://www.php.net/manual/es/)
- [MySQL Documentation](https://dev.mysql.com/doc/)

### Soporte
- Issues: [GitHub Issues](https://github.com/danjohn007/GestorContenidos/issues)
- Email: admin@gestorcontenidos.mx

### Videos Tutoriales (Próximamente)
- Instalación en XAMPP
- Configuración básica
- Gestión de noticias
- Administración de usuarios

---

## Actualizaciones

Para actualizar el sistema:

```bash
cd /ruta/al/proyecto
git pull origin main
# Si hay cambios en la base de datos, ejecutar scripts de migración
```

---

**¡Listo!** Tu sistema de gestión de contenidos está configurado y funcionando. 

Para cualquier duda o problema, consulta la sección de [Solución de Problemas](#solución-de-problemas) o contacta al soporte.

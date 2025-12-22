# Guía de Contribución

¡Gracias por tu interés en contribuir al Sistema de Gestión de Contenidos! Esta guía te ayudará a empezar.

## 🤝 Cómo Contribuir

### Reportar Bugs
1. Verifica que el bug no haya sido reportado antes
2. Crea un nuevo issue con:
   - Título descriptivo
   - Pasos para reproducir el error
   - Comportamiento esperado vs actual
   - Versión de PHP y MySQL
   - Capturas de pantalla si aplica

### Sugerir Mejoras
1. Abre un issue con la etiqueta "enhancement"
2. Describe claramente la mejora propuesta
3. Explica por qué sería útil
4. Proporciona ejemplos si es posible

### Contribuir Código

#### 1. Fork del Repositorio
```bash
git clone https://github.com/tu-usuario/GestorContenidos.git
cd GestorContenidos
```

#### 2. Crear una Rama
```bash
git checkout -b feature/nueva-funcionalidad
# o
git checkout -b fix/correccion-bug
```

#### 3. Hacer Cambios
- Sigue las convenciones de código
- Añade comentarios cuando sea necesario
- Actualiza la documentación si aplica

#### 4. Commit
```bash
git add .
git commit -m "Add: descripción clara del cambio"
```

#### 5. Push y Pull Request
```bash
git push origin feature/nueva-funcionalidad
```

Luego crea un Pull Request en GitHub.

## 📝 Convenciones de Código

### PHP
- PSR-12 para estilo de código
- Nombres de clases en PascalCase
- Nombres de métodos en camelCase
- Nombres de variables en snake_case
- Comentarios en español o inglés

```php
<?php
/**
 * Descripción breve de la clase
 */
class MiClase {
    private $mi_variable;
    
    /**
     * Descripción del método
     * @param string $parametro
     * @return bool
     */
    public function miMetodo($parametro) {
        // Código aquí
        return true;
    }
}
```

### SQL
- Nombres de tablas en minúsculas
- Usar snake_case para columnas
- Siempre usar PDO prepared statements
- Comentar queries complejas

### HTML/CSS
- Indentación de 4 espacios
- Usar clases de Tailwind CSS
- Mantener accesibilidad (alt, aria-labels)

## 🧪 Testing

Antes de enviar un PR:
1. Prueba tu código localmente
2. Verifica que no hay errores PHP
3. Asegúrate de que la base de datos funciona
4. Prueba en diferentes navegadores si es UI

## 📚 Áreas de Contribución

### Módulos Pendientes
- [ ] Editor WYSIWYG completo
- [ ] Gestión de multimedia con galería
- [ ] Sistema de comentarios con moderación
- [ ] SEO avanzado y sitemap automático
- [ ] Estadísticas detalladas
- [ ] Sistema de notificaciones
- [ ] API REST
- [ ] Multi-idioma

### Mejoras Técnicas
- [ ] CSRF protection
- [ ] Rate limiting
- [ ] Cache de contenidos
- [ ] Optimización de queries
- [ ] Tests unitarios
- [ ] Tests de integración

### Documentación
- [ ] Videos tutoriales
- [ ] Ejemplos de uso
- [ ] Guías avanzadas
- [ ] Traducción a inglés

## 🏷️ Etiquetas de Commit

- `Add:` Nueva funcionalidad
- `Fix:` Corrección de bugs
- `Update:` Actualización de código existente
- `Remove:` Eliminación de código
- `Refactor:` Refactorización sin cambio de funcionalidad
- `Docs:` Cambios en documentación
- `Style:` Cambios de formato (no afectan funcionalidad)
- `Test:` Añadir o modificar tests

## ⚖️ Licencia

Al contribuir, aceptas que tu código se distribuirá bajo la misma licencia MIT del proyecto.

## 📞 Contacto

- Issues: [GitHub Issues](https://github.com/danjohn007/GestorContenidos/issues)
- Email: admin@gestorcontenidos.mx

¡Gracias por contribuir! 🎉

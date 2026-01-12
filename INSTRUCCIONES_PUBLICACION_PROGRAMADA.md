# Instrucciones para Publicación Automática de Noticias Programadas

## Descripción
El sistema incluye un script `publicar_programadas.php` que publica automáticamente las noticias que han sido programadas para publicación en una fecha/hora específica.

## Configuración del Cron Job

Para que las noticias programadas se publiquen automáticamente, es necesario configurar un cron job en el servidor que ejecute el script periódicamente.

### Opción 1: Ejecución cada 15 minutos (Recomendado)

Agregue la siguiente línea al crontab del servidor:

```bash
*/15 * * * * /usr/bin/php /ruta/completa/al/proyecto/publicar_programadas.php >> /var/log/publicador.log 2>&1
```

### Opción 2: Ejecución cada 5 minutos (Para mayor precisión)

```bash
*/5 * * * * /usr/bin/php /ruta/completa/al/proyecto/publicar_programadas.php >> /var/log/publicador.log 2>&1
```

### Opción 3: Ejecución cada hora

```bash
0 * * * * /usr/bin/php /ruta/completa/al/proyecto/publicar_programadas.php >> /var/log/publicador.log 2>&1
```

## Pasos para Configurar en Linux/Unix

1. Abrir el editor de crontab:
   ```bash
   crontab -e
   ```

2. Agregar una de las líneas anteriores (reemplazando `/ruta/completa/al/proyecto/` con la ruta real)

3. Guardar y cerrar el editor

4. Verificar que el cron job está activo:
   ```bash
   crontab -l
   ```

## Pasos para Configurar en cPanel

1. Ingresar al panel de control de cPanel
2. Buscar "Cron Jobs" o "Tareas Cron"
3. En "Agregar tarea cron":
   - **Minuto**: */15 (para cada 15 minutos)
   - **Hora**: * (todas las horas)
   - **Día**: * (todos los días)
   - **Mes**: * (todos los meses)
   - **Día de la semana**: * (todos los días de la semana)
   - **Comando**: `/usr/bin/php /home/usuario/public_html/publicar_programadas.php`
4. Hacer clic en "Agregar tarea cron"

## Verificación Manual

Para probar que el script funciona correctamente, puede ejecutarlo manualmente desde el navegador:

```
https://sudominio.com/publicar_programadas.php
```

Nota: Debe estar autenticado en el sistema para poder ejecutarlo desde el navegador.

## Logs

El script genera logs que se pueden revisar para verificar su ejecución:

- Si está configurado el cron job, los logs se guardan en `/var/log/publicador.log`
- También se pueden ver logs en tiempo real ejecutando:
  ```bash
  tail -f /var/log/publicador.log
  ```

## Funcionamiento

1. El script busca noticias que cumplan las siguientes condiciones:
   - Estado: `publicado`
   - `fecha_programada` no es NULL
   - `fecha_programada` es menor o igual a la fecha/hora actual
   - `fecha_publicacion` es NULL (no han sido publicadas todavía)

2. Para cada noticia encontrada:
   - Establece `fecha_publicacion` a la fecha/hora actual
   - Registra la acción en el log de auditoría del sistema

3. Muestra un resumen de las noticias publicadas y las próximas programadas

## Solución de Problemas

### El script no se ejecuta
- Verificar que el cron job esté configurado correctamente
- Verificar que la ruta al script sea correcta
- Verificar permisos de ejecución del archivo

### Las noticias no se publican
- Verificar que las noticias estén en estado "publicado" con fecha_programada
- Verificar que fecha_publicacion sea NULL
- Revisar los logs para ver si hay errores

### Permisos insuficientes
Asegurar que el archivo tenga permisos de lectura/ejecución:
```bash
chmod 755 publicar_programadas.php
```

## Recomendaciones

1. Configurar el cron job para ejecutarse cada 15 minutos como máximo
2. Monitorear los logs regularmente para detectar problemas
3. Al programar noticias, establecer horarios que coincidan con los intervalos del cron (por ejemplo, :00, :15, :30, :45)

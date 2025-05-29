# SIGI - Sistema de Gestión Institucional

Este es el repositorio base para el Sistema de Gestión Institucional (SIGI) para Institutos Superiores del Perú.

## Estructura del Proyecto

- `app/`: Contiene la lógica de la aplicación (Modelos, Vistas, Controladores, Configuración).
- `public/`: Contiene el Front Controller (`index.php`), assets públicos (CSS, JS, imágenes).
- `vendor/`: Dependencias de Composer.
- `.env`: Archivo de configuración de variables de entorno (no versionado).

## Instalación

1.  Clona este repositorio o crea la estructura de carpetas manualmente.
2.  Navega a la raíz del proyecto (`sigi/`) en tu terminal.
3.  Ejecuta `composer install` para instalar las dependencias de PHP.
4.  Copia `.env.example` a `.env` y configura tus credenciales de base de datos y la clave de la aplicación.
5.  Configura tu servidor web (Apache/Nginx) para que el `DocumentRoot` apunte a la carpeta `public/`.
    Alternativamente, puedes usar el servidor PHP integrado para desarrollo:
    `php -S localhost:8000 -t public`
// 6.  Importa el esquema de la base de datos (archivos `.sql` que se proporcionarán).

## Contribución

Se agradecen las contribuciones. Por favor, abre un issue o un pull request.

## Licencia

Este proyecto está licenciado bajo la licencia MIT.

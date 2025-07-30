# Chat Encuesta

Este proyecto contiene un ejemplo sencillo de chat en PHP que utiliza la API de OpenAI y MySQL. Incluye scripts de registro, inicio de sesión y una interfaz de chat donde el usuario puede seleccionar tema claro u oscuro y un color de acento.

## Archivos principales
- `db.php`: conexión PDO a la base de datos `marhar345_merlin`.
- `schema.sql`: script para crear la estructura de tablas.
- `register.php` / `login.php` / `logout.php`: autenticación básica.
- `chat.php`: interfaz conversacional que almacena cada mensaje y consulta la API de OpenAI.
- `openai.php`: función helper para comunicarse con la API.

Antes de usar la aplicación, importa `schema.sql` en tu base de datos (por ejemplo, con phpMyAdmin) y configura las credenciales en `db.php`.

Para que la API funcione necesitas definir la variable de entorno `OPENAI_API_KEY` con tu clave privada.

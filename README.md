# Chat Encuesta

Este proyecto contiene un ejemplo sencillo de chat en PHP que utiliza la API de OpenAI y MySQL. Incluye scripts de registro, inicio de sesión y una interfaz de chat donde el usuario puede seleccionar tema claro u oscuro y un color de acento.

## Archivos principales
- `db.php`: conexión PDO a la base de datos `marhar345_merlin`.
- `schema.sql`: script para crear la estructura de tablas.
- `register.php` / `login.php` / `logout.php`: autenticación básica.
- `admin.php`: panel protegido para gestionar usuarios y preguntas.
- `chat.php`: interfaz conversacional que almacena cada mensaje y consulta la API de OpenAI.
- `openai.php`: función helper para comunicarse con la API.

Antes de usar la aplicación, importa `schema.sql` en tu base de datos (por ejemplo, con phpMyAdmin) y configura las credenciales en `db.php`.

Para que la API funcione necesitas definir la variable de entorno `OPENAI_API_KEY` con tu clave privada.

## Preguntas iniciales y prompts
`prompts.php` contiene las instrucciones y preguntas base que se envían a la API cuando un usuario inicia la conversación. Puedes ejecutar `php prompts.php` para mostrarlas por consola.
`init_prompts.php` carga esas preguntas en la tabla `preguntas_admin` si quieres mantener un registro en la base de datos.

Ejecuta `php init_prompts.php` una vez para pre-cargar las preguntas.

Para acceder al panel de administración (`admin.php`) crea un usuario con la
columna `es_admin` establecida en `1` dentro de la tabla `usuarios`.

## Privacidad y gestión de datos

Todos los mensajes intercambiados en el chat se guardan en la base de datos y se
envían a la API de OpenAI para generar las respuestas. La información de perfil
de los usuarios también se almacena con el fin de mantener la conversación y el
historial.

Cada usuario puede actualizar o eliminar sus datos personales desde la página
`profile.php` una vez iniciada la sesión. Si se elimina la cuenta, se borran su
perfil, preferencias y conversaciones asociadas.

# Tarea-1.2---Extension-de-API-OSTICKET

# osTicket API Usuarios

Este proyecto es una extensión de la API de osTicket, que añade funcionalidad para crear usuarios mediante solicitudes HTTP. La funcionalidad permite que el sistema acepte peticiones que contengan información de usuarios y los registre en la base de datos, incluyendo la creación de contraseñas.

## Características

- **Creación de usuarios a través de la API**: Permite crear un nuevo usuario proporcionando los datos necesarios como nombre completo, correo electrónico y contraseña.
- **Validación de datos**: Asegura que todos los datos necesarios sean válidos y estén presentes.
- **Manejo de errores detallado**: Devuelve mensajes claros y específicos para los diferentes casos de error, como email ya existente, validación fallida, etc.
- **Autenticación mediante API Key**: Protege la API mediante una clave única.

## Requisitos

- osTicket v1.18.1 (o superior).
- Servidor web con PHP (se recomienda usar [XAMPP](https://www.apachefriends.org/index.html) para entorno local).
- Clave API generada en el panel de administración de osTicket.

## Instalación


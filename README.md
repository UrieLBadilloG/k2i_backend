
## Requisitos del Proyecto

1. **Login en Laravel con OAuth2**, debidamente validado.
2. **Carga masiva de datos** desde un archivo Excel a la base de datos.
3. **Procedimientos almacenados** para migrar datos desde una tabla temporal a tablas estructuradas.
4. **Endpoints** para consultar personas, sus teléfonos y direcciones.
5. **Roles de usuario**:
    - `admin`: Puede cargar archivos Excel y consultar datos.
    - `user`: Solo puede consultar datos.

---

## Requisitos previos

Antes de iniciar, asegúrate de tener instalado:

- **PHP >= 8.1**
- **Composer**
- **MySQL**
- **Node.js y npm** (para manejo de dependencias de frontend, si es necesario en el futuro)
- **Git**

---

## Pasos para configurar el proyecto desde GitHub

### Clonar el repositorio

```bash
git clone https://github.com/UrieLBadilloG/k2i_backend.git
cd k2i_backend
```

### Instalar dependencias de PHP con Composer

```bash
composer install
```

### Configurar el archivo `.env`

Copia el archivo de ejemplo:

```bash
cp .env.example .env
```

Configura los valores de conexión a la base de datos en el archivo `.env`:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=<NOMBRE_DE_TU_BD>
DB_USERNAME=<USUARIO>
DB_PASSWORD=<CONTRASEÑA>
```

### Generar la clave de la aplicación

```bash
php artisan key:generate
```

---

### Configurar las migraciones y restaurar tablas

Si las migraciones originales no están disponibles, las tablas se encuentran en la carpeta `backup`. Importa las tablas desde esta carpeta a tu base de datos utilizando herramientas como HeidiSQL o phpMyAdmin.

Si prefieres usar migraciones:

```bash
php artisan migrate
```

### Ejecutar los procedimientos almacenados (SP)

Copia y pega los siguientes SP en tu herramienta MySQL (como phpMyAdmin o un cliente MySQL):

```sql
DELIMITER $$

CREATE PROCEDURE migrate_data()
BEGIN
    INSERT INTO persona (nombre, paterno, materno)
    SELECT DISTINCT nombre, paterno, materno FROM temporal;

    INSERT INTO telefono (persona_id, telefono)
    SELECT p.id, t.telefono FROM temporal t
    INNER JOIN persona p ON t.nombre = p.nombre AND t.paterno = p.paterno AND t.materno = p.materno;

    INSERT INTO direccion (persona_id, calle, numero_exterior, numero_interior, colonia, cp)
    SELECT p.id, t.calle, t.numero_exterior, t.numero_interior, t.colonia, t.cp
    FROM temporal t
    INNER JOIN persona p ON t.nombre = p.nombre AND t.paterno = p.paterno AND t.materno = p.materno;
END$$

DELIMITER ;

DELIMITER $$
CREATE PROCEDURE paginate_personas(IN start_index INT, IN page_size INT)
BEGIN
    SELECT p.id AS persona_id, p.nombre, p.paterno, p.materno, t.telefono, d.calle, d.numero_exterior, d.numero_interior, d.colonia, d.cp
    FROM persona p
    LEFT JOIN telefono t ON p.id = t.persona_id
    LEFT JOIN direccion d ON p.id = d.persona_id
    LIMIT start_index, page_size;
END$$
DELIMITER ;

DELIMITER $$
CREATE PROCEDURE get_persona_details(IN persona_id INT)
BEGIN
    SELECT p.id AS persona_id, p.nombre, p.paterno, p.materno, t.telefono, d.calle, d.numero_exterior, d.numero_interior, d.colonia, d.cp
    FROM persona p
    LEFT JOIN telefono t ON p.id = t.persona_id
    LEFT JOIN direccion d ON p.id = d.persona_id
    WHERE p.id = persona_id;
END$$
DELIMITER ;
```

---

### Iniciar el servidor

```bash
php artisan serve
```

---

## Endpoints de la API

### 1. Autenticación

#### `POST /api/login`

- Descripción: Inicia sesión y devuelve un token de acceso.
- Body:
```json
{
  "email": "admin@example.com",
  "password": "password123"
}
```

- Respuesta exitosa:
```json
{
  "token": "Bearer <TOKEN>"
}
```

#### `POST /api/logout`

- Descripción: Cierra la sesión del usuario autenticado.
- Headers:
```
Authorization: Bearer <TOKEN>
```

- Respuesta exitosa:
```json
{
  "message": "Sesión cerrada exitosamente"
}
```

#### `GET /api/user`

- Descripción: Devuelve información del usuario autenticado.
- Headers:
```
Authorization: Bearer <TOKEN>
```

---

### 2. Carga de datos

#### `POST /api/upload-excel`

- Descripción: Carga un archivo Excel al servidor y migra los datos a la base de datos.
- Headers:
```
Authorization: Bearer <TOKEN_ADMIN>
```

- Body (multipart/form-data):
```
file: <archivo_excel.xlsx>
```

- Respuesta exitosa:
```json
{
  "message": "Archivo cargado y datos migrados exitosamente"
}
```

---

### 3. Consulta de datos

#### `GET /api/personas`

- Descripción: Devuelve una lista paginada de personas.
- Headers:
```
Authorization: Bearer <TOKEN>
```

- Query Params:
```
page=1&page_size=100
```

- Respuesta exitosa:
```json
[
  {
    "id": 1,
    "nombre": "Juan",
    "paterno": "Pérez",
    "materno": "Ramírez",
    "telefono": ["5551234567"],
    "direccion": [
      {
        "calle": "Primera",
        "numero_exterior": "101",
        "numero_interior": "A",
        "colonia": "Centro",
        "cp": "12345"
      }
    ]
  }
]
```

#### `GET /api/persona/{id}`

- Descripción: Devuelve detalles de una persona específica.
- Headers:
```
Authorization: Bearer <TOKEN>
```

- Respuesta exitosa:
```json
{
  "id": 1,
  "nombre": "Juan",
  "paterno": "Pérez",
  "materno": "Ramírez",
  "telefonos": ["5551234567"],
  "direcciones": [
    {
      "calle": "Primera",
      "numero_exterior": "101",
      "numero_interior": "A",
      "colonia": "Centro",
      "cp": "12345"
    }
  ]
}
```

---

## Roles y Middlewares

### Roles soportados:

- `admin`: Puede cargar datos y consultar.
- `user`: Solo puede consultar.

### Middlewares personalizados:

- `isAdmin`: Solo permite acceso a usuarios con rol `admin`.
- `isUser`: Permite acceso a cualquier usuario autenticado.

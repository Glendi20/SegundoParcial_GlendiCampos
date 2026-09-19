# Sistema de Citas Médicas — Laravel 12

Aplicación de agenda de citas médicas construida con Laravel 12, MySQL (en Docker) y FullCalendar. Permite crear, listar, reprogramar (drag & drop) y cambiar el estado de citas, validando en el servidor que un doctor no tenga dos citas activas que se solapen.

## Arquitectura por capas

| Capa | Ubicación | Responsabilidad |
|---|---|---|
| Presentación / calendario | `resources/views/calendario`, `resources/js/calendario.js` | Blade + Alpine.js + FullCalendar. Solo UI y llamadas fetch a la API. |
| API (HTTP) | `app/Http/Controllers/Api`, `app/Http/Requests`, `app/Http/Resources` | Controladores delgados: validan la entrada (Form Requests) y formatean la salida (API Resources). |
| Lógica de negocio | `app/Services`, `app/Enums`, `app/Exceptions` | `CitaService` centraliza la validación de conflictos de horario y las transiciones de estado. |
| Acceso a datos | `app/Models`, `database/migrations`, `database/seeders`, `database/factories` | Eloquent + MySQL. |

Los controladores **no** contienen lógica de negocio: delegan siempre en un Service.

## Requisitos

- PHP 8.2+, Composer
- Node.js 18+ y npm
- Docker Desktop (con motor Linux funcionando — WSL2 o Hyper-V en Windows)

## Puesta en marcha

```bash
# 1. Instalar dependencias
composer install
npm install

# 2. Variables de entorno (ya incluidas en .env, ajustar si es necesario)
cp .env.example .env   # solo si no existe .env
php artisan key:generate

# 3. Levantar MySQL en Docker (con persistencia por volumen)
docker compose up -d

# 4. Migrar el esquema y cargar datos semilla
php artisan migrate --seed

# 5. Compilar assets (o usar `npm run dev` para desarrollo con recarga en caliente)
npm run build

# 6. Levantar la aplicación
php artisan serve
```

La aplicación queda disponible en `http://127.0.0.1:8000` y phpMyAdmin en `http://127.0.0.1:8081` (usuario `root`, password `root_password`).

## Base de datos

`docker-compose.yml` levanta MySQL 8 con:

- Persistencia en el volumen nombrado `mysql_data` (los datos sobreviven a `docker compose down`; usar `docker compose down -v` para borrarlos).
- Base de datos `citas_medicas`, usuario `citas_user` / `citas_password`.
- Puerto publicado `3307` en el host (evita chocar con una instalación local de MySQL, que **no** debe usarse: el motor solo corre en Docker).

Esquema (`database/migrations`):

- `pacientes`: nombre, apellido, teléfono, email, fecha de nacimiento.
- `doctores`: nombre, apellido, especialidad, teléfono, email.
- `citas`: paciente_id, doctor_id, inicio, fin, motivo, estado (`pendiente`, `confirmada`, `cancelada`, `atendida`), con índice compuesto `(doctor_id, inicio, fin)` para acelerar la validación de solapamiento.

Datos semilla (`database/seeders`): 5 pacientes, 4 doctores y 6 citas de ejemplo en distintos estados.

## API REST

Ver también la sección "Referencia orientativa de endpoints" del enunciado. Todas las respuestas son JSON.

| Método | Ruta | Éxito | Errores |
|---|---|---|---|
| GET | `/api/citas` (filtros `doctor_id`, `paciente_id`, `desde`, `hasta`) | 200 | 400 |
| POST | `/api/citas` | 201 | 400, 409 |
| GET | `/api/citas/{id}` | 200 | 404 |
| PUT | `/api/citas/{id}` (reprogramar) | 200 | 400, 404, 409 |
| PATCH | `/api/citas/{id}/estado` | 200 | 400, 404 |
| GET | `/api/doctores` | 200 | — |
| GET | `/api/pacientes` | 200 | — |

La validación de disponibilidad (RQNF-07) ocurre en `App\Services\CitaService::asegurarSinConflicto()`, ejecutada dentro de una transacción con `lockForUpdate()` al reprogramar, por lo que no puede evitarse desde el cliente.

## Interfaz (FullCalendar)

- Vistas de mes (`dayGridMonth`) y semana (`timeGridWeek`).
- Clic en una fecha/hora vacía → crea una cita.
- Clic en un evento → muestra el detalle y permite confirmar / marcar atendida / cancelar.
- Arrastrar un evento (drag & drop) o redimensionarlo → reprograma la cita vía `PUT /api/citas/{id}`; si el servidor responde 409 el evento vuelve a su posición original.
- Color del evento según `estado` (pendiente=ámbar, confirmada=azul, atendida=verde, cancelada=rojo).

## Nota sobre zona horaria

`config/app.php` usa `UTC` (valor por defecto de Laravel) para que la aplicación sea portable entre entornos. El frontend (`resources/js/calendario.js`) convierte siempre las fechas del formulario y del drag & drop a UTC real (`toISOString()`) antes de enviarlas a la API, así que la hora que el usuario ve y selecciona en su navegador se conserva sin importar la zona horaria del servidor. Si se despliega para una clínica en una zona horaria fija, se puede además establecer `'timezone' => 'America/Tegucigalpa'` (o la que corresponda) en `config/app.php` para que las fechas generadas en el servidor (seeders, jobs) coincidan con el horario local del negocio.

## Pruebas

```bash
php artisan test
```

`tests/Feature/CitaApiTest.php` cubre: creación válida, rechazo de datos inválidos (400), conflicto de horario (409), que una cita cancelada libera el horario, reprogramación válida e inválida, cambio de estado, 404 en recurso inexistente y filtros de listado.

## Flujo Git

Ver [EVIDENCIA.md](EVIDENCIA.md) para el detalle de ramas, Pull Requests y evidencia de ejecución.

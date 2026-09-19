# Evidencia — Serie II

## 1. Backlog y trazabilidad

| Rama | RQF cubiertos | RQNF cubiertos | PR |
|---|---|---|---|
| `feature/docker-mysql-schema` | RQF-01 (datos base) | RQNF-01, RQNF-02, RQNF-04 | `[PENDIENTE: link al PR #]` |
| `feature/api-rest-citas` | RQF-01, RQF-06, RQF-07, RQF-08 | RQNF-03, RQNF-04 | `[PENDIENTE: link al PR #]` |
| `feature/validacion-conflictos-estados` | RQF-03, RQF-05, RQF-10 | RQNF-03, RQNF-04, RQNF-07 | `[PENDIENTE: link al PR #]` |
| `feature/fullcalendar-ui` | RQF-02, RQF-04, RQF-09, RQF-10 | RQNF-06 | `[PENDIENTE: link al PR #]` |

> Las ramas, commits, Pull Requests y merges a `main` los crea el estudiante (ver guía de commits acordada). Esta sección se completa al finalizar ese flujo.

## 2. Docker — MySQL con persistencia

```bash
docker compose up -d
```

Salida de `docker ps`:

```
CONTAINER ID   IMAGE          COMMAND                  CREATED          STATUS                    PORTS                                         NAMES
7f4846689b0c   phpmyadmin:5   "/docker-entrypoint.…"   10 minutes ago   Up 10 minutes             0.0.0.0:8081->80/tcp, [::]:8081->80/tcp      citas_phpmyadmin
fb84d568eced   mysql:8.0      "docker-entrypoint.s…"   10 minutes ago   Up 10 minutes (healthy)   0.0.0.0:3307->3306/tcp, [::]:3307->3306/tcp  citas_mysql
```

MySQL 8 corre exclusivamente en Docker (puerto de host `3307` para no chocar con ningún motor local), con persistencia en el volumen nombrado `mysql_data`. phpMyAdmin queda disponible en `http://127.0.0.1:8081`.

## 3. Migraciones y datos semilla

```bash
php artisan migrate --seed
```

```
INFO  Preparing database.
Creating migration table ................................................. DONE

INFO  Running migrations.
0001_01_01_000000_create_users_table ...................................... DONE
0001_01_01_000001_create_cache_table ....................................... DONE
0001_01_01_000002_create_jobs_table ........................................ DONE
2026_09_19_134151_create_doctores_table .................................... DONE
2026_09_19_134151_create_pacientes_table ................................... DONE
2026_09_19_134152_create_citas_table ....................................... DONE

INFO  Seeding database.
Database\Seeders\PacienteSeeder ............................................ DONE
Database\Seeders\DoctorSeeder ............................................... DONE
Database\Seeders\CitaSeeder ................................................. DONE
```

Quedan sembrados 5 pacientes, 4 doctores y 6 citas (en distintos estados: pendiente, confirmada, cancelada, atendida).

## 4. API REST — evidencia de endpoints

Todas las pruebas se ejecutaron contra `http://127.0.0.1:8000` (Laravel) con MySQL corriendo en Docker.

### 4.1 Listar doctores — `GET /api/doctores` (200)

```bash
curl http://127.0.0.1:8000/api/doctores
```

```json
{"data":[
  {"id":1,"nombre":"Eduardo","apellido":"Castillo","nombre_completo":"Dr(a). Eduardo Castillo","especialidad":"Medicina General","telefono":"98001122","email":"eduardo.castillo@clinica.com"},
  {"id":2,"nombre":"Patricia","apellido":"Vasquez","nombre_completo":"Dr(a). Patricia Vasquez","especialidad":"Pediatria","telefono":"98002233","email":"patricia.vasquez@clinica.com"},
  {"id":3,"nombre":"Roberto","apellido":"Mendoza","nombre_completo":"Dr(a). Roberto Mendoza","especialidad":"Cardiologia","telefono":"98003344","email":"roberto.mendoza@clinica.com"},
  {"id":4,"nombre":"Silvia","apellido":"Reyes","nombre_completo":"Dr(a). Silvia Reyes","especialidad":"Dermatologia","telefono":"98004455","email":"silvia.reyes@clinica.com"}
]}
```

### 4.2 Crear una cita — `POST /api/citas` (201)

```bash
curl -X POST http://127.0.0.1:8000/api/citas \
  -H "Content-Type: application/json" \
  -d '{"paciente_id":1,"doctor_id":1,"inicio":"2026-10-05 09:00:00","fin":"2026-10-05 09:30:00","motivo":"Consulta general"}'
```

`HTTP 201`

```json
{"data":{"id":7,"paciente":{"id":1,"nombre_completo":"Maria Lopez", "..."},"doctor":{"id":1,"nombre_completo":"Dr(a). Eduardo Castillo","..."},"inicio":"2026-10-05T09:00:00+00:00","fin":"2026-10-05T09:30:00+00:00","motivo":"Consulta general","estado":"pendiente","estado_label":"Pendiente","color":"#f59e0b"}}
```

### 4.3 Conflicto de horario — `POST /api/citas` (409, RQF-03 / RQNF-07)

```bash
curl -X POST http://127.0.0.1:8000/api/citas \
  -H "Content-Type: application/json" \
  -d '{"paciente_id":2,"doctor_id":1,"inicio":"2026-10-05 09:15:00","fin":"2026-10-05 09:45:00","motivo":"Otra consulta"}'
```

`HTTP 409`

```json
{"message":"El doctor seleccionado ya tiene una cita activa que se solapa con ese horario."}
```

### 4.4 Datos inválidos — `POST /api/citas` (400)

```bash
curl -X POST http://127.0.0.1:8000/api/citas \
  -H "Content-Type: application/json" \
  -d '{"paciente_id":null,"doctor_id":null,"inicio":"","fin":"","motivo":""}'
```

`HTTP 400`

```json
{"message":"The paciente id field is required. (and 4 more errors)","errors":{"paciente_id":["The paciente id field is required."],"doctor_id":["The doctor id field is required."],"inicio":["The inicio field is required."],"fin":["The fin field is required."],"motivo":["The motivo field is required."]}}
```

### 4.5 Cita inexistente — `GET /api/citas/999999` (404)

```bash
curl http://127.0.0.1:8000/api/citas/999999
```

`HTTP 404`

```json
{"message":"Recurso no encontrado."}
```

### 4.6 Reprogramar — `PUT /api/citas/{id}` (200)

```bash
curl -X PUT http://127.0.0.1:8000/api/citas/7 \
  -H "Content-Type: application/json" \
  -d '{"inicio":"2026-10-05 14:00:00","fin":"2026-10-05 14:30:00"}'
```

`HTTP 200` — la cita queda con `inicio: "2026-10-05T14:00:00+00:00"`.

### 4.7 Cambiar estado — `PATCH /api/citas/{id}/estado` (200)

```bash
curl -X PATCH http://127.0.0.1:8000/api/citas/7/estado \
  -H "Content-Type: application/json" \
  -d '{"estado":"confirmada"}'
```

`HTTP 200` — la cita queda con `estado: "confirmada"`, `color: "#2563eb"`.

## 5. Pruebas automatizadas

```bash
php artisan test
```

```
PASS  Tests\Feature\CitaApiTest
✓ crea una cita correctamente
✓ rechaza datos invalidos con 400
✓ rechaza doble reserva para el mismo doctor con 409
✓ permite agendar con otro doctor en el mismo horario
✓ una cita cancelada no bloquea el horario
✓ reprograma una cita con drag and drop
✓ no reprograma si genera conflicto con otra cita
✓ cambia el estado de una cita
✓ devuelve 404 para una cita inexistente
✓ lista citas filtrando por doctor y rango de fechas
✓ lista doctores y pacientes

PASS  Tests\Feature\ExampleTest
✓ the application returns a successful response

Tests:    13 passed (23 assertions)
```

`./vendor/bin/pint` (estilo de código): `passed`.

## 6. Interfaz — FullCalendar

Probado con Playwright (Chromium headless) contra la app real, sin errores de consola.

- ![Vista de mes con citas coloreadas por estado](docs/evidencia/01-vista-mes.png)
- ![Modal de detalle al hacer clic en un evento](docs/evidencia/02-detalle-cita.png)
- ![Modal de creación al hacer clic en una fecha vacía](docs/evidencia/03-crear-cita.png)
- ![Vista de semana](docs/evidencia/04-vista-semana.png)
- ![Cita reprogramada con drag & drop (día 25 → día 28)](docs/evidencia/05-drag-and-drop.png)

**Nota de zona horaria:** el `<input type="datetime-local">` y los eventos de `dateClick`/`eventDrop` de FullCalendar no llevan offset de zona horaria; `resources/js/calendario.js` los convierte explícitamente a UTC (`Date.prototype.toISOString()`) antes de enviarlos a la API, de modo que la hora que ve el usuario en su navegador coincide con la que se guarda, sin importar la zona horaria del servidor (`config/app.php` usa `UTC`).

## 7. Historial Git

```bash
git log --graph --all --oneline
```

```
[PENDIENTE: pegar salida una vez creadas las ramas/commits/PRs]
```

Pull Requests fusionados a `main`:

1. `[PENDIENTE]` `feature/docker-mysql-schema` → `main`
2. `[PENDIENTE]` `feature/api-rest-citas` → `main`
3. `[PENDIENTE]` `feature/validacion-conflictos-estados` → `main`
4. `[PENDIENTE]` `feature/fullcalendar-ui` → `main`

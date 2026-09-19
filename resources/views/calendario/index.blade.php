<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Citas Médicas') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-800" x-data="appCitas()">

    <header class="bg-white border-b border-slate-200 shadow-sm">
        <div class="max-w-6xl mx-auto px-4 py-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <h1 class="text-xl font-semibold text-slate-900">Agenda de Citas Médicas</h1>

            <div class="flex flex-wrap items-center gap-3">
                <label class="flex items-center gap-2 text-sm text-slate-600">
                    Doctor:
                    <select
                        class="rounded-md border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                        x-model="filtroDoctorId"
                        @change="aplicarFiltro()"
                    >
                        <option value="">Todos</option>
                        <template x-for="doctor in doctores" :key="doctor.id">
                            <option :value="doctor.id" x-text="doctor.nombre_completo + ' — ' + doctor.especialidad"></option>
                        </template>
                    </select>
                </label>

                <button
                    type="button"
                    class="rounded-md bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700"
                    @click="abrirCreacionManual()"
                >
                    + Nueva cita
                </button>
            </div>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 py-6">
        <div class="mb-4 flex flex-wrap gap-4 text-sm">
            <span class="flex items-center gap-2"><span class="h-3 w-3 rounded-full" style="background:#f59e0b"></span> Pendiente</span>
            <span class="flex items-center gap-2"><span class="h-3 w-3 rounded-full" style="background:#2563eb"></span> Confirmada</span>
            <span class="flex items-center gap-2"><span class="h-3 w-3 rounded-full" style="background:#16a34a"></span> Atendida</span>
            <span class="flex items-center gap-2"><span class="h-3 w-3 rounded-full" style="background:#ef4444"></span> Cancelada</span>
        </div>

        <div class="rounded-lg bg-white p-3 shadow-sm md:p-4">
            <div x-ref="calendarEl"></div>
        </div>
    </main>

    {{-- Modal: crear cita --}}
    <div
        x-show="modalCreacion"
        x-cloak
        class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/50 px-4"
        @keydown.escape.window="cerrarModales()"
    >
        <div class="w-full max-w-md rounded-lg bg-white p-5 shadow-xl" @click.outside="cerrarModales()">
            <h2 class="mb-4 text-lg font-semibold text-slate-900">Nueva cita</h2>

            <p x-show="error" x-text="error" class="mb-3 rounded-md bg-red-50 px-3 py-2 text-sm text-red-700"></p>

            <form class="space-y-3" @submit.prevent="guardarCita()">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Paciente</label>
                    <select required class="mt-1 w-full rounded-md border-slate-300 text-sm" x-model="form.paciente_id">
                        <option value="" disabled>Seleccione un paciente</option>
                        <template x-for="paciente in pacientes" :key="paciente.id">
                            <option :value="paciente.id" x-text="paciente.nombre_completo"></option>
                        </template>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Doctor</label>
                    <select required class="mt-1 w-full rounded-md border-slate-300 text-sm" x-model="form.doctor_id">
                        <option value="" disabled>Seleccione un doctor</option>
                        <template x-for="doctor in doctores" :key="doctor.id">
                            <option :value="doctor.id" x-text="doctor.nombre_completo + ' — ' + doctor.especialidad"></option>
                        </template>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Inicio</label>
                        <input required type="datetime-local" class="mt-1 w-full rounded-md border-slate-300 text-sm" x-model="form.inicio">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Fin</label>
                        <input required type="datetime-local" class="mt-1 w-full rounded-md border-slate-300 text-sm" x-model="form.fin">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Motivo</label>
                    <input required type="text" maxlength="255" class="mt-1 w-full rounded-md border-slate-300 text-sm" x-model="form.motivo" placeholder="Ej. Consulta general">
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" class="rounded-md px-3 py-2 text-sm text-slate-600 hover:bg-slate-100" @click="cerrarModales()">Cancelar</button>
                    <button type="submit" :disabled="guardando" class="rounded-md bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-60">
                        <span x-show="!guardando">Guardar cita</span>
                        <span x-show="guardando">Guardando…</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal: detalle de cita --}}
    <div
        x-show="modalDetalle"
        x-cloak
        class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/50 px-4"
        @keydown.escape.window="cerrarModales()"
    >
        <div class="w-full max-w-md rounded-lg bg-white p-5 shadow-xl" @click.outside="cerrarModales()" x-show="citaSeleccionada">
            <template x-if="citaSeleccionada">
                <div>
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-slate-900">Detalle de la cita</h2>
                        <span
                            class="rounded-full px-2 py-1 text-xs font-medium text-white"
                            :style="`background:${citaSeleccionada.color}`"
                            x-text="citaSeleccionada.estado_label"
                        ></span>
                    </div>

                    <dl class="space-y-2 text-sm">
                        <div>
                            <dt class="font-medium text-slate-500">Paciente</dt>
                            <dd x-text="citaSeleccionada.paciente?.nombre_completo"></dd>
                        </div>
                        <div>
                            <dt class="font-medium text-slate-500">Doctor</dt>
                            <dd x-text="(citaSeleccionada.doctor?.nombre_completo ?? '') + ' — ' + (citaSeleccionada.doctor?.especialidad ?? '')"></dd>
                        </div>
                        <div>
                            <dt class="font-medium text-slate-500">Horario</dt>
                            <dd x-text="new Date(citaSeleccionada.inicio).toLocaleString() + ' - ' + new Date(citaSeleccionada.fin).toLocaleTimeString()"></dd>
                        </div>
                        <div>
                            <dt class="font-medium text-slate-500">Motivo</dt>
                            <dd x-text="citaSeleccionada.motivo"></dd>
                        </div>
                    </dl>

                    <div class="mt-5 flex flex-wrap justify-end gap-2">
                        <button
                            type="button"
                            class="rounded-md bg-blue-50 px-3 py-2 text-sm font-medium text-blue-700 hover:bg-blue-100"
                            x-show="citaSeleccionada.estado === 'pendiente'"
                            @click="cambiarEstado('confirmada')"
                        >Confirmar</button>

                        <button
                            type="button"
                            class="rounded-md bg-green-50 px-3 py-2 text-sm font-medium text-green-700 hover:bg-green-100"
                            x-show="['pendiente', 'confirmada'].includes(citaSeleccionada.estado)"
                            @click="cambiarEstado('atendida')"
                        >Marcar atendida</button>

                        <button
                            type="button"
                            class="rounded-md bg-red-50 px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-100"
                            x-show="['pendiente', 'confirmada'].includes(citaSeleccionada.estado)"
                            @click="cambiarEstado('cancelada')"
                        >Cancelar cita</button>

                        <button type="button" class="rounded-md px-3 py-2 text-sm text-slate-600 hover:bg-slate-100" @click="cerrarModales()">Cerrar</button>
                    </div>
                </div>
            </template>
        </div>
    </div>

</body>
</html>

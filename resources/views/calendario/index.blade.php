<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Citas Médicas') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 antialiased" x-data="appCitas()">

    <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/90 backdrop-blur">
        <div class="mx-auto flex max-w-7xl flex-col gap-4 px-4 py-4 sm:px-6 md:flex-row md:items-center md:justify-between lg:px-8">
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-sm shadow-indigo-200">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                        <path d="M8 2v3M16 2v3M3.5 9h17M5 5h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M9 14.5l1.8 1.8L15.5 12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <div>
                    <h1 class="text-lg font-semibold leading-tight text-slate-900">Agenda de Citas Médicas</h1>
                    <p class="text-xs text-slate-500">Panel de programación y seguimiento</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <label class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-4 w-4 text-slate-400">
                        <path d="M20 21a8 8 0 1 0-16 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        <circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="1.8"/>
                    </svg>
                    <span class="text-slate-500">Doctor</span>
                    <select
                        class="border-0 bg-transparent p-0 pr-6 text-sm font-medium text-slate-800 focus:ring-0"
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
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-indigo-200 transition hover:bg-indigo-700 active:bg-indigo-800"
                    @click="abrirCreacionManual()"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                        <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    Nueva cita
                </button>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="mb-5 flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1 text-xs font-medium text-amber-700 ring-1 ring-inset ring-amber-200">
                <span class="h-2 w-2 rounded-full bg-amber-500"></span> Pendiente
            </span>
            <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-200">
                <span class="h-2 w-2 rounded-full bg-blue-600"></span> Confirmada
            </span>
            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-200">
                <span class="h-2 w-2 rounded-full bg-emerald-600"></span> Atendida
            </span>
            <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-50 px-3 py-1 text-xs font-medium text-rose-700 ring-1 ring-inset ring-rose-200">
                <span class="h-2 w-2 rounded-full bg-rose-500"></span> Cancelada
            </span>
        </div>

        <div class="calendario-card rounded-2xl border border-slate-200 bg-white p-3 shadow-sm sm:p-5">
            <div x-ref="calendarEl"></div>
        </div>
    </main>

    {{-- Modal: crear cita --}}
    <div
        x-show="modalCreacion"
        x-cloak
        x-transition.opacity
        class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/40 px-4 backdrop-blur-sm"
        @keydown.escape.window="cerrarModales()"
    >
        <div
            x-show="modalCreacion"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl"
            @click.outside="cerrarModales()"
        >
            <div class="mb-5 flex items-center gap-3">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                        <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </span>
                <h2 class="text-base font-semibold text-slate-900">Nueva cita</h2>
            </div>

            <p x-show="error" x-cloak x-text="error" class="mb-4 rounded-lg bg-rose-50 px-3 py-2 text-sm text-rose-700 ring-1 ring-inset ring-rose-200"></p>

            <form class="space-y-4" @submit.prevent="guardarCita()">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Paciente</label>
                    <select required class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" x-model="form.paciente_id">
                        <option value="" disabled>Seleccione un paciente</option>
                        <template x-for="paciente in pacientes" :key="paciente.id">
                            <option :value="paciente.id" x-text="paciente.nombre_completo"></option>
                        </template>
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Doctor</label>
                    <select required class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" x-model="form.doctor_id">
                        <option value="" disabled>Seleccione un doctor</option>
                        <template x-for="doctor in doctores" :key="doctor.id">
                            <option :value="doctor.id" x-text="doctor.nombre_completo + ' — ' + doctor.especialidad"></option>
                        </template>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Inicio</label>
                        <input required type="datetime-local" class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" x-model="form.inicio">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Fin</label>
                        <input required type="datetime-local" class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" x-model="form.fin">
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Motivo</label>
                    <input required type="text" maxlength="255" class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" x-model="form.motivo" placeholder="Ej. Consulta general">
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" class="rounded-lg px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100" @click="cerrarModales()">Cancelar</button>
                    <button type="submit" :disabled="guardando" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-indigo-200 transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-60">
                        <svg x-show="guardando" class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" class="opacity-25"/>
                            <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round" class="opacity-90"/>
                        </svg>
                        <span x-text="guardando ? 'Guardando…' : 'Guardar cita'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal: detalle de cita --}}
    <div
        x-show="modalDetalle"
        x-cloak
        x-transition.opacity
        class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/40 px-4 backdrop-blur-sm"
        @keydown.escape.window="cerrarModales()"
    >
        <div
            x-show="modalDetalle && citaSeleccionada"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl"
            @click.outside="cerrarModales()"
        >
            <template x-if="citaSeleccionada">
                <div>
                    <div class="mb-5 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-slate-500">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                                    <path d="M8 2v3M16 2v3M3.5 9h17M5 5h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <h2 class="text-base font-semibold text-slate-900">Detalle de la cita</h2>
                        </div>
                        <span
                            class="rounded-full px-2.5 py-1 text-xs font-semibold text-white"
                            :style="`background:${citaSeleccionada.color}`"
                            x-text="citaSeleccionada.estado_label"
                        ></span>
                    </div>

                    <dl class="space-y-3 text-sm">
                        <div class="flex items-start gap-3 rounded-lg bg-slate-50 px-3 py-2.5">
                            <dt class="w-20 shrink-0 font-medium text-slate-500">Paciente</dt>
                            <dd class="font-medium text-slate-800" x-text="citaSeleccionada.paciente?.nombre_completo"></dd>
                        </div>
                        <div class="flex items-start gap-3 rounded-lg bg-slate-50 px-3 py-2.5">
                            <dt class="w-20 shrink-0 font-medium text-slate-500">Doctor</dt>
                            <dd class="font-medium text-slate-800" x-text="(citaSeleccionada.doctor?.nombre_completo ?? '') + ' — ' + (citaSeleccionada.doctor?.especialidad ?? '')"></dd>
                        </div>
                        <div class="flex items-start gap-3 rounded-lg bg-slate-50 px-3 py-2.5">
                            <dt class="w-20 shrink-0 font-medium text-slate-500">Horario</dt>
                            <dd class="font-medium text-slate-800" x-text="new Date(citaSeleccionada.inicio).toLocaleString() + ' - ' + new Date(citaSeleccionada.fin).toLocaleTimeString()"></dd>
                        </div>
                        <div class="flex items-start gap-3 rounded-lg bg-slate-50 px-3 py-2.5">
                            <dt class="w-20 shrink-0 font-medium text-slate-500">Motivo</dt>
                            <dd class="font-medium text-slate-800" x-text="citaSeleccionada.motivo"></dd>
                        </div>
                    </dl>

                    <div class="mt-6 flex flex-wrap justify-end gap-2">
                        <button
                            type="button"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-blue-50 px-3 py-2 text-sm font-medium text-blue-700 transition hover:bg-blue-100"
                            x-show="citaSeleccionada.estado === 'pendiente'"
                            @click="cambiarEstado('confirmada')"
                        >Confirmar</button>

                        <button
                            type="button"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-700 transition hover:bg-emerald-100"
                            x-show="['pendiente', 'confirmada'].includes(citaSeleccionada.estado)"
                            @click="cambiarEstado('atendida')"
                        >Marcar atendida</button>

                        <button
                            type="button"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-rose-50 px-3 py-2 text-sm font-medium text-rose-700 transition hover:bg-rose-100"
                            x-show="['pendiente', 'confirmada'].includes(citaSeleccionada.estado)"
                            @click="cambiarEstado('cancelada')"
                        >Cancelar cita</button>

                        <button type="button" class="rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100" @click="cerrarModales()">Cerrar</button>
                    </div>
                </div>
            </template>
        </div>
    </div>

</body>
</html>

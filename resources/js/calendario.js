import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import esLocale from '@fullcalendar/core/locales/es';

/**
 * La instancia de FullCalendar vive fuera del estado reactivo de Alpine
 * para que Alpine no intente convertir el objeto Calendar (con referencias
 * circulares) en un proxy reactivo.
 */
let calendarInstance = null;

function aInputDatetime(date) {
    const pad = (n) => String(n).padStart(2, '0');

    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
}

async function peticionJson(url, opciones = {}) {
    const respuesta = await fetch(url, {
        ...opciones,
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            ...(opciones.headers || {}),
        },
    });

    const datos = await respuesta.json().catch(() => null);

    return { ok: respuesta.ok, status: respuesta.status, datos };
}

export function registerAppCitas(Alpine) {
    Alpine.data('appCitas', () => ({
        doctores: [],
        pacientes: [],
        filtroDoctorId: '',
        modalCreacion: false,
        modalDetalle: false,
        guardando: false,
        error: null,
        form: {
            paciente_id: '',
            doctor_id: '',
            inicio: '',
            fin: '',
            motivo: '',
        },
        citaSeleccionada: null,

        async init() {
            await Promise.all([this.cargarDoctores(), this.cargarPacientes()]);
            this.iniciarCalendario();
        },

        async cargarDoctores() {
            const { ok, datos } = await peticionJson('/api/doctores');
            this.doctores = ok ? datos.data : [];
        },

        async cargarPacientes() {
            const { ok, datos } = await peticionJson('/api/pacientes');
            this.pacientes = ok ? datos.data : [];
        },

        iniciarCalendario() {
            calendarInstance = new Calendar(this.$refs.calendarEl, {
                plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
                locale: esLocale,
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek',
                },
                height: 'auto',
                selectable: true,
                editable: true,
                eventDurationEditable: true,
                dateClick: (info) => this.abrirCreacion(info),
                eventClick: (info) => this.abrirDetalle(info),
                eventDrop: (info) => this.reprogramar(info),
                eventResize: (info) => this.reprogramar(info),
                events: (fetchInfo, successCallback, failureCallback) =>
                    this.obtenerEventos(fetchInfo, successCallback, failureCallback),
            });

            calendarInstance.render();
        },

        async obtenerEventos(fetchInfo, successCallback, failureCallback) {
            const params = new URLSearchParams({
                desde: fetchInfo.startStr,
                hasta: fetchInfo.endStr,
            });

            if (this.filtroDoctorId) {
                params.set('doctor_id', this.filtroDoctorId);
            }

            const { ok, datos } = await peticionJson(`/api/citas?${params.toString()}`);

            if (!ok) {
                failureCallback(new Error('No se pudieron cargar las citas.'));
                return;
            }

            successCallback(
                datos.data.map((cita) => ({
                    id: String(cita.id),
                    title: `${cita.paciente.nombre_completo} · ${cita.motivo}`,
                    start: cita.inicio,
                    end: cita.fin,
                    backgroundColor: cita.color,
                    borderColor: cita.color,
                    extendedProps: cita,
                }))
            );
        },

        refrescar() {
            calendarInstance?.refetchEvents();
        },

        aplicarFiltro() {
            this.refrescar();
        },

        abrirCreacion(info) {
            this.error = null;

            const inicio = info.date ? new Date(info.date) : new Date();
            if (info.allDay) {
                inicio.setHours(9, 0, 0, 0);
            }
            const fin = new Date(inicio.getTime() + 30 * 60000);

            this.form = {
                paciente_id: '',
                doctor_id: this.filtroDoctorId || '',
                inicio: aInputDatetime(inicio),
                fin: aInputDatetime(fin),
                motivo: '',
            };

            this.modalCreacion = true;
        },

        abrirCreacionManual() {
            this.abrirCreacion({ date: new Date(), allDay: false });
        },

        cerrarModales() {
            this.modalCreacion = false;
            this.modalDetalle = false;
            this.error = null;
        },

        async guardarCita() {
            this.guardando = true;
            this.error = null;

            const { ok, status, datos } = await peticionJson('/api/citas', {
                method: 'POST',
                body: JSON.stringify({
                    ...this.form,
                    // El input datetime-local no lleva zona horaria: se interpreta como
                    // hora local del navegador y se convierte a UTC real para el servidor.
                    inicio: new Date(this.form.inicio).toISOString(),
                    fin: new Date(this.form.fin).toISOString(),
                }),
            });

            this.guardando = false;

            if (!ok) {
                this.error = this.mensajeError(status, datos);
                return;
            }

            this.modalCreacion = false;
            this.refrescar();
        },

        abrirDetalle(info) {
            this.citaSeleccionada = { ...info.event.extendedProps, id: info.event.id };
            this.modalDetalle = true;
        },

        async reprogramar(info) {
            // event.start/end son instantes reales (Date); toISOString() da UTC exacto.
            // event.startStr/endStr no sirven aqui: bajo timeZone 'local' (por defecto)
            // FullCalendar los formatea sin offset, lo que el servidor mal interpretaria como UTC.
            const { ok, datos } = await peticionJson(`/api/citas/${info.event.id}`, {
                method: 'PUT',
                body: JSON.stringify({
                    inicio: info.event.start.toISOString(),
                    fin: info.event.end.toISOString(),
                }),
            });

            if (!ok) {
                alert(datos?.message || 'No se pudo reprogramar la cita: el horario ya no está disponible.');
                info.revert();
                return;
            }

            this.refrescar();
        },

        async cambiarEstado(estado) {
            const id = this.citaSeleccionada.id;
            const { ok, datos } = await peticionJson(`/api/citas/${id}/estado`, {
                method: 'PATCH',
                body: JSON.stringify({ estado }),
            });

            if (!ok) {
                alert(datos?.message || 'No se pudo actualizar el estado de la cita.');
                return;
            }

            this.citaSeleccionada = datos.data;
            this.refrescar();
        },

        mensajeError(status, datos) {
            if (status === 409) {
                return datos?.message || 'El doctor ya tiene una cita en ese horario.';
            }

            if (status === 400 && datos?.errors) {
                return Object.values(datos.errors).flat().join(' ');
            }

            return datos?.message || 'Ocurrió un error al procesar la solicitud.';
        },
    }));
}

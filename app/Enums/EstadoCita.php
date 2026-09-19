<?php

namespace App\Enums;

enum EstadoCita: string
{
    case Pendiente = 'pendiente';
    case Confirmada = 'confirmada';
    case Cancelada = 'cancelada';
    case Atendida = 'atendida';

    /**
     * Estados que ocupan un espacio en la agenda del doctor.
     * Una cita cancelada libera el horario y no cuenta como conflicto.
     */
    public static function estadosActivos(): array
    {
        return [self::Pendiente, self::Confirmada, self::Atendida];
    }

    public function color(): string
    {
        return match ($this) {
            self::Pendiente => '#f59e0b',
            self::Confirmada => '#2563eb',
            self::Cancelada => '#ef4444',
            self::Atendida => '#16a34a',
        };
    }

    public function etiqueta(): string
    {
        return match ($this) {
            self::Pendiente => 'Pendiente',
            self::Confirmada => 'Confirmada',
            self::Cancelada => 'Cancelada',
            self::Atendida => 'Atendida',
        };
    }
}

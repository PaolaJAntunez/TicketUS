<?php

namespace App\Models;

// Note: Eloquent Models broadly mirror the structure of a User model
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Áreas/departamentos disponibles al crear un usuario. Lista fija (no una
     * tabla) porque no hay todavía un caso de uso que requiera administrarlas
     * desde la UI; si eso cambia, se convierte en catálogo.
     */
    const DEPARTMENTS = [
        'Auditoría Interna',
        'Recursos Humanos',
        'Tecnología / TI',
        'Finanzas',
        'Contabilidad',
        'Legal',
        'Compras',
        'Logística',
        'Operaciones',
        'Ventas',
        'Marketing',
        'Atención al Cliente',
        'Producción',
        'Calidad',
        'Seguridad y Salud Ocupacional',
        'Administración',
        'Gerencia General',
        'Mantenimiento',
        'Investigación y Desarrollo',
        'Comunicaciones / Relaciones Públicas',
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'position',
        'department',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function assignedTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'assigned_to');
    }
}
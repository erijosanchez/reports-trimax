<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'cargo',
        'email',
        'password',
        'sede',
        'firma_imagen',
        'es_gerente_general',
        'puede_ver_ventas_consolidadas',
        'puede_ver_descuentos_especiales',
        'puede_ver_consultar_orden',
        'puede_ver_acuerdos_comerciales',
        'puede_ver_lead_time',
        'puede_ver_pendiente_entrega_montura',
        'puede_ver_venta_clientes',
        'puede_ver_ordenes_x_sede',
        'puede_ver_asignacion_bases',
        'puede_crear_requerimientos',
        'puede_gestionar_requerimientos',
        'puede_ver_todos_requerimientos',
        'puede_ver_productividad_sedes',
        'is_active',
        'last_login_at',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'puede_ver_ventas_consolidadas' => 'boolean',
        'puede_ver_descuentos_especiales' => 'boolean',
        'puede_ver_consultar_orden' => 'boolean',
        'puede_ver_acuerdos_comerciales' => 'boolean',
        'puede_ver_lead_time' => 'boolean',
        'puede_ver_pendiente_entrega_montura' => 'boolean',
        'puede_ver_venta_clientes' => 'boolean',
        'puede_ver_ordenes_x_sede' => 'boolean',
        'puede_ver_asignacion_bases' => 'boolean',
        'puede_crear_requerimientos' => 'boolean',
        'puede_gestionar_requerimientos' => 'boolean',
        'puede_ver_todos_requerimientos' => 'boolean',
        'puede_ver_productividad_sedes' => 'boolean',
        'es_gerente_general' => 'boolean',
        'last_login_at' => 'datetime',
        'two_factor_confirmed_at' => 'datetime',
    ];

    public function dashboards()
    {
        return $this->belongsToMany(Dashboard::class, 'dashboard_user')
            ->withPivot('can_view')
            ->withTimestamps();
    }

    public function sessions()
    {
        return $this->hasMany(UserSession::class);
    }

    public function activeSessions()
    {
        return $this->hasMany(UserSession::class)->where('is_online', true);
    }

    public function locations()
    {
        return $this->hasMany(UserLocation::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(UserActivityLog::class);
    }

    public function uploadedFiles()
    {
        return $this->hasMany(UploadedFile::class);
    }

    public function requerimientos()
    {
        return $this->hasMany(RequerimientoPersonal::class, 'solicitante_id');
    }


    /**
     * Verificar si el usuario tiene acceso a un dashboard específico
     */

    public function hasAccessToDashboard($dashboardId): bool
    {
        if ($this->hasRole(['super_admin', 'admin'])) {
            return true;
        }
        return $this->dashboards()->where('dashboard_id', $dashboardId)->exists();
    }

    // ROLES

    public function isAdmin(): bool
    {
        return $this->hasRole(['admin']);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function isMarketing()
    {
        return $this->hasRole('marketing');
    }

    public function isConsultor()
    {
        return $this->hasRole('consultor');
    }

    public function isRrhh(): bool
    {
        return $this->hasRole('rrhh');
    }

    public function isSede(): bool
    {
        return $this->hasRole(['sede']);
    }

    //PERMISOS ------------------------------
    public function puedeVerVentasConsolidadas(): bool
    {
        return $this->isSuperAdmin() || $this->puede_ver_ventas_consolidadas;
    }

    public function puedeVerDescuentosEspeciales()
    {
        return $this->isSuperAdmin() || $this->puede_ver_descuentos_especiales;
    }

    public function puedeVerConsultarOrden(): bool
    {
        // Sede siempre puede por defecto
        if ($this->isSede()) return true;
        return $this->isSuperAdmin() || $this->isAdmin() || $this->isConsultor()
            || $this->puede_ver_consultar_orden;
    }

    public function puedeVerAcuerdosComerciales(): bool
    {
        return $this->isSuperAdmin() || $this->isAdmin() || $this->isConsultor()
            || $this->puede_ver_acuerdos_comerciales;
    }

    public function puedeVerLeadTime(): bool
    {
        return $this->isSuperAdmin() || $this->isAdmin() || $this->isConsultor()
            || $this->puede_ver_lead_time;
    }

    public function puedeVerVentaClientes(): bool
    {
        return $this->isSuperAdmin() || $this->isAdmin() || $this->puede_ver_venta_clientes;
    }

    public function puedeVerOrdenesXSede(): bool
    {
        return $this->isSuperAdmin() || $this->isAdmin() || $this->puede_ver_ordenes_x_sede;
    }

    public function puedeVerAsignacionBases(): bool
    {
        return $this->isSuperAdmin() || $this->puede_ver_asignacion_bases;
    }

    /**
     * Solo el superadmin puede otorgar este permiso desde el panel de usuarios.
     * RRHH también puede crear requerimientos por defecto.
     */
    public function puedeCrearRequerimientos(): bool
    {
        return $this->isSuperAdmin() || $this->isRrhh() || $this->puede_crear_requerimientos;
    }

    /**
     * Ver requerimientos:
     * - RRHH y superadmin ven TODOS
     * - Los demás solo ven los suyos (filtrado en el controller)
     */
    public function puedeVerTodosLosRequerimientos(): bool
    {
        return $this->isSuperAdmin() || $this->isRrhh();
    }

    /**
     * Gestionar requerimientos (cambiar estado, asignar RH, agregar notas)
     */
    public function puedeGestionarRequerimientos(): bool
    {
        return $this->isSuperAdmin() || $this->isRrhh();
    }

    public function esGerenteGeneral(): bool
    {
        return (bool) $this->es_gerente_general;
    }

    public function tieneFirmaRegistrada(): bool
    {
        return !empty($this->firma_imagen);
    }

    /**
     * Módulo Productividad Sedes — Cobranza:
     * - Sede puede ver y enviar su propio reporte.
     * - Admin/Superadmin ven todos.
     */
    public function puedeVerCobranzaSedes(): bool
    {
        return $this->isSuperAdmin() || $this->isAdmin() || $this->isSede()
            || $this->puede_ver_productividad_sedes;
    }

    /**
     * Ver pediente de entrega montura:
     * - Superadmin, admin y consultor pueden ver por defecto
     */
    public function puedeVerPendienteEntregaMontura(): bool
    {
        return $this->isSuperAdmin() || $this->isAdmin() || $this->puede_ver_pendiente_entrega_montura;
    }

    public function puedeVerMotorizados(): bool
    {
        return $this->isSuperAdmin() || $this->isAdmin() || (bool) $this->puede_ver_motorizados;
    }

    public function getRoleName()
    {
        if ($this->isSuperAdmin()) return 'Super Admin';
        if ($this->isAdmin()) return 'Admin';
        if ($this->isRrhh()) return 'RRHH';
        if ($this->isMarketing()) return 'Marketing';
        if ($this->isConsultor()) return 'Consultor';
        if ($this->isSede()) return 'Sede - ' . $this->getSedeName();

        return 'Usuario';
    }

    // ---------------- SCOPES -------------------

    /**
     * Obtener el nombre de la sede del usuario
     */
    public function getSedeName(): string
    {
        return $this->sede ? ucfirst(strtolower($this->sede)) : 'Sin asignar';
    }

    /**
     * Verificar si el usuario tiene una sede asignada
     */
    public function hasSede(): bool
    {
        return !is_null($this->sede) && !empty($this->sede);
    }

    public function isOnline(): bool
    {
        return $this->activeSessions()
            ->where('last_activity', '>=', now()->subMinutes(10))
            ->exists();
    }

    public function lastActivityAt(): ?\Illuminate\Support\Carbon
    {
        $session = $this->sessions()
            ->whereNotNull('last_activity')
            ->orderByDesc('last_activity')
            ->first();

        return $session?->last_activity?->setTimezone('America/Lima');
    }

    public function lastSeenText(): string
    {
        if ($this->isOnline()) {
            return 'En línea';
        }

        $lastActivity = $this->lastActivityAt();

        if (! $lastActivity) {
            return $this->last_login_at
                ? 'Última vez ' . $this->last_login_at->setTimezone('America/Lima')->diffForHumans()
                : 'Sin actividad';
        }

        $nowLima = now('America/Lima');

        // Si por algún desfase del reloj del servidor la hora queda en el futuro, truncamos a ahora
        if ($lastActivity->gt($nowLima)) {
            $lastActivity = $nowLima->copy();
        }

        if ($lastActivity->isToday()) {
            return 'Última vez hoy a las ' . $lastActivity->format('H:i');
        } elseif ($lastActivity->isYesterday()) {
            return 'Última vez ayer a las ' . $lastActivity->format('H:i');
        } elseif ($lastActivity->diffInDays($nowLima) < 7) {
            return 'Última vez ' . $lastActivity->diffForHumans();
        }

        return 'Última vez el ' . $lastActivity->format('d/m/Y');
    }

    public function hasTwoFactorEnabled(): bool
    {
        return !is_null($this->two_factor_secret) && !is_null($this->two_factor_confirmed_at);
    }

    public function updateLastLogin(): void
    {
        $this->update(['last_login_at' => now()]);
    }

    public function totalUsageTime(): int
    {
        return $this->sessions()
            ->whereNotNull('session_duration')
            ->sum('session_duration');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOnline($query)
    {
        return $query->whereHas('activeSessions', function ($q) {
            $q->where('last_activity', '>=', now()->subMinutes(10));
        });
    }
}

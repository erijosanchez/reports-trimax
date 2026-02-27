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
        'email',
        'password',
        'sede',
        'puede_ver_ventas_consolidadas',
        'puede_ver_descuentos_especiales',
        'puede_ver_consultar_orden',
        'puede_ver_acuerdos_comerciales',
        'puede_ver_lead_time',
        'puede_crear_requerimiento',
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
        'puede_crear_requerimiento' => 'boolean',
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
            ->where('last_activity', '>=', now()->subMinutes(5))
            ->exists();
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
            $q->where('last_activity', '>=', now()->subMinutes(5));
        });
    }

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

    /**
     * Puede crear requerimientos de personal.
     * SuperAdmin siempre puede, el resto depende del flag.
     */
    public function puedeCrearRequerimiento(): bool
    {
        return $this->isSuperAdmin() || (bool) $this->puede_crear_requerimiento;
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
}

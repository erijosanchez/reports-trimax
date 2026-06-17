<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserLocation;
use App\Services\ActivityLogService;
use App\Services\LocationService;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * Arma el arreglo plano de una ubicación para mapa/tabla.
     */
    private function mapLocationPayload(User $user, UserLocation $location): array
    {
        return [
            'user_id'           => $user->id,
            'name'              => $user->name,
            'email'             => $user->email,
            'latitude'          => $location->latitude !== null ? (float) $location->latitude : null,
            'longitude'         => $location->longitude !== null ? (float) $location->longitude : null,
            'city'              => $location->city,
            'region'            => $location->region,
            'country'           => $location->country,
            'formatted_address' => $location->formatted_address,
            'street_name'       => $location->street_name,
            'street_number'     => $location->street_number,
            'district'          => $location->district,
            'accuracy'          => $location->accuracy !== null ? (float) $location->accuracy : null,
            'last_seen'         => $location->created_at?->diffForHumans(),
            'is_online'         => $user->isOnline(),
            'history_url'       => route('admin.locations.user-history', $user->id),
        ];
    }

    /**
     * Mapa con la última ubicación GPS de cada usuario.
     */
    public function map()
    {
        $usersWithLocations = User::with('latestGpsLocation')
            ->whereHas('locations', fn($q) => $q->where('location_type', 'gps'))
            ->get()
            ->map(function ($user) {
                $location = $user->latestGpsLocation;
                return $location ? $this->mapLocationPayload($user, $location) : null;
            })
            ->filter()
            ->values();

        return view('admin.locations.map', compact('usersWithLocations'));
    }

    /**
     * Lista/historial global de ubicaciones GPS con filtros.
     */
    public function index(Request $request)
    {
        $query = UserLocation::with(['user', 'session'])
            ->where('location_type', 'gps')
            ->latest('created_at');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('city')) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $locations = $query->paginate(50)->withQueryString();
        $users = User::active()->orderBy('name')->get();

        return view('admin.locations.index', compact('locations', 'users'));
    }

    /**
     * Historial de ubicaciones GPS de un usuario específico.
     */
    public function userHistory($userId)
    {
        $user = User::findOrFail($userId);

        $base = UserLocation::where('user_id', $userId)->where('location_type', 'gps');

        $locations = (clone $base)
            ->orderByDesc('created_at')
            ->paginate(50);

        $uniqueCities = LocationService::getUniqueCities($userId);

        $stats = [
            'total_locations'   => (clone $base)->count(),
            'cities_visited'    => $uniqueCities->count(),
            'countries_visited' => (clone $base)->whereNotNull('country')->distinct()->count('country'),
        ];

        // Puntos para el mini-mapa: últimos 100, en orden cronológico (antiguo → reciente).
        $mapPoints = (clone $base)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderByDesc('created_at')
            ->limit(100)
            ->get(['latitude', 'longitude', 'accuracy', 'formatted_address', 'city', 'created_at'])
            ->map(fn($l) => [
                'lat'      => (float) $l->latitude,
                'lng'      => (float) $l->longitude,
                'accuracy' => $l->accuracy !== null ? (float) $l->accuracy : null,
                'address'  => $l->formatted_address ?: ($l->city ?: 'Ubicación no disponible'),
                'time'     => $l->created_at?->format('d/m/Y H:i'),
            ])
            ->reverse()
            ->values();

        // Ver la ubicación de otra persona es sensible: queda auditado.
        ActivityLogService::log(
            auth()->id(),
            'view_location_history',
            'User',
            $user->id,
            "Consultó el historial de ubicaciones de {$user->name}"
        );

        return view('admin.locations.user-history', compact('user', 'locations', 'uniqueCities', 'stats', 'mapPoints'));
    }

    /**
     * API: última ubicación GPS de cada usuario (para refrescar el mapa sin recargar).
     */
    public function liveLocations()
    {
        $locations = User::with('latestGpsLocation')
            ->whereHas('locations', fn($q) => $q->where('location_type', 'gps'))
            ->get()
            ->map(function ($user) {
                $location = $user->latestGpsLocation;
                return $location ? $this->mapLocationPayload($user, $location) : null;
            })
            ->filter(fn($loc) => $loc['latitude'] !== null && $loc['longitude'] !== null)
            ->values();

        return response()->json([
            'locations'  => $locations,
            'online'     => $locations->where('is_online', true)->count(),
            'updated_at' => now('America/Lima')->format('H:i:s'),
        ]);
    }
}

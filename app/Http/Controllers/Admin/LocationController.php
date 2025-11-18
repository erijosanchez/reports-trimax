<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserLocation;
use App\Services\LocationService;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * Mapa con todas las ubicaciones en tiempo real
     */
    public function map()
    {
        // Obtener usuarios con su última ubicación
        $usersWithLocations = User::with(['locations' => function ($query) {
            $query->latest('created_at')->limit(1);
        }])
            ->whereHas('locations')
            ->get()
            ->map(function ($user) {
                $location = $user->locations->first();
                return [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'latitude' => $location->latitude,
                    'longitude' => $location->longitude,
                    'city' => $location->city,
                    'region' => $location->region,
                    'country' => $location->country,
                    'ip' => $location->ip_address,
                    'is_vpn' => $location->is_vpn,
                    'location_type' => $location->location_type ?? 'ip',          // ← NUEVO
                    'formatted_address' => $location->formatted_address,  // ← NUEVO
                    'accuracy' => $location->accuracy,                    // ← NUEVO
                    'street_name' => $location->street_name,              // ← NUEVO
                    'street_number' => $location->street_number,
                    'district' => $location->district,
                    'last_seen' => $location->created_at->diffForHumans(),
                    'is_online' => $user->isOnline(),
                ];
            })
            ->filter(fn($loc) => $loc['latitude'] && $loc['longitude']);

        return view('admin.locations.map', compact('usersWithLocations'));
    }

    /**
     * Lista de ubicaciones por usuario
     */
    public function index(Request $request)
    {
        $query = UserLocation::with(['user', 'session'])
            ->latest('created_at');

        // Filtrar por usuario
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filtrar por ciudad
        if ($request->filled('city')) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }

        // Filtrar por fecha
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $locations = $query->paginate(50);
        $users = User::active()->orderBy('name')->get();

        return view('admin.locations.index', compact('locations', 'users'));
    }

    /**
     * Historial de ubicaciones de un usuario específico
     */
    public function userHistory($userId)
    {
        $user = User::findOrFail($userId);

        $locations = UserLocation::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        $uniqueCities = LocationService::getUniqueCities($userId);

        $stats = [
            'total_locations' => UserLocation::where('user_id', $userId)->count(),
            'cities_visited' => $uniqueCities->count(),
            'countries_visited' => UserLocation::where('user_id', $userId)
                ->whereNotNull('country')
                ->distinct('country')
                ->count(),
        ];

        return view('admin.locations.user-history', compact('user', 'locations', 'uniqueCities', 'stats'));
    }

    /**
     * API: Obtener ubicaciones en tiempo real (para el mapa)
     */
    public function liveLocations()
    {
        $locations = User::with(['locations' => function ($query) {
            $query->latest('created_at')->limit(1);
        }])
            ->whereHas('locations')
            ->get()
            ->map(function ($user) {
                $location = $user->locations->first();
                return [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'latitude' => $location->latitude,
                    'longitude' => $location->longitude,
                    'city' => $location->formatted_location,
                    'is_online' => $user->isOnline(),
                ];
            })
            ->filter(fn($loc) => $loc['latitude'] && $loc['longitude'])
            ->values();

        return response()->json($locations);
    }
}

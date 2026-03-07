<?php $__env->startSection('title', 'Mapa de Ubicaciones'); ?>

<?php $__env->startSection('content'); ?>
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-12">
                <div class="home-tab">
                    <div class="d-sm-flex align-items-center justify-content-between border-bottom"></div>
                    <div class="tab-content-basic tab-content">
                        <div class="tab-pane fade show active" id="overview" role="tabpanel">
                            
                            <!-- TÍTULO -->
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="d-flex align-items-center justify-content-between statistics-details">
                                        <div>
                                            <h3 class="rate-percentage"><i class="mdi mdi-map"></i> Mapa de Ubicaciones GPS</h3>
                                            <p style="color:#666;">Seguimiento en tiempo real de ubicaciones precisas</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ESTADÍSTICAS -->
                            <div class="row">
                                <div class="d-flex flex-column col-lg-4">
                                    <div class="flex-grow row">
                                        <div class="grid-margin col-md-6 col-lg-12 stretch-card">
                                            <div class="bg-warning card-rounded card">
                                                <div class="pb-3 card-body">
                                                    <div class="row">
                                                        <div class="col-sm-12">
                                                            <p class="mb-1 text-white"><i class="mdi mdi-map-marker"></i> Usuarios con GPS</p>
                                                            <h2 class="text-white"><?php echo $usersWithLocations->count(); ?></h2>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex flex-column col-lg-4">
                                    <div class="flex-grow row">
                                        <div class="grid-margin col-md-6 col-lg-12 stretch-card">
                                            <div class="bg-success card-rounded card">
                                                <div class="pb-3 card-body">
                                                    <div class="row">
                                                        <div class="col-sm-12">
                                                            <p class="mb-1 text-white">🟢 En línea ahora</p>
                                                            <h2 class="text-white">
                                                                <?php echo $usersWithLocations->where('is_online', true)->count(); ?>

                                                            </h2>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex flex-column col-lg-4">
                                    <div class="flex-grow row">
                                        <div class="grid-margin col-md-6 col-lg-12 stretch-card">
                                            <div class="bg-primary card-rounded card">
                                                <div class="pb-3 card-body">
                                                    <div class="row">
                                                        <div class="col-sm-12">
                                                            <p class="mb-1 text-white"><i class="mdi mdi-city"></i> Ciudades</p>
                                                            <h2 class="text-white">
                                                                <?php echo $usersWithLocations->pluck('city')->unique()->filter()->count(); ?>

                                                            </h2>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- MAPA INTERACTIVO -->
                            <div class="row">
                                <div class="d-flex flex-column col-lg-12">
                                    <div class="flex-grow row">
                                        <div class="grid-margin col-12 stretch-card">
                                            <div class="card-rounded card">
                                                <div style="border:1px solid #ddd;border-radius:4px;overflow:hidden;margin-bottom:2rem;">
                                                    <div id="map" style="height:600px;width:100%;"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- TABLA DE UBICACIONES -->
                                <div class="d-flex flex-column col-lg-12">
                                    <div class="flex-grow row">
                                        <div class="grid-margin col-12 stretch-card">
                                            <div class="card-rounded card">
                                                <div class="card-body">
                                                    <div class="d-sm-flex align-items-start justify-content-between">
                                                        <div>
                                                            <h3 class="card-title"><i class="mdi mdi-map-marker"></i> Ubicaciones GPS Actuales</h3>
                                                        </div>
                                                    </div>
                                                    <div class="table-responsive mt-3">
                                                        <table class="table">
                                                            <thead>
                                                                <tr style="background:#f0f0f0;">
                                                                    <th style="padding:0.75rem;border:1px solid #ddd;">Estado</th>
                                                                    <th style="padding:0.75rem;border:1px solid #ddd;">Usuario</th>
                                                                    <th style="padding:0.75rem;border:1px solid #ddd;">📍 Ubicación GPS</th>
                                                                    <th style="padding:0.75rem;border:1px solid #ddd;">🎯 Precisión</th>
                                                                    <th style="padding:0.75rem;border:1px solid #ddd;">⏰ Última Actualización</th>
                                                                    <th style="padding:0.75rem;border:1px solid #ddd;">Acciones</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php $__empty_1 = true; $__currentLoopData = $usersWithLocations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $location): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                                    <tr>
                                                                        <!-- Estado Online/Offline -->
                                                                        <td style="padding:0.75rem;border:1px solid #ddd;text-align:center;">
                                                                            <?php if($location['is_online']): ?>
                                                                                <span style="display:inline-block;width:12px;height:12px;background:#28a745;border-radius:50%;" title="Online"></span>
                                                                            <?php else: ?>
                                                                                <span style="display:inline-block;width:12px;height:12px;background:#6c757d;border-radius:50%;" title="Offline"></span>
                                                                            <?php endif; ?>
                                                                        </td>

                                                                        <!-- Usuario -->
                                                                        <td style="padding:0.75rem;border:1px solid #ddd;">
                                                                            <strong><?php echo $location['name']; ?></strong><br>
                                                                            <small style="color:#666;"><?php echo $location['email']; ?></small>
                                                                        </td>

                                                                        <!-- Ubicación GPS -->
                                                                        <td style="padding:0.75rem;border:1px solid #ddd;">
                                                                            <div style="display:flex;align-items:start;gap:0.5rem;">
                                                                                <span style="font-size:1.5rem;">📱</span>
                                                                                <div>
                                                                                    <?php if($location['formatted_address']): ?>
                                                                                        <div style="font-size:0.95rem;margin-bottom:0.25rem;">
                                                                                            <?php echo $location['formatted_address']; ?>

                                                                                        </div>
                                                                                    <?php elseif($location['city']): ?>
                                                                                        <div style="font-size:0.95rem;">
                                                                                            <?php if($location['street_name']): ?>
                                                                                                <?php echo $location['street_name']; ?>

                                                                                                <?php if($location['street_number']): ?>
                                                                                                    #<?php echo $location['street_number']; ?>

                                                                                                <?php endif; ?>
                                                                                                <br>
                                                                                            <?php endif; ?>
                                                                                            <?php if($location['district']): ?>
                                                                                                <?php echo $location['district']; ?>,
                                                                                            <?php endif; ?>
                                                                                            <?php echo $location['city']; ?>

                                                                                            <?php if($location['region']): ?>
                                                                                                , <?php echo $location['region']; ?>

                                                                                            <?php endif; ?>
                                                                                        </div>
                                                                                    <?php else: ?>
                                                                                        <small style="color:#999;">Ubicación no disponible</small>
                                                                                    <?php endif; ?>
                                                                                    
                                                                                    <?php if($location['latitude'] && $location['longitude']): ?>
                                                                                        <small style="color:#999;font-size:0.8rem;">
                                                                                            <?php echo number_format($location['latitude'], 4); ?>, <?php echo number_format($location['longitude'], 4); ?>

                                                                                        </small>
                                                                                    <?php endif; ?>
                                                                                </div>
                                                                            </div>
                                                                        </td>

                                                                        <!-- Precisión -->
                                                                        <td style="padding:0.75rem;border:1px solid #ddd;text-align:center;">
                                                                            <?php if($location['accuracy']): ?>
                                                                                <span style="padding:0.3rem 0.8rem;background:
                                                                                    <?php if($location['accuracy'] < 50): ?> #28a745
                                                                                    <?php elseif($location['accuracy'] < 100): ?> #5cb85c
                                                                                    <?php elseif($location['accuracy'] < 500): ?> #ffc107
                                                                                    <?php else: ?> #dc3545
                                                                                    <?php endif; ?>
                                                                                    ;color:white;border-radius:12px;font-size:0.85rem;font-weight:bold;">
                                                                                    <?php echo number_format($location['accuracy'], 0); ?>m
                                                                                </span>
                                                                                <br>
                                                                                <small style="color:#666;">
                                                                                    <?php if($location['accuracy'] < 50): ?>
                                                                                        ⭐ Excelente
                                                                                    <?php elseif($location['accuracy'] < 100): ?>
                                                                                        ✓ Buena
                                                                                    <?php elseif($location['accuracy'] < 500): ?>
                                                                                        △ Regular
                                                                                    <?php else: ?>
                                                                                        ○ Baja
                                                                                    <?php endif; ?>
                                                                                </small>
                                                                            <?php else: ?>
                                                                                <span style="color:#999;">N/A</span>
                                                                            <?php endif; ?>
                                                                        </td>

                                                                        <!-- Última Actualización -->
                                                                        <td style="padding:0.75rem;border:1px solid #ddd;">
                                                                            <small><?php echo $location['last_seen']; ?></small>
                                                                        </td>

                                                                        <!-- Acciones -->
                                                                        <td style="padding:0.75rem;border:1px solid #ddd;">
                                                                            <a href="<?php echo route('admin.locations.user-history', $location['user_id']); ?>" 
                                                                               class="btn btn-sm btn-primary">
                                                                                📊 Ver Historial
                                                                            </a>
                                                                        </td>
                                                                    </tr>
                                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                                    <tr>
                                                                        <td colspan="6" style="padding:2rem;text-align:center;color:#999;">
                                                                            📍 No hay ubicaciones GPS registradas aún
                                                                        </td>
                                                                    </tr>
                                                                <?php endif; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        // Inicializar mapa centrado en Perú
        const map = L.map('map').setView([-12.0464, -77.0428], 6);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        const locations = <?php echo json_encode($usersWithLocations, 15, 512) ?>;

        if (locations.length > 0) {
            const bounds = [];
            
            locations.forEach(location => {
                if (!location.latitude || !location.longitude) return;
                
                bounds.push([location.latitude, location.longitude]);
                
                const color = location.is_online ? '#28a745' : '#6c757d';
                
                const marker = L.circleMarker([location.latitude, location.longitude], {
                    radius: 10,
                    fillColor: color,
                    color: 'white',
                    weight: 2,
                    opacity: 1,
                    fillOpacity: 0.8
                }).addTo(map);

                const popupContent = `
                    <div style="min-width:220px;">
                        <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.5rem;">
                            <span style="font-size:1.3rem;">📱</span>
                            <strong>${location.name}</strong>
                        </div>
                        <small style="color:#666;">${location.email}</small>
                        <hr style="margin:0.5rem 0;">
                        ${location.formatted_address || location.city || 'Ubicación no disponible'}
                        ${location.accuracy ? `<br><small style="color:#28a745;">Precisión: ${Math.round(location.accuracy)}m</small>` : ''}
                        <br><small style="color:#999;">${location.last_seen}</small>
                    </div>
                `;

                marker.bindPopup(popupContent);
            });

            if (bounds.length > 0) {
                map.fitBounds(bounds, { padding: [50, 50] });
            }
        }

        // Auto-refresh cada 60 segundos
        setInterval(() => location.reload(), 60000);
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/admin/locations/map.blade.php ENDPATH**/ ?>
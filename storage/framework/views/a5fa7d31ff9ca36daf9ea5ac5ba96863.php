<?php $__env->startSection('title', $file->original_name); ?>

<?php $__env->startSection('content'); ?>
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-12">
                <div class="home-tab">
                    <div class="tab-content-basic tab-content">
                        <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview">

                            <!-- Header -->
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="d-flex align-items-center justify-content-between mb-4">
                                        <div class="d-flex align-items-center">
                                            <a href="<?php echo route('files.index'); ?>" class="btn btn-light btn-sm me-3">
                                                <i class="mdi mdi-arrow-left"></i>
                                            </a>
                                            <div>
                                                <h3 class="rate-percentage mb-0">
                                                    <?php if($file->file_type === 'pdf'): ?>
                                                        <i class="mdi mdi-file-pdf-box text-danger me-2"></i>
                                                    <?php else: ?>
                                                        <i class="mdi mdi-file-excel-box text-success me-2"></i>
                                                    <?php endif; ?>
                                                    <?php echo $file->original_name; ?>

                                                </h3>
                                                <?php if($file->description): ?>
                                                    <p class="text-muted mt-1 mb-0"><?php echo $file->description; ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div>
                                            <a href="<?php echo route('files.download', $file->id); ?>" class="btn btn-success">
                                                <i class="mdi mdi-download me-1"></i>Descargar
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Información del Archivo -->
                            <div class="row mb-4">
                                <div class="col-lg-12 grid-margin stretch-card">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="card-title mb-4">
                                                <i class="mdi mdi-information-outline text-primary me-2"></i>Información del
                                                Archivo
                                            </h4>
                                            <div class="row">
                                                <div class="col-md-3 mb-3">
                                                    <div class="d-flex align-items-center">
                                                        <i class="mdi mdi-file-document text-primary me-2 mdi-24px"></i>
                                                        <div>
                                                            <small class="text-muted d-block">Tipo de Archivo</small>
                                                            <span
                                                                class="badge badge-primary"><?php echo strtoupper($file->file_type); ?></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-3 mb-3">
                                                    <div class="d-flex align-items-center">
                                                        <i class="mdi mdi-weight text-primary me-2 mdi-24px"></i>
                                                        <div>
                                                            <small class="text-muted d-block">Tamaño</small>
                                                            <strong><?php echo $file->file_size_formatted; ?></strong>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-3 mb-3">
                                                    <div class="d-flex align-items-center">
                                                        <img class="img-xs rounded-circle me-2"
                                                            src="https://ui-avatars.com/api/?name=<?php echo urlencode($file->user->name); ?>&background=6366f1&color=fff"
                                                            alt="profile">
                                                        <div>
                                                            <small class="text-muted d-block">Subido por</small>
                                                            <strong><?php echo $file->user->name; ?></strong>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-3 mb-3">
                                                    <div class="d-flex align-items-center">
                                                        <i class="mdi mdi-calendar-clock text-primary me-2 mdi-24px"></i>
                                                        <div>
                                                            <small class="text-muted d-block">Fecha de Subida</small>
                                                            <strong><?php echo $file->created_at->format('d/m/Y H:i'); ?></strong>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Vista Previa -->
                            <div class="row">
                                <div class="col-lg-12 grid-margin stretch-card">
                                    <div class="card">
                                        <div class="card-body">
                                            <h4 class="card-title mb-4">
                                                <i class="mdi mdi-eye-outline text-primary me-2"></i>Vista Previa
                                            </h4>

                                            <?php if($file->file_type === 'pdf'): ?>
                                                <div class="pdf-viewer-container">
                                                    <iframe src="data:application/pdf;base64,<?php echo base64_encode($content); ?>"
                                                        width="100%" height="800"
                                                        style="border: 1px solid #e3e6f0; border-radius: 8px;">
                                                        <p>Tu navegador no soporta visualización de PDFs.
                                                            <a href="<?php echo route('files.download', $file->id); ?>">Descarga el
                                                                archivo aquí</a>
                                                        </p>
                                                    </iframe>
                                                </div>
                                            <?php elseif(in_array($file->file_type, ['xlsx', 'xls', 'csv'])): ?>
                                                <div class="text-center py-5">
                                                    <i
                                                        class="mdi mdi-file-excel-box text-success mdi-48px mb-3 d-block"></i>
                                                    <h4 class="mb-3">Vista previa de Excel</h4>
                                                    <p class="text-muted mb-4">
                                                        La vista previa de archivos Excel no está disponible en el
                                                        navegador.<br>
                                                        Descarga el archivo para verlo en Microsoft Excel o Google Sheets.
                                                    </p>
                                                    <div class="d-flex justify-content-center gap-2">
                                                        <a href="<?php echo route('files.download', $file->id); ?>"
                                                            class="btn btn-success">
                                                            <i class="mdi mdi-download me-1"></i>Descargar Archivo
                                                        </a>
                                                        <a href="https://docs.google.com/spreadsheets/u/0/" target="_blank"
                                                            class="btn btn-outline-success">
                                                            <i class="mdi mdi-google-drive me-1"></i>Abrir en Google Sheets
                                                        </a>
                                                    </div>
                                                    <small class="text-muted mt-3 d-block">
                                                        También puedes abrir el archivo con Microsoft Excel, LibreOffice o
                                                        cualquier aplicación compatible
                                                    </small>
                                                </div>
                                            <?php else: ?>
                                                <div class="text-center py-5">
                                                    <i
                                                        class="mdi mdi-file-alert-outline text-warning mdi-48px mb-3 d-block"></i>
                                                    <h4 class="mb-3">Vista previa no disponible</h4>
                                                    <p class="text-muted mb-4">
                                                        Este tipo de archivo no puede ser visualizado en el navegador.
                                                    </p>
                                                    <a href="<?php echo route('files.download', $file->id); ?>"
                                                        class="btn btn-primary">
                                                        <i class="mdi mdi-download me-1"></i>Descargar para Ver
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Botones de Acción -->
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="d-flex gap-2 justify-content-between">
                                        <a href="<?php echo route('files.index'); ?>" class="btn btn-light">
                                            <i class="mdi mdi-arrow-left me-1"></i>Volver a Archivos
                                        </a>

                                        <div class="d-flex gap-2">
                                            <a href="<?php echo route('files.download', $file->id); ?>" class="btn btn-success">
                                                <i class="mdi mdi-download me-1"></i>Descargar
                                            </a>
                                            <?php if($file->user_id === auth()->id() || auth()->user()->isAdmin()): ?>
                                                <form method="POST" action="<?php echo route('files.destroy', $file->id); ?>"
                                                    style="display:inline;">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('DELETE'); ?>
                                                    <button type="submit"
                                                        onclick="return confirm('¿Estás seguro de eliminar este archivo?')"
                                                        class="btn btn-danger">
                                                        <i class="mdi mdi-delete me-1"></i>Eliminar
                                                    </button>
                                                </form>
                                            <?php endif; ?>
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

    <style>
        /* Fix para gap en navegadores que no lo soporten */
        .d-flex.gap-2>*+* {
            margin-left: 0.5rem;
        }

        /* Mejoras para el visor de PDF */
        .pdf-viewer-container {
            position: relative;
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
        }

        .pdf-viewer-container iframe {
            display: block;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        /* Responsive para el iframe */
        @media (max-width: 768px) {
            .pdf-viewer-container iframe {
                height: 500px;
            }
        }
    </style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/files/view.blade.php ENDPATH**/ ?>
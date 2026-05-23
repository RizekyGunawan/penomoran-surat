<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

<div class="container mt-5">
    <h1 class="mb-4">Rekapitulasi Unit Kerja</h1>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger" role="alert">
            <?= $error ?>
        </div>
    <?php elseif (isset($data) && $data['success']): ?>
        
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Informasi Laporan</h5>
                        <p><strong>Tanggal Laporan:</strong> <?= $data['reportDate'] ?></p>
                        <p><strong>Dibuat:</strong> <?= $data['generatedAt'] ?></p>
                        <p>
                            <strong>Data Tersedia:</strong><br>
                            SP2D: <span class="badge <?= $data['dataAvailability']['sp2d'] ? 'bg-success' : 'bg-danger' ?>">
                                <?= $data['dataAvailability']['sp2d'] ? 'Ya' : 'Tidak' ?>
                            </span>
                            Akrual: <span class="badge <?= $data['dataAvailability']['akrual'] ? 'bg-success' : 'bg-danger' ?>">
                                <?= $data['dataAvailability']['akrual'] ? 'Ya' : 'Tidak' ?>
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <h3 class="mb-3">Daftar Unit</h3>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Unit ID</th>
                        <th>Nama Unit</th>
                        <th colspan="2" class="text-center">SP2D</th>
                        <th colspan="2" class="text-center">Akrual</th>
                    </tr>
                    <tr>
                        <th></th>
                        <th></th>
                        <th>Pagu</th>
                        <th>Realisasi (%)</th>
                        <th>Pagu</th>
                        <th>Realisasi (%)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['units'] as $unit): ?>
                        <tr>
                            <td><?= $unit['unitId'] ?></td>
                            <td><?= $unit['unitName'] ?></td>
                            <td><?= number_format($unit['sp2d']['pagu'], 0, ',', '.') ?></td>
                            <td>
                                <?php if ($unit['sp2d']['hasData']): ?>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar" role="progressbar" 
                                             style="width: <?= $unit['sp2d']['capaian'] ?>%" 
                                             aria-valuenow="<?= $unit['sp2d']['capaian'] ?>" 
                                             aria-valuemin="0" aria-valuemax="100">
                                            <?= number_format($unit['sp2d']['capaian'], 2) ?>%
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td><?= number_format($unit['akrual']['pagu'], 0, ',', '.') ?></td>
                            <td>
                                <?php if ($unit['akrual']['hasData']): ?>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-info" role="progressbar" 
                                             style="width: <?= $unit['akrual']['capaian'] ?>%" 
                                             aria-valuenow="<?= $unit['akrual']['capaian'] ?>" 
                                             aria-valuemin="0" aria-valuemax="100">
                                            <?= number_format($unit['akrual']['capaian'], 2) ?>%
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="row mt-4">
            <div class="col-md-12">
                <button class="btn btn-primary" onclick="location.reload()">
                    <i class="fa fa-refresh"></i> Refresh Data
                </button>
            </div>
        </div>
        
    <?php else: ?>
        <div class="alert alert-warning" role="alert">
            Tidak ada data tersedia
        </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>

<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>

    <?php if (session()->has('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> <?= session('error') ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif ?>

    <div class="row">
        <div class="col-md-5 col-lg-4 mx-auto">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-filter"></i> Pilih Jenis Dokumen
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= base_url('dashboard/filterPenomoran') ?>">
                        <?= csrf_field() ?>

                        <div class="form-group mb-4">
                            <label for="jenis_dokumen" class="form-label">
                                <strong>Jenis Dokumen</strong>
                                <span class="text-danger">*</span>
                            </label>
                            <select class="form-control" id="jenis_dokumen" name="jenis_dokumen" required>                                <?php foreach ($jenisDokumenList as $jenis): ?>
                                    <option value="<?= esc($jenis) ?>">
                                        <?= esc($jenis) ?>
                                    </option>
                                <?php endforeach ?>
                            </select>
                        </div>

                        <div class="form-group mb-4">
                            <label for="tahun" class="form-label">
                                <strong>Tahun</strong>
                                <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="form-control" id="tahun" name="tahun" value="<?= $tahun ?>" min="1900" max="2100" required>
                            <small class="form-text text-muted">Tahun otomatis diisi dengan tahun saat ini</small>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-arrow-right"></i> Lanjutkan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?= $this->endSection() ?>

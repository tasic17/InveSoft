<?php
// views/inventory/overview.php
?>
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header pb-0">
                <div class="d-flex align-items-center">
                    <h6>Pregled Inventara</h6>
                    <a href="/inventory/add-product" class="btn btn-primary btn-sm ms-auto">Dodaj Proizvod</a>
                </div>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                <div class="table-responsive p-0">
                    <table class="table align-items-center mb-0">
                        <thead>
                        <tr>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Proizvod</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Kategorija</th>
                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Cena</th>
                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Količina</th>
                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Akcije</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($params as $item): ?>
                            <tr>
                                <td>
                                    <div class="d-flex px-2 py-1">
                                        <div class="d-flex flex-column justify-content-center">
                                            <h6 class="mb-0 text-sm"><?= $item['naziv'] ?></h6>
                                            <p class="text-xs text-secondary mb-0"><?= $item['opis'] ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <p class="text-xs font-weight-bold mb-0"><?= $item['kategorija'] ?></p>
                                </td>
                                <td class="align-middle text-center">
                                    <span class="text-secondary text-xs font-weight-bold"><?= number_format($item['cena'], 2) ?> RSD</span>
                                </td>
                                <td class="align-middle text-center">
                                    <span class="text-secondary text-xs font-weight-bold"><?= $item['kolicina'] ?></span>
                                </td>
                                <td class="align-middle text-center">
                                    <a href="/inventory/update-stock?id=<?= $item['proizvodID'] ?>" class="btn btn-link text-dark px-3 mb-0">
                                        <i class="fas fa-plus text-dark me-2"></i>Ažuriraj
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

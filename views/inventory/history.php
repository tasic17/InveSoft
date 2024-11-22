<?php
// views/inventory/history.php
?>
<div class="card">
    <div class="card-header pb-0">
        <h6>Istorija Promena Zaliha</h6>
    </div>
    <div class="card-body px-0 pt-0 pb-2">
        <div class="table-responsive p-0">
            <table class="table align-items-center mb-0">
                <thead>
                <tr>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Proizvod</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Korisnik</th>
                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tip</th>
                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Količina</th>
                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Datum</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($params as $item): ?>
                    <tr>
                        <td>
                            <div class="d-flex px-2 py-1">
                                <div class="d-flex flex-column justify-content-center">
                                    <h6 class="mb-0 text-sm"><?= $item['proizvod'] ?></h6>
                                </div>
                            </div>
                        </td>
                        <td>
                            <p class="text-xs font-weight-bold mb-0"><?= $item['korisnik'] ?></p>
                        </td>
                        <td class="align-middle text-center">
                            <span class="badge badge-sm bg-<?= $item['tip_promene'] === 'Ulaz' ? 'success' : 'danger' ?>"><?= $item['tip_promene'] ?></span>
                        </td>
                        <td class="align-middle text-center">
                            <span class="text-secondary text-xs font-weight-bold"><?= $item['kolicina'] ?></span>
                        </td>
                        <td class="align-middle text-center">
                            <span class="text-secondary text-xs font-weight-bold"><?= date('d.m.Y H:i', strtotime($item['datum_promene'])) ?></span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
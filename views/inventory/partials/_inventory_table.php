<?php
// views/inventory/partials/_inventory_table.php
?>
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
            <?php foreach ($params['items'] as $item): ?>
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
                        <span class="text-secondary text-xs font-weight-bold">€<?= number_format($item['cena'], 2, ',', '.') ?></span>
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

<?php if ($params['totalPages'] > 1): ?>
    <div class="d-flex justify-content-center mt-4">
        <nav aria-label="Page navigation">
            <ul class="pagination">
                <?php if ($params['currentPage'] > 1): ?>
                    <li class="page-item prev-next">
                        <a class="page-link" href="#" onclick="navigateToPage(<?= $params['currentPage'] - 1 ?>); return false;">
                            <i class="fa fa-angle-left me-2"></i>Previous
                        </a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $params['totalPages']; $i++): ?>
                    <li class="page-item <?= $i === $params['currentPage'] ? 'active' : '' ?>">
                        <a class="page-link" href="#" onclick="navigateToPage(<?= $i ?>); return false;">
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <?php if ($params['currentPage'] < $params['totalPages']): ?>
                    <li class="page-item prev-next">
                        <a class="page-link" href="#" onclick="navigateToPage(<?= $params['currentPage'] + 1 ?>); return false;">
                            Next<i class="fa fa-angle-right ms-2"></i>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
<?php endif; ?>
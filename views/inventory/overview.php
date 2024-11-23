<?php
// views/inventory/overview.php
?>
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header pb-0">
                <div class="d-flex align-items-center">
                    <h6>Pregled Inventara</h6>
                    <div class="ms-auto">
                        <input type="text"
                               id="searchInput"
                               class="form-control"
                               placeholder="Pretraži..."
                               value="<?= htmlspecialchars($params['search']) ?>">
                    </div>
                </div>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                <div id="inventoryTable">
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
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .pagination .page-link {
        min-width: 90px;
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .pagination .page-item:not(.prev-next) .page-link {
        min-width: 40px;
    }
</style>

<script>
    let currentPage = <?= $params['currentPage'] ?>;
    let typingTimer;
    const doneTypingInterval = 300;

    function updateInventory(page = 1, search = '') {
        fetch(`/inventory?ajax=1&page=${page}&search=${encodeURIComponent(search)}`)
            .then(response => response.json())
            .then(data => {
                let tableHtml = generateTableHtml(data.items);
                let paginationHtml = generatePaginationHtml(data.totalPages, data.currentPage);

                document.querySelector('#inventoryTable').innerHTML = tableHtml + paginationHtml;
            })
            .catch(error => console.error('Error:', error));
    }

    function generateTableHtml(items) {
        let html = `
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
                <tbody>`;

        items.forEach(item => {
            html += `
            <tr>
                <td>
                    <div class="d-flex px-2 py-1">
                        <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm">${item.naziv}</h6>
                            <p class="text-xs text-secondary mb-0">${item.opis}</p>
                        </div>
                    </div>
                </td>
                <td>
                    <p class="text-xs font-weight-bold mb-0">${item.kategorija}</p>
                </td>
                <td class="align-middle text-center">
                    <span class="text-secondary text-xs font-weight-bold">€${parseFloat(item.cena).toLocaleString('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>
                </td>
                <td class="align-middle text-center">
                    <span class="text-secondary text-xs font-weight-bold">${item.kolicina}</span>
                </td>
                <td class="align-middle text-center">
                    <a href="/inventory/update-stock?id=${item.proizvodID}" class="btn btn-link text-dark px-3 mb-0">
                        <i class="fas fa-plus text-dark me-2"></i>Ažuriraj
                    </a>
                </td>
            </tr>`;
        });

        html += `
                </tbody>
            </table>
        </div>`;

        return html;
    }

    function generatePaginationHtml(totalPages, currentPage) {
        if (totalPages <= 1) return '';

        let html = `
        <div class="d-flex justify-content-center mt-4">
            <nav aria-label="Page navigation">
                <ul class="pagination">`;

        // Previous button
        if (currentPage > 1) {
            html += `
            <li class="page-item prev-next">
                <a class="page-link" href="#" onclick="navigateToPage(${currentPage - 1}); return false;">
                    <i class="fa fa-angle-left me-2"></i>Previous
                </a>
            </li>`;
        }

        // Page numbers
        for (let i = 1; i <= totalPages; i++) {
            if (i === currentPage) {
                html += `
                <li class="page-item active">
                    <a class="page-link" href="#" onclick="return false;">${i}</a>
                </li>`;
            } else {
                html += `
                <li class="page-item">
                    <a class="page-link" href="#" onclick="navigateToPage(${i}); return false;">${i}</a>
                </li>`;
            }
        }

        // Next button
        if (currentPage < totalPages) {
            html += `
            <li class="page-item prev-next">
                <a class="page-link" href="#" onclick="navigateToPage(${currentPage + 1}); return false;">
                    Next<i class="fa fa-angle-right ms-2"></i>
                </a>
            </li>`;
        }

        html += `
                </ul>
            </nav>
        </div>`;

        return html;
    }

    function navigateToPage(page) {
        currentPage = page;
        updateInventory(page, document.getElementById('searchInput').value);
    }

    document.getElementById('searchInput').addEventListener('keyup', function() {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(() => {
            currentPage = 1;
            updateInventory(1, this.value);
        }, doneTypingInterval);
    });

    document.getElementById('searchInput').addEventListener('keydown', function() {
        clearTimeout(typingTimer);
    });
</script>
<?php
// views/inventory/stockReport.php
$stockChanges = $params['stockChanges'];
$categoryStock = $params['categoryStock'];

// Process stock changes data
$stockData = [];
$runningTotal = 0;
foreach ($stockChanges as $change) {
    $date = $change['date'];
    if ($change['tip_promene'] === 'Ulaz') {
        $runningTotal += intval($change['kolicina']);
    } else {
        $runningTotal -= intval($change['kolicina']);
    }
    $stockData[$date] = $runningTotal;
}
ksort($stockData);

// Process category data
$categoryLabels = array_column($categoryStock, 'category_name');
$categoryValues = array_column($categoryStock, 'total_quantity');

// Get product distribution within each category
$productsByCategory = [];
$query = "SELECT 
            k.naziv as category_name,
            p.naziv as product_name,
            z.kolicina as quantity
          FROM kategorije k
          JOIN proizvodi p ON k.kategorijaID = p.kategorijaID
          JOIN zalihe z ON p.proizvodID = z.proizvodID
          ORDER BY k.naziv, p.naziv";
$promenaModel = new app\models\PromenaZalihaModel();
$products = $promenaModel->executeQuery($query);

foreach ($products as $product) {
    $categoryName = $product['category_name'];
    if (!isset($productsByCategory[$categoryName])) {
        $productsByCategory[$categoryName] = [
            'labels' => [],
            'data' => []
        ];
    }
    $productsByCategory[$categoryName]['labels'][] = $product['product_name'];
    $productsByCategory[$categoryName]['data'][] = intval($product['quantity']);
}
?>

<style>
    .report-header {
        background: linear-gradient(to right, #2E93fA, #4556AC);
        color: white;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .chart-container {
        background: white;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        margin-bottom: 2rem;
    }

    .chart-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #344767;
        text-align: center;
        margin-bottom: 1.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #eee;
    }

    .search-container {
        position: relative;
        margin-bottom: 20px;
    }

    .search-results {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        z-index: 1000;
        max-height: 200px;
        overflow-y: auto;
        display: none;
    }

    .search-item {
        padding: 8px 12px;
        cursor: pointer;
        border-bottom: 1px solid #eee;
    }

    .search-item:hover {
        background: #f8f9fa;
    }

    .chart-wrapper {
        background: white;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
</style>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header pb-0">
                <div class="report-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <h4 class="mb-0">Izveštaj o Zalihama</h4>
                        <button onclick="refreshCharts()" class="btn btn-light btn-sm">
                            <i class="fas fa-sync-alt me-2"></i>Osveži
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Product Search Section -->
                <div class="chart-wrapper">
                    <div class="chart-title">Analiza Proizvoda</div>
                    <div class="row">
                        <div class="col-md-6 mx-auto">
                            <div class="search-container">
                                <input type="text"
                                       id="productSearch"
                                       class="form-control"
                                       placeholder="Pretraži proizvod..."
                                       autocomplete="off">
                                <div id="searchResults" class="search-results"></div>
                            </div>
                        </div>
                    </div>
                    <div id="productChartContainer" class="chart-container" style="height: 400px; display: none;">
                        <canvas id="productChart"></canvas>
                    </div>
                </div>

                <!-- Stock Changes Line Chart -->
                <div class="chart-wrapper">
                    <div class="chart-title">Ukupne Zalihe kroz Vreme</div>
                    <div class="chart-container" style="height: 400px;">
                        <canvas id="stockLineChart"></canvas>
                    </div>
                </div>

                <!-- Main Category Distribution -->
                <div class="chart-wrapper">
                    <div class="chart-title">Distribucija Zaliha po Kategorijama</div>
                    <div class="chart-container" style="height: 400px;">
                        <canvas id="categoryPieChart"></canvas>
                    </div>
                </div>

                <!-- Individual Category Charts -->
                <div class="chart-wrapper">
                    <div class="chart-title">Distribucija Proizvoda po Kategorijama</div>
                    <div class="row">
                        <?php foreach ($productsByCategory as $categoryName => $data): ?>
                            <div class="col-md-6 mb-4">
                                <div class="chart-container" style="height: 300px;">
                                    <div class="chart-title"><?= htmlspecialchars($categoryName) ?></div>
                                    <canvas id="categoryChart_<?= md5($categoryName) ?>"></canvas>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Chart color palette
    const COLORS = [
        '#2E93fA', '#66DA26', '#546E7A', '#E91E63', '#FF9800',
        '#4CAF50', '#8884d8', '#FF5722', '#9C27B0', '#3F51B5'
    ];

    // Line Chart
    const lineCtx = document.getElementById('stockLineChart').getContext('2d');
    const stockLineChart = new Chart(lineCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode(array_keys($stockData)) ?>,
            datasets: [{
                label: 'Ukupna Količina',
                data: <?= json_encode(array_values($stockData)) ?>,
                borderColor: '#8884d8',
                borderWidth: 2,
                fill: false,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    backgroundColor: 'rgba(255, 255, 255, 0.95)',
                    titleColor: '#000',
                    bodyColor: '#000',
                    borderColor: '#ddd',
                    borderWidth: 1,
                    padding: 10,
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return `Količina: ${context.parsed.y}`;
                        }
                    }
                },
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Količina'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Datum'
                    }
                }
            }
        }
    });

    // Main Category Pie Chart
    const pieCtx = document.getElementById('categoryPieChart').getContext('2d');
    const categoryPieChart = new Chart(pieCtx, {
        type: 'pie',
        data: {
            labels: <?= json_encode($categoryLabels) ?>,
            datasets: [{
                data: <?= json_encode($categoryValues) ?>,
                backgroundColor: COLORS.slice(0, <?= count($categoryLabels) ?>),
                borderWidth: 1,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    backgroundColor: 'rgba(255, 255, 255, 0.95)',
                    titleColor: '#000',
                    bodyColor: '#000',
                    borderColor: '#ddd',
                    borderWidth: 1,
                    padding: 10,
                    callbacks: {
                        label: function(context) {
                            return `Količina: ${context.parsed}`;
                        }
                    }
                },
                legend: {
                    position: 'right',
                    labels: {
                        boxWidth: 15,
                        padding: 15
                    }
                }
            }
        }
    });

    // Individual Category Charts
    <?php foreach ($productsByCategory as $categoryName => $data): ?>
    new Chart(document.getElementById('categoryChart_<?= md5($categoryName) ?>').getContext('2d'), {
        type: 'pie',
        data: {
            labels: <?= json_encode($data['labels']) ?>,
            datasets: [{
                data: <?= json_encode($data['data']) ?>,
                backgroundColor: COLORS.slice(0, <?= count($data['labels']) ?>),
                borderWidth: 1,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    backgroundColor: 'rgba(255, 255, 255, 0.95)',
                    titleColor: '#000',
                    bodyColor: '#000',
                    borderColor: '#ddd',
                    borderWidth: 1,
                    padding: 10,
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            return `${context.label}: ${context.parsed} (${percentage}%)`;
                        }
                    }
                },
                legend: {
                    position: 'right',
                    labels: {
                        boxWidth: 15,
                        padding: 10,
                        font: {
                            size: 11
                        }
                    }
                }
            }
        }
    });
    <?php endforeach; ?>

    // Product Search and Chart
    let productChart = null;
    let searchTimeout = null;

    document.getElementById('productSearch').addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        const searchTerm = e.target.value;

        if (searchTerm.length < 2) {
            document.getElementById('searchResults').style.display = 'none';
            return;
        }

        searchTimeout = setTimeout(() => {
            fetch(`/inventory/search-products?term=${encodeURIComponent(searchTerm)}`)
                .then(response => response.json())
                .then(products => {
                    const resultsDiv = document.getElementById('searchResults');
                    resultsDiv.innerHTML = '';

                    products.forEach(product => {
                        const div = document.createElement('div');
                        div.className = 'search-item';
                        div.textContent = product.naziv;
                        div.onclick = () => selectProduct(product.proizvodID, product.naziv);
                        resultsDiv.appendChild(div);
                    });

                    resultsDiv.style.display = products.length ? 'block' : 'none';
                });
        }, 300);
    });

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.search-container')) {
            document.getElementById('searchResults').style.display = 'none';
        }
    });

    function selectProduct(productId, productName) {
        document.getElementById('searchResults').style.display = 'none';
        document.getElementById('productSearch').value = productName;

        fetch(`/inventory/product-history?id=${productId}`)
            .then(response => response.json())
            .then(data => {
                const chartContainer = document.getElementById('productChartContainer');
                chartContainer.style.display = 'block';

                if (productChart) {
                    productChart.destroy();
                }

                const ctx = document.getElementById('productChart').getContext('2d');
                productChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.dates,
                        datasets: [{
                            label: 'Ulaz',
                            data: data.inflow,
                            borderColor: '#66DA26',
                            backgroundColor: '#66DA26',
                            type: 'bar'
                        }, {
                            label: 'Izlaz',
                            data: data.outflow,
                            borderColor: '#E91E63',
                            backgroundColor: '#E91E63',
                            type: 'bar'
                        }, {
                            label: 'Ukupno Stanje',
                            data: data.totalStock,
                            borderColor: '#2E93fA',
                            backgroundColor: 'rgba(46, 147, 250, 0.1)',
                            type: 'line',
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                                backgroundColor: 'rgba(255, 255, 255, 0.95)',
                                titleColor: '#000',
                                bodyColor: '#000',
                                borderColor: '#ddd',
                                borderWidth: 1,
                                padding: 10
                            },
                            legend: {
                                position: 'top',
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Količina'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Datum'
                                }
                            }
                        }
                    }
                });
            });
    }

    // Function to refresh charts
    function refreshCharts() {
        fetch('/inventory/stock-report?ajax=1')
            .then(response => response.json())
            .then(data => {
                // Process stock changes data
                const stockData = {};
                let runningTotal = 0;
                data.stockChanges
                    .sort((a, b) => new Date(a.date) - new Date(b.date))
                    .forEach(change => {
                        if (change.tip_promene === 'Ulaz') {
                            runningTotal += parseInt(change.kolicina);
                        } else {
                            runningTotal -= parseInt(change.kolicina);
                        }
                        stockData[change.date] = runningTotal;
                    });

                // Update line chart
                stockLineChart.data.labels = Object.keys(stockData);
                stockLineChart.data.datasets[0].data = Object.values(stockData);
                stockLineChart.update();

                // Update main category pie chart
                categoryPieChart.data.labels = data.categoryStock.map(item => item.category_name);
                categoryPieChart.data.datasets[0].data = data.categoryStock.map(item => item.total_quantity);
                categoryPieChart.update();

                // Reload the page to update individual category charts
                // This ensures all category charts are properly updated with new data
                window.location.reload();
            })
            .catch(error => {
                console.error('Error refreshing charts:', error);
                toastr.error('Došlo je do greške prilikom osvežavanja podataka.');
            });
    }

    // Auto refresh every 5 minutes
    setInterval(refreshCharts, 300000);
</script>
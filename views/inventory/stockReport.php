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

<div class="row">
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header pb-0">
                <div class="d-flex align-items-center">
                    <h6>Izveštaj o Zalihama</h6>
                    <button onclick="refreshCharts()" class="btn btn-sm btn-primary ms-auto">
                        <i class="fas fa-sync-alt me-2"></i>Osveži
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Stock Changes Line Chart -->
                <div class="chart-container" style="height: 400px; margin-bottom: 2rem;">
                    <h6 class="text-center mb-3">Ukupne Zalihe kroz Vreme</h6>
                    <canvas id="stockLineChart"></canvas>
                </div>

                <!-- Main Category Distribution Pie Chart -->
                <div class="chart-container" style="height: 400px; margin-bottom: 2rem;">
                    <h6 class="text-center mb-3">Distribucija Zaliha po Kategorijama</h6>
                    <canvas id="categoryPieChart"></canvas>
                </div>

                <!-- Individual Category Charts -->
                <h6 class="text-center mb-4">Distribucija Proizvoda po Kategorijama</h6>
                <div class="row">
                    <?php foreach ($productsByCategory as $categoryName => $data): ?>
                        <div class="col-md-6 mb-4">
                            <div class="chart-container" style="height: 300px;">
                                <h6 class="text-center mb-3"><?= htmlspecialchars($categoryName) ?></h6>
                                <canvas id="categoryChart_<?= md5($categoryName) ?>"></canvas>
                            </div>
                        </div>
                    <?php endforeach; ?>
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

    // Function to refresh all charts
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

                // Refresh the page to update individual category charts
                // This is a simple solution - for a more sophisticated approach,
                // you would need to modify the controller to return product distribution data
                window.location.reload();
            })
            .catch(error => console.error('Error:', error));
    }

    // Auto refresh every 5 minutes
    setInterval(refreshCharts, 300000);
</script>
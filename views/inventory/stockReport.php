<?php
// views/inventory/stockReport.php
$stockChanges = $params['stockChanges'];
$categoryStock = $params['categoryStock'];
$productsByCategory = $params['productsByCategory'];

// Process stock changes data for the line chart
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
?>

<style>
    /* Chart Containers */
    .chart-wrapper {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 24px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .chart-container {
        background: white;
        border-radius: 8px;
        padding: 15px;
        position: relative;
    }

    .main-chart {
        height: 400px;
    }

    .category-chart-container {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        height: 100%;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        transition: all 0.3s ease;
    }

    .category-chart-container:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.05);
    }

    /* Titles */
    .chart-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #344767;
        text-align: center;
        margin-bottom: 1.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #eee;
    }

    .subchart-title {
        font-size: 0.95rem;
        font-weight: 600;
        color: #344767;
        text-align: center;
        margin-bottom: 1rem;
        padding-bottom: 0.25rem;
        border-bottom: 1px solid #eee;
    }

    /* Search Container */
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
    }

    .search-item {
        padding: 8px 12px;
        cursor: pointer;
        border-bottom: 1px solid #eee;
        transition: background-color 0.2s ease;
    }

    .search-item:hover {
        background-color: #f8f9fa;
    }

    /* Responsive Adjustments */
    @media (max-width: 992px) {
        .main-chart {
            height: 300px;
        }
    }

    @media (max-width: 768px) {
        .chart-wrapper {
            padding: 15px;
        }

        .chart-title {
            font-size: 1rem;
        }
    }
</style>

<div class="row">
    <div class="col-12">
        <div class="card">
            <!-- Header Section -->
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
                <!-- Search Section -->
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

                <!-- Inventory Overview Section -->
                <div class="row">
                    <!-- Total Stock Changes Chart -->
                    <div class="col-12 mb-4">
                        <div class="chart-wrapper">
                            <div class="chart-title">
                                <i class="fas fa-chart-line me-2"></i>
                                Ukupne Zalihe kroz Vreme
                            </div>
                            <div class="chart-container main-chart">
                                <canvas id="stockLineChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Main Category Distribution -->
                    <div class="col-12 mb-4">
                        <div class="chart-wrapper">
                            <div class="chart-title">
                                <i class="fas fa-chart-pie me-2"></i>
                                Distribucija Zaliha po Kategorijama
                            </div>
                            <div class="chart-container main-chart">
                                <canvas id="categoryPieChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Individual Category Charts -->
                <div class="chart-wrapper">
                    <div class="chart-title">
                        <i class="fas fa-boxes me-2"></i>
                        Detaljna Distribucija po Kategorijama
                    </div>
                    <div class="row" style="min-height: 600px;">
                        <?php foreach ($productsByCategory as $categoryName => $data): ?>
                            <div class="col-lg-6 mb-4">
                                <div class="category-chart-container">
                                    <div class="subchart-title"><?= htmlspecialchars($categoryName) ?></div>
                                    <div class="chart-container" style="height: 280px;">
                                        <canvas id="categoryChart_<?= md5($categoryName) ?>"></canvas>
                                    </div>
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
    const categoryData = <?= json_encode($categoryStock) ?>;

    // Sort the data by total_quantity in descending order
    categoryData.sort((a, b) => parseInt(b.total_quantity) - parseInt(a.total_quantity));

    // Calculate total for percentages
    const totalQuantity = categoryData.reduce((sum, item) => sum + parseInt(item.total_quantity), 0);

    // Add percentage to labels
    const categoryLabels = categoryData.map(item => {
        const percentage = ((parseInt(item.total_quantity) / totalQuantity) * 100).toFixed(1);
        return `${item.category_name} (${percentage}%)`;
    });

    const categoryPieChart = new Chart(pieCtx, {
        type: 'pie',
        data: {
            labels: categoryLabels,
            datasets: [{
                data: categoryData.map(item => item.total_quantity),
                backgroundColor: COLORS.slice(0, categoryData.length),
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
                            const value = context.raw;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `Količina: ${value.toLocaleString()}`;
                        }
                    }
                },
                legend: {
                    position: 'right',
                    labels: {
                        boxWidth: 15,
                        padding: 15,
                        generateLabels: function(chart) {
                            const data = chart.data;
                            if (data.labels.length && data.datasets.length) {
                                const dataset = data.datasets[0];
                                const total = dataset.data.reduce((a, b) => a + b, 0);

                                return data.labels.map((label, i) => {
                                    const value = dataset.data[i];
                                    const percentage = ((value / total) * 100).toFixed(1);
                                    return {
                                        text: `${label}`,
                                        fillStyle: dataset.backgroundColor[i],
                                        strokeStyle: dataset.borderColor,
                                        lineWidth: dataset.borderWidth,
                                        hidden: isNaN(dataset.data[i]) || chart.getDatasetMeta(0).data[i].hidden,
                                        index: i
                                    };
                                });
                            }
                            return [];
                        }
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
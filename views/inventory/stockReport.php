<?php
$stockData = $params['stockData'];
$categoryStock = $params['categoryStock'];
$productsByCategory = $params['productsByCategory'];
$detailedChanges = $params['detailedChanges'];
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
    .date-filters {
        background: white;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .filter-button {
        margin-top: 23px;
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
                            <div class="search-container mb-3">
                                <input type="text"
                                       id="productSearch"
                                       class="form-control"
                                       placeholder="Pretraži proizvod..."
                                       autocomplete="off">
                                <div id="searchResults" class="search-results"></div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Od datuma:</label>
                                        <input type="date" id="productStartDate" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Do datuma:</label>
                                        <input type="date" id="productEndDate" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="productChartContainer" class="chart-container" style="height: 400px; display: none;">
                        <canvas id="productChart"></canvas>
                    </div>
                </div>

                <!-- Inventory Overview Section -->
                <div class="row">
                    <!-- Total Stock Bar Chart -->
                    <div class="col-12 mb-4">
                        <div class="chart-wrapper">
                            <div class="chart-title">
                                <i class="fas fa-chart-bar me-2"></i>
                                Ukupne Zalihe kroz Vreme
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Od datuma:</label>
                                        <input type="date" id="stockStartDate" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Do datuma:</label>
                                        <input type="date" id="stockEndDate" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button class="btn btn-primary" onclick="updateStockChart()">Primeni</button>
                                </div>
                            </div>
                            <div class="chart-container main-chart">
                                <canvas id="stockBarChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Category Distribution Pie Chart -->
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
    const COLORS = [
        '#2E93fA', '#66DA26', '#546E7A', '#E91E63', '#FF9800',
        '#4CAF50', '#8884d8', '#FF5722', '#9C27B0', '#3F51B5'
    ];

    // Initialize the Stock Bar Chart
    const barCtx = document.getElementById('stockBarChart').getContext('2d');
    const stockData = <?= json_encode($stockData) ?>;

    let stockBarChart = new Chart(barCtx, {
        type: 'bar',
        data: {
            labels: Object.keys(stockData),
            datasets: [{
                label: 'Ulaz',
                data: Object.values(stockData).map(d => d.ulaz),
                backgroundColor: 'rgba(102, 218, 38, 0.5)',
                borderColor: 'rgba(102, 218, 38, 1)',
                borderWidth: 1,
                stack: 'stack0'
            }, {
                label: 'Izlaz',
                data: Object.values(stockData).map(d => d.izlaz),
                backgroundColor: 'rgba(233, 30, 99, 0.5)',
                borderColor: 'rgba(233, 30, 99, 1)',
                borderWidth: 1,
                stack: 'stack0'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index'
            },
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
                            const label = context.dataset.label;
                            const value = context.parsed.y;
                            return `${label}: ${value}`;
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

    // Initialize Category Pie Chart
    const pieCtx = document.getElementById('categoryPieChart').getContext('2d');
    const categoryData = <?= json_encode($categoryStock) ?>;
    categoryData.sort((a, b) => parseInt(b.total_quantity) - parseInt(a.total_quantity));
    const totalQuantity = categoryData.reduce((sum, item) => sum + parseInt(item.total_quantity), 0);
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
                            return `Količina: ${value.toLocaleString()} (${percentage}%)`;
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

    // Initialize Individual Category Charts
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

    // Product Search and Chart Functionality
    let productChart = null;
    let searchTimeout = null;
    let selectedProductId = null;

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

    function selectProduct(productId, productName) {
        selectedProductId = productId;
        document.getElementById('searchResults').style.display = 'none';
        document.getElementById('productSearch').value = productName;
        updateProductChart();
    }

    function updateProductChart() {
        if (!selectedProductId) return;

        const startDate = document.getElementById('productStartDate').value;
        const endDate = document.getElementById('productEndDate').value;
        let url = `/inventory/product-history?id=${selectedProductId}`;

        if (startDate && endDate) {
            url += `&startDate=${startDate}&endDate=${endDate}`;
        }

        fetch(url)
            .then(response => response.json())
            .then(data => {
                const chartContainer = document.getElementById('productChartContainer');
                chartContainer.style.display = 'block';

                if (productChart) {
                    productChart.destroy();
                }

                const ctx = document.getElementById('productChart').getContext('2d');
                productChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: data.dates,
                        datasets: [{
                            label: 'Ulaz',
                            data: data.inflow,
                            backgroundColor: 'rgba(102, 218, 38, 0.5)',
                            borderColor: 'rgba(102, 218, 38, 1)',
                            borderWidth: 1
                        }, {
                            label: 'Izlaz',
                            data: data.outflow,
                            backgroundColor: 'rgba(233, 30, 99, 0.5)',
                            borderColor: 'rgba(233, 30, 99, 1)',
                            borderWidth: 1
                        }, {
                            label: 'Ukupno Stanje',
                            data: data.totalStock,
                            type: 'line',
                            borderColor: '#2E93fA',
                            backgroundColor: 'rgba(46, 147, 250, 0.1)',
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false
                        },
                        plugins: {
                            tooltip: {
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

    function updateStockChart() {
        const startDate = document.getElementById('stockStartDate').value;
        const endDate = document.getElementById('stockEndDate').value;

        if (!startDate || !endDate) {
            toastr.warning('Molimo odaberite oba datuma');
            return;
        }

        fetch(`/inventory/stock-report?ajax=1&startDate=${startDate}&endDate=${endDate}`)
            .then(response => response.json())
            .then(data => {
                stockBarChart.data.labels = Object.keys(data.stockData);
                stockBarChart.data.datasets[0].data = Object.values(data.stockData).map(d => d.ulaz);
                stockBarChart.data.datasets[1].data = Object.values(data.stockData).map(d => d.izlaz);
                stockBarChart.update();
            })
            .catch(error => {
                console.error('Error updating stock chart:', error);
                toastr.error('Došlo je do greške prilikom ažuriranja grafikona');
            });
    }

    // Event Listeners for Date Filters
    document.getElementById('productStartDate').addEventListener('change', updateProductChart);
    document.getElementById('productEndDate').addEventListener('change', updateProductChart);

    // Event Listener for Clicking Outside Search Results
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.search-container')) {
            document.getElementById('searchResults').style.display = 'none';
        }
    });

    // Function to Refresh All Charts
    function refreshCharts() {
        const startDate = document.getElementById('stockStartDate').value;
        const endDate = document.getElementById('stockEndDate').value;
        let url = '/inventory/stock-report?ajax=1';

        if (startDate && endDate) {
            url += `&startDate=${startDate}&endDate=${endDate}`;
        }

        fetch(url)
            .then(response => response.json())
            .then(data => {
                // Update stock bar chart
                stockBarChart.data.labels = Object.keys(data.stockData);
                stockBarChart.data.datasets[0].data = Object.values(data.stockData).map(d => d.ulaz);
                stockBarChart.data.datasets[1].data = Object.values(data.stockData).map(d => d.izlaz);
                stockBarChart.update();

                // Update category pie chart
                categoryPieChart.data.labels = data.categoryStock.map(item => {
                    const total = data.categoryStock.reduce((sum, cat) => sum + parseInt(cat.total_quantity), 0);
                    const percentage = ((parseInt(item.total_quantity) / total) * 100).toFixed(1);
                    return `${item.category_name} (${percentage}%)`;
                });
                categoryPieChart.data.datasets[0].data = data.categoryStock.map(item => item.total_quantity);
                categoryPieChart.update();

                // Update product chart if a product is selected
                if (selectedProductId) {
                    updateProductChart();
                }
            })
            .catch(error => {
                console.error('Error refreshing charts:', error);
                toastr.error('Došlo je do greške prilikom osvežavanja podataka');
            });
    }

    // Auto refresh every 5 minutes
    setInterval(refreshCharts, 300000);
</script>
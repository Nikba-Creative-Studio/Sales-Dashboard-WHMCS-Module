<?php

// Import the WHMCS Capsule Database ORM
use WHMCS\Database\Capsule;

// Ensure the file is accessed within WHMCS
if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

/**
 * Activate Sales Dashboard Module
 *
 * This function is called when the module is activated. It can be used to 
 * set up any required database tables or perform other setup tasks.
 *
 * @return array An array containing the status and description of the activation process.
 */
function sales_dashboard_activate() {
    // Add activation logic here (e.g., creating database tables)
    return [
        'status' => 'success',
        'description' => 'Sales Dashboard module has been activated successfully.'
    ];
}

/**
 * Deactivate Sales Dashboard Module
 *
 * This function is called when the module is deactivated. It can be used to 
 * clean up any resources such as database tables that are no longer needed.
 *
 * @return array An array containing the status and description of the deactivation process.
 */
function sales_dashboard_deactivate() {
    // Add deactivation logic here (e.g., removing database tables)
    return [
        'status' => 'success',
        'description' => 'Sales Dashboard module has been deactivated successfully.'
    ];
}

/**
 * Upgrade Sales Dashboard Module
 *
 * This function handles upgrades between versions of the module.
 *
 * @param array $vars The variables passed by WHMCS, including the current version of the module.
 * @return void
 */
function sales_dashboard_upgrade($vars) {
    $version = $vars['version'];
    
    // Handle upgrades between versions here
}

/**
 * Configure Sales Dashboard Module
 *
 * This function defines the configuration options for the module in the WHMCS admin area.
 *
 * @return array An array of configuration options.
 */
function sales_dashboard_config() {
    return [
        'name' => 'Sales Dashboard',
        'description' => 'A detailed dashboard for managing and analyzing sales in WHMCS.',
        'version' => '1.0.0',
        'author' => 'Nikba Creative Studio',
        'fields' => [
            'enabled' => [
                'FriendlyName' => 'Enable Module',
                'Type' => 'yesno',
                'Description' => 'Check to enable the Sales Dashboard module.',
                'Default' => 'yes',
            ],
        ],
    ];
}

/**
 * Retrieve Sales Data with Filters
 *
 * This function retrieves sales data based on optional filters such as date range and status.
 *
 * @param string|null $startDate Optional start date for filtering.
 * @param string|null $endDate Optional end date for filtering.
 * @param string|null $status Optional status for filtering.
 * @return \Illuminate\Support\Collection The filtered sales data.
 */
function get_sales_data($startDate = null, $endDate = null, $status = null) {
    $query = Capsule::table('tblinvoices')
        ->join('tblclients', 'tblinvoices.userid', '=', 'tblclients.id')
        ->select(
            'tblinvoices.id as invoice_id',
            'tblclients.firstname',
            'tblclients.lastname',
            'tblinvoices.total',
            'tblinvoices.date',
            'tblinvoices.paymentmethod',
            'tblinvoices.status'
        );

    // Apply start date filter if provided
    if ($startDate) {
        $query->where('tblinvoices.date', '>=', $startDate);
    }

    // Apply end date filter if provided
    if ($endDate) {
        $query->where('tblinvoices.date', '<=', $endDate);
    }

    // Apply status filter if provided
    if ($status) {
        $query->where('tblinvoices.status', '=', $status);
    }

    // Execute the query and get the results
    $salesData = $query->get();

    return $salesData;
}

/**
 * Add a hook to display the Sales Dashboard in the admin area sidebar.
 *
 * This hook adds a link to the Sales Dashboard in the WHMCS admin area sidebar.
 */
add_hook('AdminAreaPage', 1, function($vars) {
    return [
        'sidebar' => [
            'Sales Dashboard' => [
                'icon' => 'fas fa-chart-line',
                'href' => 'addonmodules.php?module=sales_dashboard',
                'label' => 'Sales Dashboard',
            ]
        ]
    ];
});

/**
 * Output Sales Dashboard Page
 *
 * This function generates the HTML output for the Sales Dashboard page, including
 * widgets for total clients, invoices, sales, and services, as well as charts 
 * showing monthly data.
 *
 * @param array $vars The variables passed by WHMCS.
 * @return void
 */
function sales_dashboard_output($vars) {
    // Get total counts for clients, invoices, sales, and services
    $totalClients = Capsule::table('tblclients')->count();
    $totalInvoices = Capsule::table('tblinvoices')->count();
    $totalSales = Capsule::table('tblinvoices')->sum('total');
    $totalServices = Capsule::table('tblhosting')->count();

    // Get monthly data for charts
    $salesData = get_monthly_sales_data();
    $clientsData = get_monthly_clients_data();
    $servicesData = get_monthly_services_data();

    // Prepare data for charts
    $salesChartData = prepare_chart_data($salesData, 'total_sales');
    $invoicesChartData = prepare_chart_data($salesData, 'total_invoices');
    $clientsChartData = prepare_chart_data($clientsData, 'total_clients');
    $servicesChartData = prepare_chart_data($servicesData, 'total_services');

    // Pagination settings
    $limit = 100;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $limit;

    // Get the currency symbol
    $currency = getCurrency();

    // Output the tab structure for Data and Charts views
    echo '
    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation" class="active"><a href="#data" aria-controls="data" role="tab" data-toggle="tab">Data</a></li>
        <li role="presentation"><a href="#charts" aria-controls="charts" role="tab" data-toggle="tab">Charts</a></li>
    </ul>
    
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="data">
            <div class="row" style="margin-top: 2rem;">
                <!-- Total Clients Widget -->
                <div class="col-md-3">
                    <div class="card text-white bg-primary" style="border-radius: 0.5rem;">
                        <div class="card-body" style="padding: 1rem; margin-bottom: 2rem;">
                            <h5 class="card-title">Total Clients</h5>
                            <p class="card-text" style="font-size: 2rem; font-weight: bold;">' . $totalClients . '</p>
                        </div>
                    </div>
                </div>
                <!-- Total Invoices Widget -->
                <div class="col-md-3">
                    <div class="card text-white bg-success" style="border-radius: 0.5rem;">
                        <div class="card-body" style="padding: 1rem; margin-bottom: 2rem;">
                            <h5 class="card-title">Total Invoices</h5>
                            <p class="card-text" style="font-size: 2rem; font-weight: bold;">' . $totalInvoices . '</p>
                        </div>
                    </div>
                </div>
                <!-- Total Sales Widget -->
                <div class="col-md-3">
                    <div class="card text-white bg-warning" style="border-radius: 0.5rem;">
                        <div class="card-body" style="padding: 1rem; margin-bottom: 2rem;">
                            <h5 class="card-title">Total Sales</h5>
                            <p class="card-text" style="font-size: 2rem; font-weight: bold;">' . number_format($totalSales, 2) . ' ' . $currency['code'] . '</p>
                        </div>
                    </div>
                </div>
                <!-- Total Services Widget -->
                <div class="col-md-3">
                    <div class="card text-white bg-danger" style="border-radius: 0.5rem;">
                        <div class="card-body" style="padding: 1rem; margin-bottom: 2rem;">
                            <h5 class="card-title">Total Services</h5>
                            <p class="card-text" style="font-size: 2rem; font-weight: bold;">' . $totalServices . '</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div role="tabpanel" class="tab-pane" id="charts">
            <div class="row" style="margin-top: 2rem;">
                <!-- Clients Chart -->
                <div class="col-md-6">
                    <div class="panel panel-primary">
                        <div class="panel-heading">Clients</div>
                        <div class="panel-body">
                            <canvas id="clientsChart" style="min-height: 300px;"></canvas>
                        </div>
                    </div>
                </div>
                <!-- Invoices Chart -->
                <div class="col-md-6">
                    <div class="panel panel-success">
                        <div class="panel-heading">Invoices</div>
                        <div class="panel-body">
                            <canvas id="invoicesChart" style="min-height: 300px;"></canvas>
                        </div>
                    </div>
                </div>
                <!-- Sales Chart -->
                <div class="col-md-6">
                    <div class="panel panel-warning">
                        <div class="panel-heading">Sales</div>
                        <div class="panel-body">
                            <canvas id="salesChart" style="min-height: 300px;"></canvas>
                        </div>
                    </div>
                </div>
                <!-- Services Chart -->
                <div class="col-md-6">
                    <div class="panel panel-danger">
                        <div class="panel-heading">Services</div>
                        <div class="panel-body">
                            <canvas id="servicesChart" style="min-height: 300px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    ';

    // Filtering and pagination setup
    $period = isset($_GET['period']) ? $_GET['period'] : 'month';
    $status = isset($_GET['status']) ? $_GET['status'] : '';
    $startDate = null;
    $endDate = null;

    // Adjust the date range based on the selected period
    if ($period === 'week') {
        $endDate = date('Y-m-d');
        $startDate = date('Y-m-d', strtotime('-1 week'));
    } elseif ($period === 'month') {
        $endDate = date('Y-m-d');
        $startDate = date('Y-m-d', strtotime('-1 month'));
    } elseif ($period === 'year') {
        $endDate = date('Y-m-d');
        $startDate = date('Y-m-d', strtotime('-1 year'));
    } elseif ($period === 'custom') {
        $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : null;
        $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : null;
    }

    // Retrieve sales data based on the selected filters
    $salesData = get_sales_data($startDate, $endDate, $status);

    // Retrieve data for pagination
    $salesData = Capsule::table('tblinvoices')
        ->join('tblclients', 'tblinvoices.userid', '=', 'tblclients.id')
        ->select(
            'tblinvoices.id as invoice_id',
            'tblclients.id as userid',
            'tblclients.firstname',
            'tblclients.lastname',
            'tblinvoices.total',
            'tblinvoices.date',
            'tblinvoices.paymentmethod',
            'tblinvoices.status'
        )
        ->when($startDate, function ($query) use ($startDate) {
            return $query->where('tblinvoices.date', '>=', $startDate);
        })
        ->when($endDate, function ($query) use ($endDate) {
            return $query->where('tblinvoices.date', '<=', $endDate);
        })
        ->when($status, function ($query) use ($status) {
            return $query->where('tblinvoices.status', '=', $status);
        })
        ->skip($offset)
        ->take($limit)
        ->get();

    $totalRecords = Capsule::table('tblinvoices')
        ->join('tblclients', 'tblinvoices.userid', '=', 'tblclients.id')
        ->when($startDate, function ($query) use ($startDate) {
            return $query->where('tblinvoices.date', '>=', $startDate);
        })
        ->when($endDate, function ($query) use ($endDate) {
            return $query->where('tblinvoices.date', '<=', $endDate);
        })
        ->when($status, function ($query) use ($status) {
            return $query->where('tblinvoices.status', '=', $status);
        })
        ->count();

    $totalPages = ceil($totalRecords / $limit);

    // Output filtering form
    echo '<div class="panel panel-default">';
    echo '<div class="panel-body">';
    echo '<form method="GET" action="addonmodules.php" class="form-inline mb-4">';
    echo '<input type="hidden" name="module" value="sales_dashboard">';

    echo '<div class="row">';

    // Period dropdown
    echo '<div class="col-md-2">';
    echo '<div class="form-group">';
    echo '<label for="period" class="mr-2" style="margin-right: 1rem;">Period:</label>';
    echo '<select name="period" id="period" class="form-control" onchange="toggleCustomDates()">';
    echo '<option value="week"' . ($period == 'week' ? ' selected' : '') . '>Week</option>';
    echo '<option value="month"' . ($period == 'month' ? ' selected' : '') . '>Month</option>';
    echo '<option value="year"' . ($period == 'year' ? ' selected' : '') . '>Year</option>';
    echo '<option value="custom"' . ($period == 'custom' ? ' selected' : '') . '>Custom</option>';
    echo '</select>';
    echo '</div>';
    echo '</div>';

    // Custom date range
    echo '<div class="col-md-6">';
    echo '<div class="form-group mr-3" id="custom-dates" style="display:' . ($period == 'custom' ? 'block' : 'none') . ';">';
    echo '<label for="start_date" class="mr-2" style="margin-right: 1rem;">Start Date:</label>';
    echo '<input type="date" name="start_date" id="start_date" class="form-control mr-2" value="' . htmlspecialchars($startDate ?: '') . '">';
    echo '<label for="end_date" class="mr-2" style="margin-right: 1rem; margin-left: 2rem;">End Date:</label>';
    echo '<input type="date" name="end_date" id="end_date" class="form-control" value="' . htmlspecialchars($endDate ?: '') . '">';
    echo '</div>';
    echo '</div>';

    // Status dropdown
    echo '<div class="col-md-2">';
    echo '<div class="form-group mr-3">';
    echo '<label for="status" class="mr-2" style="margin-right: 1rem;">Status:</label>';
    echo '<select name="status" id="status" class="form-control">';
    echo '<option value="">All</option>';
    foreach ($statuses as $s) {
        echo '<option value="' . htmlspecialchars($s) . '"' . ($status == $s ? ' selected' : '') . '>' . htmlspecialchars(ucfirst($s)) . '</option>';
    }
    echo '</select>';
    echo '</div>';
    echo '</div>';

    // Filter button
    echo '<div class="col-md-2 text-right">';
    echo '<button type="submit" class="btn btn-primary">Filter</button>';
    echo '</div>';

    echo '</div>';
    echo '</form>';
    echo '</div>';
    echo '</div>';

    // Output table with sales data
    echo '<table class="datatable table table-bordered table-striped" style="width:100%">';
    echo '<thead><tr>';
    echo '<th width="100">Invoice ID</th>';
    echo '<th>Client Name</th>';
    echo '<th width="100">Total</th>';
    echo '<th width="150">Date</th>';
    echo '<th>Payment Method</th>';
    echo '<th width="100">Status</th>';
    echo '</tr></thead>';
    echo '<tbody>';

    if ($salesData->count() > 0) {
        foreach ($salesData as $sale) {
            // Create the link to the invoice
            $invoiceLink = 'invoices.php?action=edit&id=' . urlencode($sale->invoice_id);
        
            // Create the link to the client profile
            $clientLink = 'clientssummary.php?userid=' . urlencode($sale->userid);
        
            echo '<tr>';
            echo '<td class="text-center"><a href="' . $invoiceLink . '" target="_blank">' . htmlspecialchars($sale->invoice_id) . '</a></td>';
            echo '<td><a href="' . $clientLink . '" target="_blank">' . htmlspecialchars($sale->firstname . ' ' . $sale->lastname) . '</a></td>';
            echo '<td class="text-center">' . htmlspecialchars(number_format($sale->total, 2)) . '</td>';
            echo '<td class="text-center">' . htmlspecialchars($sale->date) . '</td>';
            echo '<td>' . htmlspecialchars(ucfirst($sale->paymentmethod)) . '</td>';
            echo '<td class="text-center">' . htmlspecialchars(ucfirst($sale->status)) . '</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="6">No items!</td></tr>';
    }

    echo '</tbody>';
    echo '</table>';

    // Pagination links
    if ($totalPages > 1) {
        echo '<nav aria-label="Page navigation" class="text-center">';
        echo '<ul class="pagination">';
    
        // Previous Page Link
        if ($page > 1) {
            echo '<li><a href="' . get_pagination_url($page - 1) . '">&laquo;</a></li>';
        }
    
        // Page Number Links
        for ($i = 1; $i <= $totalPages; $i++) {
            echo '<li class="' . ($i == $page ? 'active' : '') . '"><a href="' . get_pagination_url($i) . '">' . $i . '</a></li>';
        }
    
        // Next Page Link
        if ($page < $totalPages) {
            echo '<li><a href="' . get_pagination_url($page + 1) . '">&raquo;</a></li>';
        }
    
        echo '</ul>';
        echo '</nav>';
    }

    // Script for generating charts using Chart.js
    echo '
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var ctx1 = document.getElementById("clientsChart").getContext("2d");
            var clientsChart = new Chart(ctx1, {
                type: "line",
                data: {
                    labels: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
                    datasets: [{
                        label: "Total Clients",
                        data: ' . json_encode($clientsChartData) . ',
                        borderColor: "rgba(54, 162, 235, 1)",
                        borderWidth: 2,
                        fill: false
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            var ctx2 = document.getElementById("invoicesChart").getContext("2d");
            var invoicesChart = new Chart(ctx2, {
                type: "line",
                data: {
                    labels: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
                    datasets: [{
                        label: "Total Invoices",
                        data: ' . json_encode($invoicesChartData) . ',
                        borderColor: "rgba(75, 192, 192, 1)",
                        borderWidth: 2,
                        fill: false
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            var ctx3 = document.getElementById("salesChart").getContext("2d");
            var salesChart = new Chart(ctx3, {
                type: "line",
                data: {
                    labels: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
                    datasets: [{
                        label: "Total Sales",
                        data: ' . json_encode($salesChartData) . ',
                        borderColor: "rgba(255, 206, 86, 1)",
                        borderWidth: 2,
                        fill: false
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            var ctx4 = document.getElementById("servicesChart").getContext("2d");
            var servicesChart = new Chart(ctx4, {
                type: "line",
                data: {
                    labels: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
                    datasets: [{
                        label: "Total Services",
                        data: ' . json_encode($servicesChartData) . ',
                        borderColor: "rgba(255, 99, 132, 1)",
                        borderWidth: 2,
                        fill: false
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });
    </script>
    ';
}

/**
 * Get pagination URL
 *
 * This function constructs the pagination URL with the given page number.
 *
 * @param int $page The page number to link to.
 * @return string The constructed pagination URL.
 */
function get_pagination_url($page) {
    // Get the current query parameters
    $queryParams = $_GET;

    // Update the 'page' parameter
    $queryParams['page'] = $page;

    // Rebuild the URL with the updated query parameters
    return 'addonmodules.php?' . http_build_query($queryParams);
}

/**
 * Get Monthly Sales Data
 *
 * This function retrieves and aggregates sales data by month for a given year.
 *
 * @param int|null $year The year to retrieve data for. Defaults to the current year.
 * @return \Illuminate\Support\Collection The aggregated sales data by month.
 */
function get_monthly_sales_data($year = null) {
    $year = $year ?: date('Y');

    $salesData = Capsule::table('tblinvoices')
        ->select(
            Capsule::raw('MONTH(tblinvoices.date) as month'),
            Capsule::raw('COUNT(tblinvoices.id) as total_invoices'),
            Capsule::raw('SUM(tblinvoices.total) as total_sales')
        )
        ->whereYear('tblinvoices.date', $year)
        ->groupBy(Capsule::raw('MONTH(tblinvoices.date)'))
        ->orderBy('month')
        ->get();

    return $salesData;
}

/**
 * Get Monthly Clients Data
 *
 * This function retrieves and aggregates client data by month for a given year.
 *
 * @param int|null $year The year to retrieve data for. Defaults to the current year.
 * @return \Illuminate\Support\Collection The aggregated client data by month.
 */
function get_monthly_clients_data($year = null) {
    $year = $year ?: date('Y');

    $clientsData = Capsule::table('tblclients')
        ->select(
            Capsule::raw('MONTH(tblclients.datecreated) as month'),
            Capsule::raw('COUNT(tblclients.id) as total_clients')
        )
        ->whereYear('tblclients.datecreated', $year)
        ->groupBy(Capsule::raw('MONTH(tblclients.datecreated)'))
        ->orderBy('month')
        ->get();

    return $clientsData;
}

/**
 * Get Monthly Services Data
 *
 * This function retrieves and aggregates service data by month for a given year.
 *
 * @param int|null $year The year to retrieve data for. Defaults to the current year.
 * @return \Illuminate\Support\Collection The aggregated service data by month.
 */
function get_monthly_services_data($year = null) {
    $year = $year ?: date('Y');

    $servicesData = Capsule::table('tblhosting')
        ->select(
            Capsule::raw('MONTH(tblhosting.regdate) as month'),
            Capsule::raw('COUNT(tblhosting.id) as total_services')
        )
        ->whereYear('tblhosting.regdate', $year)
        ->groupBy(Capsule::raw('MONTH(tblhosting.regdate)'))
        ->orderBy('month')
        ->get();

    return $servicesData;
}

/**
 * Prepare Chart Data
 *
 * This function prepares the data for Chart.js by filling in missing months with zeros.
 *
 * @param \Illuminate\Support\Collection $monthlyData The monthly data collection.
 * @param string $field The field name to extract data for.
 * @return array The prepared data array with values for each month.
 */
function prepare_chart_data($monthlyData, $field) {
    // Initialize all months to 0
    $data = array_fill(1, 12, 0);

    // Populate the data array with actual values
    foreach ($monthlyData as $item) {
        $data[$item->month] = $item->$field;
    }

    // Return the array with data for each month
    return array_values($data);
}

?>

<?php 
require_once("../includes/auth.php"); 
require_once("../includes/db.php"); 

// Pagination settings
$records_per_page = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Filter settings
$location_filter = isset($_GET['location']) ? $_GET['location'] : '';
$severity_filter = isset($_GET['severity']) ? $_GET['severity'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build WHERE clause
$where_conditions = [];
$params = [];
$types = '';

if ($location_filter) {
    $where_conditions[] = "location LIKE ?";
    $params[] = "%$location_filter%";
    $types .= 's';
}

if ($severity_filter) {
    $where_conditions[] = "severity = ?";
    $params[] = $severity_filter;
    $types .= 's';
}

if ($date_from) {
    $where_conditions[] = "date >= ?";
    $params[] = $date_from;
    $types .= 's';
}

if ($date_to) {
    $where_conditions[] = "date <= ?";
    $params[] = $date_to;
    $types .= 's';
}

if ($search) {
    $where_conditions[] = "(location LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= 'ss';
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Get total records for pagination
$count_query = "SELECT COUNT(*) as total FROM traffic_data $where_clause";
$count_stmt = $conn->prepare($count_query);
if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$total_records = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);

// Get filtered and paginated results
$query = "SELECT * FROM traffic_data $where_clause ORDER BY date DESC, time DESC LIMIT ? OFFSET ?";
$params[] = $records_per_page;
$params[] = $offset;
$types .= 'ii';

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Get unique locations for filter dropdown
$locations_query = "SELECT DISTINCT location FROM traffic_data ORDER BY location";
$locations_result = $conn->query($locations_query);

// Get statistics
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN severity = 'high' THEN 1 ELSE 0 END) as high_severity,
    SUM(CASE WHEN severity = 'medium' THEN 1 ELSE 0 END) as medium_severity,
    SUM(CASE WHEN severity = 'low' THEN 1 ELSE 0 END) as low_severity,
    SUM(CASE WHEN DATE(date) = CURDATE() THEN 1 ELSE 0 END) as today_records
    FROM traffic_data $where_clause";
$stats_stmt = $conn->prepare($stats_query);
if (!empty($where_conditions)) {
    $filter_params = array_slice($params, 0, -2); // Remove LIMIT and OFFSET
    $filter_types = substr($types, 0, -2);
    $stats_stmt->bind_param($filter_types, ...$filter_params);
}
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Traffic Data Management</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            margin-bottom: 25px;
            text-align: center;
        }

        .header h1 {
            color: #333;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-number {
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #666;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .stat-card.total .stat-number { color: #667eea; }
        .stat-card.high .stat-number { color: #e74c3c; }
        .stat-card.medium .stat-number { color: #f39c12; }
        .stat-card.low .stat-number { color: #27ae60; }
        .stat-card.today .stat-number { color: #9b59b6; }

        .controls {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }

        .controls-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .controls-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .control-group {
            display: flex;
            flex-direction: column;
        }

        .control-group label {
            margin-bottom: 5px;
            font-weight: 600;
            color: #555;
        }

        .control-group input,
        .control-group select,
        .control-group textarea {
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .control-group input:focus,
        .control-group select:focus,
        .control-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-right: 10px;
            margin-bottom: 10px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-success {
            background: linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%);
            color: white;
        }

        .btn-warning {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }

        .btn-danger {
            background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .table-header {
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #555;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        tbody tr {
            transition: background-color 0.3s ease;
        }

        tbody tr:hover {
            background-color: #f8f9fa;
        }

        .severity-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .severity-high {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }

        .severity-medium {
            background: #fff8e1;
            color: #f57c00;
            border: 1px solid #ffecb3;
        }

        .severity-low {
            background: #e8f5e8;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }

        .action-buttons {
            display: flex;
            gap: 5px;
        }

        .btn-sm {
            padding: 6px 10px;
            font-size: 12px;
            border-radius: 6px;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            padding: 20px;
            background: #f8f9fa;
        }

        .pagination a,
        .pagination span {
            padding: 8px 12px;
            border-radius: 6px;
            text-decoration: none;
            color: #667eea;
            border: 1px solid #e0e0e0;
            transition: all 0.3s ease;
        }

        .pagination a:hover {
            background: #667eea;
            color: white;
        }

        .pagination .current {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        .no-records {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }

        .no-records i {
            font-size: 48px;
            margin-bottom: 20px;
            color: #ccc;
        }

        .export-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
        }

        .modal-content {
            background: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            position: relative;
        }

        .modal-close {
            position: absolute;
            right: 15px;
            top: 15px;
            font-size: 24px;
            cursor: pointer;
            color: #999;
        }

        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }

        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Alert/Message Styles */
        .alert {
            padding: 15px;
            margin: 20px 0;
            border-radius: 8px;
            display: none;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            .controls-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .table-responsive {
                font-size: 14px;
            }
            
            th, td {
                padding: 10px 8px;
            }
            
            .export-buttons {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1><i class="fas fa-traffic-light"></i> Traffic Data Management System</h1>
        </div>

        <!-- Alert Messages -->
        <div id="alertMessage" class="alert"></div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card total">
                <div class="stat-number"><?php echo number_format($stats['total']); ?></div>
                <div class="stat-label">Total Records</div>
            </div>
            <div class="stat-card high">
                <div class="stat-number"><?php echo number_format($stats['high_severity']); ?></div>
                <div class="stat-label">High Severity</div>
            </div>
            <div class="stat-card medium">
                <div class="stat-number"><?php echo number_format($stats['medium_severity']); ?></div>
                <div class="stat-label">Medium Severity</div>
            </div>
            <div class="stat-card low">
                <div class="stat-number"><?php echo number_format($stats['low_severity']); ?></div>
                <div class="stat-label">Low Severity</div>
            </div>
            <div class="stat-card today">
                <div class="stat-number"><?php echo number_format($stats['today_records']); ?></div>
                <div class="stat-label">Today's Records</div>
            </div>
        </div>

        <!-- Controls Panel -->
        <div class="controls">
            <div class="controls-header">
                <h3><i class="fas fa-filter"></i> Filters & Controls</h3>
                <div class="export-buttons">
                    <button class="btn btn-success" onclick="exportData('csv')">
                        <i class="fas fa-file-csv"></i> Export CSV
                    </button>
                    <button class="btn btn-warning" onclick="exportData('pdf')">
                        <i class="fas fa-file-pdf"></i> Export PDF
                    </button>
                    <button class="btn btn-primary" onclick="refreshData()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>

            <form method="GET" id="filterForm">
                <div class="controls-grid">
                    <div class="control-group">
                        <label for="search">Search:</label>
                        <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search location or description...">
                    </div>
                    
                    <div class="control-group">
                        <label for="location">Location:</label>
                        <select id="location" name="location">
                            <option value="">All Locations</option>
                            <?php while ($loc = $locations_result->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($loc['location']); ?>" 
                                        <?php echo $location_filter === $loc['location'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($loc['location']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="control-group">
                        <label for="severity">Severity:</label>
                        <select id="severity" name="severity">
                            <option value="">All Severities</option>
                            <option value="high" <?php echo $severity_filter === 'high' ? 'selected' : ''; ?>>High</option>
                            <option value="medium" <?php echo $severity_filter === 'medium' ? 'selected' : ''; ?>>Medium</option>
                            <option value="low" <?php echo $severity_filter === 'low' ? 'selected' : ''; ?>>Low</option>
                        </select>
                    </div>
                    
                    <div class="control-group">
                        <label for="date_from">Date From:</label>
                        <input type="date" id="date_from" name="date_from" value="<?php echo $date_from; ?>">
                    </div>
                    
                    <div class="control-group">
                        <label for="date_to">Date To:</label>
                        <input type="date" id="date_to" name="date_to" value="<?php echo $date_to; ?>">
                    </div>
                </div>
                
                <div class="controls-footer" style="margin-top: 20px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Apply Filters
                    </button>
                    <a href="?" class="btn" style="background: #6c757d; color: white;">
                        <i class="fas fa-times"></i> Clear Filters
                    </a>
                    <button type="button" class="btn btn-success" onclick="openAddModal()">
                        <i class="fas fa-plus"></i> Add New Record
                    </button>
                    <button type="button" class="btn btn-warning" onclick="bulkActions()">
                        <i class="fas fa-tasks"></i> Bulk Actions
                    </button>
                </div>
            </form>
        </div>

        <!-- Data Table -->
        <div class="table-container">
            <div class="table-header">
                <h3><i class="fas fa-table"></i> Traffic Records (<?php echo number_format($total_records); ?> total)</h3>
                <div>
                    <span>Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
                </div>
            </div>

            <div class="loading" id="loading">
                <div class="spinner"></div>
                <p>Loading data...</p>
            </div>

            <div class="table-responsive">
                <?php if ($result->num_rows > 0): ?>
                <table id="dataTable">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAll" onchange="toggleSelectAll()"></th>
                            <th><i class="fas fa-hashtag"></i> ID</th>
                            <th><i class="fas fa-map-marker-alt"></i> Location</th>
                            <th><i class="fas fa-calendar"></i> Date</th>
                            <th><i class="fas fa-clock"></i> Time</th>
                            <th><i class="fas fa-exclamation-triangle"></i> Severity</th>
                            <th><i class="fas fa-info-circle"></i> Description</th>
                            <th><i class="fas fa-tools"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr data-id="<?php echo $row['id']; ?>">
                            <td><input type="checkbox" class="record-checkbox" value="<?php echo $row['id']; ?>"></td>
                            <td><strong>#<?php echo $row['id']; ?></strong></td>
                            <td>
                                <i class="fas fa-map-marker-alt" style="color: #667eea;"></i>
                                <?php echo htmlspecialchars($row['location']); ?>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($row['date'])); ?></td>
                            <td><?php echo date('g:i A', strtotime($row['time'])); ?></td>
                            <td>
                                <span class="severity-badge severity-<?php echo $row['severity']; ?>">
                                    <?php echo ucfirst($row['severity']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars(substr($row['description'], 0, 50)) . (strlen($row['description']) > 50 ? '...' : ''); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-primary btn-sm" onclick="viewRecord(<?php echo $row['id']; ?>)" title="View">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-warning btn-sm" onclick="editRecord(<?php echo $row['id']; ?>)" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm" onclick="deleteRecord(<?php echo $row['id']; ?>)" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="no-records">
                    <i class="fas fa-inbox"></i>
                    <h3>No Records Found</h3>
                    <p>No traffic data matches your current filters.</p>
                    <a href="?" class="btn btn-primary">Clear Filters</a>
                </div>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=1<?php echo $query_string = http_build_query(array_filter($_GET, function($k) { return $k !== 'page'; }, ARRAY_FILTER_USE_KEY)) ? '&' . $query_string : ''; ?>">
                        <i class="fas fa-angle-double-left"></i>
                    </a>
                    <a href="?page=<?php echo $page-1; ?><?php echo $query_string ? '&' . $query_string : ''; ?>">
                        <i class="fas fa-angle-left"></i>
                    </a>
                <?php endif; ?>

                <?php for ($i = max(1, $page-2); $i <= min($total_pages, $page+2); $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="current"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?page=<?php echo $i; ?><?php echo $query_string ? '&' . $query_string : ''; ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page+1; ?><?php echo $query_string ? '&' . $query_string : ''; ?>">
                        <i class="fas fa-angle-right"></i>
                    </a>
                    <a href="?page=<?php echo $total_pages; ?><?php echo $query_string ? '&' . $query_string : ''; ?>">
                        <i class="fas fa-angle-double-right"></i>
                    </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div id="recordModal" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeModal()">&times;</span>
            <h3 id="modalTitle">Add New Record</h3>
            <form id="recordForm">
                <input type="hidden" id="recordId" name="id">
                <div class="control-group">
                    <label for="modalLocation">Location:</label>
                    <input type="text" id="modalLocation" name="location" required>
                </div>
                <div class="control-group">
                    <label for="modalDate">Date:</label>
                    <input type="date" id="modalDate" name="date" required>
                </div>
                <div class="control-group">
                    <label for="modalTime">Time:</label>
                    <input type="time" id="modalTime" name="time" required>
                </div>
                <div class="control-group">
                    <label for="modalSeverity">Severity:</label>
                    <select id="modalSeverity" name="severity" required>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
                <div class="control-group">
                    <label for="modalDescription">Description:</label>
                    <textarea id="modalDescription" name="description" rows="4" required></textarea>
                </div>
                <div style="margin-top: 20px;">
                    <button type="submit" class="btn btn-primary" id="saveBtn">
                        <i class="fas fa-save"></i> Save Record
                    </button>
                    <button type="button" class="btn" onclick="closeModal()" style="background: #6c757d; color: white;">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Modal -->
    <div id="viewModal" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeViewModal()">&times;</span>
            <h3 id="viewModalTitle">View Record</h3>
            <div id="viewContent">
                <!-- Record details will be loaded here -->
            </div>
            <div style="margin-top: 20px;">
                <button type="button" class="btn btn-warning" onclick="editFromView()" id="editFromViewBtn">
                    <i class="fas fa-edit"></i> Edit Record
                </button>
                <button type="button" class="btn" onclick="closeViewModal()" style="background: #6c757d; color: white;">
                    <i class="fas fa-times"></i> Close
                </button>
            </div>
        </div>
    </div>

    <script>
        let currentRecordId = null;

        // Auto-submit form on filter change
        document.querySelectorAll('#filterForm select, #filterForm input[type="date"]').forEach(element => {
            element.addEventListener('change', function() {
                document.getElementById('filterForm').submit();
            });
        });

        // Search with delay
        let searchTimeout;
        document.getElementById('search').addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                document.getElementById('filterForm').submit();
            }, 500);
        });

        // Select all functionality
        function toggleSelectAll() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.record-checkbox');
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
        }

        // Show alert message
        function showAlert(message, type = 'info') {
            const alert = document.getElementById('alertMessage');
            alert.className = `alert alert-${type}`;
            alert.innerHTML = message;
            alert.style.display = 'block';
            
            // Auto hide after 5 seconds
            setTimeout(() => {
                alert.style.display = 'none';
            }, 5000);
        }

        // Export functions
        function exportData(format) {
            const url = new URL(window.location);
            url.searchParams.set('export', format);
            window.open(url.toString(), '_blank');
        }

        // Refresh data
        function refreshData() {
            document.getElementById('loading').style.display = 'block';
            setTimeout(() => {
                location.reload();
            }, 1000);
        }

        // View Record Function
        function viewRecord(id) {
            fetch('get_record.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({id: id})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const record = data.data;
                    document.getElementById('viewModalTitle').textContent = `View Record #${record.id}`;
                    document.getElementById('editFromViewBtn').setAttribute('data-id', record.id);
                    
                    const content = `
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                            <div class="control-group">
                                <label><strong>ID:</strong></label>
                                <p>#${record.id}</p>
                            </div>
                            <div class="control-group">
                                <label><strong>Location:</strong></label>
                                <p><i class="fas fa-map-marker-alt" style="color: #667eea;"></i> ${record.location}</p>
                            </div>
                            <div class="control-group">
                                <label><strong>Date:</strong></label>
                                <p>${new Date(record.date).toLocaleDateString()}</p>
                            </div>
                            <div class="control-group">
                                <label><strong>Time:</strong></label>
                                <p>${record.time}</p>
                            </div>
                            <div class="control-group">
                                <label><strong>Severity:</strong></label>
                                <p><span class="severity-badge severity-${record.severity}">${record.severity.charAt(0).toUpperCase() + record.severity.slice(1)}</span></p>
                            </div>
                        </div>
                        <div class="control-group" style="margin-top: 15px;">
                            <label><strong>Description:</strong></label>
                            <p style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-top: 5px;">${record.description}</p>
                        </div>
                    `;
                    
                    document.getElementById('viewContent').innerHTML = content;
                    document.getElementById('viewModal').style.display = 'block';
                } else {
                    showAlert('Error loading record: ' + data.error, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Error loading record', 'error');
            });
        }

        // Edit Record Function
        function editRecord(id) {
            fetch('get_record.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({id: id})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const record = data.data;
                    currentRecordId = id;
                    
                    document.getElementById('modalTitle').textContent = `Edit Record #${id}`;
                    document.getElementById('recordId').value = id;
                    document.getElementById('modalLocation').value = record.location;
                    document.getElementById('modalDate').value = record.date;
                    document.getElementById('modalTime').value = record.time;
                    document.getElementById('modalSeverity').value = record.severity;
                    document.getElementById('modalDescription').value = record.description;
                    
                    document.getElementById('recordModal').style.display = 'block';
                } else {
                    showAlert('Error loading record: ' + data.error, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Error loading record', 'error');
            });
        }

        // Edit from view modal
        function editFromView() {
            const id = document.getElementById('editFromViewBtn').getAttribute('data-id');
            closeViewModal();
            editRecord(id);
        }

        // Delete Record Function
        function deleteRecord(id) {
            if (confirm('Are you sure you want to delete record #' + id + '?')) {
                fetch('delete_record.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({id: id})
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('Record deleted successfully!', 'success');
                        // Remove the row from table
                        const row = document.querySelector(`tr[data-id="${id}"]`);
                        if (row) {
                            row.remove();
                        }
                        // Refresh page after 1 second to update statistics
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    } else {
                        showAlert('Error deleting record: ' + data.error, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('Error deleting record', 'error');
                });
            }
        }

        // Open Add Modal
        function openAddModal() {
            currentRecordId = null;
            document.getElementById('modalTitle').textContent = 'Add New Record';
            document.getElementById('recordId').value = '';
            document.getElementById('recordForm').reset();
            
            // Set current date and time
            const now = new Date();
            document.getElementById('modalDate').value = now.toISOString().split('T')[0];
            document.getElementById('modalTime').value = now.toTimeString().split(' ')[0].substring(0, 5);
            
            document.getElementById('recordModal').style.display = 'block';
        }

        // Close Modal
        function closeModal() {
            document.getElementById('recordModal').style.display = 'none';
        }

        // Close View Modal
        function closeViewModal() {
            document.getElementById('viewModal').style.display = 'none';
        }

        // Handle Form Submission
        document.getElementById('recordForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = {
                id: document.getElementById('recordId').value,
                location: document.getElementById('modalLocation').value,
                date: document.getElementById('modalDate').value,
                time: document.getElementById('modalTime').value,
                severity: document.getElementById('modalSeverity').value,
                description: document.getElementById('modalDescription').value
            };

            // Disable save button
            const saveBtn = document.getElementById('saveBtn');
            const originalText = saveBtn.innerHTML;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            saveBtn.disabled = true;

            fetch('save_record.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    closeModal();
                    // Refresh page to show updated data
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    showAlert('Error saving record: ' + data.error, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Error saving record', 'error');
            })
            .finally(() => {
                // Re-enable save button
                saveBtn.innerHTML = originalText;
                saveBtn.disabled = false;
            });
        });

        // Bulk Actions
        function bulkActions() {
            const selected = document.querySelectorAll('.record-checkbox:checked');
            if (selected.length === 0) {
                showAlert('Please select records first', 'error');
                return;
            }
            
            const action = prompt(`${selected.length} records selected. Enter action:\n- delete: Delete selected records\n- export: Export selected records`, 'delete');
            
            if (action === 'delete') {
                if (confirm(`Are you sure you want to delete ${selected.length} selected records? This action cannot be undone.`)) {
                    const ids = Array.from(selected).map(cb => cb.value);
                    
                    fetch('delete_records.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ids: ids})
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showAlert(data.message, 'success');
                            // Remove deleted rows
                            ids.forEach(id => {
                                const row = document.querySelector(`tr[data-id="${id}"]`);
                                if (row) {
                                    row.remove();
                                }
                            });
                            // Refresh page to update statistics
                            setTimeout(() => {
                                location.reload();
                            }, 1000);
                        } else {
                            showAlert('Error deleting records: ' + data.error, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showAlert('Error deleting records', 'error');
                    });
                }
            } else if (action === 'export') {
                const ids = Array.from(selected).map(cb => cb.value);
                const url = new URL(window.location);
                url.searchParams.set('export', 'csv');
                url.searchParams.set('ids', ids.join(','));
                window.open(url.toString(), '_blank');
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const recordModal = document.getElementById('recordModal');
            const viewModal = document.getElementById('viewModal');
            if (event.target === recordModal) {
                closeModal();
            }
            if (event.target === viewModal) {
                closeViewModal();
            }
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Escape key to close modals
            if (e.key === 'Escape') {
                closeModal();
                closeViewModal();
            }
            
            // Ctrl+N to add new record
            if (e.ctrlKey && e.key === 'n') {
                e.preventDefault();
                openAddModal();
            }
            
            // Ctrl+R to refresh
            if (e.ctrlKey && e.key === 'r') {
                e.preventDefault();
                refreshData();
            }
        });

        // Initialize tooltips and other features when page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Add tooltips to action buttons
            const tooltips = document.querySelectorAll('[title]');
            tooltips.forEach(element => {
                element.addEventListener('mouseenter', function() {
                    // You can add custom tooltip implementation here if needed
                });
            });
        });
    </script>
</body>
</html>
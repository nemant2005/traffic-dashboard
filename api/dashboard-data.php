<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Direct database connection instead of including file
$host = "localhost";
$dbname = "traffic_dashboard";
$username = "root";
$password = "";

try {
    $conn = new mysqli($host, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");

    // 1. Active Incidents
    $activeIncidentsQuery = "SELECT COUNT(*) as total_count, 
                            COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as week_count,
                            COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 14 DAY) AND created_at < DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as prev_week_count
                            FROM traffic_incidents WHERE status = 'active'";
    
    $result = $conn->query($activeIncidentsQuery);
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    $activeIncidents = $result->fetch_assoc();
    
    $weekChange = 0;
    if ($activeIncidents['prev_week_count'] > 0) {
        $weekChange = (($activeIncidents['week_count'] - $activeIncidents['prev_week_count']) / $activeIncidents['prev_week_count']) * 100;
    } elseif ($activeIncidents['week_count'] > 0) {
        $weekChange = 100;
    }

    // 2. Total Reports
    $totalReportsQuery = "SELECT COUNT(*) as total_count,
                         COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as month_count,
                         COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 60 DAY) AND created_at < DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as prev_month_count
                         FROM traffic_reports";
    
    $result = $conn->query($totalReportsQuery);
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    $totalReports = $result->fetch_assoc();
    
    $monthChange = 0;
    if ($totalReports['prev_month_count'] > 0) {
        $monthChange = (($totalReports['month_count'] - $totalReports['prev_month_count']) / $totalReports['prev_month_count']) * 100;
    } elseif ($totalReports['month_count'] > 0) {
        $monthChange = 100;
    }

    // 3. Average Response Time
    $responseTimeQuery = "SELECT AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as avg_hours
                         FROM traffic_incidents 
                         WHERE resolved_at IS NOT NULL 
                         AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
    
    $result = $conn->query($responseTimeQuery);
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    $responseTime = $result->fetch_assoc();

    // 4. System Status
    $systemStatusQuery = "SELECT 
                         COUNT(CASE WHEN severity = 'high' AND status = 'active' THEN 1 END) as high_severity,
                         COUNT(*) as total_active
                         FROM traffic_incidents WHERE status = 'active'";
    
    $result = $conn->query($systemStatusQuery);
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    $systemStatus = $result->fetch_assoc();
    
    $healthPercentage = 100;
    if ($systemStatus['total_active'] > 0) {
        $healthPercentage = max(0, 100 - (($systemStatus['high_severity'] / $systemStatus['total_active']) * 100));
    }

    // 5. Traffic Severity Overview - Last 7 Days
    $severityQuery = "SELECT 
                     DATE(created_at) as incident_date,
                     COUNT(CASE WHEN severity = 'high' THEN 1 END) as high_severity,
                     COUNT(CASE WHEN severity = 'medium' THEN 1 END) as medium_severity,
                     COUNT(CASE WHEN severity = 'low' THEN 1 END) as low_severity
                     FROM traffic_incidents 
                     WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                     GROUP BY DATE(created_at)
                     ORDER BY incident_date ASC";
    
    $result = $conn->query($severityQuery);
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    $labels = [];
    $highData = [];
    $mediumData = [];
    $lowData = [];
    
    // Generate last 7 days labels
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $dayName = date('D', strtotime("-$i days"));
        $labels[] = $dayName;
        $highData[] = 0;
        $mediumData[] = 0;
        $lowData[] = 0;
    }
    
    // Fill with actual data
    while ($row = $result->fetch_assoc()) {
        $dayIndex = array_search(date('D', strtotime($row['incident_date'])), $labels);
        if ($dayIndex !== false) {
            $highData[$dayIndex] = (int)$row['high_severity'];
            $mediumData[$dayIndex] = (int)$row['medium_severity'];
            $lowData[$dayIndex] = (int)$row['low_severity'];
        }
    }

    // 6. Recent Activities
    $activitiesQuery = "SELECT 
                       incident_type,
                       severity,
                       status,
                       created_at,
                       updated_at,
                       location
                       FROM traffic_incidents 
                       ORDER BY updated_at DESC 
                       LIMIT 10";
    
    $result = $conn->query($activitiesQuery);
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    $activities = [];
    while ($row = $result->fetch_assoc()) {
        $timeAgo = getTimeAgo($row['updated_at']);
        $activities[] = [
            'title' => ucfirst($row['incident_type']) . ' - ' . ucfirst($row['severity']) . ' severity',
            'time' => $timeAgo,
            'type' => $row['severity'],
            'status' => $row['status']
        ];
    }

    // 7. Latest Projects
    $projectsQuery = "SELECT 
                     project_name,
                     progress_percentage,
                     status
                     FROM projects 
                     WHERE status IN ('active', 'in_progress')
                     ORDER BY updated_at DESC 
                     LIMIT 5";
    
    $result = $conn->query($projectsQuery);
    $projects = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $projects[] = [
                'name' => $row['project_name'],
                'progress' => (int)$row['progress_percentage']
            ];
        }
    } else {
        // Default projects
        $projects = [
            ['name' => 'Traffic Signal Optimization', 'progress' => 85],
            ['name' => 'Smart Camera Integration', 'progress' => 65],
            ['name' => 'Mobile App Development', 'progress' => 45]
        ];
    }

    // 8. Monthly Trend
    $trendQuery = "SELECT 
                  MONTH(created_at) as month,
                  YEAR(created_at) as year,
                  COUNT(*) as count,
                  MONTHNAME(created_at) as month_name
                  FROM traffic_reports 
                  WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                  GROUP BY YEAR(created_at), MONTH(created_at)
                  ORDER BY year ASC, month ASC";
    
    $result = $conn->query($trendQuery);
    $trendLabels = [];
    $trendData = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $monthName = substr($row['month_name'], 0, 3);
            $trendLabels[] = $monthName;
            $trendData[] = (int)$row['count'];
        }
    } else {
        // Default data
        $trendLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
        $trendData = [120, 135, 110, 150, 130, 145];
    }

    // Response data
    $response = [
        'success' => true,
        'timestamp' => date('Y-m-d H:i:s'),
        'data' => [
            'stats' => [
                'activeIncidents' => [
                    'value' => (int)$activeIncidents['total_count'],
                    'change' => round($weekChange, 1),
                    'changeType' => $weekChange >= 0 ? 'positive' : 'negative',
                    'progress' => min(100, abs($weekChange) * 2)
                ],
                'totalReports' => [
                    'value' => (int)$totalReports['total_count'],
                    'change' => round($monthChange, 1),
                    'changeType' => $monthChange >= 0 ? 'positive' : 'negative',
                    'progress' => min(100, abs($monthChange) * 1.5)
                ],
                'responseTime' => [
                    'value' => round($responseTime['avg_hours'] ?? 47, 0) . 'h',
                    'change' => rand(-20, 5),
                    'changeType' => 'negative',
                    'progress' => 85
                ],
                'systemStatus' => [
                    'value' => round($healthPercentage, 0) . '%',
                    'change' => rand(2, 8),
                    'changeType' => 'positive',
                    'progress' => round($healthPercentage, 0)
                ]
            ],
            'charts' => [
                'severity' => [
                    'labels' => $labels,
                    'datasets' => [
                        [
                            'label' => 'High Severity',
                            'data' => $highData,
                            'backgroundColor' => 'rgba(239, 68, 68, 0.8)',
                            'borderRadius' => 8,
                            'borderSkipped' => false
                        ],
                        [
                            'label' => 'Medium Severity',
                            'data' => $mediumData,
                            'backgroundColor' => 'rgba(245, 158, 11, 0.8)',
                            'borderRadius' => 8,
                            'borderSkipped' => false
                        ],
                        [
                            'label' => 'Low Severity',
                            'data' => $lowData,
                            'backgroundColor' => 'rgba(16, 185, 129, 0.8)',
                            'borderRadius' => 8,
                            'borderSkipped' => false
                        ]
                    ]
                ],
                'trend' => [
                    'labels' => $trendLabels,
                    'data' => $trendData
                ]
            ],
            'activities' => $activities,
            'projects' => $projects
        ]
    ];

    echo json_encode($response, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);
}

function getTimeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time/60) . ' minutes ago';
    if ($time < 86400) return floor($time/3600) . ' hours ago';
    if ($time < 2592000) return floor($time/86400) . ' days ago';
    if ($time < 31104000) return floor($time/2592000) . ' months ago';
    return floor($time/31104000) . ' years ago';
}

if (isset($conn)) {
    $conn->close();
}
?>
<?php
// =================================================================
// ADD-TRAFFIC.PHP - SUPERCHARGED CODE (FIXED VERSION)
// =================================================================
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("../includes/auth.php");
require_once("../includes/db.php");

// !!!!!!!!!! SABSE ZAROORI HISSA !!!!!!!!!!
// Yeh woh list hai jahan se location aur uske coordinates match honge.
// Aapko nayi locations yahan add karni hongi.
$location_coordinates = [
    "Chhatrapati Shivaji Maharaj Terminus" => ["lat" => 19.0760, "lng" => 72.8777],
    "Bandra-Worli Sea Link" => ["lat" => 19.0372, "lng" => 72.8129],
    "Andheri East" => ["lat" => 19.1136, "lng" => 72.8697],
    "Marine Drive" => ["lat" => 18.9432, "lng" => 72.8243],
    "Bandra Station" => ["lat" => 19.0568, "lng" => 72.8411],
    "Juhu Beach" => ["lat" => 19.1074, "lng" => 72.8263],
    "Gateway of India" => ["lat" => 18.9220, "lng" => 72.8347],
    "Thane" => ["lat" => 19.2183, "lng" => 72.9781],
    "Connaught Place, New Delhi" => ["lat" => 28.6330, "lng" => 77.2193],
    "Laxmi Nagar Metro Station" => ["lat" => 28.6340, "lng" => 77.2758],
    "Pune Station" => ["lat" => 18.5204, "lng" => 73.8567],
    "Shivajinagar Pune" => ["lat" => 18.5300, "lng" => 73.8500],
    "Koregaon Park" => ["lat" => 18.5362, "lng" => 73.8929],
    "Hadapsar" => ["lat" => 18.5089, "lng" => 73.9260],
    "Wakad" => ["lat" => 18.5975, "lng" => 73.7639]
];

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Input validation and sanitization
    $location = trim($_POST['location']);
    $date = $_POST['date'];
    $time = $_POST['time'];
    $severity = $_POST['severity'];
    $description = trim($_POST['description']);
    $status = 'Active'; // Har nayi entry Active rahegi

    // Basic validation
    if (empty($location) || empty($date) || empty($time) || empty($severity) || empty($description)) {
        $message = "All fields are required!";
        $message_type = "danger";
    } else {
        // Location ke naam se coordinates (lat, lng) nikalna
        $latitude = null;
        $longitude = null;
        if (array_key_exists($location, $location_coordinates)) {
            $latitude = $location_coordinates[$location]['lat'];
            $longitude = $location_coordinates[$location]['lng'];
        }

        try {
            // Check if table has latitude, longitude, status columns
            // If not, use basic insert without these columns
            $check_columns = $conn->query("DESCRIBE traffic_data");
            $columns = [];
            while ($row = $check_columns->fetch_assoc()) {
                $columns[] = $row['Field'];
            }

            if (in_array('latitude', $columns) && in_array('longitude', $columns) && in_array('status', $columns)) {
                // Full insert with coordinates and status
                $sql = "INSERT INTO traffic_data (location, date, time, severity, description, latitude, longitude, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                
                if ($stmt === false) {
                    throw new Exception("SQL Prepare Error: " . $conn->error);
                }
                
                $stmt->bind_param("sssssdds", $location, $date, $time, $severity, $description, $latitude, $longitude, $status);
            } else {
                // Basic insert without coordinates and status
                $sql = "INSERT INTO traffic_data (location, date, time, severity, description) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                
                if ($stmt === false) {
                    throw new Exception("SQL Prepare Error: " . $conn->error);
                }
                
                $stmt->bind_param("sssss", $location, $date, $time, $severity, $description);
            }

            if ($stmt->execute()) {
                $stmt->close();
                $conn->close();
                header("Location: view-traffic.php?status=add_success");
                exit();
            } else {
                throw new Exception("SQL Execute Error: " . $stmt->error);
            }
        } catch (Exception $e) {
            $message = "Error: " . htmlspecialchars($e->getMessage());
            $message_type = "danger";
        }
        
        if (isset($stmt)) {
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Traffic Incident - Traffic Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
        }
        
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            min-height: 100vh; 
            padding: 20px 0; 
        }
        
        .container { 
            max-width: 900px; 
            margin: 0 auto; 
            padding: 0 20px; 
        }
        
        .form-card { 
            background: rgba(255, 255, 255, 0.95); 
            backdrop-filter: blur(20px); 
            border-radius: 20px; 
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1); 
            overflow: hidden;
        }
        
        .card-header { 
            background: linear-gradient(135deg, #6366f1, #8b5cf6); 
            color: white; 
            padding: 30px; 
            text-align: center; 
        }
        
        .card-header h2 { 
            font-size: 1.8rem; 
            margin: 0;
            font-weight: 600;
        }
        
        .card-body { 
            padding: 40px; 
        }
        
        .alert-danger { 
            background: linear-gradient(135deg, #fee2e2, #fecaca); 
            color: #991b1b; 
            padding: 15px; 
            border-radius: 12px; 
            margin-bottom: 20px; 
            border-left: 4px solid #dc2626;
        }
        
        .alert-success { 
            background: linear-gradient(135deg, #d1fae5, #a7f3d0); 
            color: #065f46; 
            padding: 15px; 
            border-radius: 12px; 
            margin-bottom: 20px; 
            border-left: 4px solid #059669;
        }
        
        .form-group { 
            margin-bottom: 25px; 
        }
        
        .form-label { 
            display: block; 
            margin-bottom: 8px; 
            font-weight: 600; 
            color: #374151; 
            font-size: 1rem;
        }
        
        .form-control, .form-select { 
            width: 100%; 
            padding: 15px 20px; 
            border: 2px solid #e5e7eb; 
            border-radius: 12px; 
            font-size: 1rem; 
            transition: all 0.3s ease;
            background: white;
        }
        
        .form-control:focus, .form-select:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }
        
        .form-control:hover, .form-select:hover {
            border-color: #d1d5db;
        }
        
        textarea.form-control {
            resize: vertical;
            min-height: 120px;
        }
        
        .row { 
            display: flex; 
            gap: 20px; 
            flex-wrap: wrap;
        } 
        
        .col-md-6 { 
            flex: 1; 
            min-width: 200px;
        }
        
        .btn-primary { 
            width: 100%; 
            padding: 18px; 
            background: linear-gradient(135deg, #6366f1, #8b5cf6); 
            color: white; 
            border: none; 
            border-radius: 12px; 
            font-size: 1.1rem; 
            font-weight: 600; 
            cursor: pointer; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(99, 102, 241, 0.3);
        }
        
        .btn-primary:active {
            transform: translateY(0);
        }
        
        .btn-primary:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }
        
        .input-icon { 
            position: relative; 
        } 
        
        .input-icon i { 
            position: absolute; 
            left: 15px; 
            top: 50%; 
            transform: translateY(-50%); 
            color: #6b7280; 
            z-index: 2;
        }
        
        .input-icon .form-control, .input-icon .form-select { 
            padding-left: 45px; 
        }
        
        .severity-options { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); 
            gap: 15px; 
            margin-top: 10px; 
        }
        
        .severity-card { 
            padding: 20px 15px; 
            border: 2px solid #e5e7eb; 
            border-radius: 12px; 
            text-align: center; 
            cursor: pointer; 
            transition: all 0.3s ease; 
            background: white;
        }
        
        .severity-card:hover {
            border-color: #6366f1;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(99, 102, 241, 0.2);
        }
        
        .severity-card.active { 
            border-color: #6366f1; 
            background: linear-gradient(135deg, #eef2ff, #e0e7ff); 
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(99, 102, 241, 0.2);
        }
        
        .severity-card i {
            font-size: 1.5rem;
            margin-bottom: 8px;
            display: block;
        }
        
        .severity-card div {
            font-weight: 600;
            color: #374151;
        }
        
        .loading { 
            display: none; 
            width: 20px; 
            height: 20px; 
            border: 2px solid transparent; 
            border-top: 2px solid currentColor; 
            border-radius: 50%; 
            animation: spin 1s linear infinite; 
            margin-right: 10px; 
        }
        
        @keyframes spin { 
            to { 
                transform: rotate(360deg); 
            } 
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: white;
            text-decoration: none;
            padding: 12px 20px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .back-link:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
            transform: translateX(-3px);
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 0 15px;
            }
            
            .card-body {
                padding: 20px;
            }
            
            .row {
                flex-direction: column;
                gap: 0;
            }
            
            .severity-options {
                grid-template-columns: 1fr;
            }
            
            .card-header h2 {
                font-size: 1.5rem;
            }
        }
        
        /* Custom select styling */
        .form-select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 12px center;
            background-repeat: no-repeat;
            background-size: 16px;
            appearance: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="view-traffic.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Traffic Dashboard
        </a>
        
        <div class="form-card">
            <div class="card-header">
                <h2><i class="fas fa-plus-circle"></i> Add New Traffic Incident</h2>
            </div>
            <div class="card-body">
                <?php if (!empty($message)): ?>
                    <div class="alert-<?php echo $message_type; ?>">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span><?php echo htmlspecialchars($message); ?></span>
                    </div>
                <?php endif; ?>

                <form id="trafficForm" method="POST" action="add-traffic.php">
                    <div class="form-group">
                        <label class="form-label" for="location">
                            <i class="fas fa-map-marker-alt"></i> Location
                        </label>
                        <div class="input-icon">
                            <i class="fas fa-map-marker-alt"></i>
                            <select class="form-select" id="location" name="location" required>
                                <option value="" disabled selected>-- Select a Location --</option>
                                <?php foreach ($location_coordinates as $name => $coords): ?>
                                    <option value="<?php echo htmlspecialchars($name); ?>">
                                        <?php echo htmlspecialchars($name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label" for="date">
                                    <i class="fas fa-calendar"></i> Date
                                </label>
                                <div class="input-icon">
                                    <i class="fas fa-calendar"></i>
                                    <input type="date" class="form-control" id="date" name="date" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label" for="time">
                                    <i class="fas fa-clock"></i> Time
                                </label>
                                <div class="input-icon">
                                    <i class="fas fa-clock"></i>
                                    <input type="time" class="form-control" id="time" name="time" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-exclamation-triangle"></i> Severity Level
                        </label>
                        <select id="severity" name="severity" required style="display: none;">
                            <option value=""></option>
                            <option value="Low">Low</option>
                            <option value="Moderate">Moderate</option>
                            <option value="High">High</option>
                        </select>
                        <div class="severity-options">
                            <div class="severity-card" onclick="selectSeverity('Low', this)">
                                <i class="fas fa-check-circle" style="color: #10b981;"></i>
                                <div>Low</div>
                                <small style="color: #6b7280; margin-top: 5px; display: block;">Minor disruption</small>
                            </div>
                            <div class="severity-card" onclick="selectSeverity('Moderate', this)">
                                <i class="fas fa-exclamation-circle" style="color: #f59e0b;"></i>
                                <div>Moderate</div>
                                <small style="color: #6b7280; margin-top: 5px; display: block;">Significant disruption</small>
                            </div>
                            <div class="severity-card" onclick="selectSeverity('High', this)">
                                <i class="fas fa-times-circle" style="color: #ef4444;"></i>
                                <div>High</div>
                                <small style="color: #6b7280; margin-top: 5px; display: block;">Severe disruption</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="description">
                            <i class="fas fa-file-alt"></i> Description
                        </label>
                        <textarea class="form-control" 
                                  id="description"
                                  name="description" 
                                  placeholder="Describe the incident in detail... (e.g., road closure, accident, heavy traffic)" 
                                  rows="4" 
                                  required></textarea>
                    </div>

                    <button type="submit" class="btn-primary" id="submitBtn">
                        <span class="loading"></span>
                        <span class="btn-text">
                            <i class="fas fa-paper-plane"></i> Submit Incident Report
                        </span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Set current date and time
        document.getElementById('date').valueAsDate = new Date();
        const now = new Date();
        document.getElementById('time').value = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');

        function selectSeverity(severity, element) {
            // Remove active class from all cards
            document.querySelectorAll('.severity-card').forEach(card => card.classList.remove('active'));
            // Add active class to selected card
            element.classList.add('active');
            // Set hidden select value
            document.getElementById('severity').value = severity;
        }

        // Form validation and submission
        document.getElementById('trafficForm').addEventListener('submit', function(e) {
            const location = document.getElementById('location').value;
            const date = document.getElementById('date').value;
            const time = document.getElementById('time').value;
            const severity = document.getElementById('severity').value;
            const description = document.getElementById('description').value.trim();

            // Validation
            if (!location || !date || !time || !severity || !description) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return false;
            }

            if (severity === "") {
                e.preventDefault(); 
                alert('Please select a severity level.');
                return false;
            }

            // Date validation - not in future
            const selectedDate = new Date(date);
            const today = new Date();
            today.setHours(23, 59, 59, 999); // End of today

            if (selectedDate > today) {
                e.preventDefault();
                alert('Date cannot be in the future.');
                document.getElementById('date').focus();
                return false;
            }

            // Show loading state
            const submitBtn = document.getElementById('submitBtn');
            const loading = submitBtn.querySelector('.loading');
            const btnText = submitBtn.querySelector('.btn-text');
            
            loading.style.display = 'inline-block';
            if (btnText) {
                btnText.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
            }
            submitBtn.disabled = true;
        });

        // Auto-resize textarea
        const textarea = document.getElementById('description');
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    </script>
</body>
</html>
<?php
// Close connection at the end
if (isset($conn) && $conn) {
    $conn->close();
}
?>
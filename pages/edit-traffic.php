<?php
// =============================================================
//  EDIT RECORD PAGE
// =============================================================
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("../includes/auth.php");
require_once("../includes/db.php");

$message = "";
$message_type = "";

// Check karna ki URL mein ID hai
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: view-traffic.php");
    exit();
}
$record_id = (int)$_GET['id'];

// FORM SUBMIT HONE PAR DATA UPDATE KARNA
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $location = trim($_POST['location']);
    $date = $_POST['date'];
    $time = $_POST['time'];
    $severity = $_POST['severity'];
    $description = trim($_POST['description']);
    $id_to_update = (int)$_POST['id'];

    // Validation
    if (empty($location) || empty($date) || empty($time) || empty($severity) || empty($description)) {
        $message = "All fields are required!";
        $message_type = "danger";
    } else {
        $sql = "UPDATE traffic_data SET location = ?, date = ?, time = ?, severity = ?, description = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("sssssi", $location, $date, $time, $severity, $description, $id_to_update);

            if ($stmt->execute()) {
                $stmt->close();
                $conn->close();
                header("Location: view-traffic.php?status=update_success");
                exit();
            } else {
                $message = "Error updating record: " . $stmt->error;
                $message_type = "danger";
            }
            $stmt->close();
        } else {
            $message = "Database error: " . $conn->error;
            $message_type = "danger";
        }
    }
}

// PURANA DATA FETCH KARNA
$sql_fetch = "SELECT * FROM traffic_data WHERE id = ?";
$stmt_fetch = $conn->prepare($sql_fetch);

if ($stmt_fetch) {
    $stmt_fetch->bind_param("i", $record_id);
    $stmt_fetch->execute();
    $result = $stmt_fetch->get_result();
    
    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
    } else {
        $stmt_fetch->close();
        $conn->close();
        header("Location: view-traffic.php?status=not_found");
        exit();
    }
    $stmt_fetch->close();
} else {
    $conn->close();
    header("Location: view-traffic.php?status=db_error");
    exit();
}

// Database connection ko yahan close karna hai agar form submit nahi hua
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    // Keep connection open for form display
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Incident #<?php echo htmlspecialchars($row['id']); ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f59e0b, #ef4444);
            min-height: 100vh;
            padding: 20px 0;
        }

        .container {
            max-width: 900px;
            margin: 2rem auto;
            padding: 0 20px;
        }

        .form-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, #f59e0b, #ef4444);
            color: white;
            padding: 25px;
            text-align: center;
        }

        .card-header h2 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 600;
        }

        .card-body {
            padding: 40px;
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
            padding: 12px 15px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }

        .form-control:focus, .form-select:focus {
            outline: none;
            border-color: #f59e0b;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
        }

        .form-control:hover, .form-select:hover {
            border-color: #d1d5db;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

        .btn-primary {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #ef4444, #f59e0b);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-primary:active {
            transform: translateY(0);
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

        .alert-danger {
            padding: 15px;
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #dc2626;
        }

        .alert-success {
            padding: 15px;
            background: #f0fdf4;
            color: #16a34a;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #16a34a;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
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
            
            .card-header h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="view-traffic.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Traffic Records
        </a>
        
        <div class="form-card">
            <div class="card-header">
                <h2><i class="fas fa-edit"></i> Edit Traffic Incident #<?php echo htmlspecialchars($row['id']); ?></h2>
            </div>
            
            <div class="card-body">
                <?php if (!empty($message)): ?>
                    <div class="alert-<?php echo $message_type; ?>">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span><?php echo htmlspecialchars($message); ?></span>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="edit-traffic.php?id=<?php echo htmlspecialchars($row['id']); ?>" id="editForm">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['id']); ?>">
                    
                    <div class="form-group">
                        <label class="form-label" for="location">
                            <i class="fas fa-map-marker-alt"></i> Location
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="location"
                               name="location" 
                               value="<?php echo htmlspecialchars($row['location']); ?>" 
                               required 
                               placeholder="Enter location of incident">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label" for="date">
                                    <i class="fas fa-calendar-alt"></i> Date
                                </label>
                                <input type="date" 
                                       class="form-control" 
                                       id="date"
                                       name="date" 
                                       value="<?php echo htmlspecialchars($row['date']); ?>" 
                                       required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label" for="time">
                                    <i class="fas fa-clock"></i> Time
                                </label>
                                <input type="time" 
                                       class="form-control" 
                                       id="time"
                                       name="time" 
                                       value="<?php echo htmlspecialchars($row['time']); ?>" 
                                       required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="severity">
                            <i class="fas fa-exclamation-circle"></i> Severity Level
                        </label>
                        <select class="form-select" id="severity" name="severity" required>
                            <option value="">Select Severity Level</option>
                            <option value="Low" <?php echo (strtolower($row['severity']) == 'low') ? 'selected' : ''; ?>>
                                🟢 Low - Minor disruption
                            </option>
                            <option value="Moderate" <?php echo (in_array(strtolower($row['severity']), ['moderate', 'medium'])) ? 'selected' : ''; ?>>
                                🟡 Moderate - Moderate disruption
                            </option>
                            <option value="High" <?php echo (strtolower($row['severity']) == 'high') ? 'selected' : ''; ?>>
                                🔴 High - Severe disruption
                            </option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="description">
                            <i class="fas fa-file-alt"></i> Description
                        </label>
                        <textarea class="form-control" 
                                  id="description"
                                  name="description" 
                                  rows="4" 
                                  required 
                                  placeholder="Provide detailed description of the incident..."><?php echo htmlspecialchars($row['description']); ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save"></i> Update Record
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Form validation
        document.getElementById('editForm').addEventListener('submit', function(e) {
            const location = document.getElementById('location').value.trim();
            const date = document.getElementById('date').value;
            const time = document.getElementById('time').value;
            const severity = document.getElementById('severity').value;
            const description = document.getElementById('description').value.trim();

            if (!location || !date || !time || !severity || !description) {
                e.preventDefault();
                alert('Please fill in all required fields.');
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
if ($conn) {
    $conn->close();
}
?>
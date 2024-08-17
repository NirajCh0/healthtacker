<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "health_tracker";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user = $_SESSION['username'];
$sql = "SELECT * FROM users WHERE username='$user'";
$result = $conn->query($sql);
$user_data = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $weight = $_POST['weight'];
    $steps = $_POST['steps'];
    $calories = $_POST['calories'];
    $sleep_hours = $_POST['sleep_hours'];
    $food_intake = $_POST['food_intake'];
    $date = date("Y-m-d");

    $stmt = $conn->prepare("INSERT INTO health_metrics (user_id, date, weight, steps, calories, sleep_hours, food_intake) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isiiids", $user_data['id'], $date, $weight, $steps, $calories, $sleep_hours, $food_intake);
    $stmt->execute();
}

$metrics_sql = "SELECT * FROM health_metrics WHERE user_id='$user_data[id]' ORDER BY date DESC";
$metrics_result = $conn->query($metrics_sql);
$metrics = [];
while ($row = $metrics_result->fetch_assoc()) {
    $metrics[] = $row;
}

$avg_weight = $avg_steps = $avg_calories = $avg_sleep = 0;
if (count($metrics) > 0) {
    foreach ($metrics as $metric) {
        $avg_weight += $metric['weight'];
        $avg_steps += $metric['steps'];
        $avg_calories += $metric['calories'];
        $avg_sleep += $metric['sleep_hours'];
    }
    $avg_weight /= count($metrics);
    $avg_steps /= count($metrics);
    $avg_calories /= count($metrics);
    $avg_sleep /= count($metrics);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container main-page">
        <h1>Dashboard</h1>
        <div class="dashboard">
            <h2>Welcome, <?php echo $user_data['name']; ?>!</h2>
            <p>Goal: <?php echo $user_data['goal']; ?></p>
            <form action="dashboard.php" method="POST">
                <input type="number" name="weight" placeholder="Weight (kg)" required>
                <input type="number" name="steps" placeholder="Steps" required>
                <input type="number" name="calories" placeholder="Calories" required>
                <input type="number" name="sleep_hours" placeholder="Sleep Hours" required>
                <input type="text" name="food_intake" placeholder="Food Intake" required>
                <button type="submit">Submit</button>
            </form>
        </div>
        <div class="dashboard">
            <h2>Your Health Metrics</h2>
            <table>
                <tr>
                    <th>Date</th>
                    <th>Weight (kg)</th>
                    <th>Steps</th>
                    <th>Calories</th>
                    <th>Sleep Hours</th>
                    <th>Food Intake</th>
                </tr>
                <?php foreach ($metrics as $metric) : ?>
                    <tr>
                        <td><?php echo $metric['date']; ?></td>
                        <td><?php echo $metric['weight']; ?></td>
                        <td><?php echo $metric['steps']; ?></td>
                        <td><?php echo $metric['calories']; ?></td>
                        <td><?php echo $metric['sleep_hours']; ?></td>
                        <td><?php echo $metric['food_intake']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <div class="dashboard">
            <h2>Average Health Metrics</h2>
            <p>Average Weight: <span id="avgWeight"><?php echo $avg_weight; ?></span> kg</p>
            <p>Average Steps: <span id="avgSteps"><?php echo $avg_steps; ?></span></p>
            <p>Average Calories: <span id="avgCalories"><?php echo $avg_calories; ?></span></p>
            <p>Average Sleep Hours: <span id="avgSleep"><?php echo $avg_sleep; ?></span> hours</p>
        </div>
        <div class="health-benefits">
            <h2>Health Benefits</h2>
            <div>
                <img src="water.jpg" alt="Drink Water" class="icon">
                <p><strong>Drinking Water:</strong> Helps maintain the balance of bodily fluids, keeps skin looking good, and energizes muscles.</p>
            </div>
            <div>
                <img src="sleep.jpg" alt="Sleep" class="icon">
                <p><strong>Sleep:</strong> Supports cognitive function, improves mood, and helps maintain physical health.</p>
            </div>
            <div>
                <img src="food.jpg" alt="Healthy Food" class="icon">
                <p><strong>Healthy Food:</strong> Provides essential nutrients, supports immune function, and reduces the risk of chronic diseases.</p>
            </div>
        </div>
        <a href="logout.php">Logout</a>
    </div>
</body>
</html>

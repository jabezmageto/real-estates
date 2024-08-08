<?php
include 'config.php';
include 'session.php'; // Ensure session management is included

// Fetch bookings for the logged-in user
$sql = "SELECT b.*, p.title, p.description, p.image, p.price FROM bookings b
        JOIN properties p ON b.property_id = p.id
        WHERE b.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if the query was successful
if ($result === false) {
    // Output the error message
    die("Error executing query: " . $conn->error);
}

// Handle form submission for client details
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_client_details'])) {
    $client_name = $_POST['client_name'];
    $client_email = $_POST['client_email'];
    $client_phone = $_POST['client_phone'];

    // Validate and sanitize input
    $client_name = htmlspecialchars($client_name);
    $client_email = filter_var($client_email, FILTER_SANITIZE_EMAIL);
    $client_phone = htmlspecialchars($client_phone);

    // Update client details in the database (assuming there is a client_details table)
    $sql_update = "UPDATE client_details SET name = ?, email = ?, phone = ? WHERE user_id = ?";
    $stmt_update = $conn->prepare($sql_update);
    if ($stmt_update) {
        $stmt_update->bind_param('sssi', $client_name, $client_email, $client_phone, $user_id);
        $stmt_update->execute();
        $stmt_update->close();
        $message = "Your Details have been captured successfully!";
        $show_modal = true;
    } else {
        $message = "Error updating client details: " . $conn->error;
        $show_modal = false;
    }
} else {
    $show_modal = false;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - Real Estate Platform</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        body {
            background-color: #f7f7f7;
            color: #333;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: whitesmoke;
            color: black;
            padding: 20px;
        }
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
        }
        .header-title h1 {
            margin: 0;
            font-size: 36px;
        }
        .nav {
            display: flex;
            justify-content: right;
            align-items: center;
        }
        .nav a {
            color: black;
            text-decoration: none;
            margin-left: 20px;
        }
        .nav a:hover {
            text-decoration: underline;
        }
        .booking-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            padding: 20px;
        }
        .booking {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .booking img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            transition: transform 0.3s;
        }
        .booking .details {
            padding: 15px;
        }
        .booking h3 {
            margin: 0 0 10px;
            font-size: 18px;
            color: #007bff;
        }
        .booking p {
            margin: 0 0 10px;
        }
        .booking-footer {
            padding: 15px;
            background-color: #f7f7f7;
            text-align: center;
            border-top: 1px solid #ddd;
        }
        .booking-footer span {
            display: block;
            margin-bottom: 5px;
        }
        .booking:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
        .booking:hover img {
            transform: scale(1.05);
        }
        .form-container {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .form-container h2 {
            margin-top: 0;
            color: #007bff;
        }
        .form-container input[type="text"],
        .form-container input[type="email"] {
            width: calc(100% - 22px);
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .form-container button {
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .form-container button:hover {
            background-color: #0056b3;
        }
        .form-container .message {
            margin-bottom: 10px;
            color: green;
        }
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
            padding-top: 60px;
        }
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        .modal-header {
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
            font-size: 18px;
        }
        .modal-body {
            padding: 20px 0;
        }
        .modal-footer {
            padding: 10px 0;
            border-top: 1px solid #ddd;
            text-align: right;
        }
        .modal-footer button {
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .modal-footer button:hover {
            background-color: #0056b3;
        }
        .modal-footer .close {
            background-color: #ccc;
        }
        .modal-footer .close:hover {
            background-color: #aaa;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-title">
                <h1>My Bookings</h1>
            </div>
            <nav class="nav">
                <a href="index.php">Home</a>
                <a href="contact.php">Contact</a>
                <a href="about.php">About</a>
                <a href="logout.php">Logout</a>
            </nav>
        </div>
    </header>
    <div class="container">
        <!-- Form to Update Client Details -->
        <div class="form-container">
            <h2>Update Client Details</h2>
            <?php if (isset($message)) echo "<div class='message'>$message</div>"; ?>
            <form method="POST" action="">
                <input type="text" name="client_name" placeholder="Name" required>
                <input type="email" name="client_email" placeholder="Email" required>
                <input type="text" name="client_phone" placeholder="Phone Number" required>
                <button type="submit" name="update_client_details">Update Details</button>
            </form>
        </div>

        <!-- Booking List -->
        <div class="booking-list">
            <?php while ($row = $result->fetch_assoc()): ?>
            <div class="booking">
                <img src="uploads/<?php echo $row['image']; ?>" alt="Property Image">
                <div class="details">
                    <h3><?php echo $row['title']; ?></h3>
                    <p><?php echo $row['description']; ?></p>
                    <p>Price: Ksh <?php echo $row['price']; ?></p>
                    <p>Status: <?php echo $row['status']; ?></p>
                </div>
                <div class="booking-footer">
                    <span>Booking Date: <?php echo $row['booking_date']; ?></span>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Modal Popup -->
    <?php if ($show_modal): ?>
    <div id="myModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close">&times;</span>
                <h2>Update Successful</h2>
            </div>
            <div class="modal-body">
                <p>Your details have been updated successfully.</p>
                <p>Would you like to search for another house or exit the page?</p>
            </div>
            <div class="modal-footer">
                <a href="search.php"><button>Search for Another House</button></a>
                <button class="close">Exit</button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script>
        // Get the modal
        var modal = document.getElementById("myModal");

        // Get the close button elements
        var span = document.getElementsByClassName("close")[0];
        var exitButton = document.querySelector(".modal-footer .close");

        // Show the modal
        if (modal) {
            modal.style.display = "block";
        }

        // When the user clicks on <span> (x), close the modal
        span.onclick = function() {
            modal.style.display = "none";
        }

        exitButton.onclick = function() {
            window.location.href = "index.php"; // Redirect to homepage or any desired page
        }

        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>

<?php
include 'config.php';
include 'session.php';

// Check if the user is logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get user ID from session
    $user_id = getUser()['id'];

    // Check if all required form fields are set
    if (isset($_POST['title'], $_POST['description'], $_POST['price_range'], $_POST['house_type'], $_POST['location']) && isset($_FILES['images'])) {
        // Process form data
        $title = $_POST['title'];
        $description = $_POST['description'];
        $price_range = $_POST['price_range'];
        $house_type = $_POST['house_type'];
        $location = $_POST['location'];

        // Process image upload
        $images = $_FILES['images'];
        $file_count = count($images['name']);

        if ($file_count < 4 || $file_count > 10) {
            echo "You must upload between 4 and 10 images.";
        } else {
            $uploaded_images = [];
            foreach ($images['tmp_name'] as $key => $tmp_name) {
                $image_name = basename($images['name'][$key]);
                $upload_path = 'uploads/' . $image_name;
                if (move_uploaded_file($tmp_name, $upload_path)) {
                    $uploaded_images[] = $image_name;
                } else {
                    echo "Failed to upload image: " . $image_name;
                }
            }

            // Save property details to the database
            $uploaded_images_str = implode(',', $uploaded_images);
            $sql = "INSERT INTO properties (title, description, price_range, house_type, location, image, user_id) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ssssssi', $title, $description, $price_range, $house_type, $location, $uploaded_images_str, $user_id);

            if ($stmt->execute()) {
                header("Location: my-properties.php?upload=success");
                exit();
            } else {
                echo "Error: " . $stmt->error;
            }
        }
    } else {
        echo "All fields are required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const fileInput = document.getElementById('imageUpload');

        fileInput.addEventListener('change', function() {
            const files = fileInput.files;
            const fileCount = files.length;
            
            if (fileCount < 4 || fileCount > 10) {
                alert('Please select between 4 and 10 images.');
                fileInput.value = ''; // Clear the input
            }
        });
    });
    </script>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Property</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(to right, #ff6f61, #deba5a);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: black;
        }

        /* Container Styling */
        .container {
            background: rgba(255, 255, 255, 0.9);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 90%;
            max-width: 600px;
            text-align: center;
        }

        /* Heading Styling */
        h1 {
            color: #333;
            margin-bottom: 20px;
            font-size: 2em;
            background: linear-gradient(to right, #ff6f61, #deba5a);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Form Styling */
        form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Input, Textarea, and Button Styling */
        .input-container {
            width: 100%;
            margin-bottom: 15px;
        }

        input, textarea, button, .dropdown-label {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
            font-size: 1em;
        }

        input[type="file"] {
            padding: 0;
        }

        input:focus, textarea:focus, .dropdown-label:focus {
            border-color: #ff6f61;
        }

        button {
            background-color: #ff6f61;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 1em;
            transition: background-color 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        button:hover {
            background-color: #deba5a;
        }

        /* Success and Error Message Styling */
        p.success {
            color: #4caf50;
            font-weight: bold;
        }

        p.error {
            color: #ff4c4c;
            font-weight: bold;
        }

        /* Link Styling */
        a {
            color: #ff6f61;
            text-decoration: none;
            font-weight: bold;
        }

        a:hover {
            text-decoration: underline;
        }

        /* Radio Button and Dropdown Styling */
        .dropdown {
            position: relative;
            display: inline-block;
            width: 100%;
            margin-bottom: 15px;
            background-color: #fff;
            border-radius: 5px;
            border: 1px solid #ddd;
            transition: border-color 0.3s ease;
        }

        .dropdown-label {
            padding: 12px;
            cursor: pointer;
            font-weight: bold;
            color: #333;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .dropdown-label:hover {
            background-color: #f5f5f5;
            border-color: #ff6f61;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #fff;
            min-width: 100%;
            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
            z-index: 1;
            border-radius: 5px;
            border: 1px solid #ddd;
            flex-direction: column;
            text-align: left;
        }

        .dropdown:hover .dropdown-content {
            display: flex;
            flex-direction: column;
        }

        .dropdown-content label {
            padding: 12px;
            cursor: pointer;
            color: #333;
            font-size: 0.9em;
            transition: background-color 0.3s ease;
        }

        .dropdown-content label:hover {
            background-color: #f5f5f5;
        }

        .dropdown-content input[type="radio"] {
            margin-right: 10px;
            accent-color: #ff6f61;
        }

        /* Hover effect for radio buttons */
        .dropdown-content input[type="radio"]:checked + label {
            background-color: #ff6f61;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Upload Property</h1>
        <p><a href="index.php">Home</a></p>
        <form action="upload.php" method="POST" enctype="multipart/form-data">
            <div class="input-container">
                <input type="text" name="title" placeholder="Title" required>
            </div>
            <div class="input-container">
                <textarea name="description" placeholder="Description" required></textarea>
            </div>
            <div class="input-container">
                <!-- Price Range Dropdown Selection -->
                <div class="dropdown">
                    <div class="dropdown-label">Select Price Range</div>
                    <div class="dropdown-content">
                        <label><input type="radio" name="price_range" value="10,000-20,000" required> 10,000-20,000</label>
                        <label><input type="radio" name="price_range" value="20,000-30,000" required> 20,000-30,000</label>
                        <label><input type="radio" name="price_range" value="30,000-40,000" required> 30,000-40,000</label>
                        <label><input type="radio" name="price_range" value="40,000+" required> 40,000+</label>
                    </div>
                </div>
            </div>
            <div class="input-container">
                <div class="dropdown">
                    <div class="dropdown-label">Select House Type</div>
                    <div class="dropdown-content">
                        <label><input type="radio" name="house_type" value="Single-bedroom" required> Single-bedroom</label>
                        <label><input type="radio" name="house_type" value="Two-bedroom" required> Two-bedroom</label>
                        <label><input type="radio" name="house_type" value="Three-bedroom" required> Three-bedroom</label>
                        <label><input type="radio" name="house_type" value="Four-bedroom+" required> Four-bedroom+</label>
                    </div>
                </div>
            </div>
            <div class="input-container">
                <input type="text" name="location" placeholder="Location" required>
            </div>
            <div class="input-container">
                <input type="file" id="imageUpload" name="images[]" multiple required>
            </div>
            <button type="submit">Upload Property</button>
        </form>
        <?php
        if (isset($_GET['upload']) && $_GET['upload'] == 'success') {
            echo "<p class='success'>Property uploaded successfully!</p>";
        }
        ?>
    </div>
</body>
</html>

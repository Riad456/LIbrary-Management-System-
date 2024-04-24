<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ensure the target directory exists
    $target_dir = "uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true); // Create directory recursively with full permissions
    }

    // Check if a file was uploaded without errors
    if (isset($_FILES["file"]) && $_FILES["file"]["error"] == 0) {
        $target_file = $target_dir . basename($_FILES["file"]["name"]);
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if the file is allowed (you can modify this to allow specific file types)
        $allowed_types = array("jpg", "jpeg", "png", "gif", "pdf");
        $file_mime = mime_content_type($_FILES["file"]["tmp_name"]); // Get MIME type
        if (!in_array($file_type, $allowed_types) || !in_array($file_mime, array("image/jpeg", "image/png", "image/gif", "application/pdf"))) {
            echo "Sorry, only JPG, JPEG, PNG, GIF, and PDF files are allowed.";
        } else {
            // Move the uploaded file to the specified directory
            if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
                // File upload success, now store information in the database
                $filename = $_FILES["file"]["name"];
                $filesize = $_FILES["file"]["size"];
                $filetype = $_FILES["file"]["type"];

                // Database connection
                $db_host = "localhost";
                $db_user = "root";
                $db_pass = "";
                $db_name = "lms";

                $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                // Sanitize inputs to prevent SQL injection
                $filename = $conn->real_escape_string($filename);
                $filesize = intval($filesize);
                $filetype = $conn->real_escape_string($filetype);

                // Insert the file information into the database
                $sql = "INSERT INTO files (filename, filesize, filetype) VALUES ('$filename', $filesize, '$filetype')";

                if ($conn->query($sql) === TRUE) {
                    echo "The file " . basename($_FILES["file"]["name"]) . " has been uploaded and the information has been stored in the database.";
                } else {
                    echo "Sorry, there was an error uploading your file and storing information in the database: " . $conn->error;
                }

                // Close the database connection
                $conn->close();
            } else {
                echo "Sorry, there was an error uploading your file.";
            }
        }
    } else {
        echo "No file was uploaded.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <title>File upload and download</title>
</head>
<body>
    <div class="container mt-5">
        <h2>Upload a file</h2>
        <form action="upload.php" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="file" class="form-label">Select file</label>
                <!-- Restrict file types -->
                <input type="file" class="form-control" name="file" id="file" accept=".jpg, .jpeg, .png, .gif, .pdf">
            </div>
            <button type="submit" class="btn btn-primary">Upload file</button>
        </form>
    </div>

</body>
</html>

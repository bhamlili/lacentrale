php
<?php
// Get the directory the current script is in
$base_directory = dirname($_SERVER['SCRIPT_NAME']);

// Function to generate a dynamic URL
function generate_url($path) {
    global $base_directory;
    // Ensure a leading slash for consistency
    $path = '/' . ltrim($path, '/');
    // If base directory is not root, prepend it to the path
    if ($base_directory !== '/' && $base_directory !== '') {
        return $base_directory . $path;
    }
    return $path;
}
?>
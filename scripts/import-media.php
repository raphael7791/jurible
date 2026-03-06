<?php
/**
 * Import images into WordPress media library via direct SQL
 * For Local by Flywheel
 *
 * Usage: php import-media.php <image_path> [--featured <post_id>] [--post <post_id>]
 */

$socket = getenv('HOME') . '/Library/Application Support/Local/run/njWhKMb7k/mysql/mysqld.sock';
$siteUrl = 'http://jurible-local.local';
$uploadsBaseDir = getenv('HOME') . '/Local Sites/jurible-local/app/public/wp-content/uploads';
$uploadsBaseUrl = $siteUrl . '/wp-content/uploads';

function get_db_connection() {
    global $socket;
    $mysqli = new mysqli(null, 'root', 'root', 'local', 0, $socket);
    $mysqli->query("SET NAMES utf8mb4");
    return $mysqli;
}

function import_image_to_media_library(string $filePath, int $parentPostId = 0): ?int
{
    global $uploadsBaseDir, $uploadsBaseUrl, $siteUrl;

    if (!file_exists($filePath)) {
        fwrite(STDERR, "File not found: $filePath\n");
        return null;
    }

    $mysqli = get_db_connection();
    $filename = basename($filePath);

    // Get relative path from uploads dir
    if (strpos($filePath, $uploadsBaseDir) === 0) {
        $relativePath = substr($filePath, strlen($uploadsBaseDir) + 1);
    } else {
        fwrite(STDERR, "File not in uploads directory\n");
        return null;
    }

    $fileUrl = $uploadsBaseUrl . '/' . $relativePath;
    $guid = $fileUrl;

    // Check if already exists
    $stmt = $mysqli->prepare("SELECT ID FROM wp_posts WHERE post_type = 'attachment' AND guid = ?");
    $stmt->bind_param('s', $guid);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        fwrite(STDERR, "Already in media library: ID {$row['ID']}\n");
        return (int)$row['ID'];
    }

    // Get file info
    $mimeType = mime_content_type($filePath);
    $title = pathinfo($filename, PATHINFO_FILENAME);
    $title = str_replace(['-', '_'], ' ', $title);
    // Remove "Aideauxtd" and everything after
    $title = preg_replace('/\s*Aideauxtd.*$/i', '', $title);
    $title = ucfirst(trim($title));
    $caption = $title; // Use same as caption/legend

    // Insert attachment post
    $sql = "INSERT INTO wp_posts (
        post_author, post_date, post_date_gmt, post_content, post_title,
        post_excerpt, post_status, comment_status, ping_status, post_name,
        post_type, post_mime_type, post_parent, guid,
        post_modified, post_modified_gmt, to_ping, pinged, post_content_filtered
    ) VALUES (
        1, NOW(), NOW(), '', ?,
        ?, 'inherit', 'open', 'closed', ?,
        'attachment', ?, ?, ?,
        NOW(), NOW(), '', '', ''
    )";

    $stmt = $mysqli->prepare($sql);
    $postName = sanitize_title($title);
    $stmt->bind_param('ssssss', $title, $caption, $postName, $mimeType, $parentPostId, $guid);

    if (!$stmt->execute()) {
        fwrite(STDERR, "Error inserting attachment: " . $mysqli->error . "\n");
        return null;
    }

    $attachmentId = $mysqli->insert_id;

    // Add _wp_attached_file meta
    $stmt = $mysqli->prepare("INSERT INTO wp_postmeta (post_id, meta_key, meta_value) VALUES (?, '_wp_attached_file', ?)");
    $stmt->bind_param('is', $attachmentId, $relativePath);
    $stmt->execute();

    // Generate basic metadata
    $imageSize = @getimagesize($filePath);
    if ($imageSize) {
        $metadata = [
            'width' => $imageSize[0],
            'height' => $imageSize[1],
            'file' => $relativePath,
            'filesize' => filesize($filePath),
            'sizes' => []
        ];
        $metaSerialized = serialize($metadata);

        $stmt = $mysqli->prepare("INSERT INTO wp_postmeta (post_id, meta_key, meta_value) VALUES (?, '_wp_attachment_metadata', ?)");
        $stmt->bind_param('is', $attachmentId, $metaSerialized);
        $stmt->execute();
    }

    fwrite(STDERR, "Imported: $filename (ID: $attachmentId)\n");
    return $attachmentId;
}

function sanitize_title(string $title): string
{
    $title = strtolower($title);
    $title = preg_replace('/[^a-z0-9\s-]/', '', $title);
    $title = preg_replace('/[\s]+/', '-', $title);
    return trim($title, '-');
}

function set_featured_image(int $postId, int $attachmentId): bool
{
    $mysqli = get_db_connection();

    // Delete existing featured image
    $stmt = $mysqli->prepare("DELETE FROM wp_postmeta WHERE post_id = ? AND meta_key = '_thumbnail_id'");
    $stmt->bind_param('i', $postId);
    $stmt->execute();

    // Set new featured image
    $stmt = $mysqli->prepare("INSERT INTO wp_postmeta (post_id, meta_key, meta_value) VALUES (?, '_thumbnail_id', ?)");
    $stmt->bind_param('is', $postId, $attachmentId);

    if ($stmt->execute()) {
        fwrite(STDERR, "Set featured image for post $postId\n");
        return true;
    }
    return false;
}

// CLI usage - only when run directly (not when included)
if (php_sapi_name() === 'cli' && isset($argv[1]) && realpath($argv[0]) === realpath(__FILE__)) {
    $imagePath = $argv[1];
    $postId = 0;
    $setFeatured = false;

    for ($i = 2; $i < count($argv); $i++) {
        if ($argv[$i] === '--featured' && isset($argv[$i + 1])) {
            $postId = (int)$argv[$i + 1];
            $setFeatured = true;
            $i++;
        } elseif ($argv[$i] === '--post' && isset($argv[$i + 1])) {
            $postId = (int)$argv[$i + 1];
            $i++;
        }
    }

    $attachmentId = import_image_to_media_library($imagePath, $postId);

    if ($attachmentId && $setFeatured && $postId) {
        set_featured_image($postId, $attachmentId);
    }

    exit($attachmentId ? 0 : 1);
}

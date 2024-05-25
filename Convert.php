<?php

// Function to sanitize and normalize a file path
function normalizeFilePath($path)
{
    // Remove trailing and leading whitespace and quotes
    $path = trim($path, " \t\n\r\0\x0B\"'");

    // Replace backslashes followed by a space with just a space
    $path = preg_replace('/\\\\ +/', ' ', $path);

    // Normalize directory separators to /
    $path = str_replace('\\', '/', $path);

    // Resolve relative paths
    $path = realpath($path);

    return $path;
}

function read_top_x_lines($filePath, $numLines) {
    // Read the top lines of the file
    $fileContent = file($filePath, FILE_IGNORE_NEW_LINES);
    return array_slice($fileContent, 0, $numLines);
}

function check_text_for_tags($lines) {
    // Check for tags in the top lines
    foreach ($lines as $line) {
        // Trim leading and trailing whitespace
        $trimmedLine = trim($line);
        // Check if the line starts with a hash followed by a non-space character
        if (preg_match('/^#\S/', $trimmedLine)) {
            return true;
        }
    }
    return false;
}

function text_to_array($lines) {
    // Concatenate lines into a single string
    $text = implode(' ', $lines);

    // Extract tags from the text
    preg_match_all('/#(\S+)/', $text, $matches);
    $tags = $matches[1];

    // Initialize an array to store the tags
    $formattedTags = [];

    // Iterate through each tag
    foreach ($tags as $tag) {
        // Preserve the entire tag
        if (!empty($tag)) {
            $formattedTags[] = $tag;
        }
    }

    // Remove duplicates and empty elements
    $formattedTags = array_unique($formattedTags);
    $formattedTags = array_filter($formattedTags);

    // Add '#' back to each tag
    $formattedTags = array_map(function($tag) { return '#' . $tag; }, $formattedTags);

    return $formattedTags;
}

function array_to_frontmatter($tags) {
    // Remove '#' from each tag
    $tags = array_map(function($tag) { return ltrim($tag, '#'); }, $tags);

    // Remove any empty tags
    $tags = array_filter($tags);

    // If no tags, return an empty string
    if (empty($tags)) {
        return '';
    }

    // Convert array of tags to frontmatter format
    $frontmatter = "tags:\n";
    foreach ($tags as $tag) {
        if (!empty($tag)) {
            $frontmatter .= "  - $tag\n";
        }
    }
    return $frontmatter;
}

function remove_old_tag_format($filePath, $lines) {
    // Read the entire content of the file
    $fileContent = file_get_contents($filePath);

    // If tags are found in the top lines, remove them from the content
    if (check_text_for_tags($lines)) {
        // Split the content into lines
        $contentLines = explode("\n", $fileContent);

        // Remove lines containing tags from the first three lines
        foreach ($lines as $index => $line) {
            if ($index < 3 && preg_match('/^#\S/', trim($line)) && !preg_match('/^#{1,6} +\S/', trim($line))) {
                unset($contentLines[$index]);
            }
        }

        // Rebuild the content without the removed lines
        $fileContent = implode("\n", $contentLines);
    }

    // Write the modified content back to the file
    file_put_contents($filePath, $fileContent);
}

function write_tags_as_frontmatter($filePath, $tagsArray) {
    // If no tags, do not add frontmatter
    if (empty($tagsArray)) {
        return;
    }

    // Read the entire content of the file
    $fileContent = file_get_contents($filePath);

    // Prepare the frontmatter tags
    $frontmatterTags = array_to_frontmatter($tagsArray);

    // If frontmatterTags is empty, do not add frontmatter
    if (empty($frontmatterTags)) {
        return;
    }

    // Construct the frontmatter string
    $frontmatter = "---\n$frontmatterTags---";

    // Replace the existing frontmatter tags or add new ones
    if (preg_match('/---\s*tags:\s*(.*?)\s*---/s', $fileContent, $matches)) {
        // Replace existing frontmatter tags
        $fileContent = str_replace($matches[0], $frontmatter, $fileContent);
    } else {
        // Add new frontmatter tags
        $fileContent = $frontmatter . "\n" . $fileContent;
    }

    // Write the modified content back to the file
    file_put_contents($filePath, $fileContent);
}

function get_relative_path($filePath, $baseDir) {
    // Normalize paths
    $filePath = realpath($filePath);
    $baseDir = realpath($baseDir);

    // Check if both paths are valid
    if ($filePath === false || $baseDir === false) {
        return false;
    }

    // Check if $baseDir is a prefix of $filePath
    if (strpos($filePath, $baseDir) === 0) {
        // Remove $baseDir from $filePath and return the relative path
        return ltrim(substr($filePath, strlen($baseDir)), DIRECTORY_SEPARATOR);
    } else {
        // If $baseDir is not a prefix of $filePath, return the absolute path
        return $filePath;
    }
}

function process_files($fileDir) {
    // Normalize directory path
    $fileDir = normalizeFilePath($fileDir);

    // Get list of files in the directory
    $files = scandir($fileDir);

    // Iterate over files
    foreach ($files as $file) {
        // Skip '.' and '..' directories and hidden directories
        if ($file == '.' || $file == '..' || $file[0] == '.') {
            continue;
        }

        // Construct full path to the file and normalize it
        $filePath = normalizeFilePath($fileDir . DIRECTORY_SEPARATOR . $file);

        // Process files or recursively process directories
        if (is_file($filePath) && pathinfo($filePath, PATHINFO_EXTENSION) === 'md') {
            // Process individual file
            process_file($filePath, $fileDir);
        } elseif (is_dir($filePath)) {
            // Recursively process subdirectory
            process_files($filePath);
        }
    }
}

function process_file($filePath, $fileDir) {
    // Read the top lines of the file
    $topLines = read_top_x_lines($filePath, 3);

    if (check_text_for_tags($topLines)) {
        $tagsArray = text_to_array($topLines);

        // Remove old tag format from the original file
        remove_old_tag_format($filePath, $topLines);

        // Replace tags with frontmatter in the original file
        write_tags_as_frontmatter($filePath, $tagsArray);

        // Echo the relative path
        echo "Converted ✅: " . get_relative_path($filePath, $fileDir) . "\n";
    } else {
        echo "Not needed 🚫: " . get_relative_path($filePath, $fileDir) . "\n";
    }
}

// Define the file directory
$fileDir = '/Users/Reess/Library/Mobile Documents/iCloud~md~obsidian/Documents/ReessDBNew/pages-shared/z10OtherParts';
process_files($fileDir);

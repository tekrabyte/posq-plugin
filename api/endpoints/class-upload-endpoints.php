<?php
/**
 * Upload Endpoints
 * Handles image upload
 */

if (!defined('ABSPATH')) exit;

class POSQ_Upload_Endpoints {

    public static function upload_image($request) {
        // Check if file is uploaded
        $files = $request->get_file_params();
        if (empty($files['image'])) {
            return new WP_Error('no_file', 'No image file uploaded', ['status' => 400]);
        }

        $file = $files['image'];
        
        // Validate file type
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowed_types)) {
            return new WP_Error('invalid_type', 'Invalid file type. Only images allowed', ['status' => 400]);
        }

        // Validate file size (5MB max)
        if ($file['size'] > 5 * 1024 * 1024) {
            return new WP_Error('file_too_large', 'File too large. Maximum 5MB allowed', ['status' => 400]);
        }

        // Handle upload
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        $upload_overrides = ['test_form' => false];
        $uploaded_file = wp_handle_upload($file, $upload_overrides);

        if (isset($uploaded_file['error'])) {
            return new WP_Error('upload_error', $uploaded_file['error'], ['status' => 500]);
        }

        // Crop image to 1:1 square ratio
        $cropped = posq_crop_image_to_square($uploaded_file['file']);
        
        if (is_wp_error($cropped)) {
            // If cropping fails, still return the original image
            return [
                'success' => true,
                'url' => $uploaded_file['url']
            ];
        }

        // Get URL for cropped image
        $upload_dir = wp_upload_dir();
        $relative_path = str_replace($upload_dir['basedir'], '', $cropped);
        $cropped_url = $upload_dir['baseurl'] . $relative_path;

        return [
            'success' => true,
            'url' => $cropped_url
        ];
    }
}

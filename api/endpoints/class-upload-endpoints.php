<?php
/**
 * Upload Endpoints
 */

if (!defined('ABSPATH')) exit;

class POSQ_Upload_Endpoints {

    /**
     * Upload image to WordPress Media Library
     */
    public static function upload_image($request) {
        $files = $request->get_file_params();
        
        if (empty($files['image'])) {
            return new WP_Error('no_image', 'No image file provided', ['status' => 400]);
        }

        $file = $files['image'];
        
        // Validate file type
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowed_types)) {
            return new WP_Error('invalid_type', 'Invalid file type. Only JPG, PNG, GIF, and WEBP allowed', ['status' => 400]);
        }

        // Validate file size (max 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            return new WP_Error('file_too_large', 'File size exceeds 5MB limit', ['status' => 400]);
        }

        // WordPress media upload
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        $upload = wp_handle_upload($file, ['test_form' => false]);
        
        if (isset($upload['error'])) {
            return new WP_Error('upload_failed', $upload['error'], ['status' => 500]);
        }

        // Crop image to 1:1 (square) ratio
        $cropped_file = posq_crop_image_to_square($upload['file']);
        
        if (is_wp_error($cropped_file)) {
            // If crop fails, continue with original image
            $final_file = $upload['file'];
            $final_url = $upload['url'];
        } else {
            $final_file = $cropped_file;
            // Get the new URL for cropped image
            $upload_dir = wp_upload_dir();
            $final_url = str_replace($upload_dir['path'], $upload_dir['url'], $cropped_file);
        }

        // Create attachment
        $attachment = [
            'post_mime_type' => $upload['type'],
            'post_title' => sanitize_file_name($file['name']),
            'post_content' => '',
            'post_status' => 'inherit'
        ];

        $attachment_id = wp_insert_attachment($attachment, $final_file);
        
        if (is_wp_error($attachment_id)) {
            return new WP_Error('attachment_failed', 'Failed to create attachment', ['status' => 500]);
        }

        // Generate metadata
        $attachment_data = wp_generate_attachment_metadata($attachment_id, $final_file);
        wp_update_attachment_metadata($attachment_id, $attachment_data);

        return [
            'success' => true,
            'url' => $final_url,
            'attachment_id' => $attachment_id
        ];
    }
}

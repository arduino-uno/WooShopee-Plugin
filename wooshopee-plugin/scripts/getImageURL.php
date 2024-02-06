<?php

$url = "https://cf.shopee.co.id/file/id-11134207-7r98w-lp6v9ohoshfxde"; // Replace with the URL of the file you want to download
$get_image = get_image_url($url);
echo $get_image;
echo "\n\n";

function get_image_url($image_url) {

    $arr_image = explode('/', $image_url);
    $arr_size = sizeof($arr_image);
    $file_name =  $arr_image[ ($arr_size - 1) ];
    $destination = "./images/{$file_name}"; // Replace with the desired destination file path on your server

    set_error_handler(
        function ($severity, $message, $file, $line) {
            throw new ErrorException($message, $severity, $severity, $file, $line);
        }
    );

    try {

          $fileContents = file_get_contents( $image_url); // Get the contents of the file from the URL
          $fileSaving = file_put_contents( $destination, $fileContents ); // Save the file contents to the destination paths

    } catch ( Exception $e ) {

          return "Error: " . $e->getMessage();

    } finally {
          // Restore the previous error handler after changing it with the set_error_handler() function
          restore_error_handler();
          // Output a success message
          return 'File downloaded successfully!';
    };

};

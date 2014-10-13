<?php 

return array(
    //'library'     => 'imagick',
    'upload_dir'  => 'uploads',
    'upload_path' => public_path() . '/uploads/',
    'quality'     => 85,
 	's3'         => false,
    'dimensions' => array(
        'thumb'  => array(
        	"width" => 100, 
        	"height" => 100, 
        	"crop" => true,  
        	"quality" => 80
        ),
        'medium' => array(
        	"width" => 600, 
        	"height" => 400, 
        	"crop" => false, 
        	"quality" => 90
        ),
    ),
    'tables' => array(
        'images' => 'tbl_images',
        'image_sizes' => 'tbl_image_sizes'
    )
);
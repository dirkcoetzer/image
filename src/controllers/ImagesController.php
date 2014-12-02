<?php namespace Dirkcoetzer\Image;

use Image, 
	Input, 
	Response,
	Exception;

class ImagesController extends \BaseController{
	
	public function post_upload(){
		// Validate available types
		$file = Input::file('file');
		if (!in_array($file->getClientOriginalExtension(), array('jpg', 'png', 'gif', 'tiff')))
			throw new Exception('Invalid file extension. Valid extensions are jpg, png, gif, tiff', 500);

		$result = Image::upload($file, 'avatars', true);

		return Response::json(array(
			"status" => 200, 
			"results" => array($result)
		), 200);
	}

	public function store(){
		\Log::debug(__METHOD__);
		
		$input = Input::json()->all();
		\Log::debug(var_export($input, true));

		$imgData = array('id' => $input['id']);
		\DB::table(Image::getImagesTableName())->insert($imgData);

		$dimensions = \Config::get('image::image.dimensions');
		foreach ($dimensions as $size => $dimension)	            		
	   	{
	   		\Log::debug($size);

	   		if (isset($input[$size])){
	   			$imgSizesData = array(
					'image_id' => $input['id'],
					'url' => $input[$size],
					'size' => $size
	        	);

	    		\DB::table(Image::getImageSizesTableName())->insert($imgSizesData);	
	   		}
	   	}

	   	if (isset($input['original'])){
   			$imgSizesData = array(
				'image_id' => $input['id'],
				'url' => $input['original'],
				'size' => 'original'
        	);

    		\DB::table(Image::getImageSizesTableName())->insert($imgSizesData);	
   		}

		return Response::json(array(
			"status" => 200, 
			"results" => $input
		), 200);

	}
}
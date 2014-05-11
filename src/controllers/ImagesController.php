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
	
}
<?php 

class ImagesController extends BaseController{
	
	public function post_upload(){
		$result = Image::upload(Input::file('file'), 'avatars', true);

		return Response::json(array(
			"status" => 200, 
			"results" => array($result)
		), 200);
	}
	
}
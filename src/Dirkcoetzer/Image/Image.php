<?php namespace Dirkcoetzer\Image;

use Config, Log, File, DB;

class Image {
	
	/**
     * Instance of the Imagine package
     * @var Imagine\Gd\Imagine
     */
    protected $imagine;
 
    /**
     * Type of library used by the service
     * @var string
     */
    protected $library;
 
    /**
     * Initialize the image service
     * @return void
     */
    public function __construct()
    {
        if ( ! $this->imagine)
        {
            $this->library = Config::get('image::image.library', 'gd');
 
            // Now create the instance
            if     ($this->library == 'imagick') $this->imagine = new \Imagine\Imagick\Imagine();
            elseif ($this->library == 'gmagick') $this->imagine = new \Imagine\Gmagick\Imagine();
            elseif ($this->library == 'gd')      $this->imagine = new \Imagine\Gd\Imagine();
            else                                 $this->imagine = new \Imagine\Gd\Imagine();
        }
    }

    /**
	 * Resize an image
	 * @param  string  $url
	 * @param  integer $width
	 * @param  integer $height
	 * @param  boolean $crop
	 * @return string
	 */
	public function resize($url, $width = 100, $height = null, $crop = false, $quality = 90, $subDirectory)
	{
	    if ($url)
	    {
	        // URL info
	        $info = pathinfo($url);		        

	 		// The size
	        if ( ! $height) $height = $width;
	 
	        // Quality
	        $quality = Config::get('image::image.quality', $quality);
	 
	        // Directories and file names
	        $fileName       = $info['basename'];
	        $sourceDirPath  = public_path() . '/' . $info['dirname'];
	        $sourceFilePath = $sourceDirPath . '/' . $fileName;
	        $targetDirName  = $subDirectory;
	        $targetDirPath  = $sourceDirPath . '/' . $targetDirName . '/';
	        $targetFilePath = $targetDirPath . $fileName;
	        $targetUrl      = $info['dirname'] . '/' . $targetDirName . '/' . $fileName;

	        // Create directory if missing
	        try
	        {
	            // Create dir if missing
	            if ( ! File::isDirectory($targetDirPath) and $targetDirPath) @File::makeDirectory($targetDirPath);
	 
	            // Set the size
	            $size = new \Imagine\Image\Box($width, $height);
	 
	            // Now the mode
	            $mode = $crop ? \Imagine\Image\ImageInterface::THUMBNAIL_OUTBOUND : \Imagine\Image\ImageInterface::THUMBNAIL_INSET;
	 
	            if ( ! File::exists($targetFilePath) or (File::lastModified($targetFilePath) < File::lastModified($sourceFilePath)))
	            {
	                $this->imagine->open($sourceFilePath)
	                              ->thumbnail($size, $mode)
	                              ->save($targetFilePath, array('quality' => $quality));	                
	            }
	        }
	        catch (\Exception $e)
	        {
	            Log::error('[IMAGE SERVICE] Failed to resize image "' . $url . '" [' . $e->getMessage() . ']');
	        }
 
	        return $targetUrl;
	    }
	}

	/**
	* Helper for creating thumbs
	* @param string $url
	* @param integer $width
	* @param integer $height
	* @return string
	*/
	public function thumb($url, $width, $height = null)
	{
	    return $this->resize($url, $width, $height, true);
	}

	/**
	 * Upload an image to the public storage
	 * @param  File $file
	 * @return string
	 */
	public function upload($file, $dir = null, $createDimensions = false, $customDimensions = array())
	{
		Log::debug(__METHOD__);

		if ($file)
	    {
	        // Generate random dir
	        if ( ! $dir) $dir = str_random(8);

	        // Get file info and try to move	       
	        $destination = Config::get('image::image.upload_path') . $dir;
	        $filename    = time() . "_" . $file->getClientOriginalName();
	        $url        = Config::get('image::image.upload_dir') . '/' . $dir . '/' . $filename;	        

	        $uploaded = $file->move($destination, $filename);
	 		if ($uploaded)
	        {	        	
	        	// save the main image
	        	DB::table($this->getImagesTableName())->insert(array(
	        		"id" => $filename
	        	));
	        	
	            if ($createDimensions) {
	            	// Get default dimensions
				    $dimensions = Config::get('image::image.dimensions');

				    if (is_array($dimensions)) $dimensions = array_merge($dimensions, $customDimensions);

	            	foreach ($dimensions as $size => $dimension)	            		
	    			{
	    				$resizedUrl = $this->createDimensions($url, $size, $dimension);		            	
		            	
		            	if (Config::get('image::image.s3'))
		 					$resizedUrl = $this->push($resizedUrl, $dir . "/" . $size, $filename);

		 				// Save the image to the database
		 				DB::table($this->getImageSizesTableName())->insert(array(
		 					'image_id' => $image->id,
		 					'url' => $resizedUrl,
		 					'size' => $size
			        	));

		            	$this->uploads[$size] = $resizedUrl;
		            }
	 			}

	 			if (Config::get('image::image.s3'))
	 				$url = $this->push($url, $dir, $filename);

	 			// Save the image to the database
	 			DB::table($this->getImageSizesTableName())->insert(array(
 					'image_id' => $image->id,
 					'url' => $url,
 					'size' => 'original'
	        	));

 				$this->uploads["id"] = $image->id;
	 			$this->uploads["original"] = $url; 

	            return $this->uploads;
	        }

	 		return false;
	    }
	}

	/**
	* Push 
	* Pushes a file to S3
	*
	* @param string path
	* @param string filename
	*
	* @return url
	*/
	public function push($path, $dir, $filename){
		$s3 = \AWS::get('s3');
		$response = $s3->putObject(array(
		    'Bucket'     => Config::get('image::image.upload_dir'),
		    'Key'        => $dir . "/" . $filename,
		    'SourceFile' => $path,
		    'ACL'    	 => 'public-read',
		));

		return $response['ObjectURL'];
	}

	/**
	 * Creates image dimensions based on a configuration
	 * @param  string $url
	 * @param  array  $dimensions
	 * @return void
	 */
	public function createDimensions($url, $size, $dimension)
	{		
	    // Get dimmensions and quality
        $width   = (int) $dimension["width"];
        $height  = isset($dimension["height"]) ?  (int) $dimension["height"] : $width;
        $crop    = isset($dimension["crop"]) ? (bool) $dimension["crop"] : false;
        $quality = isset($dimension["quality"]) ?  (int) $dimension["quality"] : Config::get('image::image.quality');
 
        // Run resizer
        return $resizedUrl = $this->resize($url, $width, $height, $crop, $quality, $size);
	}

	public function getImagesTableName(){
		\Log::debug(__METHOD__);

		return Config::get('image::image.tables.images', 'tbl_images');
	}

	public function getImageSizesTableName(){
		return Config::get('image::image.tables.image_sizes', 'tbl_image_sizes');
	}
}
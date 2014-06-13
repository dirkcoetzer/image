<?php namespace Dirkcoetzer\Image\Models;

use Config;

class Image extends \Eloquent{

	/**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;
    
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = "images";

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = array(
		"id", "name", "body"
	);

	/**
	* Thumb Image Relation
	*
	*/
	public function thumb(){
		return $this->hasOne('Dirkcoetzer\Image\Models\ImageSize', 'image_id', 'id')->where('size', 'thumb');
	}

	/**
	* Medium Image Relation
	*
	*/
	public function medium(){
		return $this->hasOne('Dirkcoetzer\Image\Models\ImageSize', 'image_id', 'id')->where('size', 'medium');
	}

	/**
	* Original Image Relation
	*
	*/
	public function original(){
		return $this->hasOne('Dirkcoetzer\Image\Models\ImageSize', 'image_id', 'id')->where('size', 'original');
	}

}
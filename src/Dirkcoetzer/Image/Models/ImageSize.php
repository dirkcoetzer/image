<?php namespace Dirkcoetzer\Image\Models;

class ImageSize extends \Eloquent{

	/**
	 * The primary key for the model.
	 *
	 * @var string
	 */
	protected $primaryKey = 'id';

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'tbl_image_sizes';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = array(
		"image_id", "url", "size"
	);
}
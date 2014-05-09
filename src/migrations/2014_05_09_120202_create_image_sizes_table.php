<?php

use Illuminate\Database\Migrations\Migration;

class CreateImageSizesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('image_sizes', function($table)
		{
			$table->increments('id');
			$table->string('image_id', 100);		    
		    $table->string('url', 250);
		    $table->string('size', 50);
		    $table->timestamps();

		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('image_sizes');
	}

}
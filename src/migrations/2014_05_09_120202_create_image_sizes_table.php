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
		Schema::create('images', function($table)
		{
			$table->string('id', 100);
			$table->primary('id');
			$table->string('name', 100)->nullable();		    
		    $table->string('body', 250)->nullable();		    
		    $table->timestamps();

		});

		Schema::create('image_sizes', function($table)
		{
			$table->increments('id');
			$table->string('image_id', 100);			
			$table->foreign('image_id')->references('id')->on('images')->onDelete('cascade');
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
		Schema::dropIfExists('images');
	}

}
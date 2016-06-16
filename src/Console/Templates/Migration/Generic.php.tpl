<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class {{DOMAIN}}s extends Migration
{
	public function up()
	{
		Schema::create('{{TABLE}}', function (Blueprint $table) {
			$table->increments('id');
			$table->string('name');

			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::dropIfExists('{{TABLE}}');
	}
}

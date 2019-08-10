<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDriversTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('drivers', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('image')->nullable();
            $table->string('frontId')->nullable();
            $table->string('backId')->nullable();
            $table->string('telephone');
            $table->boolean('busy')->default(0);

            $table->unsignedBigInteger('identity')->unique()->nullable();

            $table->string('lat')->nullable();
            $table->string('lng')->nullable();
            $table->string('CurrentBalance')->default('0');
            $table->enum('canReceiveOrder',['0', '1'])->default(1);
            $table->enum('availability',['off','on'])->default('off');
            $table->longtext('deviceToken')->nullable();

            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

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
        Schema::dropIfExists('drivers');
    }
}

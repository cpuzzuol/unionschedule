<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vacation_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('requested_by')->nullable();
            $table->date('date_requested');
            $table->string('decision')->nullable(true);
            $table->dateTime('decision_date')->nullable(true);
            $table->unsignedBigInteger('decision_by')->nullable();
            $table->foreign('requested_by')->references('id')->on('users')->onDelete('CASCADE');
            $table->foreign('decision_by')->references('id')->on('users')->onDelete('SET NULL');
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
        Schema::dropIfExists('vacation_requests');
    }
}

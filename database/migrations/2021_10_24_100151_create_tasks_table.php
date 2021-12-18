<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('list_id')->constrained()->onDelete('CASCADE');
            $table->unsignedBigInteger('executor_user_id');
            $table->foreign('executor_user_id')->references('id')->on('users')->onDelete('CASCADE');
            $table->boolean('is_completed')->nullable();
            $table->text('description')->nullable();
            $table->integer('urgency');
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
        Schema::dropIfExists('tasks');
    }
}

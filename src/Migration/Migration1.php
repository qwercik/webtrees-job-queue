<?php

declare(strict_types=1);

namespace Komputeryk\Webtrees\JobQueue\Migration;

use Fisharebest\Webtrees\DB;
use Fisharebest\Webtrees\Schema\MigrationInterface;
use Illuminate\Database\Schema\Blueprint;

class Migration1 implements MigrationInterface
{
    public function upgrade(): void
    {
        if (!DB::schema()->hasTable('job_queue')) {
            DB::schema()->create('job_queue', static function(Blueprint $table): void {
                $table->unsignedInteger('id', true)->autoIncrement();
                $table->timestamps();
                $table->string('job', 50);
                $table->string('status', 20)->default('new');
                $table->text('data')->nullable();
                $table->text('message')->nullable();
            });
        }
    }
}

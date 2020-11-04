<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Carbon\Carbon;
use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Schema\Blueprint;
use stdClass;

/**
 * This migration creates the news table and copies the existing News table records to the new one.
 */
class CreateNewsTable extends Migration
{
    use ChangesReferences;
    use Reference;

    /**
     * Creates the news table, copies the data and drops the News table.
     */
    public function up(): void
    {
        $hasPreviousNewsTable = $this->schema->hasTable('News');

        if ($hasPreviousNewsTable) {
            // Rename because some SQL DBMS handle identifiers case insensitive
            $this->schema->rename('News', 'PreviousNews');
        }

        $this->createNewNewsTable();

        if ($hasPreviousNewsTable) {
            $this->copyPreviousToNewNewsTable();
            $this->changeReferences(
                'PreviousNews',
                'ID',
                'news',
                'id'
            );
            $this->schema->drop('PreviousNews');
        }
    }

    /**
     * Recreates the previous News table, copies back the data and drops the new news table.
     */
    public function down(): void
    {
        // Rename as some SQL DBMS handle identifiers case insensitive
        $this->schema->rename('news', 'new_news');

        $this->createPreviousNewsTable();
        $this->copyNewToPreviousNewsTable();
        $this->changeReferences(
            'new_news',
            'id',
            'News',
            'ID'
        );

        $this->schema->drop('new_news');
    }

    /**
     * @return void
     */
    private function createNewNewsTable(): void
    {
        $this->schema->create('news', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title', 150);
            $table->text('text');
            $table->boolean('is_meeting')->default(0);
            $this->referencesUser($table);
            $table->timestamps();
        });
    }

    /**
     * @return void
     */
    private function copyPreviousToNewNewsTable(): void
    {
        $connection = $this->schema->getConnection();
        /** @var stdClass[] $previousNewsRecords */
        $previousNewsRecords = $connection
            ->table('PreviousNews')
            ->get();

        foreach ($previousNewsRecords as $previousNews) {
            $date = Carbon::createFromTimestamp($previousNews->Datum);
            $connection->table('news')->insert([
                'id'         => $previousNews->ID,
                'title'      => $previousNews->Betreff,
                'text'       => $previousNews->Text,
                'is_meeting' => $previousNews->Treffen,
                'user_id'    => $previousNews->UID,
                'created_at' => $date,
                'updated_at' => $date,
            ]);
        }
    }

    /**
     * @return void
     */
    private function createPreviousNewsTable(): void
    {
        $this->schema->create('News', function (Blueprint $table) {
            $table->increments('ID');
            $table->integer('Datum');
            $table->string('Betreff', 150)
                ->default('');
            $table->text('Text');
            $this->references($table, 'users', 'UID');
            $table->boolean('Treffen')->default(false);
        });
    }

    /**
     * @return void
     */
    private function copyNewToPreviousNewsTable(): void
    {
        $connection = $this->schema->getConnection();
        /** @var Collection|stdClass[] $newsRecords */
        $newsRecords = $connection
            ->table('new_news')
            ->get();

        foreach ($newsRecords as $newsRecord) {
            $date = Carbon::createFromFormat('Y-m-d H:i:s', $newsRecord->created_at)
                ->getTimestamp();

            $connection->table('News')->insert([
                'ID'      => $newsRecord->id,
                'Datum'   => $date,
                'Betreff' => $newsRecord->title,
                'Text'    => $newsRecord->text,
                'UID'     => $newsRecord->user_id,
                'Treffen' => $newsRecord->is_meeting,
            ]);
        }
    }
}

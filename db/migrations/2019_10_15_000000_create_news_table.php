<?php
declare(strict_types=1);

namespace Engelsystem\Migrations;

use Carbon\Carbon;
use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;
use stdClass;

/**
 * This migration creates the news table and copies the existing News table records to the new one.
 */
class CreateNewsTable extends Migration
{
    use ChangesReferences, Reference;

    /**
     * Creates the news table, copies the data and drops the News table.
     */
    public function up(): void
    {
        $hasPreviousNewsTable = $this->schema->hasTable('News');

        if ($hasPreviousNewsTable) {
            // rename because some SQL DBMS handle identifiers case insensitive
            $this->schema->rename('News', 'PreviousNews');
        }

        $this->createNewNewsTable();

        if ($hasPreviousNewsTable) {
            $this->copyPreviousToNewNewsTable();
            $this->changeReferences(
                'PreviousNews',
                'ID',
                'news',
                'id',
                'unsignedInteger'
            );
            $this->schema->drop('PreviousNews');
        }
    }

    /**
     * Recreates the previous News table, copies back the data and drops the new news table.
     */
    public function down(): void
    {
        // rename because some SQL DBMS handle identifiers case insensitive
        $this->schema->rename('news', 'new_news');

        $this->createPreviousNewsTable();
        $this->copyNewToPreviousNewsTable();
        $this->changeReferences(
            'new_news',
            'id',
            'News',
            'ID',
            'unsignedInteger'
        );
        $this->schema->drop('new_news');
    }

    private function createNewNewsTable(): void
    {
        $this->schema->create('news', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title', 150);
            $table->text('text');
            $table->boolean('is_meeting')->default(0);
            $this->referencesUser($table, false);
            $table->timestamps();
        });
    }

    private function copyPreviousToNewNewsTable(): void
    {
        /** @var stdClass[] $previousNewsRecords */
        $previousNewsRecords = $this->schema
            ->getConnection()
            ->table('PreviousNews')
            ->get();

        foreach ($previousNewsRecords as $previousNews) {
            $date = Carbon::createFromTimestamp($previousNews->Datum);
            $this->schema->getConnection()->table('news')->insert([
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

    private function createPreviousNewsTable(): void
    {
        $this->schema->create('News', function (Blueprint $table) {
            $table->increments('ID');
            $table->integer('Datum');
            $table->string('Betreff', 150)
                ->default('');
            $table->text('Text');
            $table->boolean('Treffen');
            $table->unsignedInteger('UID');
            $table->foreign('UID')
                ->references('id')
                ->on('users');
        });
    }

    private function copyNewToPreviousNewsTable(): void
    {
        /** @var stdClass[] $newsRecords */
        $newsRecords = $this->schema
            ->getConnection()
            ->table('new_news')
            ->get();

        foreach ($newsRecords as $newsRecord) {
            $date = Carbon::createFromFormat('Y-m-d H:i:s', $newsRecord->created_at)
                ->getTimestamp();

            $this->schema->getConnection()->table('News')->insert([
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

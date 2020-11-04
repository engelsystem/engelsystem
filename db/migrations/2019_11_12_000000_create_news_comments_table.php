<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Schema\Blueprint;
use stdClass;

/**
 * This migration creates the news_comments table and copies the existing NewsComments table records to the new one.
 */
class CreateNewsCommentsTable extends Migration
{
    use ChangesReferences;
    use Reference;

    /**
     * Creates the news_comments table, copies the data and drops the NewsComments table.
     */
    public function up(): void
    {
        $this->createNewNewsCommentsTable();

        if ($this->schema->hasTable('NewsComments')) {
            $this->copyPreviousToNewNewsCommentsTable();
            $this->changeReferences(
                'NewsComments',
                'ID',
                'news_comments',
                'id'
            );
            $this->schema->drop('NewsComments');
        }
    }

    /**
     * Recreates the previous NewsComments table, copies back the data and drops the new news_comments table.
     */
    public function down(): void
    {
        $this->createPreviousNewsCommentsTable();
        $this->copyNewToPreviousNewsCommentsTable();
        $this->changeReferences(
            'news_comments',
            'id',
            'NewsComments',
            'ID'
        );

        $this->schema->drop('news_comments');
    }

    /**
     * @return void
     */
    private function createNewNewsCommentsTable(): void
    {
        $this->schema->create('news_comments', function (Blueprint $table) {
            $table->increments('id');
            $this->references($table, 'news', 'news_id');
            $table->text('text');
            $this->referencesUser($table);
            $table->timestamps();
        });
    }

    /**
     * @return void
     */
    private function copyPreviousToNewNewsCommentsTable(): void
    {
        $connection = $this->schema->getConnection();
        /** @var stdClass[] $previousNewsCommentsRecords */
        $previousNewsCommentsRecords = $connection
            ->table('NewsComments')
            ->get();

        foreach ($previousNewsCommentsRecords as $previousNewsComment) {
            $connection->table('news_comments')->insert([
                'id'         => $previousNewsComment->ID,
                'news_id'    => $previousNewsComment->Refid,
                'text'       => $previousNewsComment->Text,
                'user_id'    => $previousNewsComment->UID,
                'created_at' => $previousNewsComment->Datum,
                'updated_at' => $previousNewsComment->Datum,
            ]);
        }
    }

    /**
     * @return void
     */
    private function createPreviousNewsCommentsTable(): void
    {
        $this->schema->create('NewsComments', function (Blueprint $table) {
            $table->increments('ID');
            $this->references($table, 'news', 'Refid');
            $table->dateTime('Datum');
            $table->text('Text');
            $this->references($table, 'users', 'UID');
        });
    }

    /**
     * @return void
     */
    private function copyNewToPreviousNewsCommentsTable(): void
    {
        $connection = $this->schema->getConnection();
        /** @var Collection|stdClass[] $newsCommentsRecords */
        $newsCommentsRecords = $connection
            ->table('news_comments')
            ->get();

        foreach ($newsCommentsRecords as $newsCommentRecord) {
            $connection->table('NewsComments')->insert([
                'ID'    => $newsCommentRecord->id,
                'Datum' => $newsCommentRecord->created_at,
                'Refid' => $newsCommentRecord->news_id,
                'Text'  => $newsCommentRecord->text,
                'UID'   => $newsCommentRecord->user_id,
            ]);
        }
    }
}

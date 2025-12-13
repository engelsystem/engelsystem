<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Builder as SchemaBuilder;

class AddGoodieScoreCalculationFunction extends Migration
{
    protected Connection $db;

    public function __construct(SchemaBuilder $schema)
    {
        parent::__construct($schema);
        $this->db = $this->schema->getConnection();
    }

    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->db->statement(
        /** @lang MySQL */
            'CREATE DEFINER=`engelsystem`@`%` FUNCTION `goodie_score` (
                    `user_id` INT, 
                    `night_shift_start` INT, 
                    `night_shift_end` INT, 
                    `night_shift_multiplier` INT,
                    `now` TIMESTAMP
                ) RETURNS INT(11)  
                BEGIN
                    DECLARE done INT;
                    DECLARE shift_start TIMESTAMP;
                    DECLARE shift_end TIMESTAMP;
                    DECLARE freeloaded BOOLEAN;
                
                    DECLARE START_OF_DAY TIMESTAMP;
                    DECLARE NIGHT_START TIMESTAMP;
                    DECLARE NIGHT_END TIMESTAMP;
                    DECLARE NIGHT_SHIFT_SECONDS int;
                    DECLARE SHIFT_SUM int;
                    DECLARE GOODY_SUM int;
                    
                    DECLARE DAYS_DIFF int;
                
                    DECLARE shift_cursor CURSOR FOR
                            SELECT s.start, s.end, se.freeloaded_by IS NOT NULL AS freeloaded 
                            FROM users u
                            JOIN shift_entries se ON se.user_id = u.id
                            JOIN shifts s ON se.shift_id = s.id
                            WHERE u.id = user_id
                            AND s.end < now;
                    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
                
                    OPEN shift_cursor;
                    
                    Set GOODY_SUM = 0;
                    read_loop: LOOP
                        FETCH shift_cursor INTO shift_start, shift_end, freeloaded;
                        IF done THEN
                                LEAVE read_loop;
                        END IF;
                        IF freeloaded THEN
                            SET SHIFT_SUM = TIMESTAMPDIFF(SECOND, shift_start, shift_end) * -night_shift_multiplier;
                        ELSE
                            SET DAYS_DIFF = 0;
                            SET NIGHT_SHIFT_SECONDS = 0;
                            SET START_OF_DAY = TIMESTAMP(DATE(shift_start));
                            
                            WHILE START_OF_DAY < shift_end DO
                                SET NIGHT_START = TIMESTAMPADD(HOUR, night_shift_start, START_OF_DAY);
                                SET NIGHT_END = TIMESTAMPADD(HOUR, night_shift_end, START_OF_DAY);
                                SET NIGHT_SHIFT_SECONDS = NIGHT_SHIFT_SECONDS + GREATEST(0, TIMESTAMPDIFF(
                                    SECOND, 
                                    GREATEST(shift_start, NIGHT_START),
                                    LEAST(shift_end, NIGHT_END)
                                ));
                                SET DAYS_DIFF = DAYS_DIFF + 1;
                                SET START_OF_DAY = TIMESTAMPADD(DAY, 1 , START_OF_DAY);
                            END WHILE;
                            
                            SET SHIFT_SUM = TIMESTAMPDIFF(SECOND, shift_start, shift_end) 
                                                + (NIGHT_SHIFT_SECONDS * (night_shift_multiplier - 1));
                        END IF;
                    
                        SET GOODY_SUM = GOODY_SUM + SHIFT_SUM;
                    END LOOP;
                    CLOSE shift_cursor;
                    RETURN GOODY_SUM;
                END;'
        );
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->db->statement(
            /** @lang MySQL */
            'DROP FUNCTION `goodie_score`;'
        );
    }
}

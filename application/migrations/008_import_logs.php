<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Import_logs extends CI_Migration {
    public function up() {
        $this->dbforge->add_field([
            'id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE,'auto_increment'=>TRUE],
            'kind' => ['type'=>'VARCHAR','constraint'=>32], // units|buildings|research|spells|heroes|items
            'filename' => ['type'=>'VARCHAR','constraint'=>255],
            'dry_run' => ['type'=>'TINYINT','constraint'=>1,'default'=>1],
            'mode' => ['type'=>'VARCHAR','constraint'=>16,'default'=>'noop'], // noop|tx_rollback|commit
            'stats' => ['type'=>'MEDIUMTEXT','null'=>TRUE], // json: inserted, updated, skipped, errors
            'issues' => ['type'=>'MEDIUMTEXT','null'=>TRUE], // json list per-row
            'diffs' => ['type'=>'LONGTEXT','null'=>TRUE], // json diffs (optional large)
            'created_at' => ['type'=>'INT','constraint'=>10,'unsigned'=>TRUE],
            'actor_user_id' => ['type'=>'BIGINT','constraint'=>20,'unsigned'=>TRUE],
        ]);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('import_logs', TRUE);
    }

    public function down() {
        $this->dbforge->drop_table('import_logs', TRUE);
    }
}

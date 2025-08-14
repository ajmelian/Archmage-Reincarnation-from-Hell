<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Performance_indexes extends CI_Migration {
    public function up() {
        // Helper para añadir índice si no existe
        $addIndex = function($table, $name, $sql) {
            $exists = $this->db->query("SHOW INDEX FROM `{$table}` WHERE Key_name = ".$this->db->escape($name))->row_array();
            if (!$exists) { $this->db->query($sql); }
        };

        if ($this->db->table_exists('market_listings')) {
            $addIndex('market_listings','idx_ml_status_created',"ALTER TABLE `market_listings` ADD INDEX `idx_ml_status_created` (`status`,`created_at`)");
            $addIndex('market_listings','idx_ml_item_status',"ALTER TABLE `market_listings` ADD INDEX `idx_ml_item_status` (`item_id`,`status`)");
            $addIndex('market_listings','idx_ml_price',"ALTER TABLE `market_listings` ADD INDEX `idx_ml_price` (`price`)");
        }
        if ($this->db->table_exists('market_trades')) {
            $addIndex('market_trades','idx_mt_item_created',"ALTER TABLE `market_trades` ADD INDEX `idx_mt_item_created` (`item_id`,`created_at`)");
            $addIndex('market_trades','idx_mt_buyer_seller',"ALTER TABLE `market_trades` ADD INDEX `idx_mt_buyer_seller` (`buyer_realm_id`,`seller_realm_id`)");
        }
        if ($this->db->table_exists('auctions')) {
            $addIndex('auctions','idx_auc_status_ends',"ALTER TABLE `auctions` ADD INDEX `idx_auc_status_ends` (`status`,`ends_at`)");
            $addIndex('auctions','idx_auc_created',"ALTER TABLE `auctions` ADD INDEX `idx_auc_created` (`created_at`)");
        }
        if ($this->db->table_exists('auction_bids')) {
            $addIndex('auction_bids','idx_ab_auction_id',"ALTER TABLE `auction_bids` ADD INDEX `idx_ab_auction_id` (`auction_id`)");
            $addIndex('auction_bids','idx_ab_bidder',"ALTER TABLE `auction_bids` ADD INDEX `idx_ab_bidder` (`realm_id`)");
        }
        if ($this->db->table_exists('alliances')) {
            $addIndex('alliances','idx_all_name_tag',"ALTER TABLE `alliances` ADD UNIQUE INDEX `idx_all_name_tag` (`name`,`tag`)");
        }
        if ($this->db->table_exists('alliance_members')) {
            $addIndex('alliance_members','idx_am_alliance_role',"ALTER TABLE `alliance_members` ADD INDEX `idx_am_alliance_role` (`alliance_id`,`role`)");
        }
        if ($this->db->table_exists('audit_log')) {
            $addIndex('audit_log','idx_al_user_time',"ALTER TABLE `audit_log` ADD INDEX `idx_al_user_time` (`user_id`,`created_at`)");
            $addIndex('audit_log','idx_al_realm_time',"ALTER TABLE `audit_log` ADD INDEX `idx_al_realm_time` (`realm_id`,`created_at`)");
        }
        if ($this->db->table_exists('mod_actions')) {
            $addIndex('mod_actions','idx_ma_target_action',"ALTER TABLE `mod_actions` ADD INDEX `idx_ma_target_action` (`target_realm_id`,`action`)");
            $addIndex('mod_actions','idx_ma_expires',"ALTER TABLE `mod_actions` ADD INDEX `idx_ma_expires` (`expires_at`)");
        }
        if ($this->db->table_exists('inventory')) {
            $addIndex('inventory','idx_inv_realm_item',"ALTER TABLE `inventory` ADD INDEX `idx_inv_realm_item` (`realm_id`,`item_id`)");
        }
        if ($this->db->table_exists('economy_history')) {
            $addIndex('economy_history','idx_eh_realm_time',"ALTER TABLE `economy_history` ADD INDEX `idx_eh_realm_time` (`realm_id`,`created_at`)");
        }
        if ($this->db->table_exists('users')) {
            $addIndex('users','idx_users_email',"ALTER TABLE `users` ADD UNIQUE INDEX `idx_users_email` (`email`)");
        }
        if ($this->db->table_exists('realms')) {
            $addIndex('realms','idx_realms_user',"ALTER TABLE `realms` ADD UNIQUE INDEX `idx_realms_user` (`user_id`)");
            $addIndex('realms','idx_realms_alliance',"ALTER TABLE `realms` ADD INDEX `idx_realms_alliance` (`alliance_id`)");
        }
    }
    public function down() {
        // No se eliminan índices en downgrade
    }
}

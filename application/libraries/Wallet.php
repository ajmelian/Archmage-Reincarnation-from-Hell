<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Wallet {

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->database();
    }

    private function ensure(int $realmId): void {
        $row = $this->CI->db->get_where('wallets', ['realm_id'=>$realmId])->row_array();
        if (!$row) $this->CI->db->insert('wallets', ['realm_id'=>$realmId,'gold'=>0,'mana'=>0,'updated_at'=>time()]);
    }

    public function balance(int $realmId): array {
        $this->ensure($realmId);
        $row = $this->CI->db->get_where('wallets', ['realm_id'=>$realmId])->row_array();
        return ['gold'=>(int)$row['gold'],'mana'=>(int)$row['mana']];
    }

    public function add(int $realmId, string $res, int $amount, string $reason, string $refType=null, int $refId=null): void {
        $col = ($res==='mana') ? 'mana' : (($res==='research') ? 'research' : 'gold');
        $this->ensure($realmId);
        $this->CI->db->trans_start();
        $this->CI->db->set($col, "$col+$amount", FALSE)->set('updated_at', time())->where('realm_id',$realmId)->update('wallets');
        $this->CI->db->insert('wallet_logs', ['realm_id'=>$realmId,'res'=>$col,'delta'=>$amount,'reason'=>$reason,'ref_type'=>$refType,'ref_id'=>$refId,'created_at'=>time()]);
        $this->CI->db->trans_complete();
    }

    public function spend(int $realmId, string $res, int $amount, string $reason, string $refType=null, int $refId=null): void {
        $col = ($res==='mana') ? 'mana' : (($res==='research') ? 'research' : 'gold');
        $this->ensure($realmId);
        // check balance
        $row = $this->CI->db->get_where('wallets', ['realm_id'=>$realmId])->row_array();
        if ((int)$row[$col] < $amount) throw new Exception('Insufficient '.$col);
        $this->CI->db->trans_start();
        $this->CI->db->set($col, "$col-$amount", FALSE)->set('updated_at', time())->where('realm_id',$realmId)->update('wallets');
        $this->CI->db->insert('wallet_logs', ['realm_id'=>$realmId,'res'=>$col,'delta'=>-$amount,'reason'=>$reason,'ref_type'=>$refType,'ref_id'=>$refId,'created_at'=>time()]);
        $this->CI->db->trans_complete();
    }
}

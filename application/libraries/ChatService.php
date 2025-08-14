<?php defined('BASEPATH') OR exit('No direct script access allowed');

class ChatService {
    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->database();
        $this->CI->load->config('chat');
    }

    private function realm(): ?array {
        $uid = (int)$this->CI->session->userdata('userId');
        if (!$uid) return null;
        return $this->CI->db->get_where('realms',['user_id'=>$uid])->row_array();
    }

    public function ensureChannel(string $type, string $key, string $name): array {
        $row = $this->CI->db->get_where('chat_channels', ['type'=>$type,'key'=>$key])->row_array();
        if ($row) return $row;
        $this->CI->db->insert('chat_channels', ['type'=>$type,'key'=>$key,'name'=>$name,'created_at'=>time()]);
        $id = (int)$this->CI->db->insert_id();
        return $this->CI->db->get_where('chat_channels',['id'=>$id])->row_array();
    }

    public function allianceChannelFor(int $realmId): ?array {
        // find alliance
        if (!$this->CI->db->table_exists('alliance_members')) return null;
        $m = $this->CI->db->get_where('alliance_members',['realm_id'=>$realmId])->row_array();
        if (!$m) return null;
        $aid = (int)$m['alliance_id'];
        $a = $this->CI->db->get_where('alliances',['id'=>$aid])->row_array();
        $name = $a ? ('Alianza: '.$a['name']) : ('Alianza #'.$aid);
        return $this->ensureChannel('alliance', 'ally-'.$aid, $name);
    }

    public function canReadChannel(int $realmId, array $channel): bool {
        if ($channel['type']==='global') return true;
        if ($channel['type']==='alliance') {
            $m = $this->CI->db->get_where('alliance_members',['realm_id'=>$realmId])->row_array();
            if (!$m) return false;
            $key = 'ally-'.(int)$m['alliance_id'];
            return $channel['key']===$key;
        }
        if ($channel['type']==='private') {
            $m = $this->CI->db->get_where('chat_members',['channel_id'=>$channel['id'],'realm_id'=>$realmId])->row_array();
            return (bool)$m;
        }
        return false;
    }

    public function post(int $realmId, int $channelId, string $text): int {
        $text = trim($text);
        $max = (int)($this->CI->config->item('chat')['max_len'] ?? 400);
        if ($text==='' || mb_strlen($text) > $max) throw new Exception('Invalid text length');
        // fetch channel and authorize
        $ch = $this->CI->db->get_where('chat_channels',['id'=>$channelId])->row_array();
        if (!$ch) throw new Exception('Channel not found');
        if (!$this->canReadChannel($realmId, $ch)) throw new Exception('Not allowed');
        $this->CI->db->insert('chat_messages',[
            'channel_id'=>$channelId,'realm_id'=>$realmId,'text'=>$text,'created_at'=>time()
        ]);
        return (int)$this->CI->db->insert_id();
    }

    public function poll(int $realmId, int $channelId, int $afterId=0, int $limit=50): array {
        $ch = $this->CI->db->get_where('chat_channels',['id'=>$channelId])->row_array();
        if (!$ch) throw new Exception('Channel not found');
        if (!$this->canReadChannel($realmId, $ch)) throw new Exception('Not allowed');
        $limit = min(max(1,$limit), (int)($this->CI->config->item('chat')['poll_batch'] ?? 50));
        $where = "channel_id={$channelId}";
        if ($afterId>0) $where .= " AND id>".(int)$afterId;
        $rows = $this->CI->db->order_by('id','ASC')->limit($limit)->get_where('chat_messages', $where)->result_array();
        // attach realm name
        $out = [];
        foreach ($rows as $r) {
            $realm = $this->CI->db->get_where('realms',['id'=>$r['realm_id']])->row_array();
            $out[] = [
                'id'=>(int)$r['id'],
                'realm_id'=>(int)$r['realm_id'],
                'realm_name'=>$realm ? $realm['name'] : ('#'.$r['realm_id']),
                'text'=>$r['text'],
                'created_at'=>(int)$r['created_at']
            ];
        }
        return $out;
    }

    // Direct messages (privados 1:1)
    public function sendDM(int $fromRealmId, int $toRealmId, string $subject, string $body): int {
        $subject = trim($subject); $body = trim($body);
        if ($body==='') throw new Exception('Body required');
        $this->CI->db->insert('dm_messages',[
            'from_realm_id'=>$fromRealmId,'to_realm_id'=>$toRealmId,
            'subject'=>$subject ?: null, 'body'=>$body, 'created_at'=>time()
        ]);
        return (int)$this->CI->db->insert_id();
    }

    public function inbox(int $realmId, int $limit=50): array {
        return $this->CI->db->order_by('created_at','DESC')->limit($limit)->get_where('dm_messages',[
            'to_realm_id'=>$realmId,'deleted_by_to'=>0
        ])->result_array();
    }

    public function sent(int $realmId, int $limit=50): array {
        return $this->CI->db->order_by('created_at','DESC')->limit($limit)->get_where('dm_messages',[
            'from_realm_id'=>$realmId,'deleted_by_from'=>0
        ])->result_array();
    }

    public function read(int $realmId, int $id): array {
        $row = $this->CI->db->get_where('dm_messages',['id'=>$id])->row_array();
        if (!$row) throw new Exception('Message not found');
        if ((int)$row['to_realm_id']!==$realmId and (int)$row['from_realm_id']!==$realmId) throw new Exception('Not allowed');
        if ((int)$row['to_realm_id']===$realmId && empty($row['read_at'])) {
            $this->CI->db->update('dm_messages',['read_at'=>time()],['id'=>$id]);
        }
        return $row;
    }

    public function delete(int $realmId, int $id): void {
        $row = $this->CI->db->get_where('dm_messages',['id'=>$id])->row_array();
        if (!$row) return;
        if ((int)$row['from_realm_id']===$realmId) {
            $this->CI->db->update('dm_messages',['deleted_by_from'=>1],['id'=>$id]);
        }
        if ((int)$row['to_realm_id']===$realmId) {
            $this->CI->db->update('dm_messages',['deleted_by_to'=>1],['id'=>$id]);
        }
        // hard delete if both sides deleted
        $r2 = $this->CI->db->get_where('dm_messages',['id'=>$id])->row_array();
        if ($r2 && (int)$r2['deleted_by_from']===1 && (int)$r2['deleted_by_to']===1) {
            $this->CI->db->delete('dm_messages',['id'=>$id]);
        }
    }
}

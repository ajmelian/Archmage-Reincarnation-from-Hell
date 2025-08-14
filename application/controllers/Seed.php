<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Seed extends CI_Controller {
    public function __construct() {
        parent::__construct();
        if (!is_cli()) show_404();
        $this->load->database();
        $this->load->model(['User_model','Realm_model','Unit_model']);
    }

    public function demo() {
        echo "Seeding demo users and realms...\n";
        $users = [
            ['email'=>'alice@example.com','display_name'=>'Alice','password'=>'secret123'],
            ['email'=>'bob@example.com','display_name'=>'Bob','password'=>'secret123'],
        ];
        foreach ($users as $u) {
            $exists = $this->User_model->findByEmail($u['email']);
            if (!$exists) {
                $algo = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT;
                $uid = $this->User_model->create([
                    'email'=>$u['email'],
                    'display_name'=>$u['display_name'],
                    'pass_hash'=>password_hash($u['password'], $algo),
                    'created_at'=>time()
                ]);
                $realm = $this->Realm_model->getOrCreate((int)$uid);
                $state = $this->Realm_model->loadState($realm);
                // Give some initial army if units exist
                $units = $this->Unit_model->all();
                if ($units) {
                    $state['army'][$units[0]['id']] = 50;
                    $this->Realm_model->saveState((int)$realm['id'], $state);
                }
                echo "Created user {$u['email']} (password: {$u['password']})\n";
            } else {
                echo "User {$u['email']} already exists.\n";
            }
        }
        echo "Done.\n";
    }

    public function items() {
        echo "Seeding items to Alice and promoting to admin...\n";
        $alice = $this->User_model->findByEmail('alice@example.com');
        if ($alice) {
            $this->db->where('id', $alice['id'])->update('users', ['role'=>'admin']);
            $realm = $this->db->get_where('realms', ['user_id'=>$alice['id']])->row_array();
            if ($realm) {
                $this->load->model('Inventory_model');
                $this->Inventory_model->add((int)$realm['id'], 'banner_of_valor', 2);
                $this->Inventory_model->add((int)$realm['id'], 'tome_of_wisdom', 1);
                echo "Added items to Alice's realm.\n";
            }
        } else {
            echo "Alice not found. Run seed demo first.\n";
        }
        echo "Done.\n";
    }
}
<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Golden extends CI_Controller {
    public function __construct() {
        parent::__construct();
        if (!is_cli()) show_404();
        $this->load->config('formulas');
        $this->tol = (float)($this->config->item('formulas')['tolerance'] ?? 0.0001);
        $this->base = getcwd();
    }

    public function run($suite='all') {
        $fails = 0;
        if ($suite==='all' || $suite==='economy') $fails += $this->economy();
        if ($suite==='all' || $suite==='combat')  $fails += $this->combat();
        if ($suite==='all' || $suite==='spells')  $fails += $this->spells();
        if ($fails === 0) echo "All golden tests passed.\\n";
        else echo "Golden tests FAILED: $fails cases.\\n";
        exit($fails > 0 ? 1 : 0);
    }

    private function economy(): int {
        $this->load->library('Formulas/EconomyFormula');
        $f = new EconomyFormula();
        $file = APPPATH.'tests/golden/economy_cases.csv';
        if (!is_file($file)) { echo "No economy cases.\\n"; return 0; }
        $fh = fopen($file,'r'); $h = fgetcsv($fh);
        $fails = 0; $i=1;
        while(($r=fgetcsv($fh))!==false) {
            $i++; $row = array_combine($h,$r);
            $state = json_decode($row['state'], true) ?: [];
            $expected = json_decode($row['expected'], true) ?: [];
            $out = $f->produce($state);
            $ok = ($out['gold']==$expected['gold'] && $out['mana']==$expected['mana'] && $out['research']==$expected['research']);
            if (!$ok) { $fails++; echo "[ECON] Case {$row['id']} mismatch: got ".json_encode($out)." expected ".json_encode($expected)."\\n"; }
        }
        fclose($fh);
        echo "Economy: ".($i-1)." cases; fails=$fails\\n";
        return $fails;
    }

    private function combat(): int {
        $this->load->library('Formulas/CombatFormula');
        $f = new CombatFormula();
        $file = APPPATH.'tests/golden/combat_cases.csv';
        if (!is_file($file)) { echo "No combat cases.\\n"; return 0; }
        $fh = fopen($file,'r'); $h = fgetcsv($fh);
        $fails = 0; $i=1;
        while(($r=fgetcsv($fh))!==false) {
            $i++; $row = array_combine($h,$r);
            $A = json_decode($row['sideA'], true) ?: [];
            $B = json_decode($row['sideB'], true) ?: [];
            $res = $f->resolveRound($A,$B);
            $exp = json_decode($row['expected'], true) ?: [];
            $ok = ($res['winner']===$exp['winner'] and json_encode($res['lossesA'])===json_encode($exp['lossesA']) and json_encode($res['lossesB'])===json_encode($exp['lossesB']));
            if (!$ok) { $fails++; echo "[COMBAT] Case {$row['id']} mismatch\\n got: ".json_encode($res)."\\n exp: ".json_encode($exp)."\\n"; }
        }
        fclose($fh);
        echo "Combat: ".($i-1)." cases; fails=$fails\\n";
        return $fails;
    }

    private function spells(): int {
        $this->load->library('Formulas/SpellFormula');
        $f = new SpellFormula();
        $file = APPPATH.'tests/golden/spells_cases.csv';
        if (!is_file($file)) { echo "No spells cases.\\n"; return 0; }
        $fh = fopen($file,'r'); $h = fgetcsv($fh);
        $fails = 0; $i=1;
        while(($r=fgetcsv($fh))!==false) {
            $i++; $row = array_combine($h,$r);
            $type = $row['type']; $baseCost = (int)$row['base_cost']; $level=(int)$row['level'];
            $exp = json_decode($row['expected'], true) ?: [];
            $power = $f->powerForLevel($level);
            $cost  = $f->manaCost($baseCost, $level);
            $dur   = $f->duration($type);
            $ok = (abs($power - $exp['power']) <= $this->tol) and ($cost===$exp['cost']) and ($dur===$exp['duration']);
            if (!$ok) { $fails++; echo "[SPELL] Case {$row['id']} mismatch\\n"; }
        }
        fclose($fh);
        echo "Spells: ".($i-1)." cases; fails=$fails\\n";
        return $fails;
    }
}

<?php

namespace App\Gateway;

use Illuminate\Database\ConnectionInterface;

class GadgetGateway
{
    /**
     * @var ConnectionInterface
     */
    private $db;

    public function __construct()
    {
        $this->db = app('db');
    }

    // Question
    function getGadget($nama)
    {
        $gadget = $this->db->table('questions')
            ->where('nama', 'like', "%$nama%")
            ->orWhere('brand', 'like', "%$nama%")
            ->get();

        if ($gadget) {
            return $gadget;
        }

        return null;
    }
}

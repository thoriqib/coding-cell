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
        $gadget = $this->db->table('gadget')
            ->where('nama', 'like', "%$nama%")
            ->orWhere('brand', 'like', "%$nama%")
            ->dd();

        if ($gadget) {
            return (array) $gadget;
        }

        return null;
    }
}

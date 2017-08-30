<?php

namespace App\Repositories;

use App\Device;

class DeviceRepository
{
    /**
     * Get all of the deivces
     *
     * @return Collection
     */
    public function get()
    {
        return Device::all();
    }
}
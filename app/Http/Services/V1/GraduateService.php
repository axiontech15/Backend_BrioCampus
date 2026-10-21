<?php

namespace App\Http\Services\V1;

use App\Models\Graduate;

class GraduateService
{
    public function getAllGraduates()
    {
        return Graduate::all();
    }
    public function getGraduateById(int $id)
    {
        $graduate = Graduate::find($id);
        if (!$graduate) {
            throw new \Exception('Graduate not found');
        }
        return $graduate;
    }
    public function createGraduate(array $data)
    {
        $graduate = Graduate::create($data);
        if (!$graduate) {
            throw new \Exception('Failed to create Graduate');
        }
        return $graduate;
    }
    public function updateGraduate(int $id, array $data)
    {
        $graduate = Graduate::find($id);
        if (!$graduate) {
            throw new \Exception('Graduate not found');
        }
        $graduate->update($data);
        return $graduate;
    }
    public function deleteGraduate(int $id)
    {
        $graduate = Graduate::find($id);
        if (!$graduate) {
            throw new \Exception('Graduate not found');
        }
        $graduate->delete();
        return $graduate;
    }
}
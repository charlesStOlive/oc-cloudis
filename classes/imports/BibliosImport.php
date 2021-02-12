<?php namespace Waka\Cloudis\Classes\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Waka\Cloudis\Models\Biblio;

class BibliosImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $biblio = new Biblio();
            $biblio->id = $row['id'] ?? null;
            $biblio->name = $row['name'] ?? null;
            $biblio->slug = $row['slug'] ?? null;
            $biblio->save();
        }
    }
}

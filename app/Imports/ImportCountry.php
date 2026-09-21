<?php
namespace App\Imports;
use App\Models\Country;
use Maatwebsite\Excel\Concerns\ToModel;
class ImportCountry implements ToModel
{
   public function model(array $row)
   {
       return new Country([
           'country_name' => $row[0],

       ]);
   }
}

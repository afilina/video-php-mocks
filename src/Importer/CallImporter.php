<?php
namespace App\Importer;

use App\Model\Call;
use App\Service\DatabaseWriter;

class CallImporter
{
    public function importFromCsv(string $filePath, DatabaseWriter $db):array
    {
        $fileHandle = fopen($filePath, 'r');
        $records = [];
        while ($csvLine = fgets($fileHandle)) {
            $records[] = $this->parseCsvLine($csvLine);
        }
        fclose($fileHandle);

        $db->saveRecords($records);

        return $records;
    }
    
    public function parseCsvLine(string $csvLine):Call
    {
        $lineArray = str_getcsv($csvLine, ',', '');
        $startDate = new \DateTime($lineArray[1]);
        $endDate = new \DateTime($lineArray[2]);

        if (!$this->isExistingCustomer((int)$lineArray[0]))
        {
            throw new \UnexpectedValueException('Customer must exist in the database');
        }

        $call = new Call();
        $call->setCustomerId((int)$lineArray[0]);
        $call->setMinutes($startDate->diff($endDate)->i);
        $call->setDate($startDate->setTime(0, 0, 0));
        return $call;
    }
    
    public function isExistingCustomer(int $customerId):bool
    {
        return in_array($customerId, [1,2,3]) === true;
    }    
}

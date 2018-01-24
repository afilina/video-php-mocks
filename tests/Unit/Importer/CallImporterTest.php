<?php

use PHPUnit\Framework\TestCase;
use App\Importer\CallImporter;

class CallImporterTest extends TestCase
{
    // Stub
    public function testParseCsvLine_WithOneLine()
    {
        // Setup stub
        $double = $this->getMockBuilder(CallImporter::class)
             ->setMethodsExcept(['parseCsvLine'])
             ->getMock();
        $double->method('isExistingCustomer')->willReturn(true);

        // Setup data
        $csvLine = '1,2017-11-01 09:00:00,2017-11-01 09:05:00';

        // Setup expectations
        $expectedMinutes = 5;

        // Exercise
        $actualRecord = $double->parseCsvLine($csvLine);

        // Verify
        $this->assertEquals($expectedMinutes, $actualRecord->getMinutes());
    }

    // Mock + dummy
    public function testImportFromCsv_WithTwoRecords()
    {
        // Setup mock & expectations
        $double = $this->getMockBuilder(CallImporter::class)
             ->setMethodsExcept(['importFromCsv'])
             ->getMock();
        $double->expects($this->exactly(2))->method('parseCsvLine');

        // Setup dummy
        $dummyDb = $this->createMock(\App\Service\DatabaseWriter::class);

        // Setup data
        $filePath = __DIR__.'/../../Fixtures/call-records-2-rows.csv';

        // Exercise
        $actualRecordsArray = $double->importFromCsv($filePath, $dummyDb);

        // Verify
        $this->assertCount(2, $actualRecordsArray);
    }

    // Spy + stub
    /**
     * Setup spy (and auto-verify)
     * @expectedException \UnexpectedValueException
     */
    public function testParseCsvLine_WithInvalidCustomer()
    {
        // Setup stub
        $double = $this->getMockBuilder(CallImporter::class)
            ->setMethodsExcept(['parseCsvLine'])
            ->getMock();
        $double->method('isExistingCustomer')->willReturn(false);

        // Setup data
        $csvLine = '5,2017-11-01 09:00:00,2017-11-01 09:05:00';

        // Exercise
        $double->parseCsvLine($csvLine);
    }

    // Custom spy
    public function testImportFromCsv_SavesRecords()
    {
        // Setup mock
        $double = $this->getMockBuilder(CallImporter::class)
            ->setMethodsExcept(['importFromCsv'])->getMock();

        // Setup spy
        $dummyDb = $this->createMock(\App\Service\DatabaseWriter::class);
        $dummyDb->expects($spy = $this->any())->method('saveRecords');

        // Setup data
        $filePath = __DIR__.'/../../Fixtures/call-records-2-rows.csv';

        // Exercise
        $double->importFromCsv($filePath, $dummyDb);

        // Verify
        $invocations = $spy->getInvocations();

        $this->assertCount(1, $invocations);
        $firstParameter = $invocations[0]->getParameters()[0];

        $this->assertInternalType('array', $firstParameter);
        $this->assertInstanceOf(\App\Model\Call::class, $firstParameter[0]);
    }

    // Fake + stub + spy
    /**
     * @expectedException \UnexpectedValueException
     */
    public function testImportFromCsv_InvalidCustomerFake()
    {
        // Setup stub
        $double = $this->getMockBuilder(CallImporter::class)
            ->setMethodsExcept(['parseCsvLine'])->getMock();

        // Setup fake
        $double->method('isExistingCustomer')
            ->will($this->returnCallback(
                function (int $customerId) {
                    if ($customerId >= 1) {
                        return false;
                    }
                    return true;
                }
            ));

        // Setup data
        $csvLine = '1000,2017-11-01 09:00:00,2017-11-01 09:05:00';

        // Exercise
        $double->parseCsvLine($csvLine);
    }
}

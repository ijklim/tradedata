<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StockTest extends TestCase
{
    use RefreshDatabase;
    use \App\Traits\Model;

    /**
     * Test Stock related functions.
     *
     * @return void
     */
    public function testStock()
    {
        $folderName = $this->getFolderName();
        $data = ['symbol' => 'QQQ', 'name' => 'PowerShares QQQ Trust, Series 1'];
        // Hint: reset($data) will return first element of $data, end($data) the last element
        $dataField1Key = array_keys($data)[0];
        $dataField1Value = reset($data);
        $dataField2Key = array_keys($data)[1];
        $dataField2Value = end($data);


        // Testing .show and .index, data should not exist
        $this->get("/$folderName/$dataField1Value")
            ->assertDontSee($dataField1Value)
            ->assertDontSee($dataField2Value);
        $this->get("/$folderName")
            ->assertDontSee($dataField1Value)
            ->assertDontSee($dataField2Value);

        // Testing .store, adding data to table, success will redirect to .index page
        $this->json('POST', "/$folderName", $data)
            ->assertSessionHas('success')
            ->assertRedirect($folderName);

        // Testing .show displays added data
        $this->get("/$folderName/$dataField1Value")
        ->assertSee($dataField1Value)
        ->assertSee($dataField2Value);

        // Ensure duplicates cannot be entered
        $this->json('POST', "/$folderName", $data)
            ->assertSessionHas('error');
        
        
        
            // Add another set of data, ensure success
        $data2 = [$dataField1Key => 'SPY', $dataField2Key => 'SPDR S and P'];
        $data2Field1Value = reset($data2);
        $data2Field2Value = end($data2);
        $dataWithKeyViolation = [$dataField1Key => $dataField1Value, $dataField2Key => $data2Field2Value];
        
        $this->json('POST', "/$folderName", $data2)
            ->assertSessionHas('success')
            ->assertRedirect($folderName);
        
        // Test edit prevent key violation
        $this->json('PUT', "/$folderName/$data2Field1Value", $dataWithKeyViolation)
            ->assertSessionHas('error');
        
        // Test .edit page is available, check field contains default value
        $this->get("/$folderName/$data2Field1Value/edit")
            ->assertStatus(200)
            ->assertDontSee($dataField2Value)
            ->assertSee($data2Field1Value)
            ->assertSee($data2Field2Value);

        // Test edit can be done when valid, testing .update
        $randomText = ' (ETF)';
        $data3 = [$dataField1Key => $data2Field1Value, $dataField2Key => $data2Field2Value . $randomText];
        $data3Field2Value = end($data3);
        $this->json('PUT', "/$folderName/$data2Field1Value", $data3)
            ->assertSessionHas('success')
            ->assertRedirect($folderName);
        // Edited text is found on index page, and suffix should not be added to first symbol
        $this->get("/$folderName")
            ->assertSee($data2Field1Value)
            ->assertSee($data3Field2Value)
            ->assertDontSee($dataField2Value . $randomText);
        
        // Testing .destroy, first data
        $this->json('DELETE', "/$folderName/$dataField1Value")
            ->assertSessionHas('success')
            ->assertRedirect($folderName);
        // Stock should no longer be on index page
        $this->get("/$folderName")
            ->assertSee($data3Field2Value)
            ->assertDontSee($dataField2Value);
    }
}

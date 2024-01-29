<?php

namespace App\Components\Common\Services;
use App\Components\Common\Models\Settings;


class SaleOrderNumberService
{
    private $setting;
    private $lockedSetting;

    public function __construct()
    {
        
        $this->setting = Settings::query();
        $this->lockedSetting = $this->setting->lockForUpdate()->first();
    }

    public function setNextSaleOrderNumber()
    {
        $currentNumber = $this->nextSaleOrderNumber();
        $this->increaseSaleOrderNumber();

        return $currentNumber;
    }

    public function setSaleOrderNumber(Int $saleOrder)
    {
        $this->lockedSetting->order_number = $saleOrder;
        return $this->lockedSetting->save();
    }

    public function nextSaleOrderNumber()
    {
        return $this->setting->first()->order_number;
    }

    private function increaseSaleOrderNumber()
    {
        $this->lockedSetting->order_number++;
        return $this->lockedSetting->save();
    }

    // $currentInvoiceNumber = optional(Invoice::query()->orderByDesc('invoice_number')->limit(1)->first())->invoice_number;
}

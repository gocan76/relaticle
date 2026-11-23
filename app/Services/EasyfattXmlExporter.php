<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\QuotationArea;
use App\Models\Quotation;
use DOMDocument;
use DOMElement;
use Illuminate\Support\Collection;

final readonly class EasyfattXmlExporter
{
    /**
     * @param  Collection<int, Quotation>  $quotations
     */
    public function generate(Collection $quotations): string
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $root = $dom->createElement('EasyfattDocuments');
        $root->setAttribute('AppVersion', '2');
        $root->setAttribute('Creator', 'Relaticle');
        $root->setAttribute('CreatorUrl', 'relaticle.com');
        $dom->appendChild($root);

        $documents = $dom->createElement('Documents');
        $root->appendChild($documents);

        foreach ($quotations as $quotation) {
            $documents->appendChild($this->document($dom, $quotation));
        }

        return $dom->saveXML() ?: '';
    }

    private function document(DOMDocument $dom, Quotation $quotation): DOMElement
    {
        $company = $quotation->company;

        $element = $dom->createElement('Document');

        $this->append($dom, $element, 'DocumentType', 'Q');
        $this->append($dom, $element, 'Date', ($quotation->issued_at ?? now())->toDateString());
        $this->appendNumber($dom, $element, $quotation);

        if ($company !== null) {
            $this->append($dom, $element, 'CustomerName', $company->name);
            $this->append($dom, $element, 'CustomerAddress', $company->address);
            $this->append($dom, $element, 'CustomerPostcode', $company->postal_code);
            $this->append($dom, $element, 'CustomerCity', $company->city);
            $this->append($dom, $element, 'CustomerProvince', $company->province);
            $this->append($dom, $element, 'CustomerCountry', $company->country);
            $this->append($dom, $element, 'CustomerVatCode', $company->partita_iva);
        }

        $this->append($dom, $element, 'TotalWithoutTax', $this->amount($quotation->subtotal_amount));
        $this->append($dom, $element, 'VatAmount', $this->amount($quotation->iva_amount));
        $this->append($dom, $element, 'Total', $this->amount($quotation->total_amount));

        $rows = $dom->createElement('Rows');

        foreach ($quotation->fornitura_items_with_totals as $item) {
            $rows->appendChild($this->row(
                $dom,
                $item['description'],
                $item['quantity'],
                $item['unit_price'],
                $item['unit'],
            ));
        }

        foreach (QuotationArea::cases() as $area) {
            $amount = (float) $quotation->getAttribute($area->column());

            if ($amount > 0.0) {
                $rows->appendChild($this->row($dom, $area->getLabel(), 1, $amount, null));
            }
        }

        if ($rows->hasChildNodes()) {
            $element->appendChild($rows);
        }

        return $element;
    }

    private function row(DOMDocument $dom, string $description, float|int|string $quantity, float $price, ?string $unit): DOMElement
    {
        $element = $dom->createElement('Row');

        $this->append($dom, $element, 'Description', $description);
        $this->append($dom, $element, 'Qty', $this->number($quantity));
        $this->append($dom, $element, 'Um', $unit);
        $this->append($dom, $element, 'Price', $this->amount($price));
        $this->appendVatCode($dom, $element);

        return $element;
    }

    private function appendNumber(DOMDocument $dom, DOMElement $element, Quotation $quotation): void
    {
        $number = $quotation->getAttribute('number');

        if (! is_string($number) || $number === '') {
            return;
        }

        if (is_numeric($number)) {
            $this->append($dom, $element, 'Number', $this->number($number));

            return;
        }

        $this->append($dom, $element, 'CustomField1', $number);
    }

    private function appendVatCode(DOMDocument $dom, DOMElement $element): void
    {
        $percentage = (string) (int) round(Quotation::IVA_RATE * 100);

        $vatCode = $dom->createElement('VatCode', $percentage);
        $vatCode->setAttribute('Perc', $percentage);
        $vatCode->setAttribute('Class', 'Imponibile');
        $vatCode->setAttribute('Description', "Aliquota {$percentage}%");

        $element->appendChild($vatCode);
    }

    private function append(DOMDocument $dom, DOMElement $element, string $name, ?string $value): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $element->appendChild($dom->createElement($name, $value));
    }

    private function amount(float $value): string
    {
        return number_format($value, 2, '.', '');
    }

    private function number(float|int|string $value): string
    {
        $normalized = is_string($value) ? str_replace(',', '.', $value) : (string) $value;

        if (! is_numeric($normalized)) {
            return '0';
        }

        return rtrim(rtrim(number_format((float) $normalized, 4, '.', ''), '0'), '.');
    }
}

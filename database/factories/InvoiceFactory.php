<?php

namespace Database\Factories;

use App\Actions\Invoices\GenerateInvoiceNumber;
use App\Models\Client;
use App\Models\InvoiceStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statusId = InvoiceStatus::query()
            ->where('key', 'draft')
            ->value('id');

        $year = (int) now()->year;
        $sequence = (int) fake()->numberBetween(1, 999);
        $generator = new GenerateInvoiceNumber;
        $total = fake()->randomFloat(2, 1000, 100000);

        return [
            'client_id' => Client::factory(),
            'status_id' => $statusId,
            'invoice_year' => $year,
            'invoice_seq' => $sequence,
            'invoice_number' => $generator->format($sequence, $year),
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(15)->toDateString(),
            'subtotal' => $total,
            'total' => $total,
            'note' => fake()->optional()->sentence(),
        ];
    }
}

<?php

namespace App\Rules;

use App\Models\PaymentGateway;
use App\Models\SmsParser;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ParserRegexIsUnique implements ValidationRule
{
    public function __construct(
        protected ?PaymentGateway $paymentGateway,
        protected ?int $current_sms_parser_id = null
    )
    {}

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        //TODO refactoring
        $parsers = SmsParser::query()
            ->when($this->paymentGateway, function ($query) {
                $query->where('payment_gateway_id', $this->paymentGateway->id);
            })
            ->get();

        $regex = '/' . $value . '/mi';

        foreach ($parsers as $parser) {
            if ($parser->id === $this->current_sms_parser_id) {
                continue;
            }

            try {
                preg_match_all($regex, $parser->format, $matches, PREG_SET_ORDER);
            } catch (\Exception $e) {}

            if (! empty($matches[0]['amount'])) {
                $fail("Regex должен быть уникальным т.е. не пересекаться с остальными парсерами."); //TODO локализация
            }
        }
    }
}

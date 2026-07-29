<?php

declare(strict_types=1);

namespace App\Http\Requests\Development;

use App\Data\DevelopmentTestResultData;
use App\Models\DevelopmentTestRun;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RecordDevelopmentTestResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        $testRun = $this->route('developmentTestRun');

        return $testRun instanceof DevelopmentTestRun
            && $this->user()?->can('update', $testRun) === true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return DevelopmentTestResultData::rules();
    }

    public function toDto(): DevelopmentTestResultData
    {
        return DevelopmentTestResultData::fromValidated($this->validated());
    }
}

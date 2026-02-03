<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Admin can update, Accountant can only view
        return $this->user()->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $invoiceId = $this->route('invoice');
        
        return [
            'vendor_id' => 'sometimes|required|exists:vendors,id',
            'number' => 'sometimes|required|string|max:255|unique:invoices,number,' . $invoiceId,
            'amount' => 'sometimes|required|numeric|min:0.01|max:999999999.99',
            'status' => 'required|in:draft,sent,paid,overdue,cancelled',
            'date' => 'sometimes|required|date',
            'due_date' => 'nullable|date|after_or_equal:date',
            'notes' => 'nullable|string|max:2000',
        ];
    }

    /**
     * Get custom messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'vendor_id.required' => 'Vendor is required',
            'vendor_id.exists' => 'Selected vendor is invalid',
            'number.required' => 'Invoice number is required',
            'number.unique' => 'Invoice number must be unique',
            'amount.required' => 'Amount is required',
            'amount.min' => 'Amount must be greater than 0',
            'status.required' => 'Status is required',
            'status.in' => 'Invalid status selected',
            'date.required' => 'Invoice date is required',
            'due_date.after_or_equal' => 'Due date must be after or equal to invoice date',
            'notes.max' => 'Notes cannot exceed 2000 characters',
        ];
    }
}
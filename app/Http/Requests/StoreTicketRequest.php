<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Kullanıcı giriş yapmışsa ticket oluşturabilir
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'priority' => 'required|in:low,medium,high',
            'message' => 'required|string', // Form alanında message adı kullanılıyor
        ];
    }
    
    /**
     * Gelen verileri ticket modeliyle uyumlu hale getirmek için prepare metodu
     */
    protected function prepareForValidation()
    {
        // Message alanının içeriğini description olarak kullanmak için
        if ($this->has('message') && !$this->has('description')) {
            $this->merge([
                'description' => $this->message,
            ]);
        }
    }
    
    /**
     * Hata mesajlarını özelleştir
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Talep başlığı zorunludur.',
            'title.max' => 'Talep başlığı en fazla 255 karakter olabilir.',
            'department_id.required' => 'Departman seçimi zorunludur.',
            'department_id.exists' => 'Seçilen departman geçerli değil.',
            'priority.required' => 'Öncelik seviyesi seçimi zorunludur.',
            'priority.in' => 'Geçersiz öncelik seviyesi.',
            'message.required' => 'Talep açıklaması zorunludur.',
        ];
    }
} 
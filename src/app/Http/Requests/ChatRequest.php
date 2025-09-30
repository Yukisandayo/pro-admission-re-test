<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChatRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'message' => 'required_without:images|nullable|string|max:400',
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpeg,png',
        ];
    }

    public function after()
    {
        return [
            function (\Illuminate\Validation\Validator $validator) {
                if (empty($this->message) && !$this->hasFile('images')) {
                    $validator->errors()->add(
                        'message_or_image_required',
                        'メッセージまたは画像を送信してください'
                    );
                }
            }
        ];
    }

    public function messages()
    {
        return [
            'message.required_without' => '本文を入力してください',
            'message.max' => '本文は400文字以内で入力してください',
            'images.*.mimes' => '「.png」または「.jpeg」形式でアップロードしてください',
            'message_or_image_required' => 'メッセージまたは画像を送信してください'
        ];
    }
}

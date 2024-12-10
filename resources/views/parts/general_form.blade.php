<form class="form-block form {{ $class }} vue-form"
      id="vue-form-general"
      data-type="{{ $type }}"
      data-url="{{ \Illuminate\Support\Facades\URL::full() }}"
      data-country="{{ session('countryCode', 'other') }}"
      @submit.prevent="submitForm" v-cloak>
    <p class="form-block__title">{{ $title }}</p>
    <div class="form__fields spacer">
        <input type="text" name="name" v-model="form.name" class="form__field" :class="{ invalid: $v.form.name.$invalid && form.name }" placeholder="{{ __('name') }}">
        <input type="text" name="email" v-model="form.email" class="form__field" :class="{ invalid: $v.form.email.$invalid && form.email }" placeholder="{{ __('email2') }}">
        <input type="text" name="phone" v-model="form.phone" class="form__field" :class="{ invalid: $v.form.phone.$invalid && form.phone }" placeholder="{{ __('phone') }}">
    </div><!--form__fields-->
    @if(in_array($type,['tour', 'presentation', 'tariffs', 'demo']))
        <select class="form__field" v-model="form.country">
            <option value="RU">{{ __('russia_short') }}</option>
            <option value="KZ">{{ __('kazakhstan') }}</option>
            <option value="UA">{{ __('ukraine') }}</option>
            <option value="KG">{{ __('kyrgyzstan') }}</option>
            <option value="UZ">{{ __('uzbekistan') }}</option>
            <option value="other">{{ __('other') }}</option>
        </select>
    @endif
    @if(in_array($type,['contact', 'partner', 'review', 'faq', 'tour', 'support', 'about']))
{{--        <textarea name="question" class="form__field form__field_text" v-model="form.message" :class="{ invalid: $v.form.message.$invalid && form.message }" placeholder="{{ isset($textarea_placeholder) ? $textarea_placeholder : 'Текст сообщения' }}"></textarea>--}}
        <textarea-autosize name="question"
                           :min-height="30"
                           :max-height="350"
                           class="form__field form__field_text"
                           v-model="form.message"
                           :class="{ invalid: $v.form.message.$invalid && form.message }"
                           placeholder="{{ isset($textarea_placeholder) ? $textarea_placeholder : __('message_text') }}"></textarea-autosize>
    @endif
    <button class="form__btn" :disabled="$v.form.$invalid || loading">{{ $btn_title }}</button>
</form>

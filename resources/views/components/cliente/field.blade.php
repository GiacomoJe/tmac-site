@props(['label', 'model', 'type' => 'text'])

<div>
    <label class="block text-[10px] font-bold uppercase tracking-mono-up text-ink-faint mb-1">{{ $label }}</label>
    <input type="{{ $type }}" wire:model.blur="{{ $model }}"
           {{ $attributes->merge(['class' => 'w-full rounded-sm border border-line px-3 py-2.5 text-sm focus:border-signal focus:ring-1 focus:ring-signal']) }}>
    @error($model)
        <p class="text-accent text-xs mt-1">{{ $message }}</p>
    @enderror
</div>

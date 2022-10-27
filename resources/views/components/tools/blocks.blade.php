@props([
    'fieldId' => null,
    'blocks' => [],
])

<x-filament-tiptap-editor::dropdown-button
    label="{{ __('filament-tiptap-editor::editor.blocks.label') }}"
    icon="blocks"
    x-on:insert-block.window="$event.detail.fieldId === '{{ $fieldId }}' ? editor().chain().focus().setBlock($event.detail.attributes).run() : null"
>
    @foreach ($blocks as $block)
        <li {{ $attributes->except('action') }}>
            <button type="button"
                    x-on:click="$dispatch('close-panel');"
                    wire:click.prevent="dispatchFormEvent('tiptapeditor::createItem', '{{ $fieldId }}', '{{ $block->getName() }}')"
                    class="block w-full px-3 py-2 text-left whitespace-nowrap hover:bg-primary-500 focus:bg-primary-500">
                {{ $block->getLabel() }}
            </button>
        </li>
    @endforeach
</x-filament-tiptap-editor::dropdown-button>

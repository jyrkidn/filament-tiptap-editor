<?php

namespace FilamentTiptapEditor;

use Closure;
use Filament\Forms\ComponentContainer;
use Filament\Pages\Actions\Action;
use FilamentTiptapEditor\Components\Block;
use Illuminate\Support\Arr;
use Filament\Forms\Components\Field;
use Filament\Support\Concerns\HasExtraAlpineAttributes;
use Filament\Forms\Components\Concerns\CanBeLengthConstrained;
use Filament\Forms\Components\Concerns\HasExtraInputAttributes;
use Filament\Forms\Components\Contracts\CanBeLengthConstrained as CanBeLengthConstrainedContract;
use Illuminate\Support\Facades\Blade;

class TiptapEditor extends Field implements CanBeLengthConstrainedContract
{
    use CanBeLengthConstrained;
    use HasExtraInputAttributes;
    use HasExtraAlpineAttributes;

    protected string $view = 'filament-tiptap-editor::tiptap-editor';

    protected ?Closure $saveUploadedFileUsing = null;

    public string $profile = 'default';

    protected ?array $tools = [];

    protected ?string $disk = null;

    protected string | Closure | null $directory = null;

    protected ?array $acceptedFileTypes = null;

    protected ?int $maxFileSize = 2042;

    protected array | Closure $blocks = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->default([]);

        $this->profile = implode(',', config('filament-tiptap-editor.profiles.default'));

        $this->afterStateHydrated(static function (TiptapEditor $component, ?array $state): void {
            $items = (new \Tiptap\Editor)
                ->setContent($state ?? '<p></p>')
                ->getJSON();

            $component->state($items);
        });

        $this->registerListeners([
           'tiptapeditor::createItem' => [
               function (TiptapEditor $component, string $statePath, string $block): void {

                    if ($statePath !== $component->getStatePath()) {
                        return;
                    }

                    $livewire = $component->getLivewire();

                    $block = $this->getBlock($block);

                    $blockData = collect($block->getChildComponents())->mapWithKeys(static fn ($item) => [(string) $item->getName() => '']);

                    $action = Action::make('test')
                        ->icon('heroicon-s-cog')
                        ->iconButton()
                        ->action(function (array $data): void {
                            ray($data);
                        })
                        ->form($block->getChildComponents());

                    $livewire->dispatchBrowserEvent('insert-block', [
                        'fieldId' => $statePath,
                        'attributes' => [
                            'type' => $block->getName(),
                            'data' => $blockData,
                            'html' => static::minify(Blade::render($block->getView(), ['block' => $block, 'data' => $blockData, 'action' => $action]))
                        ]
                    ]);
               }
           ],
        ]);
    }

    public function profile(?string $profile): static
    {
        $this->profile = implode(',', config('filament-tiptap-editor.profiles.' . $profile));

        return $this;
    }

    public function tools(array $tools): static
    {
        $this->tools = $tools;

        return $this;
    }

    public function disk(?string $disk): static
    {
        $this->disk = $disk;

        return $this;
    }

    public function directory(string | Closure | null $directory): static
    {
        $this->directory = $directory;

        return $this;
    }

    public function acceptedFileTypes(?array $acceptedFileTypes): static
    {
        $this->acceptedFileTypes = $acceptedFileTypes;

        return $this;
    }

    public function maxFileSize(?int $maxFileSize): static
    {
        $this->maxFileSize = $maxFileSize;

        return $this;
    }

    public function blocks(array | Closure $blocks): static
    {
        $this->childComponents($blocks);

        return $this;
    }

    public function getTools(): string
    {
        return !$this->tools ? $this->profile : implode(',', $this->tools);
    }

    public function getDisk(): string
    {
        return $this->disk ?? config('filament-tiptap-editor.disk');
    }

    public function getDirectory(): string
    {
        return $this->directory ? $this->evaluate($this->directory) : config('filament-tiptap-editor.directory');
    }

    public function getAcceptedFileTypes(): array
    {
        return $this->acceptedFileTypes ?? config('filament-tiptap-editor.accepted_file_types');
    }

    public function getMaxFileSize(): int
    {
        return $this->maxFileSize ?? config('filament-tiptap-editor.max_file_size');
    }

    public function getBlock($name): ?Block
    {
        return Arr::first(
            $this->getBlocks(),
            fn(Block $block) => $block->getName() === $name
        );
    }

    public function getBlocks(): array
    {
        return $this->getChildComponentContainer()->getComponents();
    }

    public function getChildComponentContainers(bool $withHidden = false): array
    {
        if (isset($this->getState()['content'])) {
            return collect($this->getState()['content'])
                ->filter(fn (array $itemData): bool => $this->hasBlock($itemData['type']))
                ->map(
                    fn (array $itemData, $itemIndex): ComponentContainer => $this
                        ->getBlock($itemData['type'])
                        ->getChildComponentContainer()
                        ->getClone()
                        ->statePath("{$itemIndex}.data")
                        ->inlineLabel(false),
                )
                ->toArray();
        }

        return [];
    }

    public function hasBlock($name): bool
    {
        return (bool) $this->getBlock($name);
    }

    public function hasBlocks(): bool
    {
        return $this->getBlocks() ? 'true' : 'false';
    }

    public static function minify(string $html): string
    {
        $search = array(

            // Remove whitespaces after tags
            '/\>[^\S ]+/s',

            // Remove whitespaces before tags
            '/[^\S ]+\</s',

            // Remove multiple whitespace sequences
            '/(\s)+/s',

            // Removes comments
            '/<!--(.|\s)*?-->/'
        );
        $replace = array('>', '<', '\\1');
        $html = preg_replace($search, $replace, $html);
        return $html;
    }
}

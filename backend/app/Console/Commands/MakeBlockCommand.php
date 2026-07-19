<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeBlockCommand extends Command
{
    protected $signature = 'make:block {name : Studly block name without the "Block" suffix, e.g. Hero}';

    protected $description = 'Scaffold a new page block: a Filament block class + a Data DTO, ready to wire to a Nuxt component';

    public function handle(): int
    {
        $studly = Str::studly($this->argument('name'));
        $type = Str::snake($studly);
        $label = Str::headline($studly);

        $blockPath = app_path("Cms/Blocks/{$studly}Block.php");
        $dataPath = app_path("Data/Blocks/{$studly}Data.php");

        foreach ([$blockPath, $dataPath] as $path) {
            if (file_exists($path)) {
                $this->error("Already exists: {$path}");

                return self::FAILURE;
            }
        }

        @mkdir(dirname($dataPath), 0755, true);

        file_put_contents($blockPath, $this->blockStub($studly, $type, $label));
        file_put_contents($dataPath, $this->dataStub($studly));

        $this->components->info("Created {$blockPath}");
        $this->components->info("Created {$dataPath}");

        $this->newLine();
        $this->components->bulletList([
            "Add fields to {$studly}Block::schema() and matching properties to {$studly}Data.",
            "Create the Nuxt component: frontend/app/components/blocks/{$studly}.vue (it auto-registers).",
            'Regenerate the shared types: php artisan typescript:transform',
        ]);

        return self::SUCCESS;
    }

    protected function blockStub(string $studly, string $type, string $label): string
    {
        return <<<PHP
        <?php

        namespace App\\Cms\\Blocks;

        use App\\Data\\Blocks\\{$studly}Data;
        use Filament\\Forms\\Components\\TextInput;
        use Filament\\Support\\Icons\\Heroicon;

        class {$studly}Block extends PageBlock
        {
            public static function type(): string
            {
                return '{$type}';
            }

            public static function label(): string
            {
                return '{$label}';
            }

            public static function dataClass(): string
            {
                return {$studly}Data::class;
            }

            public static function icon(): string|\\BackedEnum|null
            {
                return Heroicon::OutlinedSquares2x2;
            }

            public static function schema(): array
            {
                return [
                    TextInput::make('title')->required(),
                    // Add fields here — each maps to a prop of your Nuxt UI component.
                ];
            }
        }

        PHP;
    }

    protected function dataStub(string $studly): string
    {
        return <<<PHP
        <?php

        namespace App\\Data\\Blocks;

        use Spatie\\LaravelData\\Data;
        use Spatie\\TypeScriptTransformer\\Attributes\\TypeScript;

        #[TypeScript]
        class {$studly}Data extends Data
        {
            public function __construct(
                public string \$title = '',
                // Add properties here — mirror your Nuxt UI component's props.
            ) {}
        }

        PHP;
    }
}

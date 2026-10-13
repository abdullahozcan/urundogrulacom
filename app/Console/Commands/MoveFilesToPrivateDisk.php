<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductDocument;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Moves already-uploaded consumer files off the public disk.
 *
 * New uploads go straight to the private disk, but files uploaded before this
 * change are still reachable at a permanent, unauthenticated `/storage/...`
 * URL — including licensed digital product payloads. This copies them into
 * storage/app/private and repoints the database rows.
 *
 * The copy is deliberately non-destructive: the original is left in place so a
 * failure can be investigated, but the DB no longer references it. Remove the
 * leftovers manually once verified.
 */
class MoveFilesToPrivateDisk extends Command
{
    protected $signature = 'files:move-to-private-disk
                            {--dry-run : Show what would move without touching anything}';

    protected $description = 'Move product documents and digital files from the public disk to the private disk';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $public = Storage::disk('public');
        $private = Storage::disk('local');

        $moved = 0;
        $skipped = 0;
        $missing = 0;

        foreach (Product::query()->whereNotNull('digital_file_path')->cursor() as $product) {
            $path = $product->digital_file_path;

            if (! $public->exists($path)) {
                $missing++;
                $this->warn("Digital file missing on public disk: {$path} (product #{$product->getKey()})");

                continue;
            }

            if ($dryRun) {
                $this->line("Would move digital file: {$path}");
                $moved++;

                continue;
            }

            $private->put($path, $public->get($path));
            $product->forceFill(['digital_file_path' => $path])->saveQuietly();
            $moved++;
            $this->line("Moved digital file: {$path}");
        }

        foreach (ProductDocument::query()->cursor() as $document) {
            $path = $document->file_path;

            if (! $public->exists($path)) {
                $missing++;
                $this->warn("Document missing on public disk: {$path}");

                continue;
            }

            if ($dryRun) {
                $this->line("Would move document: {$path}");
                $moved++;

                continue;
            }

            $private->put($path, $public->get($path));
            $moved++;
            $this->line("Moved document: {$path}");
        }

        $this->newLine();
        $this->info(($dryRun ? '[dry-run] ' : '')."Moved: {$moved}, skipped: {$skipped}, missing: {$missing}");

        if ($moved > 0 && ! $dryRun) {
            $this->comment('Originals remain on the public disk. Delete storage/app/public/{product_documents,digital_products} once verified.');
        }

        return self::SUCCESS;
    }
}
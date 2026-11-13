<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use SimpleXMLElement;

/**
 * Byte-level validation of the favicon set.
 *
 * Task requirement: a generic webpage/document icon in neutral gray #9CA3AF
 * (156,163,175) with a transparent background — no X logo, no letters, no
 * decoration — identical in design across favicon.svg, favicon-16x16.png,
 * favicon-32x32.png and favicon.ico (all embedded sizes). These tests decode
 * the actual file bytes (pure PHP, no GD dependency) so a wrong or stale
 * binary can never slip through.
 */
class FaviconTest extends TestCase
{
    private const GRAY_R = 156;
    private const GRAY_G = 163;
    private const GRAY_B = 175;

    private function publicPath(string $file): string
    {
        return dirname(__DIR__, 2).'/public/'.$file;
    }

    private function bytes(string $file): string
    {
        $path = $this->publicPath($file);
        $this->assertFileExists($path);

        return file_get_contents($path);
    }

    // =====================================================================
    // favicon.svg
    // =====================================================================

    public function test_svg_is_a_valid_xml_webpage_icon_in_neutral_gray(): void
    {
        $svg = new SimpleXMLElement($this->bytes('favicon.svg'));

        // viewBox + no stray decorative elements.
        $this->assertSame('0 0 100 100', (string) $svg['viewBox']);
        $this->assertCount(2, $svg->children(), 'Only the frame rect and the top-bar line are allowed.');
        $this->assertCount(0, $svg->xpath('//text'), 'No text/letters allowed.');
        $this->assertCount(0, $svg->xpath('//circle'), 'No circles allowed.');

        // Frame: rounded rect, unfilled (transparent background inside and out), gray stroke.
        $rect = $svg->rect;
        $this->assertSame('18', (string) $rect['x']);
        $this->assertSame('15', (string) $rect['y']);
        $this->assertSame('64', (string) $rect['width']);
        $this->assertSame('70', (string) $rect['height']);
        $this->assertSame('9', (string) $rect['rx']);
        $this->assertSame('none', (string) $rect['fill']);
        $this->assertSame('#9CA3AF', (string) $rect['stroke']);
        $this->assertSame('9', (string) $rect['stroke-width']);

        // Top bar: horizontal divider line in the same gray.
        $line = $svg->path;
        $this->assertSame('M18 33 L82 33', (string) $line['d']);
        $this->assertSame('none', (string) $line['fill']);
        $this->assertSame('#9CA3AF', (string) $line['stroke']);
        $this->assertSame('9', (string) $line['stroke-width']);
    }

    // =====================================================================
    // PNG decoding helpers (pure PHP: zlib inflate + unfilter)
    // =====================================================================

    /** @return array{0: int, 1: int, 2: string} [width, height, RGBA byte string] */
    private function decodePng(string $bytes): array
    {
        $this->assertSame("\x89PNG\r\n\x1a\n", substr($bytes, 0, 8), 'PNG signature must match.');

        $width = $height = $bitDepth = $colorType = null;
        $idat = '';
        $offset = 8;
        while ($offset + 8 <= strlen($bytes)) {
            $size = unpack('N', substr($bytes, $offset, 4))[1];
            $type = substr($bytes, $offset + 4, 4);
            $data = substr($bytes, $offset + 8, $size);

            if ($type === 'IHDR') {
                $ihdr = unpack('Nwidth/Nheight/Cbit/Ccolor/Ccompression/Cfilter/Cinterlace', $data);
                $width = $ihdr['width'];
                $height = $ihdr['height'];
                $bitDepth = $ihdr['bit'];
                $colorType = $ihdr['color'];

                $this->assertSame(8, $bitDepth, 'Expected 8-bit channels.');
                $this->assertSame(6, $colorType, 'Expected RGBA color type.');
                $this->assertSame(0, $ihdr['interlace'], 'Expected non-interlaced PNG.');
            }

            if ($type === 'IDAT') {
                $idat .= $data;
            }

            $offset += 12 + $size;
            if ($type === 'IEND') {
                break;
            }
        }

        $raw = zlib_decode($idat);
        $this->assertNotFalse($raw, 'IDAT must inflate cleanly.');

        $bpp = 4;
        $stride = $width * $bpp;
        $pixels = str_repeat("\x00", $height * $stride);
        $prevRow = str_repeat("\x00", $stride);

        for ($y = 0; $y < $height; $y++) {
            $filter = ord($raw[$y * ($stride + 1)]);
            $row = substr($raw, $y * ($stride + 1) + 1, $stride);
            $this->assertNotFalse($row, 'Every row must be present.');

            for ($x = 0; $x < $stride; $x++) {
                $a = $x >= $bpp ? ord($row[$x - $bpp]) : 0;
                $b = ord($prevRow[$x]);
                $c = $x >= $bpp ? ord($prevRow[$x - $bpp]) : 0;

                switch ($filter) {
                    case 1: $value = (ord($row[$x]) + $a) & 0xFF; break;
                    case 2: $value = (ord($row[$x]) + $b) & 0xFF; break;
                    case 3: $value = (ord($row[$x]) + intdiv($a + $b, 2)) & 0xFF; break;
                    case 4:
                        $p = $a + $b - $c;
                        $pa = abs($p - $a);
                        $pb = abs($p - $b);
                        $pc = abs($p - $c);
                        $paeth = ($pa <= $pb && $pa <= $pc) ? $a : (($pb <= $pc) ? $b : $c);
                        $value = (ord($row[$x]) + $paeth) & 0xFF;
                        break;
                    default: $value = ord($row[$x]);
                }

                $row[$x] = chr($value);
            }

            for ($x = 0; $x < $stride; $x++) {
                $pixels[$y * $stride + $x] = $row[$x];
            }
            $prevRow = $row;
        }

        return [$width, $height, $pixels];
    }

    private function pixelAt(int $width, string $pixels, int $x, int $y): array
    {
        $offset = ($y * $width + $x) * 4;

        return [ord($pixels[$offset]), ord($pixels[$offset + 1]), ord($pixels[$offset + 2]), ord($pixels[$offset + 3])];
    }

    /** Every pixel is either fully transparent or exactly the neutral gray. */
    private function assertOnlyTransparentOrGray(int $width, int $height, string $pixels, string $context): void
    {
        $seen = [];
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                [$r, $g, $b, $a] = $this->pixelAt($width, $pixels, $x, $y);
                if ($a === 0) {
                    continue;
                }
                $seen[$r.','.$g.','.$b] = true;
                $this->assertTrue(
                    $r === self::GRAY_R && $g === self::GRAY_G && $b === self::GRAY_B,
                    "$context: every visible pixel must be exactly #9CA3AF (bad RGB ($r,$g,$b) at $x,$y)."
                );
            }
        }

        $this->assertSame(
            [self::GRAY_R.','.self::GRAY_G.','.self::GRAY_B => true],
            $seen,
            "$context: visible pixels must be exactly one color, #9CA3AF."
        );
    }

    /** Shared geometric checks: transparent corner + frame hole, opaque top bar. */
    private function assertIconShape(int $width, int $height, string $pixels, string $context): void
    {
        $cx = intdiv($width, 2);

        // Corner is outside the frame: fully transparent background.
        $this->assertSame(
            [self::GRAY_R, self::GRAY_G, self::GRAY_B, 0],
            $this->pixelAt($width, $pixels, 0, 0),
            "$context: background (0,0) must be fully transparent."
        );

        // Frame interior hole (below the divider): transparent.
        $this->assertSame(
            0,
            $this->pixelAt($width, $pixels, $cx, intdiv($height, 2))[3],
            "$context: the frame interior center must stay transparent (outline icon, no filled logo)."
        );

        // The divider bar at ~33% height: solid neutral gray.
        $bar = $this->pixelAt($width, $pixels, $cx, (int) round(0.33 * $height));
        $this->assertSame(
            [self::GRAY_R, self::GRAY_G, self::GRAY_B, 255],
            $bar,
            "$context: the top-bar divider must be solid #9CA3AF."
        );
    }

    // =====================================================================
    // favicon-16x16.png / favicon-32x32.png
    // =====================================================================

    public function test_png_16x16_matches_the_webpage_icon_design(): void
    {
        [$width, $height, $pixels] = $this->decodePng($this->bytes('favicon-16x16.png'));
        $this->assertSame(16, $width);
        $this->assertSame(16, $height);

        $this->assertOnlyTransparentOrGray($width, $height, $pixels, 'favicon-16x16.png');
        $this->assertIconShape($width, $height, $pixels, 'favicon-16x16.png');
    }

    public function test_png_32x32_matches_the_webpage_icon_design(): void
    {
        [$width, $height, $pixels] = $this->decodePng($this->bytes('favicon-32x32.png'));
        $this->assertSame(32, $width);
        $this->assertSame(32, $height);

        $this->assertOnlyTransparentOrGray($width, $height, $pixels, 'favicon-32x32.png');
        $this->assertIconShape($width, $height, $pixels, 'favicon-32x32.png');
    }

    // =====================================================================
    // favicon.ico
    // =====================================================================

    /** @return array<int, array{width: int, height: int, bytes: string}> */
    private function icoEntries(string $ico): array
    {
        $this->assertSame("\x00\x00\x01\x00", substr($ico, 0, 4), 'ICO header must be 00 00 01 00.');
        $count = unpack('v', substr($ico, 4, 2))[1];
        $this->assertSame(3, $count, 'The ICO must embed 3 sizes (16, 32, 48).');

        $entries = [];
        $offset = 6;
        for ($i = 0; $i < $count; $i++) {
            $dir = unpack(
                'Cwidth/Cheight/Ccolors/Creserved/vplanes/vbitcount/VbytesInRes/VimageOffset',
                substr($ico, $offset, 16)
            );
            $offset += 16;

            $width = $dir['width'] === 0 ? 256 : $dir['width'];
            $height = $dir['height'] === 0 ? 256 : $dir['height'];

            $entry = substr($ico, $dir['imageOffset'], $dir['bytesInRes']);
            $this->assertGreaterThanOrEqual(
                $dir['bytesInRes'],
                strlen(substr($ico, $dir['imageOffset'])),
                "Entry #$i must not run past the end of the ICO."
            );

            $entries[] = ['width' => $width, 'height' => $height, 'bytes' => $entry];
        }

        return $entries;
    }

    public function test_ico_embeds_png_entries_matching_the_design(): void
    {
        $entries = $this->icoEntries($this->bytes('favicon.ico'));
        $this->assertCount(3, $entries);

        $expectedSizes = [16, 32, 48];
        foreach ($entries as $index => $entry) {
            $this->assertSame($expectedSizes[$index], $entry['width'], "Entry #$index width.");
            $this->assertSame($expectedSizes[$index], $entry['height'], "Entry #$index height.");

            // Each entry is a complete standalone PNG.
            $this->assertSame(
                "\x89PNG\r\n\x1a\n",
                substr($entry['bytes'], 0, 8),
                "Entry #$index must be PNG-compressed."
            );

            [$width, $height, $pixels] = $this->decodePng($entry['bytes']);
            $this->assertSame($entry['width'], $width, "Entry #$index IHDR width must match its directory entry.");
            $this->assertSame($entry['height'], $height, "Entry #$index IHDR height must match its directory entry.");

            $this->assertOnlyTransparentOrGray($width, $height, $pixels, "ICO entry #$index");
            $this->assertIconShape($width, $height, $pixels, "ICO entry #$index");
        }
    }

    public function test_ico_16_and_32_entries_are_byte_identical_to_the_standalone_pngs(): void
    {
        $entries = $this->icoEntries($this->bytes('favicon.ico'));

        $this->assertSame($this->bytes('favicon-16x16.png'), $entries[0]['bytes']);
        $this->assertSame($this->bytes('favicon-32x32.png'), $entries[1]['bytes']);
    }

    // =====================================================================
    // Blade references point at these files with the current cache-buster
    // =====================================================================

    public function test_all_blade_favicon_links_reference_v3_and_the_four_files(): void
    {
        $views = [
            'layouts/app.blade.php',
            'welcome.blade.php',
            'errors/403.blade.php',
            'errors/404.blade.php',
        ];

        foreach ($views as $view) {
            $blade = file_get_contents(dirname(__DIR__, 2).'/resources/views/'.$view);

            foreach (['favicon.svg', 'favicon-32x32.png', 'favicon-16x16.png', 'favicon.ico'] as $file) {
                $this->assertStringContainsString(
                    '/'.$file.'?v=3',
                    $blade,
                    "$view must reference /$file?v=3 so browsers drop the cached old X logo."
                );
            }

            $this->assertDoesNotMatchRegularExpression(
                '/favicon[^"\']*\\?v=2/',
                $blade,
                "$view must not keep stale ?v=2 links."
            );
        }
    }
}

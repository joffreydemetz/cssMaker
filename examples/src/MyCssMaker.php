<?php

use JDZ\CssMaker\CssMaker;
use JDZ\FontManager\FontsDb;
use JDZ\Output\Output;

require_once __DIR__ . '/Glyphicons.php';
require_once __DIR__ . '/Flags.php';

class MyCssMaker extends CssMaker
{
    private ?FontsDb $fontsDb = null;

    public function __construct(?Output $output = null, string $nodejsBinPath = '')
    {
        parent::__construct($output, $nodejsBinPath);

        $this->variables->set($this->screenBreakpointName, '@sm-max-width');

        $this->output->setVerbosity(\JDZ\Output\Output::VERBOSITY_ALL);

        $fontsPath = realpath(__DIR__ . '/../fonts/') . '/';
        $formats = ['ttf', 'woff2', 'woff'];
        $googleFontsApiKey = null; // ENTER YOUR GOOGLE FONT API KEY HERE

        $this->output->info('Init Fonts Manager');
        $this->output->info('  Formats: ' . implode(', ', $formats));
        $this->output->info('  Fonts path: ' . $fontsPath);
        $this->output->info('  Google Fonts API Key: ' . ($googleFontsApiKey ?: 'None'));

        $this->localtargetFontPath = $fontsPath;

        $this->fontsDb = new FontsDb($fontsPath, $formats);
        $this->fontsDb->addProvider(new \JDZ\FontManager\Providers\MrandtlfProvider());
        if ($googleFontsApiKey) {
            $this->fontsDb->addProvider(new \JDZ\FontManager\Providers\GooglefontsProvider($googleFontsApiKey));
        }

        $this->output->info('  Load fonts database');
        $this->fontsDb->load(true);
        $this->output->info('  Fonts database loaded successfully');
    }

    public function addLocalFonts(array $fonts): self
    {
        $this->output->step('addLocalFonts() (' . count($fonts) . ')');

        if (!$fonts) {
            $this->output->warn('  No local fonts to load');
            return $this;
        }

        try {
            if (!$this->fontsDb) {
                throw new \Exception('Need fontsDb to load the local fonts');
            }

            foreach ($fonts as $font) {
                try {
                    $this->fontsDb->install($font);

                    if (false === ($fontData = $this->fontsDb->get($font))) {
                        throw new \Exception('Missing font data for ' . $font);
                    }

                    $this->addFont($fontData);
                } catch (\JDZ\FontManager\Exceptions\FontException $e) {
                    throw new \Exception('Font error: ' . $e->getFontError(), 0, $e);
                } catch (\Throwable $e) {
                    throw new \Exception('Font error: ' . $e->getMessage(), 0, $e);
                }
            }

            $this->output->step('addLocalFonts() OK');
        } catch (\Throwable $e) {
            $this->output->error('/!\ Local fonts error /!\ ' . $e->getMessage());
        }

        return $this;
    }

    public function addGlyphicons(array $icons): self
    {
        $this->output->step('addGlyphicons() (' . count($icons) . ')');

        if (!$icons) {
            $this->output->warn('  No glyphicons to load');
            return $this;
        }

        try {
            $glyphicons = new Glyphicons();
            $glyphicons->addSelection($icons);

            if (true === $glyphicons->isEmpty()) {
                $this->output->warn('  No glyphicons to load');
                return $this;
            }

            if (!$this->fontsDb) {
                $this->output->error('Need fontsDb to load the local glyphicons font');
                return $this;
            }

            $glyphiconsTmpPath = $this->tmpPath . \uniqid('ICONS_') . '.less';
            $this->dumpFile($glyphiconsTmpPath, $glyphicons->toFile(), true);
            $this->tmpFiles[] = $glyphiconsTmpPath;

            $this->addLessFile('icons', $glyphiconsTmpPath);

            foreach ($glyphicons->getFonts() as $font) {
                try {
                    if (false === ($fontData = $this->fontsDb->get('Glyphicons/' . $font))) {
                        throw new \Exception('Missing font data for Glyphicons/' . $font);
                    }
                    $this->addFont($fontData);
                } catch (\JDZ\FontManager\Exceptions\FontException $e) {
                    throw new \Exception('Font error: ' . $e->getFontError(), 0, $e);
                } catch (\Throwable $e) {
                    throw $e;
                }
            }

            $this->output->step('addGlyphicons() OK');
        } catch (\Throwable $e) {
            $this->output->error('/!\ Glyphicons error /!\ ' . $e->getMessage());
        }

        return $this;
    }

    public function addFlags(array $icons): self
    {
        $this->output->step('addFlags() (' . count($icons) . ')');

        if (!$icons) {
            $this->output->warn('  No flags to load');
            return $this;
        }

        try {
            $flags = new Flags();
            $flags->addSelection($icons);

            if (true === $flags->isEmpty()) {
                $this->output->warn('  No flags to load');
                return $this;
            }

            $flagsTmpPath = $this->tmpPath . \uniqid('ICONS_') . '.less';
            $this->dumpFile($flagsTmpPath, $flags->toFile(), true);
            $this->tmpFiles[] = $flagsTmpPath;
            $this->addLessFile('icons', $flagsTmpPath);

            $this->output->step('addFlags() OK');
        } catch (\Throwable $e) {
            $this->output->error('/!\ Flags error /!\ ' . $e->getMessage());
        }

        return $this;
    }
}

<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\CssMaker;

use JDZ\CssMaker\Font;
use JDZ\CssMaker\Variables;
use JDZ\CssMaker\Merger;
use JDZ\CssMaker\Cleaner;
use JDZ\CssMaker\Exception\LessMakerException;
use JDZ\CssMaker\FontsDbInterface;
use JDZ\CssMaker\Output;

/**
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 * 
 * - add variables to buffer
 * - add mixins to buffer
 * - add files to buffer
 * - lessc to css
 * - autoprefixer 
 * */
class CssMaker
{
  protected ?string $basePath = null;
  protected ?string $tmpPath = null;
  protected ?string $targetCssPath = null;
  protected ?string $targetFontPath = null;
  protected ?string $dumpOutputPath = null;
  protected ?string $localtargetFontPath = null;

  protected Output $output;
  protected Variables $variables;

  protected array $fonts = [];
  protected array $tmpFiles = [];

  private array $files = [
    'variables' => [],
    'mixins' => [],
    'normalize' => [],
    'animations' => [],
    'fonts' => [],
    'structure' => [],
    'icons' => [],
    'mobile' => [],
    'screen' => [],
    'queries' => [],
    'print' => [],
  ];

  public function __construct()
  {
    $this->output = new Output();
    $this->variables = new Variables();
  }

  public function setBuildPaths(string $basePath, string $target = 'build'): self
  {
    $this->setBasePath($basePath . '/');
    $this->setTmpPath($basePath . '/tmp/');
    $this->setTargetCssPath($basePath . '/' . $target . '/css/');
    $this->setTargetFontPath($basePath . '/' . $target . '/fonts/');
    $this->setDumpOutputPath($basePath . '/' . $target . '/dump.txt');
    return $this;
  }

  public function setBasePath(string $basePath): self
  {
    if (!is_dir($basePath)) {
      throw new LessMakerException('Base directory does not exist: ' . $basePath);
    }

    $this->basePath = $basePath;

    return $this;
  }

  public function setTmpPath(string $tmpPath): self
  {
    if (!is_dir($tmpPath)) {
      throw new LessMakerException('Temporary path does not exist: ' . $tmpPath);
    }

    $this->tmpPath = $tmpPath;

    return $this;
  }

  public function setTargetCssPath(string $targetCssPath): self
  {
    if (!is_dir($targetCssPath)) {
      throw new LessMakerException('Target path does not exist: ' . $targetCssPath);
    }

    $this->targetCssPath = $targetCssPath;

    return $this;
  }

  public function setTargetFontPath(string $targetFontPath): self
  {
    if (!is_dir($targetFontPath)) {
      throw new LessMakerException('Fonts path does not exist: ' . $targetFontPath);
    }

    $this->targetFontPath = $targetFontPath;

    return $this;
  }

  public function setDumpOutputPath(string $dumpOutputPath): self
  {
    $this->dumpOutputPath = $dumpOutputPath;
    return $this;
  }

  public function addTmpFile(string $file): self
  {
    $this->tmpFiles[] = $file;
    return $this;
  }


  public function addLessFiles(array $paths): self
  {
    $total = 0;
    foreach ($paths as $type => $typePaths) {
      $total += count($typePaths);
    }
    $this->output->step('addLessFiles() (' . $total . ')');

    foreach (array_keys($this->files) as $fileType) {
      if (empty($paths[$fileType])) {
        continue;
      }

      if ('variables' === $fileType) {
        foreach ($paths[$fileType] as $path) {
          $this->variables->addFromFile($path);
        }
        continue;
      }

      foreach ($paths[$fileType] as $path) {
        $this->addLessFile($fileType, $path);
      }
    }

    return $this;
  }

  public function addLessFile(string $type, string $path): void
  {
    $fi = new \SplFileInfo($path);

    if ('_' === substr($fi->getFilename(), 0, 1)) {
      return;
    }

    if (!\file_exists($path)) {
      return;
    }

    $this->files[$type][] = $path;

    $this->output->info('  ' . $this->shortenPath($path));
  }


  public function process(string $theme): self
  {
    if (
      null === $this->basePath || !is_dir($this->basePath) ||
      null === $this->tmpPath || !is_dir($this->tmpPath) ||
      null === $this->targetCssPath || !is_dir($this->targetCssPath) ||
      null === $this->targetFontPath || !is_dir($this->targetFontPath)
    ) {
      throw new LessMakerException('Base, tmp, target CSS or target font paths are not set');
    }

    $lessFilePath = $this->targetCssPath . $theme . '.less';
    $cssFilePath = $this->targetCssPath . $theme . '.css';
    $minFilePath = $this->targetCssPath . $theme . '.min.css';

    $this->toLess($lessFilePath);
    $this->toCss($lessFilePath, $cssFilePath);
    $this->cleanCss($cssFilePath);
    $this->postcss($cssFilePath);
    $this->minify($cssFilePath, $minFilePath);

    $this->clean();

    return $this;
  }

  public function clean(): self
  {
    if ($this->tmpFiles) {
      $this->output->step('cleanUp()');
      foreach ($this->tmpFiles as $file) {
        if ($file) {
          $this->removeFile($file);
        }
      }
      $this->output->step('OK cleanUp()');
    }

    return $this;
  }

  protected function toLess(string $lessFilePath): void
  {
    $this->output->step('toLess()');

    $variablesTmpFile = $this->tmpPath . \uniqid('VARIABLES_') . '.less';
    $this->dumpFile($variablesTmpFile, $this->variables->toFile());
    $this->tmpFiles[] = $variablesTmpFile;
    $this->addLessFile('variables', $variablesTmpFile);

    $fontFacesCss = '';
    if ($this->fonts) {
      foreach ($this->fonts as $font) {
        $fontFacesCss .= $font->getFontFaceCss() . "\n\n";

        if ($fontThemeFile = $font->getTheme()) {
          $this->addLessFile('fonts', $fontThemeFile);
        }
      }
    }

    $merger = new Merger();

    $merger->setVariables($this->variables->all());
    $merger->setMixins($this->files['mixins']);

    $merger->setFiles($this->files['normalize']);
    $merger->setFiles($this->files['animations']);
    $merger->setFiles($this->files['fonts']);

    $this->buildLess($merger, ['structure', 'icons']);
    $this->buildLess($merger, ['mobile']);
    $this->buildLess($merger, ['screen']);
    $this->buildLess($merger, ['queries']);
    $this->buildLess($merger, ['print']);

    $less = $merger->getContent();
    if ($fontFacesCss) {
      $less = $fontFacesCss . "\n\n" . $less;
    }

    $this->dumpFile($lessFilePath, $less, true);
    $this->output->step('OK toLess()');
  }

  protected function toCss(string $lessFilePath, string $cssFilePath): void
  {
    $this->output->step('toCss()');

    try {
      $process = new \Symfony\Component\Process\Process([
        'lessc',
        $lessFilePath,
        $cssFilePath,
      ]);

      if (0 !== $process->run()) {
        throw new \Symfony\Component\Process\Exception\ProcessFailedException($process);
      }

      $this->output->step('OK toCss()');
    } catch (\Throwable $e) {
      $this->output->step('KO toCss()');
      $this->output->error('toCss() : ' . $e->getMessage());
      throw $e;
    }
  }

  protected function cleanCss(string $cssFilePath): void
  {
    $this->output->step('cleanCss()');

    try {
      $cleaner = new Cleaner(file_get_contents($cssFilePath));
      $cleaner
        ->removeSpaces()
        ->removeComments();

      $this->dumpFile($cssFilePath, $cleaner->getCss());
      $this->output->step('OK cleanCss()');
    } catch (\Throwable $e) {
      $this->output->step('KO cleanCss()');
      $this->output->error('cleanCss() : ' . $e->getMessage());
      throw $e;
    }
  }

  protected function postcss(string $cssFilePath): void
  {
    $this->output->step('postcss()');

    try {
      $process = new \Symfony\Component\Process\Process([
        'postcss',
        '--replace',
        '--verbose',
        '--config',
        '--no-map',
        'postcss.json',
        $cssFilePath,
      ]);

      if (0 !== $process->run()) {
        throw new \Symfony\Component\Process\Exception\ProcessFailedException($process);
      }

      $this->output->step('OK postcss()');
    } catch (\Throwable $e) {
      $this->output->step('KO postcss()');
      $this->output->error('postcss() : ' . $e->getMessage());
      throw $e;
    }
  }

  protected function minify(string $cssFilePath, string $minFilePath): void
  {
    $this->output->step('minify()');

    try {

      $process = new \Symfony\Component\Process\Process([
        'css-minify',
        '-f',
        //$cssFilePath,
        str_replace(getcwd(), '', $cssFilePath),
        '-o',
        dirname(str_replace(getcwd(), '', $minFilePath)),
      ]);

      if (0 !== $process->run()) {
        throw new \Symfony\Component\Process\Exception\ProcessFailedException($process);
      }

      $this->output->step('OK minify()');
    } catch (\Throwable $e) {
      $this->output->step('KO minify()');
      $this->output->error('minify() : ' . $e->getMessage());
      throw $e;
    }
  }

  public function dumpOutput(): void
  {
    try {
      file_put_contents($this->dumpOutputPath, $this->output->__toString());
      chmod($this->dumpOutputPath, 0777);
    } catch (\Throwable $e) {
      throw new \RuntimeException('Error dumping output: ' . $e->getMessage());
    }
  }


  private function buildLess(Merger $merger, array $types): void
  {
    $this->output->info('Add LESS for types: ' . implode(', ', $types));

    $types = array_unique($types);

    foreach ($this->files as $type => $files) {
      if (empty($files)) {
        continue;
      }

      if (!in_array($type, $types)) {
        continue;
      }

      if ('mobile' === $type) {
        $merger->addString('@media(max-width: @sm-max-width - 1px){ ');
        $mediaQ = true;
      } elseif ('screen' === $type) {
        $merger->addString('@media(min-width: @sm-max-width){ ');
        $mediaQ = true;
      } elseif ('print' === $type) {
        $merger->addString('@media print { ');
        $mediaQ = true;
      } else {
        $mediaQ = false;
      }

      foreach ($files as $path) {
        $this->output->dump(' - ' . str_pad(strtoupper($type), 10, ' ', STR_PAD_RIGHT) . ' ' . $this->shortenPath($path));
        $merger->addFile($path);
      }

      if ($mediaQ) {
        $merger->addString('}');
      }
    }
  }

  public function addFont(object $font): void
  {
    if (isset($this->fonts[$font->id])) {
      return;
    }

    $this->output->dump('Adding font: ' . $font->family);

    try {
      $font = new Font([
        'type' => 'local',
        'id' => $font->id,
        'family' => $font->family,
        'display' => $font->display,
        'style' => $font->style,
        'weight' => $font->weight,
        'files' => $font->files,
      ]);

      $font->load();

      foreach ($font->getFormatFiles() as $file) {
        try {
          $this->copyFile($file->source, $this->targetFontPath . $file->filename);
        } catch (\Throwable $e) {
          $this->output->error($e->getMessage());
        }
      }

      $this->fonts[$font->id] = $font;
    } catch (\Throwable $e) {
      $this->output->error($e->getMessage());
    }
  }

  public function copyFile(string $source, string $target): void
  {
    $this->output->dump('Copy file');
    $this->output->dump('  Source: ' . $this->shortenPath($source));
    $this->output->dump('  Target: ' . $this->shortenPath($target));

    if (!\file_exists($source)) {
      $this->output->dump('  Source file does not exist');
      return;
    }

    try {
      copy($source, $target);
      $this->output->dump('  OK');
    } catch (\Throwable $e) {
      $this->output->dump('  KO');

      $this->output->error('Error copying ' . $this->shortenPath($source) . ' to ' . $this->shortenPath($target));
      $this->output->error($e->getMessage());
    }
  }

  public function removeFile(string $path): void
  {
    if ($path && \file_exists($path) && is_file($path)) {
      $this->output->dump('Remove file:');
      $this->output->dump('  File: ' . $this->shortenPath($path));
      try {
        unlink($path);
        $this->output->dump('  OK');
      } catch (\Throwable $e) {
        $this->output->dump('  KO');

        $this->output->error('Error removing file ' . $this->shortenPath($path));
        $this->output->error($e->getMessage());
      }
    }
  }

  public function dumpFile(string $path, string $content, bool $showSize = true): void
  {
    $this->output->dump('Dumping content to file');
    $this->output->dump('  File: ' . $this->shortenPath($path));

    try {
      file_put_contents($path, $content);
      chmod($path, 0777);
      $this->output->dump('  OK');
      if (true === $showSize) {
        $this->output->dump('  Size: ' . (\filesize($path) / 1000) . ' Ko');
      }
    } catch (\Throwable $e) {
      $this->output->dump('  KO');

      $this->output->error('Error dumping content to ' . $this->shortenPath($path));
      $this->output->error($e->getMessage());
    }
  }


  protected function shortenPath(string $path): string
  {
    $path = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $path);

    if ($this->tmpPath && strpos($path, $this->tmpPath) === 0) {
      $path = substr($path, strlen($this->tmpPath));
      $path = str_replace(DIRECTORY_SEPARATOR, '/', $path);
      return '@TMP/' . $path;
    }

    if ($this->localtargetFontPath && strpos($path, $this->localtargetFontPath) === 0) {
      $path = substr($path, strlen($this->localtargetFontPath));
      $path = str_replace(DIRECTORY_SEPARATOR, '/', $path);
      return '@FONTS/' . $path;
    }

    if ($this->basePath && strpos($path, $this->basePath) === 0) {
      $path = substr($path, strlen($this->basePath));
      $path = str_replace(DIRECTORY_SEPARATOR, '/', $path);
      return '@BASE/' . $path;
    }

    return $path;
  }
}

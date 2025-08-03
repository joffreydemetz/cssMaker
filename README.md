# CssMaker

A PHP library for building CSS files dynamically from LESS sources with advanced preprocessing, autoprefixing, and optimization features.

## Features

- 🎨 **LESS Compilation**: Process LESS files with variables and mixins
- 🚀 **Autoprefixing**: Automatic vendor prefix addition via PostCSS
- 🧹 **CSS Optimization**: Comment removal, whitespace cleaning, and minification
- 📁 **File Organization**: Structured approach to CSS building with categories
- 🔧 **Font Management**: Integration with font loading and management
- ⚡ **Performance**: Efficient temporary file handling and caching
- 🧪 **Fully Tested**: Comprehensive test suite with 100+ tests

## Installation

```bash
composer require jdz/cssmaker
```

### Dependencies

This package requires Node.js tools for CSS processing:

```bash
composer run npm
```

Or manually install:

```bash
npm install -g less less-plugin-clean-css less-plugin-autoprefix
npm install -g postcss postcss-cli postcss-autoprefixer postcss-less-parser postcss-safe-parser postcss-discard-comments
npm install -g css-minify
```

## Configuration Files

### PostCSS Configuration (`postcss.json`)

Configure PostCSS plugins and autoprefixer settings:

```json
{
    "use": [
        "autoprefixer",
        "postcss-discard-comments",
        "postcss-safe-parser"
    ],
    "remove": true,
    "add": true,
    "autoprefixer": {
        "browsers": "> .5%, not dead"
    }
}
```

**Configuration Options:**
- `use`: Array of PostCSS plugins to apply
- `remove`: Remove existing vendor prefixes before adding new ones
- `add`: Add vendor prefixes based on browser support
- `autoprefixer.browsers`: Browser support criteria

### Browserslist Configuration (`.browserslistrc`)

Define target browsers for autoprefixing:

```
> .5%
not dead
```

**Common Browserslist Queries:**
- `> .5%`: Browsers with more than 0.5% market share
- `not dead`: Exclude browsers without security updates
- `last 2 versions`: Last 2 versions of each browser
- `ie >= 11`: Internet Explorer 11 and newer

## Basic Usage

```php
<?php

use JDZ\CssMaker\CssMaker;

// Initialize CssMaker
$cssMaker = new CssMaker();

// Set paths
$cssMaker
    ->setBasePath('/path/to/project')
    ->setTmpPath('/tmp/cssmaker')
    ->setTargetCssPath('/public/css')
    ->setTargetFontPath('/public/fonts');

// Add LESS files by category
$cssMaker
    ->addLessFiles([
        'variables' => ['variables.less'],
        'mixins' => ['mixins.less'],
        'normalize' => ['normalize.less'],
        'structure' => ['layout.less', 'components.less']
    ]);

// Process and build CSS
$result = $cssMaker->process();
```

## File Categories

CssMaker organizes LESS files into logical categories:

- **variables**: LESS variables and configuration
- **mixins**: Reusable LESS mixins and functions
- **normalize**: CSS reset and normalization
- **animations**: CSS animations and transitions
- **fonts**: Font declarations and loading
- **structure**: Layout, components, and main styles

## Advanced Features

### Variable Management

```php
use JDZ\CssMaker\Variables;

$variables = new Variables();
$variables->addFromFile('config/variables.yml');

// Variables are automatically merged into LESS compilation
```

### Font Integration

```php
// Font management (requires jdz/fontmanager)
$cssMaker->setFonts($fontArray);
```

### Output Control

```php
use JDZ\CssMaker\Output;

$output = new Output();
$cssMaker->setOutput($output);

// Access build messages and warnings
echo $output->toString();
```

### CSS Cleaning and Optimization

```php
use JDZ\CssMaker\Cleaner;

$cleaner = new Cleaner($cssContent);
$cleanedCss = $cleaner
    ->removeComments()
    ->removeSpaces()
    ->getCss();
```

## Development

### Running Tests

See [TESTS.md](TESTS.md) for test suite information.

```bash
composer test
```

Or with PHPUnit directly:

```bash
vendor/bin/phpunit
```

### Example Usage

See the complete example in `examples/example.php`:

```bash
composer run example
```

## Architecture

### Core Classes

- **CssMaker**: Main class for CSS building workflow
- **Variables**: YAML-based variable management
- **Merger**: Content merging and compilation
- **Cleaner**: CSS optimization and cleaning
- **Output**: Build process logging and messaging

### Processing Pipeline

1. **Preparation**: Load variables, mixins, and LESS files
2. **Merging**: Combine all sources into temporary files
3. **Compilation**: Process LESS to CSS using Node.js tools
4. **Post-processing**: Apply PostCSS autoprefixing and optimization
5. **Cleaning**: Remove comments, optimize whitespace
6. **Output**: Write final CSS files

## Browser Support

Default browser support targets:

- Chrome (last 2 versions)
- Firefox (last 2 versions)
- Safari (last 2 versions)
- Edge (last 2 versions)
- Browsers with > 0.5% market share
- Exclude dead browsers

Customize support in `.browserslistrc` file.

## Requirements

- **PHP**: >= 8.1
- **Node.js**: For LESS compilation and PostCSS processing
- **Composer**: For dependency management

## License

MIT License - see [LICENSE](LICENSE) file for details.

## Contributing

1. Fork the repository
2. Create a feature branch
3. Add tests for new functionality
4. Ensure all tests pass
5. Submit a pull request

